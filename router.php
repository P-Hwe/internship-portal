<?php

<<<<<<< HEAD
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(app|Controllers|Models|Views|database|cron|storage|tests|docs)(/|$)|^/\.env|/router\.php$#', $p)) { http_response_code(403); exit('Acesso negado'); }
if ($p !== '/' && is_file(__DIR__ . $p) && !preg_match('/\.php$/', $p)) return false;
=======
$p = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // @phpstan-ignore argument.type
if (preg_match('#^/(app|Controllers|Models|Views|database|cron|storage|tests|docs)(/|$)|^/\.env|/router\.php$#', $p)) {
    http_response_code(403);
    exit('Acesso negado');
} // @phpstan-ignore argument.type
if (preg_match('#^/vaga/(\d+)/?$#', $p, $m)) {
    $_GET['p'] = 'vaga';
    $_GET['id'] = $m[1];
    require __DIR__ . '/index.php';
    return;
} // @phpstan-ignore argument.type
if ($p !== '/' && is_file(__DIR__ . $p) && !preg_match('/\.php$/', $p)) {
    return false;
}
>>>>>>> develop
require __DIR__ . '/index.php';
