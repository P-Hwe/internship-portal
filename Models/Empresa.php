<?php

class Empresa
{
    public static function porId(int $id): ?array
    {
        return Database::one('SELECT * FROM empresas WHERE id = :i', ['i' => $id]);
    }
    public static function porEmail(string $e): ?array
    {
        return Database::one('SELECT * FROM empresas WHERE email = :e', ['e' => mb_strtolower($e)]);
    }
    public static function porCnpj(string $c): ?array
    {
        return Database::one('SELECT * FROM empresas WHERE cnpj = :c', ['c' => Cnpj::limpar($c)]);
    }

    public static function comporEndereco(array $d): string
    {
        $cep = preg_replace('/\D/', '', (string) ($d['cep'] ?? ''));
        $rua = trim($d['logradouro'] . ', ' . $d['numero'] . (($d['complemento'] ?? '') !== '' ? ', ' . $d['complemento'] : ''));
        return mb_substr($rua . ' - ' . $d['bairro'] . ', ' . $d['cidade'] . '/' . strtoupper($d['uf']) . ' - CEP ' . substr($cep, 0, 5) . '-' . substr($cep, 5), 0, 200);
    }

    private static function campos(array $d): array
    {
        $n = fn($k) => (($d[$k] ?? '') !== '') ? $d[$k] : null;
        return [
            'razao_social' => $d['razao_social'], 'nome' => $d['nome'], 'descricao' => $d['descricao'], 'segmento' => $d['segmento'], 'porte' => $d['porte'],
            'endereco' => self::comporEndereco($d), 'cep' => preg_replace('/\D/', '', $d['cep']), 'logradouro' => $d['logradouro'], 'numero' => $d['numero'],
            'complemento' => $n('complemento'), 'bairro' => $d['bairro'], 'cidade' => $d['cidade'], 'uf' => strtoupper($d['uf']),
            'responsavel' => $d['responsavel'], 'cargo' => $d['cargo'], 'telefone' => preg_replace('/\D/', '', $d['telefone']),
            'celular' => $n('celular') ? preg_replace('/\D/', '', $d['celular']) : null, 'email_contato' => $n('email_contato') ? mb_strtolower($d['email_contato']) : null,
            'site' => $n('site'), 'linkedin' => $n('linkedin'), 'link_principal' => $d['link_principal'] ?? 'site',
        ];
    }

    public static function criar(array $d): int
    {
        $id = Database::insert('empresas', self::campos($d) + [
            'cnpj' => Cnpj::limpar($d['cnpj']), 'email' => mb_strtolower($d['email']), 'senha_hash' => Auth::hash($d['senha']),
            'logo' => $d['logo'] ?? null, 'termo_arquivo' => $d['termo_arquivo'], 'termos_aceitos_em' => Database::agora(),
            'status' => Estado::E_PENDENTE, 'criado_em' => Database::agora(),
        ]);
        Historico::registrar('empresa', $id, 'cadastro', null, Estado::E_PENDENTE, 'empresa', $id);
        return $id;
    }

    public static function atualizarPerfil(int $id, array $d): void
    {
        $dados = self::campos($d);
        if (array_key_exists('logo', $d)) {
            $dados['logo'] = $d['logo'];
        }
        Database::update('empresas', $id, $dados);
        Historico::registrar('empresa', $id, 'perfil', null, null, 'empresa', $id);
    }

    public static function mudarStatus(int $id, string $para, ?string $motivo, string $atorTipo, ?int $atorId): bool
    {
        $e = self::porId($id);
        if (!$e || !Estado::empresaPode($e['status'], $para)) {
            return false;
        }
        $dados = ['status' => $para, 'motivo' => in_array($para, [Estado::E_REPROVADA, Estado::E_BLOQUEADA], true) ? $motivo : null];
        if (in_array($para, [Estado::E_APROVADA, Estado::E_REPROVADA], true) && $e['status'] === Estado::E_PENDENTE) {
            $dados['avaliado_em'] = Database::agora();
            $dados['aprovacao_vista'] = 0;
        }
        if ($para === Estado::E_PENDENTE) {
            $dados['avaliado_em'] = null;
        }
        Database::update('empresas', $id, $dados);
        $acao = [Estado::E_APROVADA => $e['status'] === Estado::E_BLOQUEADA ? 'desbloqueada' : 'aprovada', Estado::E_REPROVADA => 'reprovada', Estado::E_BLOQUEADA => 'bloqueada', Estado::E_PENDENTE => 'reenviada'][$para];
        Historico::registrar('empresa', $id, $acao, $e['status'], $para, $atorTipo, $atorId, $motivo);
        return true;
    }
    public static function dispensarAviso(int $id): void
    {
        Database::update('empresas', $id, ['aprovacao_vista' => 1]);
    }

    /** O aviso "empresa aprovada" aparece enquanto: aprovada, não dispensado, sem vagas publicadas ainda e há menos de 7 dias. */
    public static function mostrarAvisoAprovacao(array $e): bool
    {
        if ($e['status'] !== Estado::E_APROVADA || (int) $e['aprovacao_vista'] || !$e['avaliado_em']) {
            return false;
        }
        if (strtotime($e['avaliado_em']) < time() - 7 * 86400) {
            return false;
        }
        return !Database::val('SELECT 1 FROM vagas WHERE empresa_id = :e LIMIT 1', ['e' => $e['id']]);
    }

