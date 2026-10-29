<?php
/** Visits — tracks page views, unique visitors, and generates traffic reports. */
class Visits
{
    private const COOKIE = 'vagas_vid';
    // Keys are PT-BR (used as DB/URL values — do not translate)
    public const PAGES   = ['home' => 'Vagas (página inicial)', 'vaga' => 'Detalhe de uma vaga', 'empresas' => 'Empresas parceiras', 'empresa' => 'Página de uma empresa'];
    public const PERIODS = ['semana' => ['Semana', 'últimos 7 dias', '7 dias anteriores'], 'mes' => ['Mês', 'últimos 30 dias', '30 dias anteriores'], 'ano' => ['Ano', 'últimos 12 meses', '12 meses anteriores']];

    public static function windowMin(): int
    {
        $v = env('VISITAS_JANELA_MIN');
        return max(0, ($v !== null && $v !== '') ? (int) $v : (app_env() === 'prod' ? 30 : 1));
    }

    public static function ignoreLoggedIn(): bool
    {
        $v = env('VISITAS_IGNORAR_LOGADOS');
        return ($v !== null && $v !== '') ? $v !== '0' : app_env() === 'prod';
    }

    private static function isBot(): bool
    {
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return $ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|headless|python|curl\/|wget|java\//i', $ua) === 1;
    }

