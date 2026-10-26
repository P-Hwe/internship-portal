<?php
class Admin
{
    public static function porId(int $id): ?array { return Database::one('SELECT * FROM admins WHERE id = :i', ['i' => $id]); }
    public static function porEmail(string $e): ?array { return Database::one('SELECT * FROM admins WHERE email = :e', ['e' => mb_strtolower($e)]); }
    public static function todos(): array { return Database::all('SELECT id, nome, email, papel, ativo, trocar_senha, criado_em, ultimo_acesso FROM admins ORDER BY id'); }
    public static function ehRaiz(?array $a): bool { return $a && ($a['papel'] ?? '') === 'raiz'; }
    public static function criar(string $nome, string $email, string $senha, string $papel = 'admin', bool $trocarSenha = true): int
    {
        return Database::insert('admins', ['nome' => $nome, 'email' => mb_strtolower($email), 'senha_hash' => Auth::hash($senha), 'papel' => $papel, 'ativo' => 1, 'trocar_senha' => $trocarSenha ? 1 : 0, 'criado_em' => Database::agora()]);
    }
    public static function trocarSenha(int $id, string $nova): void { Database::update('admins', $id, ['senha_hash' => Auth::hash($nova), 'trocar_senha' => 0]); }
    public static function definirAtivo(int $id, bool $ativo): void { Database::update('admins', $id, ['ativo' => $ativo ? 1 : 0]); }
}