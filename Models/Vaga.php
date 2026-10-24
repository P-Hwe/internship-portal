<?php

class Vaga
{
    private const SEL = 'v.*, e.nome AS e_nome, e.cnpj AS e_cnpj, e.endereco AS e_endereco, e.email AS e_email, e.telefone AS e_telefone, e.site AS e_site, e.linkedin AS e_linkedin,
                         e.status AS e_status, e.ultimo_acesso AS e_acesso, e.logo AS e_logo, e.segmento AS e_segmento, e.cidade AS e_cidade, e.uf AS e_uf, e.link_principal AS e_lp';
    private const FROM = 'FROM vagas v JOIN empresas e ON e.id = v.empresa_id';
    private const CONTEUDO = ['titulo', 'tipo', 'area', 'modalidade', 'local', 'bolsa', 'carga_horas_dia', 'carga_dias', 'carga_turno', 'descricao', 'atividades', 'requisitos',
        'requisitos_desejaveis', 'beneficios', 'data_publicacao', 'data_limite', 'contato', 'imagem', 'link_site', 'link_linkedin', 'link_candidatura', 'link_plataforma'];

    public static function linhas(?string $t): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $t)), 'strlen'));
    }
    public static function normalizarLista(?string $t): string
    {
        $o = [];
        foreach (self::linhas($t) as $x) {
            $x = mb_substr($x, 0, 120);
            if (!in_array(mb_strtolower($x), array_map('mb_strtolower', $o), true)) {
                $o[] = $x;
            }
        }
        return implode("\n", array_slice($o, 0, 20));
    }
    public static function cargaTexto(?int $horas, ?string $dias, ?string $turno): string
    {
        $p = [];
        if ($horas) {
            $p[] = $horas . 'h/dia';
        }
        $sel = array_values(array_intersect(array_keys(DIAS_SEMANA), array_filter(explode(',', (string) $dias))));   // na ordem da semana
        if ($sel) {
            $todos = array_keys(DIAS_SEMANA);
            $ini = array_search($sel[0], $todos, true);
            $continuo = count($sel) > 2 && array_slice($todos, $ini, count($sel)) === $sel;
            $p[] = $continuo ? DIAS_SEMANA[$sel[0]] . ' a ' . DIAS_SEMANA[end($sel)] : implode(', ', array_map(fn($x) => DIAS_SEMANA[$x], $sel));
        }
        if ($turno && isset(TURNOS[$turno])) {
            $p[] = TURNOS[$turno];
        }
        return implode(' · ', $p);
    }

    private static function hidratar(array $r): array
    {
        $r['empresa'] = ['id' => $r['empresa_id'], 'nome' => $r['e_nome'], 'cnpj' => $r['e_cnpj'], 'endereco' => $r['e_endereco'], 'email' => $r['e_email'], 'telefone' => $r['e_telefone'],
            'site' => $r['e_site'], 'linkedin' => $r['e_linkedin'], 'status' => $r['e_status'], 'ultimo_acesso' => $r['e_acesso'], 'logo' => $r['e_logo'], 'segmento' => $r['e_segmento'],
            'cidade' => $r['e_cidade'], 'uf' => $r['e_uf'], 'link_principal' => $r['e_lp']];
        foreach (['e_nome', 'e_cnpj', 'e_endereco', 'e_email', 'e_telefone', 'e_site', 'e_linkedin', 'e_status', 'e_acesso', 'e_logo', 'e_segmento', 'e_cidade', 'e_uf', 'e_lp'] as $k) {
            unset($r[$k]);
        }
        foreach (['atividades', 'requisitos', 'requisitos_desejaveis', 'beneficios'] as $c) {
            $r[$c] = self::linhas($r[$c] ?? '');
        }
        $r['resumo'] = description_resume($r['descricao']);          // breve descrição = primeiras linhas da descrição
        $r['bolsa_txt'] = money($r['bolsa']);
        $r['area_exibicao'] = $r['area'];
        $r['situacao'] = Estado::situacaoVaga($r);                    // calculada pelas datas, no backend
        $r['dias'] = remaining_days($r['data_limite']);
        $r['carga_txt'] = self::cargaTexto($r['carga_horas_dia'] !== null ? (int) $r['carga_horas_dia'] : null, $r['carga_dias'], $r['carga_turno']) ?: ($r['carga_horaria'] ?: '');
        return $r;
    }

    public static function publicas(): array
    {
        [$sit, $p] = Estado::sqlSituacao('publicada');
        $rows = Database::all('SELECT ' . self::SEL . ' ' . self::FROM . " WHERE $sit AND e.status = 'aprovada' ORDER BY v.data_publicacao DESC, v.id DESC", $p);
        return self::marcar(array_map([self::class, 'hidratar'], $rows));
    }
    public static function encerradasPublico(int $n = 60): array
    {
        $desde = date('Y-m-d', time() - 120 * 86400);
        $rows = Database::all('SELECT ' . self::SEL . ' ' . self::FROM . " WHERE e.status = 'aprovada' AND v.data_limite >= :d AND (v.status = 'encerrada' OR (v.status = 'aprovada' AND v.data_limite < :h)) ORDER BY v.data_limite DESC LIMIT " . (int) $n, ['d' => $desde, 'h' => date('Y-m-d')]);
        return array_map([self::class, 'hidratar'], $rows);
    }
    private static function marcar(array $l): array
    {
        $padroes = array_column(Database::all('SELECT nome FROM areas WHERE padrao = 1'), 'nome');
        $cont = array_count_values(array_column($l, 'area'));
        $sal = array_filter(array_column($l, 'bolsa', 'id'), fn($b) => $b !== null);
        arsort($sal);
        $top = count($sal) >= 4 ? array_map('intval', array_slice(array_keys($sal), 0, 5)) : [];
        $limiteAtivo = time() - DIAS_RECRUTADOR_ATIVO * 86400;
        foreach ($l as &$v) {
            $v['area_exibicao'] = (in_array($v['area'], $padroes, true) || ($cont[$v['area']] ?? 0) >= AREA_MIN_VAGAS) ? $v['area'] : 'Outras';
            $v['recrutador_ativo'] = $v['empresa']['ultimo_acesso'] && strtotime($v['empresa']['ultimo_acesso']) >= $limiteAtivo;
            $v['melhor_salario'] = in_array((int) $v['id'], $top, true);
            $v['selos'] = self::selos($v);
        }
        return $l;
    }
    public static function selos(array $v): array
    {
        $s = ['tipo' => [$v['tipo'], 'vg-tag-tipo']];
        $d = remaining_days($v['data_limite']);
        if ($d <= DIAS_VAGA_VENCENDO) {
            $s['vencendo'] = [$d <= 0 ? 'Encerra hoje' : 'Encerra em ' . $d . ($d === 1 ? ' dia' : ' dias'), 'vg-tag-warn'];
        }
        if (job_is_new($v)) {
            $s['nova'] = ['Nova', 'vg-tag-new'];
        }
        if (!empty($v['destaque'])) {
            $s['destaque'] = ['Destaque', 'vg-tag-star'];
        }
        if (!empty($v['melhor_salario'])) {
            $s['salario'] = ['Melhor salário', 'vg-tag-money'];
        }
        if (!empty($v['recrutador_ativo'])) {
            $s['ativo'] = ['Recrutador ativo', 'vg-tag-hot'];
        }
        return $s;
    }
    public static function destaques(array $publicas): array
    {
        return array_slice(array_values(array_filter($publicas, fn($v) => !empty($v['destaque']))), 0, CARROSSEL_MAX);
    }

    public static function visivelPorId(int $id): ?array
    {
        $v = self::porId($id);
        if (!$v || $v['empresa']['status'] !== Estado::E_APROVADA) {
            return null;
        }
        if (!in_array($v['situacao'], ['publicada', 'expirada', 'encerrada'], true)) {
            return null;
        }
        if ($v['situacao'] === 'publicada') {
            foreach (self::publicas() as $p) {
                if ((int) $p['id'] === $id) {
                    return $p;
                }
            }
        }
        return $v;
    }
    public static function existe(int $id): bool
    {
        return (bool) Database::val('SELECT 1 FROM vagas WHERE id = :i', ['i' => $id]);
    }
    public static function relacionadas(array $vaga, array $publicas, int $n = 3): array
    {
        $o = array_values(array_filter($publicas, fn($v) => (int) $v['id'] !== (int) $vaga['id']));
        usort($o, fn($a, $b) => ($b['area'] === $vaga['area']) <=> ($a['area'] === $vaga['area']));
        return array_slice($o, 0, $n);
    }
    public static function areasFiltro(array $publicas): array
    {
        $a = array_unique(array_column($publicas, 'area_exibicao'));
        sort($a, SORT_LOCALE_STRING);
        return array_values(array_merge(array_diff($a, ['Outras']), in_array('Outras', $a, true) ? ['Outras'] : []));
    }

    public static function porId(int $id): ?array
    {
        $r = Database::one('SELECT ' . self::SEL . ' ' . self::FROM . ' WHERE v.id = :i', ['i' => $id]);
        return $r ? self::hidratar($r) : null;
    }
    public static function daEmpresaPorId(int $empId, int $id): ?array
    {
        $v = self::porId($id);
        return ($v && (int) $v['empresa_id'] === $empId) ? $v : null;
    }

    public static function contagens(?int $empId = null): array
    {
        $h = ['hoje' => date('Y-m-d')];
        $w = $empId ? 'WHERE empresa_id = :e' : '';
        $p = $h + ($empId ? ['e' => $empId] : []);
        $r = Database::one("SELECT COUNT(*) AS todas,
            SUM(CASE WHEN status = 'aguardando_aprovacao' THEN 1 ELSE 0 END) AS aguardando,
            SUM(CASE WHEN status = 'aprovada' AND data_publicacao > :hoje THEN 1 ELSE 0 END) AS agendada,
            SUM(CASE WHEN status = 'aprovada' AND data_publicacao <= :hoje AND data_limite >= :hoje THEN 1 ELSE 0 END) AS publicada,
            SUM(CASE WHEN status = 'aprovada' AND data_publicacao <= :hoje AND data_limite >= :hoje AND destaque = 1 THEN 1 ELSE 0 END) AS destaque,
            SUM(CASE WHEN status = 'aprovada' AND data_limite < :hoje THEN 1 ELSE 0 END) AS expirada,
            SUM(CASE WHEN status = 'encerrada' THEN 1 ELSE 0 END) AS encerrada,
            SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) AS rejeitada
            FROM vagas $w", $p);
        return array_map('intval', $r ?: []);
    }

    public static function listar(?int $empId, string $aba, string $busca, string $tipo, string $ordem, int $pg, int $por = POR_PAGINA, ?int $soEmpresa = null): array
    {
        $w = ['1 = 1'];
        $p = [];
        if ($empId) {
            $w[] = 'v.empresa_id = :emp';
            $p['emp'] = $empId;
        }
        if ($aba === 'destaque') {
            [$s, $sp] = Estado::sqlSituacao('publicada');
            $w[] = "$s AND v.destaque = 1";
            $p += $sp;
        } elseif (in_array($aba, Estado::SITUACOES, true)) {
            [$s, $sp] = Estado::sqlSituacao($aba);
            $w[] = $s;
            $p += $sp;
        }
        if ($busca !== '') {
            $w[] = '(LOWER(v.titulo) LIKE :q OR LOWER(e.nome) LIKE :q OR LOWER(v.codigo) LIKE :q OR LOWER(v.area) LIKE :q)';
            $p['q'] = '%' . mb_strtolower($busca) . '%';
        }
        if ($tipo !== '' && in_array($tipo, TIPOS_VAGA, true)) {
            $w[] = 'v.tipo = :tipo';
            $p['tipo'] = $tipo;
        }
        $where = implode(' AND ', $w);
        $ord = ['recentes' => 'v.id DESC', 'prazo' => 'v.data_limite ASC, v.id DESC', 'titulo' => 'v.titulo ASC', 'empresa' => 'e.nome ASC, v.id DESC'][$ordem] ?? 'v.id DESC';
        $total = (int) Database::val('SELECT COUNT(*) ' . self::FROM . " WHERE $where", $p);
        [$off] = Paginador::calc($total, $pg, $por);
        $rows = Database::all('SELECT ' . self::SEL . ' ' . self::FROM . " WHERE $where ORDER BY $ord LIMIT " . (int) $por . ' OFFSET ' . (int) $off, $p);
        return [array_map([self::class, 'hidratar'], $rows), $total];
    }
    public static function todasDaEmpresa(int $empId): array
    {
        return array_map([self::class, 'hidratar'], Database::all('SELECT ' . self::SEL . ' ' . self::FROM . ' WHERE v.empresa_id = :e ORDER BY v.id DESC', ['e' => $empId]));
    }
    public static function areas(): array
    {
        return array_column(Database::all('SELECT nome FROM areas ORDER BY padrao DESC, nome'), 'nome');
    }

    private static function semAcento(string $s): string
    {
        return mb_strtolower(strtr(trim($s), ['á' => 'a','à' => 'a','ã' => 'a','â' => 'a','é' => 'e','ê' => 'e','í' => 'i','ó' => 'o','ô' => 'o','õ' => 'o','ú' => 'u','ç' => 'c','Á' => 'a','À' => 'a','Ã' => 'a','Â' => 'a','É' => 'e','Ê' => 'e','Í' => 'i','Ó' => 'o','Ô' => 'o','Õ' => 'o','Ú' => 'u','Ç' => 'c']));
    }
    public static function resolverArea(string $escolha, string $outra): array
    {
        if ($escolha !== AREA_OUTRA) {
            foreach (self::areas() as $a) {
                if ($a === $escolha) {
                    return [$a, null];
                }
            } return [null, 'Escolha uma área.'];
        }
        $nome = preg_replace('/\s+/u', ' ', trim($outra));
        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 60) {
            return [null, 'Informe o nome da área (3 a 60 caracteres).'];
        }
        $nome = mb_strtoupper(mb_substr($nome, 0, 1)) . mb_substr($nome, 1);
        foreach (self::areas() as $a) {
            if (self::semAcento($a) === self::semAcento($nome)) {
                return [$a, null];
            }
        }
        Database::insert('areas', ['nome' => $nome, 'padrao' => 0]);
        return [$nome, null];
    }

    private static function dadosGravar(array $d): array
    {
        $carga = self::cargaTexto($d['carga_horas_dia'] ?? null, $d['carga_dias'] ?? null, $d['carga_turno'] ?? null);
        $n = fn($k) => (($d[$k] ?? '') !== '') ? $d[$k] : null;
        return [
            'titulo' => $d['titulo'], 'tipo' => $d['tipo'], 'area' => $d['area'], 'modalidade' => $d['modalidade'], 'local' => $d['local'], 'bolsa' => $d['bolsa'],
            'carga_horas_dia' => $n('carga_horas_dia'), 'carga_dias' => $n('carga_dias'), 'carga_turno' => $n('carga_turno'), 'carga_horaria' => $carga ?: null,
            'descricao' => $d['descricao'], 'atividades' => $d['atividades'] ?? '', 'requisitos' => $d['requisitos'] ?? '', 'requisitos_desejaveis' => $d['requisitos_desejaveis'] ?? '',
            'beneficios' => $d['beneficios'] ?? '', 'data_publicacao' => $d['data_publicacao'], 'data_limite' => $d['data_limite'], 'contato' => $d['contato'] ?? '', 'imagem' => $d['imagem'] ?? null,
            'link_site' => $n('link_site'), 'link_linkedin' => $n('link_linkedin'), 'link_candidatura' => $n('link_candidatura'), 'link_plataforma' => $n('link_plataforma'),
        ];
    }
    public static function criar(array $d, int $empId): int
    {
        $id = Database::insert('vagas', self::dadosGravar($d) + ['codigo' => 'TMP' . bin2hex(random_bytes(6)), 'empresa_id' => $empId, 'status' => Estado::V_AGUARDANDO, 'versao' => 1, 'criado_em' => Database::agora()]);
        Database::update('vagas', $id, ['codigo' => sprintf('VG-%s-%04d', date('Y'), $id)]);
        Historico::registrar('vaga', $id, 'criada', null, Estado::V_AGUARDANDO, 'empresa', $empId);
        // primeira publicação: o aviso "empresa aprovada" não precisa mais aparecer
        Database::update('empresas', $empId, ['aprovacao_vista' => 1]);
        return $id;
    }

    public static function camposAlterados(array $atual, array $novo): array
    {
        // bolsa vem como int/float/string conforme o banco (SQLite devolve int): compara sempre com 2 casas
        $norm = fn($v, $k) => $v === null || $v === '' ? '' : ($k === 'bolsa' ? number_format((float) $v, 2, '.', '') : trim(str_replace("\r", '', (string) $v)));   // navegadores enviam \r\n; o banco guarda \n
        $g = self::dadosGravar($novo + ['imagem' => $atual['imagem']]);
        $diff = [];
        foreach (self::CONTEUDO as $k) {
            $a = $atual[$k] ?? null;
            if (in_array($k, ['atividades', 'requisitos', 'requisitos_desejaveis', 'beneficios'], true)) {
                $a = implode("\n", (array) $a);
            }
            if ($norm($a, $k) !== $norm($g[$k] ?? null, $k)) {
                $diff[] = $k;
            }
        }
        return $diff;
    }

    public static function atualizar(int $id, array $novo, int $empId): array
    {
        $v = self::porId($id);
        if (!$v || (int) $v['empresa_id'] !== $empId) {
            throw new RuntimeException('Vaga não pertence à empresa.');
        }
        if ($v['status'] === Estado::V_ENCERRADA) {
            throw new RuntimeException('Vaga encerrada não pode ser editada.');
        }
        $dif = self::camposAlterados($v, $novo);
        if (!$dif) {
            return [false, $v['status']];
        }
        $dados = self::dadosGravar($novo + ['imagem' => $v['imagem']]) + ['atualizado_em' => Database::agora(), 'versao' => (int) $v['versao'] + 1];
        $para = $v['status'];
        if (in_array($v['status'], [Estado::V_APROVADA, Estado::V_REJEITADA], true)) {
            if (!Estado::vagaPode($v['status'], Estado::V_AGUARDANDO)) {
                throw new RuntimeException('Transição inválida.');
            }
            $para = Estado::V_AGUARDANDO;
            $dados['status'] = $para;
            $dados['motivo'] = null;
            $dados['avaliado_em'] = null;
        }
        Database::update('vagas', $id, $dados);
        Historico::registrar('vaga', $id, $v['status'] === Estado::V_REJEITADA ? 'reenviada' : 'editada', $v['status'], $para, 'empresa', $empId, 'Campos: ' . implode(', ', $dif));
        return [true, $para];
    }

    public static function mudarStatus(int $id, string $para, ?string $motivo, string $atorTipo, ?int $atorId): bool
    {
        $v = Database::one('SELECT status FROM vagas WHERE id = :i', ['i' => $id]);
        if (!$v || !Estado::vagaPode($v['status'], $para)) {
            return false;
        }
        $d = ['status' => $para, 'motivo' => $para === Estado::V_REJEITADA ? $motivo : null];
        if (in_array($para, [Estado::V_APROVADA, Estado::V_REJEITADA], true)) {
            $d['avaliado_em'] = Database::agora();
        }
        if ($para === Estado::V_ENCERRADA) {
            $d['aviso_encerrada'] = 1;
        }
        Database::update('vagas', $id, $d);
        Historico::registrar('vaga', $id, $para === Estado::V_APROVADA ? 'aprovada' : ($para === Estado::V_REJEITADA ? 'rejeitada' : 'encerrada'), $v['status'], $para, $atorTipo, $atorId, $motivo);
        return true;
    }
    public static function alternarDestaque(int $id, string $atorTipo, ?int $atorId): void
    {
        Database::run('UPDATE vagas SET destaque = 1 - destaque WHERE id = :i', ['i' => $id]);
        Historico::registrar('vaga', $id, 'destaque', null, null, $atorTipo, $atorId);
    }

    public static function rotina(): array
    {
        $h = date('Y-m-d');
        $em3 = date('Y-m-d', strtotime('+3 days'));
        $exp = 0;
        $av = 0;
        $q = 'SELECT ' . self::SEL . ' ' . self::FROM;
        foreach (Database::all($q . " WHERE v.status = 'aprovada' AND v.aviso_encerrada = 0 AND v.data_limite < :h", ['h' => $h]) as $r) {
            $v = self::hidratar($r);
            Database::update('vagas', (int) $v['id'], ['aviso_encerrada' => 1]);
            Historico::registrar('vaga', (int) $v['id'], 'expirada', 'publicada', 'expirada', 'sistema', null);
            Notificador::vagaEncerrada($v, $v['empresa']);
            $exp++;
        }
        foreach (Database::all($q . " WHERE v.status = 'aprovada' AND v.aviso_vencendo = 0 AND v.data_limite >= :h AND v.data_limite <= :t", ['h' => $h, 't' => $em3]) as $r) {
            $v = self::hidratar($r);
            Database::update('vagas', (int) $v['id'], ['aviso_vencendo' => 1]);
            Notificador::vagaVencendo($v, $v['empresa']);
            $av++;
        }
        return [$exp, $av];
    }
}
