<?php
class Link
{
    public const PLATAFORMAS = [
        'gupy'     => 'Gupy',
        'linkedin' => 'LinkedIn',
        'site'     => 'Site da empresa',
        'email'    => 'E-mail',
        'outro'    => 'Outro sistema',
    ];

    public static function normalize(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') return null;
        if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    public static function isValid(?string $url, ?string $platform = null): bool
    {
        if ($url === null || $url === '' || strlen($url) > 250 || preg_match('/[\s<>"\']/', $url)) {
            return false;
        }
        $parts = parse_url($url);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }
        $host = strtolower($parts['host']);
        if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $host)) return false;
        if ($platform === 'gupy')     return (bool) preg_match('/(^|\.)gupy\.io$/', $host);
        if ($platform === 'linkedin') return (bool) preg_match('/(^|\.)linkedin\.com$/', $host);
        return true;
    }

    public static function domain(string $url): string
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST));
    }

    /** @deprecated Use normalize() */
    public static function normalizar(?string $u): ?string  { return self::normalize($u); }
    /** @deprecated Use isValid() */
    public static function valido(?string $u, ?string $plataforma = null): bool { return self::isValid($u, $plataforma); }
    /** @deprecated Use domain() */
    public static function dominio(string $u): string       { return self::domain($u); }
}
