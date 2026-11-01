<?php
class SecurityHeaders
{
    private const PUBLIC_ROUTES = ['home', 'vaga', 'encerradas', 'tv', 'empresas', 'empresa', 'externo'];

    private const PANEL_POLICY = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline'",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data: blob:",
        "connect-src 'self'",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ];

    public static function send(?string $route): void
    {
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        if (is_https()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        if (Auth::role()) {
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
        if (!self::isPublic($route)) {
            header('Content-Security-Policy: ' . implode('; ', self::PANEL_POLICY));
        }
    }

    public static function isPublic(?string $route): bool
    {
        return is_string($route) && in_array($route, self::PUBLIC_ROUTES, true);
    }
}
