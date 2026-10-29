<?php
/** PasswordReset — handles password reset tokens and 6-digit code verification. */
class PasswordReset
{
    public const EXPIRY_MIN           = 30;
    public const MAX_ATTEMPTS         = 5;
    public const MAX_REQUESTS_PER_HOUR = 5;

    // backwards-compatible aliases for views/notifier that reference the old names
    public const VALIDADE_MIN     = self::EXPIRY_MIN;
    public const MAX_TENTATIVAS   = self::MAX_ATTEMPTS;
    public const MAX_PEDIDOS_HORA = self::MAX_REQUESTS_PER_HOUR;

    private static function table(string $profile): string
    {
        return $profile === 'admin' ? 'admins' : 'empresas';
    }

    private static function findUser(string $profile, string $email): ?array
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
        $extra = $profile === 'admin' ? ' AND ativo = 1' : '';
        return Database::one('SELECT * FROM ' . self::table($profile) . ' WHERE email = :e' . $extra, ['e' => $email]) ?: null;
    }

    public static function hashCode(int $id, string $code): string
    {
        return hash('sha256', $id . ':' . $code);
    }

    public static function request(string $profile, string $email): ?array
    {
        $user = self::findUser($profile, $email);
        if (!$user) return null;
        $oneHourAgo = date('Y-m-d H:i:s', time() - 3600);
        if ((int) Database::val('SELECT COUNT(*) FROM senha_resets WHERE perfil = :p AND usuario_id = :u AND criado_em >= :d',
            ['p' => $profile, 'u' => $user['id'], 'd' => $oneHourAgo]) >= self::MAX_REQUESTS_PER_HOUR) return null;
        Database::run('UPDATE senha_resets SET usado_em = :n WHERE perfil = :p AND usuario_id = :u AND usado_em IS NULL',
            ['n' => Database::now(), 'p' => $profile, 'u' => $user['id']]);
        $token = bin2hex(random_bytes(20));
        $code  = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $id = Database::insert('senha_resets', [
            'perfil'      => $profile,
            'usuario_id'  => (int) $user['id'],
            'token_hash'  => hash('sha256', $token),
            'codigo_hash' => '',
            'expira_em'   => date('Y-m-d H:i:s', time() + self::EXPIRY_MIN * 60),
            'tentativas'  => 0,
            'criado_em'   => Database::now(),
        ]);
        Database::update('senha_resets', $id, ['codigo_hash' => self::hashCode($id, $code)]);
        return ['usuario' => $user, 'token' => $token, 'codigo' => $code];
    }

    public static function findByToken(string $profile, string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) return null;
        return Database::one(
            'SELECT r.*, u.email, u.nome FROM senha_resets r JOIN ' . self::table($profile) . ' u ON u.id = r.usuario_id
             WHERE r.perfil = :p AND r.token_hash = :h AND r.usado_em IS NULL AND r.expira_em >= :n',
            ['p' => $profile, 'h' => hash('sha256', $token), 'n' => Database::now()]
        ) ?: null;
    }

    public static function findByCode(string $profile, string $email, string $code): ?array
    {
        $user = self::findUser($profile, $email);
        if (!$user || !preg_match('/^\d{6}$/', $code)) return null;
        $r = Database::one(
            'SELECT * FROM senha_resets WHERE perfil = :p AND usuario_id = :u AND usado_em IS NULL AND expira_em >= :n ORDER BY id DESC LIMIT 1',
            ['p' => $profile, 'u' => $user['id'], 'n' => Database::now()]
        );
        if (!$r) return null;
        if (hash_equals((string) $r['codigo_hash'], self::hashCode((int) $r['id'], $code)))
            return $r + ['email' => $user['email'], 'nome' => $user['nome']];
        $attempts = (int) $r['tentativas'] + 1;
        Database::update('senha_resets', (int) $r['id'],
            ['tentativas' => $attempts] + ($attempts >= self::MAX_ATTEMPTS ? ['usado_em' => Database::now()] : []));
        return null;
    }

    public static function complete(array $reset, string $newPassword): void
    {
        if ($reset['perfil'] === 'admin') Admin::changePassword((int) $reset['usuario_id'], $newPassword);
        else Database::update('empresas', (int) $reset['usuario_id'], ['senha_hash' => Auth::hash($newPassword)]);
        Database::run('UPDATE senha_resets SET usado_em = :n WHERE perfil = :p AND usuario_id = :u AND usado_em IS NULL',
            ['n' => Database::now(), 'p' => $reset['perfil'], 'u' => $reset['usuario_id']]);
        Database::run('DELETE FROM login_tentativas WHERE chave = :c',
            ['c' => hash('sha256', $reset['perfil'] . '|' . mb_strtolower($reset['email']) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'))]);
    }

    // ── backwards-compatible aliases ──
    /** @deprecated Use hashCode() */
    public static function hashCodigo(int $id, string $code): string { return self::hashCode($id, $code); }
    /** @deprecated Use request() */
    public static function solicitar(string $profile, string $email): ?array { return self::request($profile, $email); }
    /** @deprecated Use findByToken() */
    public static function porToken(string $profile, string $token): ?array { return self::findByToken($profile, $token); }
    /** @deprecated Use findByCode() */
    public static function porCodigo(string $profile, string $email, string $code): ?array { return self::findByCode($profile, $email, $code); }
    /** @deprecated Use complete() */
    public static function concluir(array $r, string $newPassword): void { self::complete($r, $newPassword); }
}
