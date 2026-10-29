<?php

class Mailer
{
    public static int $failures = 0;
    public static ?string $lastError = null;

    public static function mode(): string
    {
        return strtolower((string) env('MAIL_DRIVER', 'log'));
    }

    public static function modo(): string
    {
        return self::mode();
    }

    public static function recent(int $n = 10): array
    {
        return Database::all('SELECT * FROM emails_log ORDER BY id DESC LIMIT ' . (int) $n);
    }

    public static function recentes(int $n = 10): array
    {
        return self::recent($n);
    }

    public static function send(string $para, string $assunto, string $html): bool
    {
        $driver = strtolower((string) env('MAIL_DRIVER', 'log'));
        $from = (string) env('MAIL_FROM', 'vagas@localhost');
        $fromName = (string) env('MAIL_FROM_NAME', 'Vagas de Estágio - IFSP Guarulhos');
        $error = null;
        $success = false;
        try {
            if ($driver === 'smtp') {
                $success = self::smtp($para, $assunto, $html, $from, $fromName);
            } elseif ($driver === 'mail') {
                $h = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: " . self::nome($fromName) . " <$from>\r\n" . self::replyTo();
                $success = @mail($para, self::assunto($assunto), $html, $h);
                if (!$success) {
                    $error = 'mail() falhou: este servidor não tem envio local configurado. Use MAIL_DRIVER=smtp.';
                }
            } else {
                if (!is_dir(ROOT_PATH . '/storage/mail')) {
                    mkdir(ROOT_PATH . '/storage/mail', 0755, true);
                }
                $arq = ROOT_PATH . '/storage/mail/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.html';
                file_put_contents($arq, "<!-- Para: $para | Assunto: $assunto -->\n$html");
                $success = true;
            }
        } catch (Throwable $t) {
            $error = $t->getMessage();
        }
        if (!$success) {
            self::$failures++;
            self::$lastError = $error;
        }
        try {
            Database::insert('emails_log', [
                'destinatario' => $para,
                'assunto' => $assunto,
                'status' => $success ? ($driver === 'log' ? 'simulado' : 'enviado') : 'falha',
                'erro' => $error ? mb_substr($error, 0, 250) : null,
                'criado_em' => Database::now()
            ]);
        } catch (Throwable $t) {
        }
        return $success;
    }

    public static function enviar(string $para, string $assunto, string $html): bool
    {
        return self::send($para, $assunto, $html);
    }

    public static function layout(string $titulo, string $corpoHtml, ?array $botao = null): string
    {
        $button = $botao ? '<p style="margin:22px 0"><a href="' . e($botao[1]) . '" style="background:#2f9e41;color:#fff;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:8px;display:inline-block">' . e($botao[0]) . '</a></p>' : '';
        return '<!DOCTYPE html><html lang="pt-br"><body style="margin:0;background:#f3f6f3;font-family:Arial,Helvetica,sans-serif;color:#222">'
            . '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">'
            . '<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #dfe7e1">'
            . '<tr><td style="background:#1f6f2d;color:#fff;padding:18px 24px;font-size:13px;letter-spacing:.04em">IFSP CAMPUS GUARULHOS · VAGAS</td></tr>'
            . '<tr><td style="padding:24px"><h1 style="font-size:20px;margin:0 0 14px;color:#1f6f2d">' . e($titulo) . '</h1>'
            . '<div style="font-size:15px;line-height:1.55">' . $corpoHtml . '</div>' . $button . '</td></tr>'
            . '<tr><td style="padding:14px 24px;background:#f6f8f6;font-size:12px;color:#5d6b62">Mensagem automática do sistema de vagas. Não responda a este e-mail.</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

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

    private static function smtp(string $para, string $assunto, string $html, string $from, string $fromName): bool
    {
        $host = (string) env('SMTP_HOST');
        $port = (int) env('SMTP_PORT', 587);
        $security = strtolower((string) env('SMTP_SECURE', 'tls'));
        if ($host === '') {
            throw new RuntimeException('SMTP_HOST não está configurado no .env');
        }
        $verifyTls = !in_array(strtolower((string) env('SMTP_VERIFY_TLS', '1')), ['0', 'false', 'no'], true);
        $context = stream_context_create(['ssl' => ['verify_peer' => $verifyTls, 'verify_peer_name' => $verifyTls, 'allow_self_signed' => !$verifyTls]]);
        $socket = @stream_socket_client(($security === 'ssl' ? 'ssl://' : 'tcp://') . "$host:$port", $en, $es, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            throw new RuntimeException("SMTP: não conectou em $host:$port ($es). Confira SMTP_HOST, SMTP_PORT e SMTP_SECURE.");
        }
        stream_set_timeout($socket, 15);
        $read = function () use ($socket) {
            $r = '';
            while (($l = fgets($socket, 515)) !== false) {
                $r .= $l;
                if (isset($l[3]) && $l[3] === ' ') {
                    break;
                }
            }
            return $r;
        };
        $command = function (string $c, string $esp) use ($socket, $read) {
            fwrite($socket, $c . "\r\n");
            $r = $read();
            if (strpos($r, $esp) !== 0) {
                throw new RuntimeException('SMTP: ' . trim($r));
            }
        };
        if (strpos($read(), '220') !== 0) {
            throw new RuntimeException('SMTP: servidor não respondeu');
        }
        $ehlo = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
        $command("EHLO $ehlo", '250');
        if ($security === 'tls') {
            $command('STARTTLS', '220');
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('SMTP: TLS falhou');
            }
            $command("EHLO $ehlo", '250');
        }
        if (env('SMTP_USER')) {
            $command('AUTH LOGIN', '334');
            $command(base64_encode(env('SMTP_USER')), '334');
            $command(base64_encode((string) env('SMTP_PASS')), '235');
        }
        $command("MAIL FROM:<$from>", '250');
        $command("RCPT TO:<$para>", '25');
        $command('DATA', '354');
        $message = self::replyTo() . "Date: " . date('r') . "\r\nMessage-ID: <" . bin2hex(random_bytes(8)) . '@' . $ehlo . ">\r\nFrom: " . self::nome($fromName) . " <$from>\r\nTo: <$para>\r\nSubject: " . self::assunto($assunto)
            . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html));
        fwrite($socket, $message . "\r\n.\r\n");
        $r = $read();
        if (strpos($r, '250') !== 0) {
            throw new RuntimeException('SMTP: ' . trim($r));
        }
        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        return true;
    }
}
