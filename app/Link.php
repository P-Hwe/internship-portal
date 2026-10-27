<?php

class Link
{
    public const PLATAFORMAS = [
        'gupy' => 'Gupy',
        'linkedin' => 'LinkedIn',
        'site' => 'Site da empresa',
        'email' => 'E-mail',
        'outro' => 'Outro sistema',
    ];

    public static function normalizar(?string $u): ?string
    {
        $u = trim((string) $u);

        if ($u === '') {
            return null;
        }

        if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $u)) {
            $u = 'https://' . $u;
        }

        return $u;
    }

    public static function valido(?string $u, ?string $plataforma = null): bool
    {
        if ($u === null || $u === '' || strlen($u) > 250 || preg_match('/[\s<>"\']/', $u)) {
            return false;
        }

        $p = parse_url($u);

        if (
            !$p
            || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true)
            || empty($p['host'])
            || isset($p['user'])
            || isset($p['pass'])
        ) {
            return false;
        }

        $h = strtolower($p['host']);

        if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $h)) {
            return false;
        }

        if ($plataforma === 'gupy') {
            return (bool) preg_match('/(^|\.)gupy\.io$/', $h);
        }

        if ($plataforma === 'linkedin') {
            return (bool) preg_match('/(^|\.)linkedin\.com$/', $h);
        }

        return true;
    }

    public static function dominio(string $u): string
    {
        return strtolower((string) parse_url($u, PHP_URL_HOST));
    }
}
