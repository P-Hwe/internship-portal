<?php
/**
 * Session heartbeat endpoint.
 * Called by JS every 4 minutes when the user is active.
 * Extends $_SESSION['_ultima'] to prevent idle timeout.
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

// Only POST requests accepted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'reason' => 'method_not_allowed']);
    exit;
}

// CSRF check
$token = $_POST['_csrf'] ?? '';
if (!is_string($token) || !hash_equals(Csrf::token(), $token)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'reason' => 'csrf']);
    exit;
}

$profile = Auth::role();
if (!$profile) {
    echo json_encode(['ok' => false, 'reason' => 'unauthenticated']);
    exit;
}

// Extend session idle timer
$_SESSION['_ultima'] = time();

echo json_encode([
    'ok' => true,
    'profile' => $profile,
    'idle_limit' => (int) env('SESSION_IDLE_' . strtoupper($profile), $profile === 'admin' ? 1800 : 7200),
]);
