<?php

class Visitas
{
    private const COOKIE = 'vagas_vid';
    public const PAGINAS = ['home' => 'Vagas (página inicial)', 'vaga' => 'Detalhe de uma vaga', 'empresas' => 'Empresas parceiras', 'empresa' => 'Página de uma empresa'];
    public const PERIODOS = ['semana' => ['Semana', 'últimos 7 dias', '7 dias anteriores'], 'mes' => ['Mês', 'últimos 30 dias', '30 dias anteriores'], 'ano' => ['Ano', 'últimos 12 meses', '12 meses anteriores']];

    public static function janelaMin(): int
    {
        $v = env('VISITAS_JANELA_MIN');
        return max(0, ($v !== null && $v !== '') ? (int) $v : (app_env() === 'prod' ? 30 : 1));
    }
    public static function ignorarLogados(): bool
    {
        $v = env('VISITAS_IGNORAR_LOGADOS');
        return ($v !== null && $v !== '') ? $v !== '0' : app_env() === 'prod';
    }
    private static function ehRobo(): bool
    {
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return $ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|headless|python|curl\/|wget|java\//i', $ua) === 1;
    }

    public static function registrar(string $tipo, ?int $ref = null): void
    {
        try {
            if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || self::ehRobo() || (self::ignorarLogados() && Auth::perfil())) return;
            $v = (string) ($_COOKIE[self::COOKIE] ?? '');
            if (!preg_match('/^[a-f0-9]{24}$/', $v)) {
                $v = bin2hex(random_bytes(12));
                $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
                if (!headers_sent()) setcookie(self::COOKIE, $v, ['expires' => time() + 365 * 86400, 'path' => BASE_URL, 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
            }
            $desde = date('Y-m-d H:i:s', time() - self::janelaMin() * 60);
            if (self::janelaMin() > 0 && Database::val('SELECT 1 FROM visitas WHERE visitante = :v AND tipo = :t AND ref_id <=> :r AND criado_em >= :d LIMIT 1', ['v' => $v, 't' => $tipo, 'r' => $ref, 'd' => $desde])) return;
            Database::insert('visitas', ['tipo' => $tipo, 'ref_id' => $ref, 'visitante' => $v, 'criado_em' => Database::agora()]);
        } catch (Throwable $t) {   // contar visita nunca derruba a página, mas o motivo fica no log (ex.: tabela visitas ausente: rode database/migrate.php)
            @file_put_contents(ROOT_PATH . '/storage/erro.log', date('c') . ' Visitas::registrar falhou: ' . $t->getMessage() . "\n\n", FILE_APPEND);
        }
    }

}
