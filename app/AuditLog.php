<?php
class AuditLog
{
    public static function registrar(string $entity, int $id, string $action, ?string $from, ?string $to, string $actorType, ?int $actorId, ?string $detail = null): void
    {
        Database::insert('historico', [
            'entidade'    => $entity,
            'entidade_id' => $id,
            'acao'        => $action,
            'de'          => $from,
            'para'        => $to,
            'ator_tipo'   => $actorType,
            'ator_id'     => $actorId,
            'detalhe'     => $detail !== null ? mb_substr($detail, 0, 300) : null,
            'criado_em'   => Database::now(),
        ]);
    }

    private const ACTOR_JOIN = "LEFT JOIN admins aa ON (h.ator_tipo = 'admin' AND aa.id = h.ator_id) LEFT JOIN empresas ae ON (h.ator_tipo = 'empresa' AND ae.id = h.ator_id)";

    public static function da(string $entity, int $id, int $limit = 30): array
    {
        return Database::all(
            'SELECT h.*, aa.nome AS ator_admin, ae.nome AS ator_empresa, ae.responsavel AS ator_responsavel
             FROM historico h ' . self::ACTOR_JOIN . '
             WHERE h.entidade = :e AND h.entidade_id = :i
             ORDER BY h.id DESC LIMIT ' . (int) $limit,
            ['e' => $entity, 'i' => $id]
        );
    }

    public static function recentes(int $limit = 10): array
    {
        return Database::all(
            "SELECT h.*, v.titulo AS v_titulo, e.nome AS e_nome, aa.nome AS ator_admin, ae.nome AS ator_empresa, ae.responsavel AS ator_responsavel
             FROM historico h
             LEFT JOIN vagas v ON (h.entidade = 'vaga' AND v.id = h.entidade_id)
             LEFT JOIN empresas e ON (h.entidade = 'empresa' AND e.id = h.entidade_id) " . self::ACTOR_JOIN . "
             ORDER BY h.id DESC LIMIT " . (int) $limit
        );
    }

    public static function listar(?string $since, int $limit, int $offset): array
    {
        $where  = $since ? 'WHERE h.criado_em >= :i' : '';
        $params = $since ? ['i' => $since . ' 00:00:00'] : [];
        return Database::all(
            "SELECT h.*, v.titulo AS v_titulo, e.nome AS e_nome, aa.nome AS ator_admin, ae.nome AS ator_empresa, ae.responsavel AS ator_responsavel
             FROM historico h
             LEFT JOIN vagas v ON (h.entidade = 'vaga' AND v.id = h.entidade_id)
             LEFT JOIN empresas e ON (h.entidade = 'empresa' AND e.id = h.entidade_id) " . self::ACTOR_JOIN . " $where
             ORDER BY h.id DESC LIMIT " . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public static function contar(?string $since): int
    {
        return (int) Database::val(
            'SELECT COUNT(*) FROM historico h ' . ($since ? 'WHERE h.criado_em >= :i' : ''),
            $since ? ['i' => $since . ' 00:00:00'] : []
        );
    }

    public static function quem(array $entry): string
    {
        if ($entry['ator_tipo'] === 'admin') return $entry['ator_admin'] ?: 'Administrador removido';
        if ($entry['ator_tipo'] === 'empresa') {
            $contact = trim((string) ($entry['ator_responsavel'] ?? ''));
            $company = (string) ($entry['ator_empresa'] ?? '');
            if ($contact === '') return $company ?: 'Empresa';
            return ($entry['acao'] === 'cadastro' || $company === '' || $company === $contact)
                ? $contact : $contact . ' (' . $company . ')';
        }
        return 'Sistema';
    }

    private const ACTION_VERBS = [
        'criada' => 'enviou a vaga', 'aprovada' => 'aprovou', 'rejeitada' => 'rejeitou a vaga',
        'reprovada' => 'reprovou a empresa', 'encerrada' => 'encerrou a vaga', 'editada' => 'editou a vaga',
        'reenviada' => 'reenviou', 'destaque' => 'mudou o destaque da vaga', 'bloqueada' => 'bloqueou a empresa',
        'desbloqueada' => 'desbloqueou a empresa', 'cadastro' => 'cadastrou', 'perfil' => 'atualizou o perfil de',
        'expirada' => 'teve o prazo encerrado:',
    ];

    public static function frase(array $entry): array
    {
        $item = $entry['entidade'] === 'vaga'
            ? (string) ($entry['v_titulo'] ?? '')
            : ($entry['entidade'] === 'empresa' ? (string) ($entry['e_nome'] ?? '') : (string) ($entry['detalhe'] ?? ''));
        $verb = self::ACTION_VERBS[$entry['acao']] ?? $entry['acao'];
        if (in_array($entry['acao'], ['aprovada', 'reenviada'], true)) {
            $verb .= $entry['entidade'] === 'vaga'
                ? ' a vaga' : ($entry['acao'] === 'reenviada' ? ' o cadastro de' : ' a empresa');
        }
        if ($entry['acao'] === 'cadastro') {
            $verb = $entry['entidade'] === 'admin' ? 'criou o administrador' : 'cadastrou a empresa';
        }
        return [self::quem($entry), $verb, $item];
    }

    public static function ator(): array
    {
        if (!empty($_SESSION['admin_id']))   return ['admin',   (int) $_SESSION['admin_id']];
        if (!empty($_SESSION['empresa_id'])) return ['empresa', (int) $_SESSION['empresa_id']];
        return ['sistema', null];
    }

    private const ACTION_LABELS = [
        'criada' => 'Enviada para análise', 'aprovada' => 'Aprovada', 'rejeitada' => 'Rejeitada',
        'reprovada' => 'Reprovada', 'encerrada' => 'Encerrada', 'editada' => 'Editada (voltou para análise)',
        'reenviada' => 'Reenviada para análise', 'destaque' => 'Destaque alterado', 'bloqueada' => 'Bloqueada',
        'desbloqueada' => 'Desbloqueada', 'cadastro' => 'Cadastro realizado', 'perfil' => 'Perfil atualizado',
        'expirada' => 'Prazo encerrado',
    ];

    public static function rotulo(array $entry): string { return self::ACTION_LABELS[$entry['acao']] ?? $entry['acao']; }
}