    public static function record(string $type, ?int $ref = null): void
    {
        try {
            if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || self::isBot() || (self::ignoreLoggedIn() && Auth::role())) return;
            $v = (string) ($_COOKIE[self::COOKIE] ?? '');
            if (!preg_match('/^[a-f0-9]{24}$/', $v)) {
                $v = bin2hex(random_bytes(12));
                $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
                if (!headers_sent()) setcookie(self::COOKIE, $v, ['expires' => time() + 365 * 86400, 'path' => BASE_URL, 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
            }
            $since = date('Y-m-d H:i:s', time() - self::windowMin() * 60);
            if (self::windowMin() > 0 && Database::val('SELECT 1 FROM visitas WHERE visitante = :v AND tipo = :t AND ref_id <=> :r AND criado_em >= :d LIMIT 1', ['v' => $v, 't' => $type, 'r' => $ref, 'd' => $since])) return;
            Database::insert('visitas', ['tipo' => $type, 'ref_id' => $ref, 'visitante' => $v, 'criado_em' => Database::now()]);
        } catch (Throwable $t) {
            @file_put_contents(ROOT_PATH . '/storage/erro.log', date('c') . ' Visits::record failed: ' . $t->getMessage() . "\n\n", FILE_APPEND);
        }
    }

    public static function windows(string $period): array
    {
        if (!isset(self::PERIODS[$period])) $period = 'semana';
        if ($period === 'ano') {
            $start = strtotime(date('Y-m-01') . ' -11 months');
            $end   = strtotime(date('Y-m-01') . ' +1 month');
            return ['p' => $period, 'bucket' => 'mes', 'n' => 12,
                'ini' => date('Y-m-d', $start), 'fim' => date('Y-m-d', $end),
                'pini' => date('Y-m-d', strtotime(date('Y-m-d', $start) . ' -12 months')),
                'pfim' => date('Y-m-d', $start)];
        }
        $n     = $period === 'mes' ? 30 : 7;
        $start = strtotime("today -" . ($n - 1) . ' days');
        return ['p' => $period, 'bucket' => 'dia', 'n' => $n,
            'ini' => date('Y-m-d', $start), 'fim' => date('Y-m-d', strtotime('tomorrow')),
            'pini' => date('Y-m-d', $start - $n * 86400), 'pfim' => date('Y-m-d', $start)];
    }

    public static function variation(int $current, int $previous): array
    {
        if ($previous === 0) return [$current > 0 ? 'novo' : 'igual', null];
        $pct = (int) round(($current - $previous) / $previous * 100);
        return [$pct > 0 ? 'sobe' : ($pct < 0 ? 'desce' : 'igual'), $pct];
    }

    private static function totals(string $start, string $end): array
    {
        $p = ['i' => $start, 'f' => $end];
        $r = Database::one("SELECT COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo <> 'saida' AND criado_em >= :i AND criado_em < :f", $p);
        return [
            'visualizacoes' => (int) $r['v'],
            'visitantes'    => (int) $r['u'],
            'saidas'        => (int) Database::val("SELECT COUNT(*) FROM visitas WHERE tipo = 'saida' AND criado_em >= :i AND criado_em < :f", $p),
            'logins'        => (int) Database::val("SELECT COUNT(*) FROM (SELECT DISTINCT perfil, usuario_id FROM acessos_log WHERE evento = 'login_ok' AND usuario_id IS NOT NULL AND criado_em >= :i AND criado_em < :f) t", $p),
        ];
    }

    public static function summary(string $period): array
    {
        $j   = self::windows($period);
        $mes = $j['bucket'] === 'mes';
        $fmt = $mes ? "DATE_FORMAT(criado_em, '%Y-%m')" : 'DATE(criado_em)';
        $serie = function (string $start, string $end) use ($fmt) {
            $out = [];
            foreach (Database::all("SELECT $fmt AS b, COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo <> 'saida' AND criado_em >= :i AND criado_em < :f GROUP BY b", ['i' => $start, 'f' => $end]) as $r) {
                $out[$r['b']] = [(int) $r['v'], (int) $r['u']];
            }
            return $out;
        };
        $a = $serie($j['ini'], $j['fim']);
        $b = $serie($j['pini'], $j['pfim']);
        $months = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        $lines = $linesU = [];
        for ($i = 0; $i < $j['n']; $i++) {
            if ($mes) {
                $t  = strtotime($j['ini'] . " +$i months");
                $tp = strtotime($j['pini'] . " +$i months");
                $ka = date('Y-m', $t); $kb = date('Y-m', $tp);
                $rot = $months[(int) date('n', $t) - 1] . '/' . date('y', $t);
            } else {
                $t  = strtotime($j['ini'] . " +$i days");
                $tp = strtotime($j['pini'] . " +$i days");
                $ka = date('Y-m-d', $t); $kb = date('Y-m-d', $tp);
                $rot = date('d/m', $t);
            }
            $lines[]  = ['rot' => $rot, 'atual' => $a[$ka][0] ?? 0, 'anterior' => $b[$kb][0] ?? 0];
            $linesU[] = ['rot' => $rot, 'atual' => $a[$ka][1] ?? 0, 'anterior' => $b[$kb][1] ?? 0];
        }
        $byPage = function (string $start, string $end) {
            $out = [];
            foreach (Database::all("SELECT tipo, COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo <> 'saida' AND criado_em >= :i AND criado_em < :f GROUP BY tipo", ['i' => $start, 'f' => $end]) as $r) {
                $out[$r['tipo']] = [(int) $r['v'], (int) $r['u']];
            }
            return $out;
        };
        $pa = $byPage($j['ini'], $j['fim']);
        $pb = $byPage($j['pini'], $j['pfim']);
        $pages = [];
        foreach (self::PAGES as $k => $label) $pages[$k] = ['rot' => $label, 'atual' => $pa[$k][0] ?? 0, 'unicos' => $pa[$k][1] ?? 0, 'anterior' => $pb[$k][0] ?? 0];
        return ['janela' => $j, 'atual' => self::totals($j['ini'], $j['fim']), 'anterior' => self::totals($j['pini'], $j['pfim']), 'linhas' => $lines, 'linhasU' => $linesU, 'paginas' => $pages];
    }

    public static function jobRanking(string $period, int $n = 8): array
    {
        $j = self::windows($period);
        $q = fn(string $i, string $f, string $lim) => Database::all(
            "SELECT v.id, v.titulo, v.status, v.destaque, v.data_publicacao, v.data_limite, e.nome AS empresa, COUNT(*) AS views, COUNT(DISTINCT vi.visitante) AS uniq
             FROM visitas vi JOIN vagas v ON v.id = vi.ref_id JOIN empresas e ON e.id = v.empresa_id
             WHERE vi.tipo = 'vaga' AND vi.criado_em >= :i AND vi.criado_em < :f
             GROUP BY v.id, v.titulo, v.status, v.destaque, v.data_publicacao, v.data_limite, e.nome
             ORDER BY views DESC, uniq DESC, v.id DESC$lim", ['i' => $i, 'f' => $f]);
        $prev = [];
        foreach ($q($j['pini'], $j['pfim'], '') as $r) $prev[(int) $r['id']] = (int) $r['views'];
        $out = [];
        foreach ($q($j['ini'], $j['fim'], ' LIMIT ' . (int) $n) as $r) {
            $r['views'] = (int) $r['views']; $r['uniq'] = (int) $r['uniq'];
            $r['anterior'] = $prev[(int) $r['id']] ?? 0;
            $r['situacao'] = Status::situacaoVaga($r);
            $out[] = $r;
        }
        return $out;
    }

    public static function companyRanking(string $period, int $n = 8): array
    {
        $j = self::windows($period);
        $q = fn(string $i, string $f, string $lim) => Database::all(
            "SELECT e.id, e.nome, e.status, e.ultimo_acesso,
               SUM(x.tipo = 'empresa') AS pagina, SUM(x.tipo = 'vaga') AS vagas, SUM(x.tipo = 'saida') AS saidas,
               SUM(x.tipo <> 'saida') AS views, COUNT(DISTINCT CASE WHEN x.tipo <> 'saida' THEN x.visitante END) AS uniq
             FROM (SELECT ref_id AS empresa_id, tipo, visitante, criado_em FROM visitas WHERE tipo IN ('empresa','saida')
                   UNION ALL SELECT v.empresa_id, 'vaga', vi.visitante, vi.criado_em FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga') x
             JOIN empresas e ON e.id = x.empresa_id WHERE x.criado_em >= :i AND x.criado_em < :f
             GROUP BY e.id, e.nome, e.status, e.ultimo_acesso ORDER BY views DESC, uniq DESC, e.id DESC$lim", ['i' => $i, 'f' => $f]);
        $prev = [];
        foreach ($q($j['pini'], $j['pfim'], '') as $r) $prev[(int) $r['id']] = (int) $r['views'];
        $out = [];
        foreach ($q($j['ini'], $j['fim'], ' LIMIT ' . (int) $n) as $r) {
            foreach (['pagina', 'vagas', 'saidas', 'views', 'uniq'] as $k) $r[$k] = (int) $r[$k];
            $r['anterior'] = $prev[(int) $r['id']] ?? 0;
            $out[] = $r;
        }
        return $out;
    }

    public static function detail(string $type, int $id): array
    {
        $isCompany = $type === 'empresa';
        $since = fn(int $days) => date('Y-m-d H:i:s', strtotime('today') - ($days - 1) * 86400);
        $filter = $isCompany
            ? "(SELECT visitante, criado_em FROM visitas WHERE tipo = 'empresa' AND ref_id = :id UNION ALL SELECT vi.visitante, vi.criado_em FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga' AND v.empresa_id = :id2) t"
            : "(SELECT visitante, criado_em FROM visitas WHERE tipo = 'vaga' AND ref_id = :id) t";
        $p = fn(array $x = []) => $isCompany ? ['id' => $id, 'id2' => $id] + $x : ['id' => $id] + $x;
        $count = function (?string $from) use ($filter, $p) {
            $r = Database::one("SELECT COUNT(*) AS v, COUNT(DISTINCT visitante) AS u, MAX(criado_em) AS ult FROM $filter" . ($from ? ' WHERE criado_em >= :d' : ''), $p($from ? ['d' => $from] : []));
            return ['views' => (int) $r['v'], 'unicos' => (int) $r['u'], 'ultima' => $r['ult']];
        };
        $d7 = $count($since(7)); $d30 = $count($since(30)); $all = $count(null);
        $days = [];
        for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime('today') - $i * 86400)] = ['rot' => date('d/m', strtotime('today') - $i * 86400), 'visitas' => 0];
        foreach (Database::all("SELECT DATE(criado_em) AS d, COUNT(*) AS c FROM $filter WHERE criado_em >= :d GROUP BY DATE(criado_em)", $p(['d' => $since(14)])) as $r) {
            if (isset($days[$r['d']])) $days[$r['d']]['visitas'] = (int) $r['c'];
        }
        $rank = $isCompany
            ? Database::all("SELECT x AS id, SUM(c) AS c FROM (SELECT ref_id AS x, COUNT(*) AS c FROM visitas WHERE tipo = 'empresa' AND criado_em >= :a GROUP BY ref_id UNION ALL SELECT v.empresa_id AS x, COUNT(*) AS c FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga' AND vi.criado_em >= :b GROUP BY v.empresa_id) z GROUP BY x ORDER BY c DESC", ['a' => $since(30), 'b' => $since(30)])
            : Database::all("SELECT ref_id AS id, COUNT(*) AS c FROM visitas WHERE tipo = 'vaga' AND criado_em >= :a GROUP BY ref_id ORDER BY c DESC", ['a' => $since(30)]);
        $pos = null;
        foreach ($rank as $k => $r) { if ((int) $r['id'] === $id) { $pos = $k + 1; break; } }
        $exits = $isCompany ? (int) Database::val("SELECT COUNT(*) FROM visitas WHERE tipo = 'saida' AND ref_id = :id AND criado_em >= :d", ['id' => $id, 'd' => $since(30)]) : null;
        return ['d7' => $d7, 'd30' => $d30, 'tudo' => $all, 'dias' => array_values($days), 'posicao' => $pos, 'de' => count($rank), 'saidas' => $exits, 'tipo' => $type];
    }
}
