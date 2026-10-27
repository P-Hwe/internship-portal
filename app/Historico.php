<?php

class Historico
{
    public static function registrar(string $entidade, int $id, string $acao, ?string $de, ?string $para, string $atorTipo, ?int $atorId, ?string $detalhe = null): void
    {
        Database::insert(
            'historico',
            [
                'entidade' => $entidade,
                'entidade_id' => $id,
                'acao' => $acao,
                'de' => $de,
                'para' => $para,
                'ator_tipo' => $atorTipo,
                'ator_id' => $atorId,
                'detalhe' => $detalhe !== null ? mb_substr($detalhe, 0, 300) : null,
                'criado_em' => Database::agora(),
            ],
        );
    }

    public static function da(string $entidade, int $id, int $n = 30): array
    {
        return Database::all(
            'SELECT * FROM historico WHERE entidade = :e AND entidade_id = :i ORDER BY id DESC LIMIT :limit',
            ['e' => $entidade, 'i' => $id, 'limit' => (int) $n],
        );
    }

    public static function recentes(int $n = 10): array
    {
        return Database::all(
            "SELECT h.*, v.titulo AS v_titulo, e.nome AS e_nome FROM historico h
            LEFT JOIN vagas v ON (h.entidade = 'vaga' AND v.id = h.entidade_id)
            LEFT JOIN empresas e ON (h.entidade = 'empresa' AND e.id = h.entidade_id)
            ORDER BY h.id DESC LIMIT :limit",
            ['limit' => (int) $n],
        );
    }

    public static function ator(): array
    {
        if (!empty($_SESSION['admin_id'])) {
            return ['admin', (int) $_SESSION['admin_id']];
        }

        if (!empty($_SESSION['empresa_id'])) {
            return ['empresa', (int) $_SESSION['empresa_id']];
        }

        return ['sistema', null];
    }

    private const ROT = [
        'criada' => 'Enviada para análise',
        'aprovada' => 'Aprovada',
        'rejeitada' => 'Rejeitada',
        'reprovada' => 'Reprovada',
        'encerrada' => 'Encerrada',
        'editada' => 'Editada (voltou para análise)',
        'reenviada' => 'Reenviada para análise',
        'destaque' => 'Destaque alterado',
        'bloqueada' => 'Bloqueada',
        'desbloqueada' => 'Desbloqueada',
        'cadastro' => 'Cadastro realizado',
        'perfil' => 'Perfil atualizado',
        'expirada' => 'Prazo encerrado',
    ];

    public static function rotulo(array $h): string
    {
        return self::ROT[$h['acao']] ?? $h['acao'];
    }
}
