<?php
class Company
{
    public static function porId(int $id): ?array        { return Database::one('SELECT * FROM empresas WHERE id = :i', ['i' => $id]); }
    public static function porEmail(string $email): ?array { return Database::one('SELECT * FROM empresas WHERE email = :e', ['e' => mb_strtolower($email)]); }
    public static function porCnpj(string $c): ?array    { return Database::one('SELECT * FROM empresas WHERE cnpj = :c', ['c' => Cnpj::clean($c)]); }

    public static function comporEndereco(array $data): string
    {
        $cep = preg_replace('/\D/', '', (string) ($data['cep'] ?? ''));
        $rua = trim($data['logradouro'] . ', ' . $data['numero'] . (($data['complemento'] ?? '') !== '' ? ', ' . $data['complemento'] : ''));
        return mb_substr($rua . ' - ' . $data['bairro'] . ', ' . $data['cidade'] . '/' . strtoupper($data['uf']) . ' - CEP ' . substr($cep, 0, 5) . '-' . substr($cep, 5), 0, 200);
    }

    private static function campos(array $data): array
    {
        $nullable = fn($k) => (($data[$k] ?? '') !== '') ? $data[$k] : null;
        return [
            'razao_social' => $data['razao_social'], 'nome' => $data['nome'], 'descricao' => $data['descricao'], 'segmento' => $data['segmento'], 'porte' => $data['porte'],
            'endereco' => self::comporEndereco($data), 'cep' => preg_replace('/\D/', '', $data['cep']), 'logradouro' => $data['logradouro'], 'numero' => $data['numero'],
            'complemento' => $nullable('complemento'), 'bairro' => $data['bairro'], 'cidade' => $data['cidade'], 'uf' => strtoupper($data['uf']),
            'responsavel' => $data['responsavel'], 'cargo' => $data['cargo'], 'telefone' => preg_replace('/\D/', '', $data['telefone']),
            'celular' => $nullable('celular') ? preg_replace('/\D/', '', $data['celular']) : null,
            'email_contato' => $nullable('email_contato') ? mb_strtolower($data['email_contato']) : null,
            'site' => $nullable('site'), 'linkedin' => $nullable('linkedin'), 'link_principal' => $data['link_principal'] ?? 'site',
        ];
    }

    public static function criar(array $data): int
    {
        $id = Database::insert('empresas', self::campos($data) + [
            'cnpj' => Cnpj::clean($data['cnpj']), 'email' => mb_strtolower($data['email']), 'senha_hash' => Auth::hash($data['senha']),
            'logo' => $data['logo'] ?? null, 'banner' => $data['banner'] ?? null, 'termo_arquivo' => $data['termo_arquivo'], 'termos_aceitos_em' => Database::now(),
            'status' => Status::E_PENDENTE, 'criado_em' => Database::now(),
        ]);
        AuditLog::registrar('empresa', $id, 'cadastro', null, Status::E_PENDENTE, 'empresa', $id);
        return $id;
    }

    public static function atualizarPerfil(int $id, array $data): void
    {
        $fields = self::campos($data);
        if (array_key_exists('logo',   $data)) $fields['logo']   = $data['logo'];
        if (array_key_exists('banner', $data)) $fields['banner'] = $data['banner'];
        Database::update('empresas', $id, $fields);
        AuditLog::registrar('empresa', $id, 'perfil', null, null, 'empresa', $id);
    }

    public static function mudarStatus(int $id, string $to, ?string $reason, string $actorType, ?int $actorId): bool
    {
        $company = self::porId($id);
        if (!$company || !Status::empresaPode($company['status'], $to)) return false;
        $data = ['status' => $to, 'motivo' => in_array($to, [Status::E_REPROVADA, Status::E_BLOQUEADA], true) ? $reason : null];
        if (in_array($to, [Status::E_APROVADA, Status::E_REPROVADA], true) && $company['status'] === Status::E_PENDENTE) {
            $data['avaliado_em'] = Database::now(); $data['aprovacao_vista'] = 0;
        }
        if ($to === Status::E_PENDENTE) $data['avaliado_em'] = null;
        Database::update('empresas', $id, $data);
        $action = [Status::E_APROVADA => $company['status'] === Status::E_BLOQUEADA ? 'desbloqueada' : 'aprovada', Status::E_REPROVADA => 'reprovada', Status::E_BLOQUEADA => 'bloqueada', Status::E_PENDENTE => 'reenviada'][$to];
        AuditLog::registrar('empresa', $id, $action, $company['status'], $to, $actorType, $actorId, $reason);
        return true;
    }

    public static function dispensarAviso(int $id): void  { Database::update('empresas', $id, ['aprovacao_vista' => 1]); }

    public static function mostrarAvisoAprovacao(array $company): bool
    {
        if ($company['status'] !== Status::E_APROVADA || (int) $company['aprovacao_vista'] || !$company['avaliado_em']) return false;
        if (strtotime($company['avaliado_em']) < time() - 7 * 86400) return false;
        return !Database::val('SELECT 1 FROM vagas WHERE empresa_id = :e LIMIT 1', ['e' => $company['id']]);
    }

