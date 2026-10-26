<?php
class Visitas
{
    private const COOKIE = 'vagas_vid';
    public const PAGINAS = ['home' => 'Vagas (página inicial)', 'vaga' => 'Detalhe de uma vaga', 'empresas' => 'Empresas parceiras', 'empresa' => 'Página de uma empresa'];
    public const PERIODOS = ['semana' => ['Semana', 'últimos 7 dias', '7 dias anteriores'], 'mes' => ['Mês', 'últimos 30 dias', '30 dias anteriores'], 'ano' => ['Ano', 'últimos 12 meses', '12 meses anteriores']];

    public static function janelaMin(): int
    {
        $v = env('VISITAS_JANELA_MIN');
        return max(0, ($v !== null && $v !== '') ? (int) $v : (app_env() === 'prod' ? 30 : 1));
    }
    public static function ignorarLogados(): bool
    {
        $v = env('VISITAS_IGNORAR_LOGADOS');
        return ($v !== null && $v !== '') ? $v !== '0' : app_env() === 'prod';
    }
    private static function ehRobo(): bool
    {
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return $ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|headless|python|curl\/|wget|java\//i', $ua) === 1;
    }

    public static function registrar(string $tipo, ?int $ref = null): void
    {
        try {
            if (PHP_SAPI === 'cli' || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || self::ehRobo() || (self::ignorarLogados() && Auth::perfil())) return;
            $v = (string) ($_COOKIE[self::COOKIE] ?? '');
            if (!preg_match('/^[a-f0-9]{24}$/', $v)) {
                $v = bin2hex(random_bytes(12));
                $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
                if (!headers_sent()) setcookie(self::COOKIE, $v, ['expires' => time() + 365 * 86400, 'path' => BASE_URL, 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
            }
            $desde = date('Y-m-d H:i:s', time() - self::janelaMin() * 60);
            if (self::janelaMin() > 0 && Database::val('SELECT 1 FROM visitas WHERE visitante = :v AND tipo = :t AND ref_id <=> :r AND criado_em >= :d LIMIT 1', ['v' => $v, 't' => $tipo, 'r' => $ref, 'd' => $desde])) return;
            Database::insert('visitas', ['tipo' => $tipo, 'ref_id' => $ref, 'visitante' => $v, 'criado_em' => Database::agora()]);
        } catch (Throwable $t) {
            @file_put_contents(ROOT_PATH . '/storage/erro.log', date('c') . ' Visitas::registrar falhou: ' . $t->getMessage() . "\n\n", FILE_APPEND);
        }
    }
    public static function janelas(string $p): array
    {
        if (!isset(self::PERIODOS[$p])) $p = 'semana';
        if ($p === 'ano') {
            $ini = strtotime(date('Y-m-01') . ' -11 months');
            $fim = strtotime(date('Y-m-01') . ' +1 month');
            return ['p' => $p, 'bucket' => 'mes', 'n' => 12, 'ini' => date('Y-m-d', $ini), 'fim' => date('Y-m-d', $fim), 'pini' => date('Y-m-d', strtotime(date('Y-m-d', $ini) . ' -12 months')), 'pfim' => date('Y-m-d', $ini)];
        }
        $n = $p === 'mes' ? 30 : 7;
        $ini = strtotime("today -" . ($n - 1) . ' days');
        return ['p' => $p, 'bucket' => 'dia', 'n' => $n, 'ini' => date('Y-m-d', $ini), 'fim' => date('Y-m-d', strtotime('tomorrow')), 'pini' => date('Y-m-d', $ini - $n * 86400), 'pfim' => date('Y-m-d', $ini)];
    }
    public static function variacao(int $atual, int $anterior): array
    {
        if ($anterior === 0) return [$atual > 0 ? 'novo' : 'igual', null];
        $pct = (int) round(($atual - $anterior) / $anterior * 100);
        return [$pct > 0 ? 'sobe' : ($pct < 0 ? 'desce' : 'igual'), $pct];
    }

    private static function totais(string $ini, string $fim): array
    {
        $p = ['i' => $ini, 'f' => $fim];
        $r = Database::one("SELECT COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo <> 'saida' AND criado_em >= :i AND criado_em < :f", $p);
        return [
            'visualizacoes' => (int) $r['v'],
            'visitantes' => (int) $r['u'],
            'saidas' => (int) Database::val("SELECT COUNT(*) FROM visitas WHERE tipo = 'saida' AND criado_em >= :i AND criado_em < :f", $p),
            'logins' => (int) Database::val("SELECT COUNT(*) FROM (SELECT DISTINCT perfil, usuario_id FROM acessos_log WHERE evento = 'login_ok' AND usuario_id IS NOT NULL AND criado_em >= :i AND criado_em < :f) t", $p),
        ];
    }

    public static function resumo(string $periodo): array
    {
        $j = self::janelas($periodo);
        $mes = $j['bucket'] === 'mes';
        $fmt = $mes ? "DATE_FORMAT(criado_em, '%Y-%m')" : 'DATE(criado_em)';
        $serie = function (string $ini, string $fim) use ($fmt) {
            $o = [];
            foreach (Database::all("SELECT $fmt AS b, COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo <> 'saida' AND criado_em >= :i AND criado_em < :f GROUP BY b", ['i' => $ini, 'f' => $fim]) as $r) $o[$r['b']] = [(int) $r['v'], (int) $r['u']];
            return $o;
        };
        $a = $serie($j['ini'], $j['fim']);
        $b = $serie($j['pini'], $j['pfim']);
        $meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        $linhas = [];
        $linhasU = [];
        for ($i = 0; $i < $j['n']; $i++) {
            if ($mes) {
                $t = strtotime($j['ini'] . " +$i months");
                $tp = strtotime($j['pini'] . " +$i months");
                $ka = date('Y-m', $t);
                $kb = date('Y-m', $tp);
                $rot = $meses[(int) date('n', $t) - 1] . '/' . date('y', $t);
            } else {
                $t = strtotime($j['ini'] . " +$i days");
                $tp = strtotime($j['pini'] . " +$i days");
                $ka = date('Y-m-d', $t);
                $kb = date('Y-m-d', $tp);
                $rot = date('d/m', $t);
            }
            $linhas[] = ['rot' => $rot, 'atual' => $a[$ka][0] ?? 0, 'anterior' => $b[$kb][0] ?? 0];
            $linhasU[] = ['rot' => $rot, 'atual' => $a[$ka][1] ?? 0, 'anterior' => $b[$kb][1] ?? 0];
        }
        $pagina = function (string $ini, string $fim) {
            $o = [];
            foreach (Database::all("SELECT tipo, COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo <> 'saida' AND criado_em >= :i AND criado_em < :f GROUP BY tipo", ['i' => $ini, 'f' => $fim]) as $r) $o[$r['tipo']] = [(int) $r['v'], (int) $r['u']];
            return $o;
        };
        $pa = $pagina($j['ini'], $j['fim']);
        $pb = $pagina($j['pini'], $j['pfim']);
        $paginas = [];
        foreach (self::PAGINAS as $k => $rot) $paginas[$k] = ['rot' => $rot, 'atual' => $pa[$k][0] ?? 0, 'unicos' => $pa[$k][1] ?? 0, 'anterior' => $pb[$k][0] ?? 0];
        return ['janela' => $j, 'atual' => self::totais($j['ini'], $j['fim']), 'anterior' => self::totais($j['pini'], $j['pfim']), 'linhas' => $linhas, 'linhasU' => $linhasU, 'paginas' => $paginas];
    }

    public static function rankingVagas(string $periodo, int $n = 8): array
    {
        $j = self::janelas($periodo);
        $q = fn(string $i, string $f, string $lim) => Database::all("SELECT v.id, v.titulo, v.status, v.destaque, v.data_publicacao, v.data_limite, e.nome AS empresa, COUNT(*) AS views, COUNT(DISTINCT vi.visitante) AS uniq
            FROM visitas vi JOIN vagas v ON v.id = vi.ref_id JOIN empresas e ON e.id = v.empresa_id
            WHERE vi.tipo = 'vaga' AND vi.criado_em >= :i AND vi.criado_em < :f
            GROUP BY v.id, v.titulo, v.status, v.destaque, v.data_publicacao, v.data_limite, e.nome ORDER BY views DESC, uniq DESC, v.id DESC$lim", ['i' => $i, 'f' => $f]);
        $ant = [];
        foreach ($q($j['pini'], $j['pfim'], '') as $r) $ant[(int) $r['id']] = (int) $r['views'];
        $o = [];
        foreach ($q($j['ini'], $j['fim'], ' LIMIT ' . (int) $n) as $r) {
            $r['views'] = (int) $r['views'];
            $r['uniq'] = (int) $r['uniq'];
            $r['anterior'] = $ant[(int) $r['id']] ?? 0;
            $r['situacao'] = Estado::situacaoVaga($r);
            $o[] = $r;
        }
        return $o;
    }
    public static function rankingEmpresas(string $periodo, int $n = 8): array
    {
        $j = self::janelas($periodo);
        $q = fn(string $i, string $f, string $lim) => Database::all("SELECT e.id, e.nome, e.status, e.ultimo_acesso,
              SUM(x.tipo = 'empresa') AS pagina, SUM(x.tipo = 'vaga') AS vagas, SUM(x.tipo = 'saida') AS saidas, SUM(x.tipo <> 'saida') AS views,
              COUNT(DISTINCT CASE WHEN x.tipo <> 'saida' THEN x.visitante END) AS uniq
            FROM (SELECT ref_id AS empresa_id, tipo, visitante, criado_em FROM visitas WHERE tipo IN ('empresa', 'saida')
                  UNION ALL
                  SELECT v.empresa_id, 'vaga', vi.visitante, vi.criado_em FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga') x
            JOIN empresas e ON e.id = x.empresa_id WHERE x.criado_em >= :i AND x.criado_em < :f
            GROUP BY e.id, e.nome, e.status, e.ultimo_acesso ORDER BY views DESC, uniq DESC, e.id DESC$lim", ['i' => $i, 'f' => $f]);
        $ant = [];
        foreach ($q($j['pini'], $j['pfim'], '') as $r) $ant[(int) $r['id']] = (int) $r['views'];
        $o = [];
        foreach ($q($j['ini'], $j['fim'], ' LIMIT ' . (int) $n) as $r) {
            foreach (['pagina', 'vagas', 'saidas', 'views', 'uniq'] as $k) $r[$k] = (int) $r[$k];
            $r['anterior'] = $ant[(int) $r['id']] ?? 0;
            $o[] = $r;
        }
        return $o;
    }

    public static function doItem(string $tipo, int $id, int $dias = 30): array
    {
        $d = date('Y-m-d H:i:s', time() - $dias * 86400);
        if ($tipo === 'empresa') {
            $r = Database::one("SELECT COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM (SELECT visitante FROM visitas WHERE tipo = 'empresa' AND ref_id = :id AND criado_em >= :d
                UNION ALL SELECT vi.visitante FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga' AND v.empresa_id = :id2 AND vi.criado_em >= :d2) t", ['id' => $id, 'd' => $d, 'id2' => $id, 'd2' => $d]);
        } else {
            $r = Database::one("SELECT COUNT(*) AS v, COUNT(DISTINCT visitante) AS u FROM visitas WHERE tipo = 'vaga' AND ref_id = :id AND criado_em >= :d", ['id' => $id, 'd' => $d]);
        }
        return [(int) $r['v'], (int) $r['u']];
    }

    public static function detalhe(string $tipo, int $id): array
    {
        $emp = $tipo === 'empresa';
        $de = fn(int $dias) => date('Y-m-d H:i:s', strtotime('today') - ($dias - 1) * 86400);
        $filtro = $emp
            ? "(SELECT visitante, criado_em FROM visitas WHERE tipo = 'empresa' AND ref_id = :id UNION ALL SELECT vi.visitante, vi.criado_em FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga' AND v.empresa_id = :id2) t"
            : "(SELECT visitante, criado_em FROM visitas WHERE tipo = 'vaga' AND ref_id = :id) t";
        $p = fn(array $x = []) => $emp ? ['id' => $id, 'id2' => $id] + $x : ['id' => $id] + $x;
        $quanto = function (?string $desde) use ($filtro, $p) {
            $r = Database::one("SELECT COUNT(*) AS v, COUNT(DISTINCT visitante) AS u, MAX(criado_em) AS ult FROM $filtro" . ($desde ? ' WHERE criado_em >= :d' : ''), $p($desde ? ['d' => $desde] : []));
            return ['views' => (int) $r['v'], 'unicos' => (int) $r['u'], 'ultima' => $r['ult']];
        };
        $d7 = $quanto($de(7));
        $d30 = $quanto($de(30));
        $tudo = $quanto(null);
        $dias = [];
        for ($i = 13; $i >= 0; $i--) $dias[date('Y-m-d', strtotime('today') - $i * 86400)] = ['rot' => date('d/m', strtotime('today') - $i * 86400), 'visitas' => 0];
        foreach (Database::all("SELECT DATE(criado_em) AS d, COUNT(*) AS c FROM $filtro WHERE criado_em >= :d GROUP BY DATE(criado_em)", $p(['d' => $de(14)])) as $r) if (isset($dias[$r['d']])) $dias[$r['d']]['visitas'] = (int) $r['c'];
        $rank = $emp
            ? Database::all("SELECT x AS id, SUM(c) AS c FROM (SELECT ref_id AS x, COUNT(*) AS c FROM visitas WHERE tipo = 'empresa' AND criado_em >= :a GROUP BY ref_id UNION ALL SELECT v.empresa_id AS x, COUNT(*) AS c FROM visitas vi JOIN vagas v ON v.id = vi.ref_id WHERE vi.tipo = 'vaga' AND vi.criado_em >= :b GROUP BY v.empresa_id) z GROUP BY x ORDER BY c DESC", ['a' => $de(30), 'b' => $de(30)])
            : Database::all("SELECT ref_id AS id, COUNT(*) AS c FROM visitas WHERE tipo = 'vaga' AND criado_em >= :a GROUP BY ref_id ORDER BY c DESC", ['a' => $de(30)]);
        $pos = null;
        foreach ($rank as $k => $r) if ((int) $r['id'] === $id) {
            $pos = $k + 1;
            break;
        }
        $saidas = $emp ? (int) Database::val("SELECT COUNT(*) FROM visitas WHERE tipo = 'saida' AND ref_id = :id AND criado_em >= :d", ['id' => $id, 'd' => $de(30)]) : null;
        return ['d7' => $d7, 'd30' => $d30, 'tudo' => $tudo, 'dias' => array_values($dias), 'posicao' => $pos, 'de' => count($rank), 'saidas' => $saidas, 'tipo' => $tipo];
    }
}