<?php

define('ROOT_PATH', __DIR__);
(function () {
    $f = ROOT_PATH . '/.env';
    if (!is_file($f)) return;
    foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        if ($l[0] === '#' || strpos($l, '=') === false) continue;
        [$k, $v] = explode('=', $l, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
})();

function env(string $k, $padrao = null)
{
    $v = getenv($k);
    if ($v !== false && $v !== '') return $v;
    return $_ENV[$k] ?? $padrao;
}

$dir = PHP_SAPI === 'cli' ? rtrim((string) env('APP_BASE_PATH', ''), '/') : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
define('BASE_URL', $dir . '/');

// Regras de negócio 
define('CARROSSEL_MAX', 5);       // vagas no banner grande de destaques
define('DIAS_VAGA_NOVA', 7);      // selo "Nova"
define('DIAS_VAGA_VENCENDO', 7);  // selo "Encerra em X dias"
define('AREA_MIN_VAGAS', 3);      // área criada por empresa só vira filtro se tiver ao menos N vagas; senão entra em "Outras"
define('DIAS_RECRUTADOR_ATIVO', 7); // empresa que acessou o painel nos últimos N dias
define('UPLOAD_MAX_MB', 5);
define('TIPOS_VAGA', ['Estágio', 'Trainee', 'Jovem Aprendiz', 'Jovem Profissional']);
define('MODALIDADES', ['Presencial', 'Híbrido', 'Remoto']);
define('AREA_OUTRA', '__outra__');
define('SEGMENTOS', ['Tecnologia da Informação', 'Indústria', 'Comércio', 'Serviços', 'Educação', 'Saúde', 'Logística e Transporte', 'Financeiro', 'Marketing e Comunicação', 'Construção', 'Alimentação', 'Agronegócio', 'Outros']);
define('PORTES', ['MEI', 'Microempresa', 'Pequena empresa', 'Média empresa', 'Grande empresa']);
define('TURNOS', ['manha' => 'Manhã', 'tarde' => 'Tarde', 'noite' => 'Noite', 'integral' => 'Integral', 'flexivel' => 'Horário flexível']);
define('DIAS_SEMANA', ['seg' => 'Seg', 'ter' => 'Ter', 'qua' => 'Qua', 'qui' => 'Qui', 'sex' => 'Sex', 'sab' => 'Sáb']);
define('POR_PAGINA', 9);
define('UFS', ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']);

date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

spl_autoload_register(function ($c) {
    foreach (['app', 'Models', 'Controllers'] as $d) {
        $f = ROOT_PATH . "/$d/$c.php";
        if (is_file($f)) { require_once $f; return; }
    }
});

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('vagas_sid');
    session_set_cookie_params(['lifetime' => 0, 'path' => BASE_URL, 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---------- Helpers ----------
function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function url(string $pagina = 'home', array $params = []): string
{
    $params = array_merge(['p' => $pagina], $params);
    if ($pagina === 'home') { unset($params['p']); }
    return BASE_URL . ($params ? '?' . http_build_query($params) : '');
}


function url_abs(string $pagina = 'home', array $params = []): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    if (!$base) {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $base = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . url($pagina, $params);
}

function redirect(string $pagina, array $params = []): void { header('Location: ' . url($pagina, $params)); exit; }

function data_br(?string $iso): string { return $iso ? date('d/m/Y', strtotime($iso)) : ''; }

function remaining_days(string $limite): int
{
    return (int) floor((strtotime($limite . ' 23:59:59') - time()) / 86400);
}

function money($v): string { return $v === null || $v === '' ? 'A combinar' : 'R$ ' . number_format((float) $v, 2, ',', '.'); }



function parse_money(string $s): ?float
{
    $s = trim(str_replace(['R$', ' '], '', $s));
    if ($s === '') return null;
    $s = str_contains($s, ',') ? str_replace(['.', ','], ['', '.'], $s) : $s;
    return is_numeric($s) && $s >= 0 ? round((float) $s, 2) : null;
}


function fone_fmt(string $d): string
{
    $d = preg_replace('/\D/', '', $d);
    if (strlen($d) === 11) return substr($d, 0, 2) . ' ' . substr($d, 2, 5) . '-' . substr($d, 7);
    if (strlen($d) === 10) return substr($d, 0, 2) . ' ' . substr($d, 2, 4) . '-' . substr($d, 6);
    return $d;
}

function description_resume(string $desc, int $linhas = 3, int $max = 220): string
{
    $partes = array_values(array_filter(array_map('trim', preg_split('/\R/u', $desc)), 'strlen'));
    $txt = implode(' ', array_slice($partes, 0, $linhas));
    if (mb_strlen($txt) <= $max) return $txt;
    $corte = mb_substr($txt, 0, $max);
    $esp = mb_strrpos($corte, ' ');
    return rtrim(mb_substr($corte, 0, $esp ?: $max), " ,.;:") . '…';
}

function job_image(array $v, string $classe = ''): string
{
    $emp = $v['empresa']['nome'] ?? 'Empresa';
    if (!empty($v['imagem'])) {
        return '<img class="' . e($classe) . '" src="' . e(BASE_URL . $v['imagem']) . '" alt="Banner da vaga ' . e($v['titulo']) . ' - ' . e($emp) . '" loading="lazy">';
    }
    return '<div class="vg-fallback vg-fb-' . ((int) ($v['empresa_id'] ?? 0) % 6) . ' ' . e($classe) . '" role="img" aria-label="Vaga ' . e($v['titulo']) . ' - ' . e($emp) . '"><span>' . e(iniciais($emp)) . '</span><small>' . e($emp) . '</small></div>';
}

function initials(string $nome): string
{
    $i = '';
    foreach (array_slice(preg_split('/\s+/', trim($nome)), 0, 2) as $p) { if (preg_match('/./u', $p, $m)) $i .= mb_strtoupper($m[0]); }
    return $i;
}

function stamp_deadline(array $v): array
{
    $d = remaining_days($v['data_limite']);
    if ($d <= 0) return ['Encerra hoje', 'vg-badge-warn'];
    if ($d <= DIAS_VAGA_VENCENDO) return ['Encerra em ' . $d . ($d === 1 ? ' dia' : ' dias'), 'vg-badge-warn'];
    return ['Até ' . date('d/m', strtotime($v['data_limite'])), 'vg-badge-soft'];
}

function job_is_new(array $v): bool { return (time() - strtotime($v['data_publicacao'])) <= DIAS_VAGA_NOVA * 86400; }


function icon(string $nome, int $tam = 18): string
{
    static $p = [
        'chevron-left' => '<path d="M15 18l-6-6 6-6"/>', 'chevron-right' => '<path d="M9 18l6-6-6-6"/>',
        'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7"/>', 'arrow-left' => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'building' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M12 6h.01M16 6h.01M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/>',
        'plus' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>',
        'tv' => '<rect x="2" y="7" width="20" height="15" rx="2"/><path d="m17 2-5 5-5-5"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'money' => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'check' => '<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="m22 4-10 10-3-3"/>',
        'copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
        'edit' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M7 14l4-4 4 3 5-6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>', 'check-s' => '<path d="m5 12 5 5 9-10"/>', 'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.7 4H7.3a2 2 0 0 0-1.8 1.1z"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'star' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'flame' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.4-.5-2-1-3-1.1-2.1-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.2.4-2.4 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'trending' => '<path d="m22 7-8.5 8.5-5-5L2 17M16 7h6v6"/>',
    ];
    return '<svg class="vg-ico" width="' . $tam . '" height="' . $tam . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($p[$nome] ?? '') . '</svg>';
}

function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">'; }

function flash(string $tipo, string $msg): void { $_SESSION['flash'][] = [$tipo, $msg]; }


function view(string $nome, array $dados = []): void
{
    extract($dados, EXTR_SKIP);
    require ROOT_PATH . "/Views/$nome.php";
}

function render(string $nome, array $dados, string $layout): void
{
    extract($dados, EXTR_SKIP);
    ob_start(); require ROOT_PATH . "/Views/$nome.php"; $conteudo = ob_get_clean();
    require ROOT_PATH . "/Views/layouts/$layout.php";
}

function url_job(int $id): string { return env('PRETTY_URLS') && env('PRETTY_URLS') !== '0' ? BASE_URL . 'vaga/' . $id : url('vaga', ['id' => $id]); }
function url_job_abs(int $id): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    if (!$base) { $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'; $base = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'); }
    return $base . url_vaga($id);
}

function clean_post(array $p): array
{
    $o = [];
    foreach ($p as $k => $v) $o[$k] = is_array($v) ? array_values(array_filter(array_map(fn($x) => is_string($x) ? trim($x) : '', $v), 'strlen')) : (is_string($v) ? trim($v) : '');
    return $o;
}
function days_txt(int $d): string { return $d < 0 ? 'Encerrada há ' . abs($d) . ($d === -1 ? ' dia' : ' dias') : ($d === 0 ? 'Último dia' : $d . ($d === 1 ? ' dia restante' : ' dias restantes')); }

?>