<?php
class CompanyMember
{
    public const PRINCIPAL = 'principal';
    public const MANAGER = 'gestor';
    public const MEMBER = 'membro';

    public const ROLES = [
        self::PRINCIPAL => 'Responsável',
        self::MANAGER   => 'Gestor',
        self::MEMBER    => 'Recrutador',
    ];

    public const ROLE_HINTS = [
        self::PRINCIPAL => 'cadastrou a empresa e controla tudo',
        self::MANAGER   => 'publica vagas, vê o desempenho e cuida da equipe',
        self::MEMBER    => 'publica e acompanha as vagas',
    ];

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM empresa_usuarios WHERE id = :i AND excluido_em IS NULL', ['i' => $id]);
    }

    public static function byEmail(string $email): ?array
    {
        return Database::one('SELECT * FROM empresa_usuarios WHERE email = :e AND excluido_em IS NULL', ['e' => mb_strtolower(trim($email))]);
    }

    public static function emailTaken(string $email): bool
    {
        return (bool) Database::val('SELECT 1 FROM empresa_usuarios WHERE email = :e', ['e' => mb_strtolower(trim($email))]);
    }

    public static function principalOf(int $companyId): ?array
    {
        return Database::one("SELECT * FROM empresa_usuarios WHERE empresa_id = :c AND papel = 'principal' AND excluido_em IS NULL ORDER BY id LIMIT 1", ['c' => $companyId]);
    }

    public static function ofCompany(int $companyId, int $page = 1, int $perPage = 12): array
    {
        $total = (int) Database::val('SELECT COUNT(*) FROM empresa_usuarios WHERE empresa_id = :c AND excluido_em IS NULL', ['c' => $companyId]);
        [$offset, $page, $pages] = Paginator::calc($total, $page, $perPage);
        $rows = Database::all("SELECT * FROM empresa_usuarios WHERE empresa_id = :c AND excluido_em IS NULL
            ORDER BY (papel = 'principal') DESC, (papel = 'gestor') DESC, ativo DESC, nome LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset, ['c' => $companyId]);
        return [$rows, $total, $page, $pages];
    }

    public static function create(int $companyId, string $name, string $email, string $role, ?int $creatorId): array
    {
        $password = Auth::temporaryPassword();
        $id = Database::insert('empresa_usuarios', [
            'empresa_id'   => $companyId,
            'nome'         => $name,
            'email'        => mb_strtolower(trim($email)),
            'senha_hash'   => Auth::hash($password),
            'papel'        => isset(self::ROLES[$role]) && $role !== self::PRINCIPAL ? $role : self::MEMBER,
            'ativo'        => 1,
            'trocar_senha' => 1,
            'criado_por'   => $creatorId,
            'criado_em'    => Database::now(),
        ]);
        return [$id, $password];
    }

    public static function createPrincipal(int $companyId, string $name, string $email, string $passwordHash): int
    {
        return Database::insert('empresa_usuarios', [
            'empresa_id' => $companyId,
            'nome'       => $name,
            'email'      => mb_strtolower(trim($email)),
            'senha_hash' => $passwordHash,
            'papel'      => self::PRINCIPAL,
            'ativo'      => 1,
            'criado_em'  => Database::now(),
        ]);
    }

    public static function backfillPrincipals(): int
    {
        return Database::run("INSERT INTO empresa_usuarios (empresa_id, nome, email, senha_hash, papel, ativo, trocar_senha, criado_em, ultimo_acesso)
            SELECT e.id, e.responsavel, e.email, e.senha_hash, 'principal', 1, 0, e.criado_em, e.ultimo_acesso FROM empresas e
            WHERE NOT EXISTS (SELECT 1 FROM empresa_usuarios u WHERE u.empresa_id = e.id AND u.papel = 'principal')
              AND NOT EXISTS (SELECT 1 FROM empresa_usuarios u WHERE u.email = e.email)")->rowCount();
    }

    public static function setActive(int $id, bool $active, string $by): void
    {
        Database::update('empresa_usuarios', $id, ['ativo' => $active ? 1 : 0, 'desativado_por' => $active ? null : $by]);
    }

    public static function setRole(int $id, string $role): void
    {
        if ($role === self::MANAGER || $role === self::MEMBER) Database::update('empresa_usuarios', $id, ['papel' => $role]);
    }

    public static function softDelete(int $id): void
    {
        Database::update('empresa_usuarios', $id, ['ativo' => 0, 'excluido_em' => Database::now()]);
    }

    public static function changePassword(int $id, string $newPassword): void
    {
        $hash = Auth::hash($newPassword);
        Database::update('empresa_usuarios', $id, ['senha_hash' => $hash, 'trocar_senha' => 0]);
        $member = self::find($id);
        if ($member && $member['papel'] === self::PRINCIPAL) {
            Database::update('empresas', (int) $member['empresa_id'], ['senha_hash' => $hash]);
        }
    }

    public static function touchLogin(int $id): void
    {
        Database::update('empresa_usuarios', $id, ['ultimo_acesso' => Database::now()]);
    }

    public static function isPrincipal(?array $member): bool
    {
        return ($member['papel'] ?? '') === self::PRINCIPAL;
    }

    public static function canManageUsers(?array $member): bool
    {
        return in_array($member['papel'] ?? '', [self::PRINCIPAL, self::MANAGER], true);
    }

    public static function canSeeDashboard(?array $member): bool
    {
        return self::canManageUsers($member);
    }

    public static function roleLabel(string $role): string
    {
        return self::ROLES[$role] ?? $role;
    }
}
