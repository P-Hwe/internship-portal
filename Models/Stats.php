<?php

class Stats
{
    public static function empresas(): array
    {
        $r = Database::one("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) AS pendente, SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) AS aprovada,
            SUM(CASE WHEN status = 'reprovada' THEN 1 ELSE 0 END) AS reprovada, SUM(CASE WHEN status = 'bloqueada' THEN 1 ELSE 0 END) AS bloqueada FROM empresas");
        return array_map('intval', $r ?: []);
    }
    public static function porSemana(int $n = 8): array
    {
        $ini = strtotime('monday this week') - ($n - 1) * 7 * 86400;
        $b = [];
        for ($i = 0; $i < $n; $i++) {
            $b[$i] = ['rot' => date('d/m', $ini + $i * 7 * 86400), 'empresas' => 0, 'vagas' => 0];
        }
        $desde = date('Y-m-d H:i:s', $ini);
        foreach (['empresas', 'vagas'] as $t) {
            foreach (Database::all("SELECT criado_em FROM $t WHERE criado_em >= :d", ['d' => $desde]) as $r) {
                $i = (int) floor((strtotime($r['criado_em']) - $ini) / (7 * 86400));
                if ($i >= 0 && $i < $n) {
                    $b[$i][$t]++;
                }
            }
        }
        return $b;
    }
    public static function pessoasAcessaram(int $dias = 7): int
    {
        $d = date('Y-m-d H:i:s', time() - $dias * 86400);
        return (int) Database::val("SELECT COUNT(*) FROM (SELECT DISTINCT perfil, usuario_id FROM acessos_log WHERE evento = 'login_ok' AND usuario_id IS NOT NULL AND criado_em >= :d) t", ['d' => $d]);
    }
    public static function pessoasPorDia(int $dias = 7): array
    {
        $b = []; for ($i = $dias - 1; $i >= 0; $i--) $b[date('Y-m-d', time() - $i * 86400)] = ['rot' => date('d/m', time() - $i * 86400), 'pessoas' => 0, 'vistos' => []];
        foreach (Database::all("SELECT perfil, usuario_id, criado_em FROM acessos_log WHERE evento = 'login_ok' AND usuario_id IS NOT NULL AND criado_em >= :d", ['d' => date('Y-m-d 00:00:00', time() - ($dias - 1) * 86400)]) as $r) {
            $k = substr($r['criado_em'], 0, 10); if (!isset($b[$k])) continue;
            $b[$k]['vistos'][$r['perfil'] . ':' . $r['usuario_id']] = true;
        }
        foreach ($b as &$x) { $x['pessoas'] = count($x['vistos']); unset($x['vistos']); }
        return array_values($b);
    }
    public static function contasRestritas(): array
    {
        return ['empresas_bloqueadas' => (int) Database::val("SELECT COUNT(*) FROM empresas WHERE status = 'bloqueada'"), 'admins_inativos' => (int) Database::val('SELECT COUNT(*) FROM admins WHERE ativo = 0'),
            'admins_ativos' => (int) Database::val('SELECT COUNT(*) FROM admins WHERE ativo = 1')];
    }
    public static function empresasRecentes(int $n = 5): array
    {
        return Database::all('SELECT id, nome, status, criado_em FROM empresas ORDER BY id DESC LIMIT ' . (int) $n);
    }
    public static function empresasRecentes(int $n = 5): array { return Database::all('SELECT id, nome, status, criado_em FROM empresas ORDER BY id DESC LIMIT ' . (int) $n); }
}
