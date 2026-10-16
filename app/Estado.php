<?php

class Estado
{
    public const V_AGUARDANDO = 'aguardando_aprovacao', V_APROVADA = 'aprovada', V_REJEITADA = 'rejeitada', V_ENCERRADA = 'encerrada';
    public const E_PENDENTE = 'pendente', E_APROVADA = 'aprovada', E_REPROVADA = 'reprovada', E_BLOQUEADA = 'bloqueada';

    private const TV = [
        self::V_AGUARDANDO => [self::V_APROVADA, self::V_REJEITADA],
        self::V_APROVADA   => [self::V_ENCERRADA, self::V_AGUARDANDO],
        self::V_REJEITADA  => [self::V_AGUARDANDO],
        self::V_ENCERRADA  => [],
    ];
    private const TE = [
        self::E_PENDENTE  => [self::E_APROVADA, self::E_REPROVADA],
        self::E_APROVADA  => [self::E_BLOQUEADA],
        self::E_BLOQUEADA => [self::E_APROVADA],
        self::E_REPROVADA => [self::E_PENDENTE],
    ];
    public static function vagaPode(string $de, string $para): bool { return in_array($para, self::TV[$de] ?? [], true); }
    public static function empresaPode(string $de, string $para): bool { return in_array($para, self::TE[$de] ?? [], true); }

    public const SITUACOES = ['aguardando', 'agendada', 'publicada', 'expirada', 'encerrada', 'rejeitada'];
    private const ROTULO = ['aguardando' => 'Em análise', 'agendada' => 'Agendada', 'publicada' => 'Publicada', 'expirada' => 'Expirada', 'encerrada' => 'Encerrada', 'rejeitada' => 'Rejeitada'];
    private const TOM = ['aguardando' => 'warn', 'agendada' => 'info', 'publicada' => 'ok', 'expirada' => 'mute', 'encerrada' => 'mute', 'rejeitada' => 'danger'];

    public static function situacaoVaga(array $v, ?string $hoje = null): string
    {
        $hoje = $hoje ?? date('Y-m-d');
        switch ($v['status']) {
            case self::V_AGUARDANDO: return 'aguardando';
            case self::V_REJEITADA:  return 'rejeitada';
            case self::V_ENCERRADA:  return 'encerrada';
        }
        if ($hoje < $v['data_publicacao']) return 'agendada';
        if ($hoje > $v['data_limite']) return 'expirada';
        return 'publicada';
    }
    public static function rotuloSituacao(string $s): string { return self::ROTULO[$s] ?? $s; }
    public static function tomSituacao(string $s): string { return self::TOM[$s] ?? 'mute'; }

    /** Condição SQL equivalente a situacaoVaga(), para filtrar/contar no banco. @return array [sql, params] */
    public static function sqlSituacao(string $sit, string $v = 'v'): array
    {
        $h = ['hoje' => date('Y-m-d')];
        switch ($sit) {
            case 'aguardando': return ["$v.status = '" . self::V_AGUARDANDO . "'", []];
            case 'rejeitada':  return ["$v.status = '" . self::V_REJEITADA . "'", []];
            case 'encerrada':  return ["$v.status = '" . self::V_ENCERRADA . "'", []];
            case 'agendada':   return ["($v.status = '" . self::V_APROVADA . "' AND $v.data_publicacao > :hoje)", $h];
            case 'expirada':   return ["($v.status = '" . self::V_APROVADA . "' AND $v.data_limite < :hoje)", $h];
            case 'publicada':  return ["($v.status = '" . self::V_APROVADA . "' AND $v.data_publicacao <= :hoje AND $v.data_limite >= :hoje)", $h];
        }
        return ['1 = 1', []];
    }

    public const STATUS_EMPRESA = ['pendente' => ['Pendente', 'warn'], 'aprovada' => ['Aprovada', 'ok'], 'reprovada' => ['Reprovada', 'danger'], 'bloqueada' => ['Bloqueada', 'mute']];
}
