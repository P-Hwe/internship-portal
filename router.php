<?php
$p = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$blockedDirs = '#^/(app|Controllers|Models|Views|database|cron|storage|tests|docs|vendor|env|scripts)(/|$)#';
$blockedFiles = '#(^|/)\.|\.(sql|sqlite|log|bak|neon|lock|md|dist|ps1|sh)$|^/(composer\.json|router\.php|config\.php)$#';
if (preg_match($blockedDirs, $p) || preg_match($blockedFiles, $p)) { http_response_code(403); exit('Acesso negado'); }
if ($p !== '/' && is_file(__DIR__ . $p) && !preg_match('/\.php$/', $p)) return false;
if ($p === '/public/heartbeat.php') { require __DIR__ . '/public/heartbeat.php'; return true; }
require __DIR__ . '/index.php';
