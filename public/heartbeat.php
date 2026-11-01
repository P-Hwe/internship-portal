<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'reason' => 'method_not_allowed']);
    exit;
}

$token = $_POST['_csrf'] ?? '';
if (!is_string($token) || !hash_equals(Csrf::token(), $token)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'reason' => 'csrf']);
    exit;
}

Auth::normalize();
$profile = Auth::role();
if (!$profile) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'reason' => 'unauthenticated']);
    exit;
}

echo json_encode([
    'ok' => true,
    'profile' => $profile,
    'idle_limit' => (int) env('SESSION_IDLE_' . strtoupper($profile), $profile === 'admin' ? 1800 : 7200),
]);