    public static function pendentes(): array
    {
        return Database::all("SELECT * FROM empresas WHERE status = 'pendente' ORDER BY criado_em");
    }

    private const SUBS = '(SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id) AS total_vagas,
        (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status = \'aprovada\' AND v.data_publicacao <= :hoje AND v.data_limite >= :hoje) AS vagas_abertas';

    public static function abasAdmin(): array
    {
        $h = date('Y-m-d');
        $trinta = date('Y-m-d H:i:s', time() - 30 * 86400);
        $q = fn(string $w, array $p = []) => (int) Database::val("SELECT COUNT(*) FROM empresas e WHERE $w", $p);
        return [
            'pendentes' => ['Pendentes', $q("status = 'pendente'")], 'aprovadas' => ['Aprovadas', $q("status = 'aprovada'")],
            'ativas' => ['Ativas (30 dias)', $q("status = 'aprovada' AND ultimo_acesso >= :t", ['t' => $trinta])],
            'com_vagas' => ['Com vagas', $q('EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)')], 'sem_vagas' => ['Sem vagas', $q('NOT EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)')],
            'bloqueadas' => ['Bloqueadas', $q("status = 'bloqueada'")], 'reprovadas' => ['Reprovadas', $q("status = 'reprovada'")], 'todas' => ['Todas', $q('1 = 1')],
        ];
    }
    /** @return array [linhas, total] */
    public static function listarAdmin(string $aba, string $busca, int $pg, int $por = POR_PAGINA): array
    {
        $w = ['1 = 1'];
        $p = ['hoje' => date('Y-m-d')];
        switch ($aba) {
            case 'pendentes': $w[] = "e.status = 'pendente'";
                break;
            case 'aprovadas': $w[] = "e.status = 'aprovada'";
                break;
            case 'bloqueadas': $w[] = "e.status = 'bloqueada'";
                break;
            case 'reprovadas': $w[] = "e.status = 'reprovada'";
                break;
            case 'ativas': $w[] = "e.status = 'aprovada' AND e.ultimo_acesso >= :t30";
                $p['t30'] = date('Y-m-d H:i:s', time() - 30 * 86400);
                break;
            case 'com_vagas': $w[] = 'EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)';
                break;
            case 'sem_vagas': $w[] = 'NOT EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)';
                break;
        }
        if ($busca !== '') {
            $w[] = '(LOWER(e.nome) LIKE :q OR LOWER(e.razao_social) LIKE :q OR e.cnpj LIKE :qc OR LOWER(e.email) LIKE :q)';
            $p['q'] = '%' . mb_strtolower($busca) . '%';
            $p['qc'] = '%' . Cnpj::limpar($busca) . '%';
        }
        $where = implode(' AND ', $w);
        $tp = array_diff_key($p, ['hoje' => 1]);
        $total = (int) Database::val("SELECT COUNT(*) FROM empresas e WHERE $where", $tp);
        [$off] = Paginador::calc($total, $pg, $por);
        $l = Database::all('SELECT e.*, ' . self::SUBS . " FROM empresas e WHERE $where ORDER BY e.criado_em DESC, e.id DESC LIMIT " . (int) $por . ' OFFSET ' . (int) $off, $p);
        return [$l, $total];
    }
    public static function comContagem(int $id): ?array
    {
        return Database::one('SELECT e.*, ' . self::SUBS . ' FROM empresas e WHERE e.id = :id', ['hoje' => date('Y-m-d'), 'id' => $id]);
    }

    public static function parceiras(string $ordem, int $n = 12): array
    {
        $ord = ['recentes' => 'e.avaliado_em DESC, e.id DESC', 'frequentes' => 'total_vagas DESC, e.nome', 'abertas' => 'vagas_abertas DESC, e.nome'][$ordem] ?? 'e.nome';
        $extra = $ordem === 'frequentes' ? ' AND (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status IN (\'aprovada\',\'encerrada\')) > 0' : '';
        $extra .= $ordem === 'abertas' ? ' AND (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status = \'aprovada\' AND v.data_publicacao <= :hoje AND v.data_limite >= :hoje) > 0' : '';
        return Database::all('SELECT e.*, ' . self::SUBS . " FROM empresas e WHERE e.status = 'aprovada'$extra ORDER BY $ord LIMIT " . (int) $n, ['hoje' => date('Y-m-d')]);
    }
    public static function parceiraPorId(int $id): ?array
    {
        $e = self::comContagem($id);
        return ($e && $e['status'] === Estado::E_APROVADA) ? $e : null;
    }
    public static function linkExterno(array $e): ?string
    {
        $ordem = ($e['link_principal'] ?? 'site') === 'linkedin' ? ['linkedin', 'site'] : ['site', 'linkedin'];
        foreach ($ordem as $k) {
            if (!empty($e[$k]) && Link::valido($e[$k])) {
                return $e[$k];
            }
        }
        return null;
    }
    public static function segmentoTxt(array $e): string
    {
        return $e['segmento'] ?: 'Segmento não informado';
    }
}
