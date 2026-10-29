<?php
class Status
{
    public const V_AGUARDANDO = 'aguardando_aprovacao';
    public const V_APROVADA   = 'aprovada';
    public const V_REJEITADA  = 'rejeitada';
    public const V_ENCERRADA  = 'encerrada';
    public const E_PENDENTE   = 'pendente';
    public const E_APROVADA   = 'aprovada';
    public const E_REPROVADA  = 'reprovada';
    public const E_BLOQUEADA  = 'bloqueada';

    private const JOB_TRANSITIONS = [
        self::V_AGUARDANDO => [self::V_APROVADA, self::V_REJEITADA],
        self::V_APROVADA   => [self::V_ENCERRADA, self::V_AGUARDANDO],
        self::V_REJEITADA  => [self::V_AGUARDANDO],
        self::V_ENCERRADA  => [],
    ];
    private const COMPANY_TRANSITIONS = [
        self::E_PENDENTE  => [self::E_APROVADA, self::E_REPROVADA],
        self::E_APROVADA  => [self::E_BLOQUEADA],
        self::E_BLOQUEADA => [self::E_APROVADA],
        self::E_REPROVADA => [self::E_PENDENTE],
    ];

    public static function vagaPode(string $from, string $to): bool   { return in_array($to, self::JOB_TRANSITIONS[$from] ?? [], true); }
    public static function empresaPode(string $from, string $to): bool { return in_array($to, self::COMPANY_TRANSITIONS[$from] ?? [], true); }

    public const SITUACOES = ['aguardando', 'agendada', 'publicada', 'expirada', 'encerrada', 'rejeitada'];
    private const LABELS   = ['aguardando' => 'Em análise', 'agendada' => 'Agendada', 'publicada' => 'Publicada', 'expirada' => 'Expirada', 'encerrada' => 'Encerrada', 'rejeitada' => 'Rejeitada'];
    private const TONES    = ['aguardando' => 'warn', 'agendada' => 'info', 'publicada' => 'ok', 'expirada' => 'mute', 'encerrada' => 'mute', 'rejeitada' => 'danger'];

    public static function situacaoVaga(array $job, ?string $today = null): string
    {
        $today = $today ?? date('Y-m-d');
        switch ($job['status']) {
            case self::V_AGUARDANDO: return 'aguardando';
            case self::V_REJEITADA:  return 'rejeitada';
            case self::V_ENCERRADA:  return 'encerrada';
        }
        if ($today < $job['data_publicacao']) return 'agendada';
        if ($today > $job['data_limite'])     return 'expirada';
        return 'publicada';
    }

    public static function rotuloSituacao(string $s): string { return self::LABELS[$s] ?? $s; }
    public static function tomSituacao(string $s): string    { return self::TONES[$s]  ?? 'mute'; }

    public static function sqlSituacao(string $situation, string $alias = 'v'): array
    {
        $today = ['hoje' => date('Y-m-d')];
        switch ($situation) {
            case 'aguardando': return ["$alias.status = '" . self::V_AGUARDANDO . "'", []];
            case 'rejeitada':  return ["$alias.status = '" . self::V_REJEITADA  . "'", []];
            case 'encerrada':  return ["$alias.status = '" . self::V_ENCERRADA  . "'", []];
            case 'agendada':   return ["($alias.status = '" . self::V_APROVADA . "' AND $alias.data_publicacao > :hoje)", $today];
            case 'expirada':   return ["($alias.status = '" . self::V_APROVADA . "' AND $alias.data_limite < :hoje)", $today];
            case 'publicada':  return ["($alias.status = '" . self::V_APROVADA . "' AND $alias.data_publicacao <= :hoje AND $alias.data_limite >= :hoje)", $today];
        }
        return ['1 = 1', []];
    }

    public const STATUS_EMPRESA = [
        'pendente'  => ['Pendente',  'warn'],
        'aprovada'  => ['Aprovada',  'ok'],
        'reprovada' => ['Reprovada', 'danger'],
        'bloqueada' => ['Bloqueada', 'mute'],
    ];
}
