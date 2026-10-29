<?php
/** Notification — in-app notifications for the admin panel (new companies / job listings). */
class Notification
{
    public static function naoLidas(): int { return (int) Database::val('SELECT COUNT(*) FROM notificacoes WHERE lida = 0'); }

    public static function recentes(int $n = 8): array
    {
        return Database::all("SELECT n.*, e.status AS e_status, v.status AS v_status FROM notificacoes n
            LEFT JOIN empresas e ON n.tipo = 'empresa' AND e.id = n.referencia_id
            LEFT JOIN vagas v ON n.tipo = 'vaga' AND v.id = n.referencia_id
            ORDER BY n.id DESC LIMIT " . (int) $n);
    }

    public static function porId(int $id): ?array { return Database::one('SELECT * FROM notificacoes WHERE id = :i', ['i' => $id]) ?: null; }
    public static function marcarLidas(): void     { Database::run('UPDATE notificacoes SET lida = 1 WHERE lida = 0'); }
    public static function marcarLida(int $id): void { Database::run('UPDATE notificacoes SET lida = 1 WHERE id = :i', ['i' => $id]); }

    public static function destino(array $n): array
    {
        return $n['tipo'] === 'vaga'
            ? ['admin-vaga',    ['id' => (int) $n['referencia_id']]]
            : ['admin-empresa', ['id' => (int) $n['referencia_id']]];
    }

    public static function estado(array $n): array
    {
        if ($n['tipo'] === 'vaga') {
            $s = $n['v_status'] ?? null;
            if ($s === null) return ['Removida', 'mute', true];
            if ($s === Status::V_AGUARDANDO) return ['Aguardando análise', 'warn', false];
            return [['aprovada' => 'Aprovada', 'rejeitada' => 'Reprovada', 'encerrada' => 'Encerrada'][$s] ?? $s,
                    ['aprovada' => 'ok', 'rejeitada' => 'danger'][$s] ?? 'mute', true];
        }
        $s = $n['e_status'] ?? null;
        if ($s === null) return ['Removida', 'mute', true];
        if ($s === Status::E_PENDENTE) return ['Aguardando análise', 'warn', false];
        return [Status::STATUS_EMPRESA[$s][0] ?? $s, Status::STATUS_EMPRESA[$s][1] ?? 'mute', true];
    }
}
