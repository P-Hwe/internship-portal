<?php
class Admin
{
    public static function findById(int $id): ?array        { return Database::one('SELECT * FROM admins WHERE id = :i', ['i' => $id]); }
    public static function findByEmail(string $email): ?array { return Database::one('SELECT * FROM admins WHERE email = :e AND excluido_em IS NULL', ['e' => mb_strtolower($email)]); }
    public static function emailTaken(string $email): bool   { return (bool) Database::val('SELECT 1 FROM admins WHERE email = :e', ['e' => mb_strtolower($email)]); }
    public static function all(): array                     { return Database::all('SELECT id, nome, email, papel, ativo, trocar_senha, criado_em, ultimo_acesso FROM admins WHERE excluido_em IS NULL ORDER BY id'); }
    public static function isRoot(?array $admin): bool      { return $admin !== null && ($admin['papel'] ?? '') === 'raiz'; }

    public static function page(int $page, int $perPage): array
    {
        $total = (int) Database::val('SELECT COUNT(*) FROM admins WHERE excluido_em IS NULL');
        [$offset, $page, $pages] = Paginator::calc($total, $page, $perPage);
        $rows = Database::all("SELECT id, nome, email, papel, ativo, trocar_senha, criado_em, ultimo_acesso FROM admins
            WHERE excluido_em IS NULL ORDER BY (papel = 'raiz') DESC, ativo DESC, nome LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset);
        return [$rows, $total, $page, $pages];
    }

    public static function create(string $name, string $email, string $password, string $role = 'admin', bool $mustChangePassword = true): int
    {
        return Database::insert('admins', [
            'nome'        => $name,
            'email'       => mb_strtolower($email),
            'senha_hash'  => Auth::hash($password),
            'papel'       => $role,
            'ativo'       => 1,
            'trocar_senha' => $mustChangePassword ? 1 : 0,
            'criado_em'   => Database::now(),
        ]);
    }

    public static function createWithTemporaryPassword(string $name, string $email): array
    {
        $password = Auth::temporaryPassword();
        return [self::create($name, $email, $password), $password];
    }

    public static function changePassword(int $id, string $newPassword): void
    {
        Database::update('admins', $id, ['senha_hash' => Auth::hash($newPassword), 'trocar_senha' => 0]);
    }

    public static function setActive(int $id, bool $active): void
    {
        Database::update('admins', $id, ['ativo' => $active ? 1 : 0]);
    }

    public static function softDelete(int $id): void
    {
        Database::update('admins', $id, ['ativo' => 0, 'excluido_em' => Database::now()]);
    }

    // ── backwards-compatible aliases ──
    /** @deprecated Use findById() */
    public static function porId(int $id): ?array            { return self::findById($id); }
    /** @deprecated Use findByEmail() */
    public static function porEmail(string $email): ?array   { return self::findByEmail($email); }
    /** @deprecated Use all() */
    public static function todos(): array                    { return self::all(); }
    /** @deprecated Use isRoot() */
    public static function ehRaiz(?array $admin): bool       { return self::isRoot($admin); }
    /** @deprecated Use create() */
    public static function criar(string $nome, string $email, string $senha, string $papel = 'admin', bool $trocarSenha = true): int
    {
        return self::create($nome, $email, $senha, $papel, $trocarSenha);
    }
    /** @deprecated Use changePassword() */
    public static function trocarSenha(int $id, string $nova): void { self::changePassword($id, $nova); }
    /** @deprecated Use setActive() */
    public static function definirAtivo(int $id, bool $ativo): void { self::setActive($id, $ativo); }
}