    public static function pendentes(): array { return Database::all("SELECT * FROM empresas WHERE status = 'pendente' ORDER BY criado_em"); }

    private const SUBS = '(SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id) AS total_vagas,
        (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status = \'aprovada\' AND v.data_publicacao <= :hoje AND v.data_limite >= :hoje) AS vagas_abertas';

    public static function abasAdmin(): array
    {
        $thirtyAgo = date('Y-m-d H:i:s', time() - 30 * 86400);
        $q = fn(string $w, array $p = []) => (int) Database::val("SELECT COUNT(*) FROM empresas e WHERE $w", $p);
        return [
            'pendentes' => ['Pendentes', $q("status = 'pendente'")], 'aprovadas' => ['Aprovadas', $q("status = 'aprovada'")],
            'ativas' => ['Ativas (30 dias)', $q("status = 'aprovada' AND ultimo_acesso >= :t", ['t' => $thirtyAgo])],
            'com_vagas' => ['Com vagas', $q('EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)')],
            'sem_vagas' => ['Sem vagas', $q('NOT EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)')],
            'bloqueadas' => ['Bloqueadas', $q("status = 'bloqueada'")], 'reprovadas' => ['Reprovadas', $q("status = 'reprovada'")],
            'todas' => ['Todas', $q('1 = 1')],
        ];
    }

    public static function listarAdmin(string $tab, string $search, int $pg, int $perPage = POR_PAGINA): array
    {
        $where  = ['1 = 1'];
        $params = ['hoje' => date('Y-m-d')];
        switch ($tab) {
            case 'pendentes':  $where[] = "e.status = 'pendente'"; break;
            case 'aprovadas':  $where[] = "e.status = 'aprovada'"; break;
            case 'bloqueadas': $where[] = "e.status = 'bloqueada'"; break;
            case 'reprovadas': $where[] = "e.status = 'reprovada'"; break;
            case 'ativas':     $where[] = "e.status = 'aprovada' AND e.ultimo_acesso >= :t30"; $params['t30'] = date('Y-m-d H:i:s', time() - 30 * 86400); break;
            case 'com_vagas':  $where[] = 'EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)'; break;
            case 'sem_vagas':  $where[] = 'NOT EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)'; break;
        }
        if ($search !== '') {
            $where[] = '(LOWER(e.nome) LIKE :q OR LOWER(e.razao_social) LIKE :q OR e.cnpj LIKE :qc OR LOWER(e.email) LIKE :q)';
            $params['q']  = '%' . mb_strtolower($search) . '%';
            $params['qc'] = '%' . Cnpj::clean($search) . '%';
        }
        $wc     = implode(' AND ', $where);
        $total  = (int) Database::val("SELECT COUNT(*) FROM empresas e WHERE $wc", array_diff_key($params, ['hoje' => 1]));
        [$off]  = Paginator::calc($total, $pg, $perPage);
        $list   = Database::all('SELECT e.*, ' . self::SUBS . " FROM empresas e WHERE $wc ORDER BY e.criado_em DESC, e.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $off, $params);
        return [$list, $total];
    }

    public static function comContagem(int $id): ?array
    {
        return Database::one('SELECT e.*, ' . self::SUBS . ' FROM empresas e WHERE e.id = :id', ['hoje' => date('Y-m-d'), 'id' => $id]);
    }

    public static function parceiras(string $order, int $n = 12): array
    {
        $ord   = ['recentes' => 'e.avaliado_em DESC, e.id DESC', 'frequentes' => 'total_vagas DESC, e.nome', 'abertas' => 'vagas_abertas DESC, e.nome'][$order] ?? 'e.nome';
        $extra = $order === 'frequentes' ? " AND (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status IN ('aprovada','encerrada')) > 0" : '';
        $extra .= $order === 'abertas'   ? ' AND (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status = \'aprovada\' AND v.data_publicacao <= :hoje AND v.data_limite >= :hoje) > 0' : '';
        return Database::all('SELECT e.*, ' . self::SUBS . " FROM empresas e WHERE e.status = 'aprovada'$extra ORDER BY $ord LIMIT " . (int) $n, ['hoje' => date('Y-m-d')]);
    }

    public static function parceiraPorId(int $id): ?array
    {
        $company = self::comContagem($id);
        return ($company && $company['status'] === Status::E_APROVADA) ? $company : null;
    }

    public static function linkExterno(array $company): ?string
    {
        $order = ($company['link_principal'] ?? 'site') === 'linkedin' ? ['linkedin', 'site'] : ['site', 'linkedin'];
        foreach ($order as $k) { if (!empty($company[$k]) && Link::isValid($company[$k])) return $company[$k]; }
        return null;
    }

    public static function segmentoTxt(array $company): string { return $company['segmento'] ?: 'Segmento não informado'; }
}

class_alias('Company', 'Empresa');
