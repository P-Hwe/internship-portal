<?php

/**
 * Envio de e-mail. MAIL_DRIVER no .env:
 *  log  -> não envia; grava em storage/mail/*.html (desenvolvimento)
 *  mail -> função mail() do PHP (funciona na maioria dos cPanel)
 *  smtp -> SMTP próprio (SMTP_HOST/PORT/USER/PASS/SECURE=ssl|tls|none)
 * Toda tentativa fica registrada na tabela emails_log. Falha de e-mail nunca derruba a ação do usuário.
 */
class Mailer
{
    public static int $falhas = 0;          // quantos envios falharam nesta requisição
    public static ?string $ultimoErro = null;

    public static function modo(): string
    {
        return strtolower((string) env('MAIL_DRIVER', 'log'));
    }

    public static function recentes(int $n = 10): array
    {
        return Database::all('SELECT * FROM emails_log ORDER BY id DESC LIMIT ' . (int) $n);
    }

    public static function enviar(string $para, string $assunto, string $html): bool
    {
        $driver = strtolower((string) env('MAIL_DRIVER', 'log'));
        $de = (string) env('MAIL_FROM', 'vagas@localhost');
        $nomeDe = (string) env('MAIL_FROM_NAME', 'Vagas de Estágio - IFSP Guarulhos');
        $erro = null;
        $ok = false;
        try {
            if ($driver === 'smtp') {
                $ok = self::smtp($para, $assunto, $html, $de, $nomeDe);
            } elseif ($driver === 'mail') {
                $h = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: " . self::nome($nomeDe) . " <$de>\r\n" . self::replyTo();
                $ok = @mail($para, self::assunto($assunto), $html, $h);
                if (!$ok) {
                    $erro = 'mail() falhou: este servidor não tem envio local configurado. Use MAIL_DRIVER=smtp.';
                }
            } else {
                if (!is_dir(ROOT_PATH . '/storage/mail')) {
                    mkdir(ROOT_PATH . '/storage/mail', 0755, true);
                }
                $arq = ROOT_PATH . '/storage/mail/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.html';
                file_put_contents($arq, "<!-- Para: $para | Assunto: $assunto -->\n$html");
                $ok = true;
            }
        } catch (Throwable $t) {
            $erro = $t->getMessage();
        }
        if (!$ok) {
            self::$falhas++;
            self::$ultimoErro = $erro;
        }
        try {
            Database::insert('emails_log', ['destinatario' => $para, 'assunto' => $assunto,
                'status' => $ok ? ($driver === 'log' ? 'simulado' : 'enviado') : 'falha', 'erro' => $erro ? mb_substr($erro, 0, 250) : null, 'criado_em' => Database::agora()]);
        } catch (Throwable $t) {
/* log é best-effort */
        }
        return $ok;
    }

    /** Layout padrão dos e-mails (verde do IF). $botao = [texto, url] */
    public static function layout(string $titulo, string $corpoHtml, ?array $botao = null): string
    {
        $b = $botao ? '<p style="margin:22px 0"><a href="' . e($botao[1]) . '" style="background:#2f9e41;color:#fff;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:8px;display:inline-block">' . e($botao[0]) . '</a></p>' : '';
        return '<!DOCTYPE html><html lang="pt-br"><body style="margin:0;background:#f3f6f3;font-family:Arial,Helvetica,sans-serif;color:#222">'
            . '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">'
            . '<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #dfe7e1">'
            . '<tr><td style="background:#1f6f2d;color:#fff;padding:18px 24px;font-size:13px;letter-spacing:.04em">IFSP CAMPUS GUARULHOS · VAGAS</td></tr>'
            . '<tr><td style="padding:24px"><h1 style="font-size:20px;margin:0 0 14px;color:#1f6f2d">' . e($titulo) . '</h1>'
            . '<div style="font-size:15px;line-height:1.55">' . $corpoHtml . '</div>' . $b . '</td></tr>'
            . '<tr><td style="padding:14px 24px;background:#f6f8f6;font-size:12px;color:#5d6b62">Mensagem automática do sistema de vagas. Não responda a este e-mail.</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

    /** Cabeçalho Reply-To (opcional): MAIL_REPLY_TO=coordenacao.gru@ifsp.edu.br */
    private static function replyTo(): string
    {
        $r = trim((string) env('MAIL_REPLY_TO', ''));
        return filter_var($r, FILTER_VALIDATE_EMAIL) ? "Reply-To: <$r>\r\n" : '';
    }

    private static function nome(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }
    private static function assunto(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }

    private static function smtp(string $para, string $assunto, string $html, string $de, string $nomeDe): bool
    {
        $host = (string) env('SMTP_HOST');
        $porta = (int) env('SMTP_PORT', 587);
        $sec = strtolower((string) env('SMTP_SECURE', 'tls'));
        if ($host === '') {
            throw new RuntimeException('SMTP_HOST não está configurado no .env');
        }
        // SMTP_VERIFY_TLS=0 desliga a checagem do certificado (só para teste local no Windows/XAMPP sem bundle de CAs)
        $verifica = !in_array(strtolower((string) env('SMTP_VERIFY_TLS', '1')), ['0', 'false', 'no'], true);
        $ctx = stream_context_create(['ssl' => ['verify_peer' => $verifica, 'verify_peer_name' => $verifica, 'allow_self_signed' => !$verifica]]);
        $s = @stream_socket_client(($sec === 'ssl' ? 'ssl://' : 'tcp://') . "$host:$porta", $en, $es, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$s) {
            throw new RuntimeException("SMTP: não conectou em $host:$porta ($es). Confira SMTP_HOST, SMTP_PORT e SMTP_SECURE.");
        }
        stream_set_timeout($s, 15);
        $ler = function () use ($s) {
            $r = '';
            while (($l = fgets($s, 515)) !== false) {
                $r .= $l;
                if (isset($l[3]) && $l[3] === ' ') {
                    break;
                }
            } return $r;
        };
        $cmd = function (string $c, string $esp) use ($s, $ler) {
            fwrite($s, $c . "\r\n");
            $r = $ler();
            if (strpos($r, $esp) !== 0) {
                throw new RuntimeException('SMTP: ' . trim($r));
            }
        };
        if (strpos($ler(), '220') !== 0) {
            throw new RuntimeException('SMTP: servidor não respondeu');
        }
        $ehlo = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
        $cmd("EHLO $ehlo", '250');
        if ($sec === 'tls') {
            $cmd('STARTTLS', '220');
            if (!stream_socket_enable_crypto($s, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('SMTP: TLS falhou');
            }
            $cmd("EHLO $ehlo", '250');
        }
        if (env('SMTP_USER')) {
            $cmd('AUTH LOGIN', '334');
            $cmd(base64_encode(env('SMTP_USER')), '334');
            $cmd(base64_encode((string) env('SMTP_PASS')), '235');
        }
        $cmd("MAIL FROM:<$de>", '250');
        $cmd("RCPT TO:<$para>", '25');
        $cmd('DATA', '354');
        $msg = self::replyTo() . "Date: " . date('r') . "\r\nMessage-ID: <" . bin2hex(random_bytes(8)) . '@' . $ehlo . ">\r\nFrom: " . self::nome($nomeDe) . " <$de>\r\nTo: <$para>\r\nSubject: " . self::assunto($assunto)
            . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html));
        fwrite($s, $msg . "\r\n.\r\n");
        $r = $ler();
        if (strpos($r, '250') !== 0) {
            throw new RuntimeException('SMTP: ' . trim($r));
        }
        fwrite($s, "QUIT\r\n");
        fclose($s);
        return true;
    }
}
