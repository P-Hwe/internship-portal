<?php
class Historico
{
    public static function registrar(string $entidade, int $id, string $acao, ?string $de, ?string $para, string $atorTipo, ?int $atorId, ?string $detalhe = null): void
    {
        Database::insert('historico', ['entidade' => $entidade, 'entidade_id' => $id, 'acao' => $acao, 'de' => $de, 'para' => $para,
            'ator_tipo' => $atorTipo, 'ator_id' => $atorId, 'detalhe' => $detalhe !== null ? mb_substr($detalhe, 0, 300) : null, 'criado_em' => Database::agora()]);
    }
    private const ATOR = 'LEFT JOIN admins aa ON (h.ator_tipo = \'admin\' AND aa.id = h.ator_id) LEFT JOIN empresas ae ON (h.ator_tipo = \'empresa\' AND ae.id = h.ator_id)';
    public static function da(string $entidade, int $id, int $n = 30): array
    {
        return Database::all('SELECT h.*, aa.nome AS ator_admin, ae.nome AS ator_empresa, ae.responsavel AS ator_responsavel FROM historico h ' . self::ATOR . ' WHERE h.entidade = :e AND h.entidade_id = :i ORDER BY h.id DESC LIMIT ' . (int) $n, ['e' => $entidade, 'i' => $id]);
    }
    public static function recentes(int $n = 10): array
    {
        return Database::all("SELECT h.*, v.titulo AS v_titulo, e.nome AS e_nome, aa.nome AS ator_admin, ae.nome AS ator_empresa, ae.responsavel AS ator_responsavel FROM historico h
            LEFT JOIN vagas v ON (h.entidade = 'vaga' AND v.id = h.entidade_id) LEFT JOIN empresas e ON (h.entidade = 'empresa' AND e.id = h.entidade_id) " . self::ATOR . "
            ORDER BY h.id DESC LIMIT " . (int) $n);
    }
    public static function quem(array $h): string
    {
        if ($h['ator_tipo'] === 'admin') return $h['ator_admin'] ?: 'Administrador removido';
        if ($h['ator_tipo'] === 'empresa') {
            $resp = trim((string) ($h['ator_responsavel'] ?? '')); $emp = (string) ($h['ator_empresa'] ?? '');
            if ($resp === '') return $emp ?: 'Empresa';
            return ($h['acao'] === 'cadastro' || $emp === '' || $emp === $resp) ? $resp : $resp . ' (' . $emp . ')';
        }
        return 'Sistema';
    }
    public static function listar(?string $ini, int $n, int $off): array
    {
        $w = $ini ? 'WHERE h.criado_em >= :i' : ''; $p = $ini ? ['i' => $ini . ' 00:00:00'] : [];
        return Database::all("SELECT h.*, v.titulo AS v_titulo, e.nome AS e_nome, aa.nome AS ator_admin, ae.nome AS ator_empresa, ae.responsavel AS ator_responsavel FROM historico h
            LEFT JOIN vagas v ON (h.entidade = 'vaga' AND v.id = h.entidade_id) LEFT JOIN empresas e ON (h.entidade = 'empresa' AND e.id = h.entidade_id) " . self::ATOR . " $w
            ORDER BY h.id DESC LIMIT " . (int) $n . ' OFFSET ' . (int) $off, $p);
    }
    public static function contar(?string $ini): int
    {
        return (int) Database::val('SELECT COUNT(*) FROM historico h ' . ($ini ? 'WHERE h.criado_em >= :i' : ''), $ini ? ['i' => $ini . ' 00:00:00'] : []);
    }
    private const VERBO = ['criada' => 'enviou a vaga', 'aprovada' => 'aprovou', 'rejeitada' => 'rejeitou a vaga', 'reprovada' => 'reprovou a empresa', 'encerrada' => 'encerrou a vaga',
        'editada' => 'editou a vaga', 'reenviada' => 'reenviou', 'destaque' => 'mudou o destaque da vaga', 'bloqueada' => 'bloqueou a empresa', 'desbloqueada' => 'desbloqueou a empresa',
        'cadastro' => 'cadastrou', 'perfil' => 'atualizou o perfil de', 'expirada' => 'teve o prazo encerrado:'];
    public static function frase(array $h): array
    {
        $item = $h['entidade'] === 'vaga' ? (string) $h['v_titulo'] : ($h['entidade'] === 'empresa' ? (string) $h['e_nome'] : (string) $h['detalhe']);
        $v = self::VERBO[$h['acao']] ?? $h['acao'];
        if (in_array($h['acao'], ['aprovada', 'reenviada'], true)) $v .= $h['entidade'] === 'vaga' ? ' a vaga' : ($h['acao'] === 'reenviada' ? ' o cadastro de' : ' a empresa');
        if ($h['acao'] === 'cadastro') $v = $h['entidade'] === 'admin' ? 'criou o administrador' : 'cadastrou a empresa';
        return [self::quem($h), $v, $item];
    }
    public static function ator(): array
    {
        if (!empty($_SESSION['admin_id'])) return ['admin', (int) $_SESSION['admin_id']];
        if (!empty($_SESSION['empresa_id'])) return ['empresa', (int) $_SESSION['empresa_id']];
        return ['sistema', null];
    }
    private const ROT = ['criada' => 'Enviada para análise', 'aprovada' => 'Aprovada', 'rejeitada' => 'Rejeitada', 'reprovada' => 'Reprovada', 'encerrada' => 'Encerrada',
        'editada' => 'Editada (voltou para análise)', 'reenviada' => 'Reenviada para análise', 'destaque' => 'Destaque alterado', 'bloqueada' => 'Bloqueada', 'desbloqueada' => 'Desbloqueada',
        'cadastro' => 'Cadastro realizado', 'perfil' => 'Perfil atualizado', 'expirada' => 'Prazo encerrado'];
    public static function rotulo(array $h): string { return self::ROT[$h['acao']] ?? $h['acao']; }
}