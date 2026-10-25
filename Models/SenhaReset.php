<?php

class SenhaReset
{
    public const VALIDADE_MIN = 30;
    public const MAX_TENTATIVAS = 5;
    public const MAX_PEDIDOS_HORA = 5;

    private static function tabela(string $perfil): string { return $perfil === 'admin' ? 'admins' : 'empresas'; }
    private static function usuario(string $perfil, string $email): ?array
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
        $extra = $perfil === 'admin' ? ' AND ativo = 1' : '';
        return Database::one('SELECT * FROM ' . self::tabela($perfil) . ' WHERE email = :e' . $extra, ['e' => $email]) ?: null;
    }
    public static function hashCodigo(int $id, string $codigo): string { return hash('sha256', $id . ':' . $codigo); }

    public static function solicitar(string $perfil, string $email): ?array
    {
        $u = self::usuario($perfil, $email);
        if (!$u) return null;
        $hora = date('Y-m-d H:i:s', time() - 3600);
        if ((int) Database::val('SELECT COUNT(*) FROM senha_resets WHERE perfil = :p AND usuario_id = :u AND criado_em >= :d', ['p' => $perfil, 'u' => $u['id'], 'd' => $hora]) >= self::MAX_PEDIDOS_HORA) return null;
        Database::run('UPDATE senha_resets SET usado_em = :n WHERE perfil = :p AND usuario_id = :u AND usado_em IS NULL', ['n' => Database::agora(), 'p' => $perfil, 'u' => $u['id']]);
        $token = bin2hex(random_bytes(20));
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $id = Database::insert('senha_resets', ['perfil' => $perfil, 'usuario_id' => (int) $u['id'], 'token_hash' => hash('sha256', $token), 'codigo_hash' => '', 'expira_em' => date('Y-m-d H:i:s', time() + self::VALIDADE_MIN * 60), 'tentativas' => 0, 'criado_em' => Database::agora()]);
        Database::update('senha_resets', $id, ['codigo_hash' => self::hashCodigo($id, $codigo)]);
        return ['usuario' => $u, 'token' => $token, 'codigo' => $codigo];
    }

    public static function porToken(string $perfil, string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $token)) return null;
        return Database::one('SELECT r.*, u.email, u.nome FROM senha_resets r JOIN ' . self::tabela($perfil) . ' u ON u.id = r.usuario_id
            WHERE r.perfil = :p AND r.token_hash = :h AND r.usado_em IS NULL AND r.expira_em >= :n', ['p' => $perfil, 'h' => hash('sha256', $token), 'n' => Database::agora()]) ?: null;
    }

    public static function porCodigo(string $perfil, string $email, string $codigo): ?array
    {
        $u = self::usuario($perfil, $email);
        if (!$u || !preg_match('/^\d{6}$/', $codigo)) return null;
        $r = Database::one('SELECT * FROM senha_resets WHERE perfil = :p AND usuario_id = :u AND usado_em IS NULL AND expira_em >= :n ORDER BY id DESC LIMIT 1', ['p' => $perfil, 'u' => $u['id'], 'n' => Database::agora()]);
        if (!$r) return null;
        if (hash_equals((string) $r['codigo_hash'], self::hashCodigo((int) $r['id'], $codigo))) return $r + ['email' => $u['email'], 'nome' => $u['nome']];
        $t = (int) $r['tentativas'] + 1;
        Database::update('senha_resets', (int) $r['id'], ['tentativas' => $t] + ($t >= self::MAX_TENTATIVAS ? ['usado_em' => Database::agora()] : []));
        return null;
    }

    public static function concluir(array $r, string $nova): void
    {
        if ($r['perfil'] === 'admin') Admin::trocarSenha((int) $r['usuario_id'], $nova);
        else Database::update('empresas', (int) $r['usuario_id'], ['senha_hash' => Auth::hash($nova)]);
        Database::run('UPDATE senha_resets SET usado_em = :n WHERE perfil = :p AND usuario_id = :u AND usado_em IS NULL', ['n' => Database::agora(), 'p' => $r['perfil'], 'u' => $r['usuario_id']]);
        Database::run('DELETE FROM login_tentativas WHERE chave = :c', ['c' => hash('sha256', $r['perfil'] . '|' . mb_strtolower($r['email']) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'))]);
    }
}
