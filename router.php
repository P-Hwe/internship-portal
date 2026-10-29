<?php
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(app|Controllers|Models|Views|database|cron|storage|tests|docs)(/|$)|^/\.env|/router\.php$#', $p)) { http_response_code(403); exit('Acesso negado'); }
if ($p !== '/' && is_file(__DIR__ . $p) && !preg_match('/\.php$/', $p)) return false;
require __DIR__ . '/index.php';
