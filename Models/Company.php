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
        $hash = Auth::hash($data['senha']);
        $id = Database::insert('empresas', self::campos($data) + [
            'cnpj' => Cnpj::clean($data['cnpj']), 'email' => mb_strtolower($data['email']), 'senha_hash' => $hash,
            'logo' => $data['logo'] ?? null, 'banner' => $data['banner'] ?? null, 'termo_arquivo' => $data['termo_arquivo'], 'termos_aceitos_em' => Database::now(),
            'status' => Status::E_PENDENTE, 'criado_em' => Database::now(),
        ]);
        CompanyMember::createPrincipal($id, $data['responsavel'], $data['email'], $hash);
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
            'encerradas' => ['Parceria encerrada', $q("status = 'encerrada'")],
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
            case 'encerradas': $where[] = "e.status = 'encerrada'"; break;
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

    /**
     * Encerra a parceria a pedido da empresa (LGPD): tira as vagas do ar, apaga dados de contato, pessoas, arquivos e o CNPJ
     * (que fica livre para um novo cadastro) e guarda só um registro mínimo para o histórico da Coordenação.
     * Devolve os dados de contato anteriores para os avisos por e-mail.
     */
    public static function encerrarParceria(int $id): ?array
    {
        $company = self::porId($id);
        if (!$company || $company['status'] === Status::E_ENCERRADA) return null;
        $members = Database::all('SELECT id, nome, email FROM empresa_usuarios WHERE empresa_id = :e', ['e' => $id]);
        $memberIds = array_map('intval', array_column($members, 'id'));
        $emails = array_values(array_unique(array_filter(array_map('mb_strtolower', array_merge([$company['email'], $company['email_contato']], array_column($members, 'email'))))));
        $files = array_filter(array_merge([$company['logo'], $company['banner']], array_column(Database::all('SELECT imagem FROM vagas WHERE empresa_id = :e AND imagem IS NOT NULL', ['e' => $id]), 'imagem')));
        $term = $company['termo_arquivo'];
        $agreement = $company['convenio_arquivo'] ?? null;
        $openJobs = (int) Database::val("SELECT COUNT(*) FROM vagas WHERE empresa_id = :e AND status <> 'encerrada'", ['e' => $id]);

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run("UPDATE vagas SET status = 'encerrada', destaque = 0, contato = '', imagem = NULL, link_site = NULL, link_linkedin = NULL,
                link_candidatura = NULL, link_plataforma = NULL, motivo = NULL WHERE empresa_id = :e", ['e' => $id]);
            if ($memberIds) {
                $in = implode(',', $memberIds);
                Database::run("DELETE FROM senha_resets WHERE perfil = 'empresa' AND usuario_id IN ($in)");
            }
            Database::run('DELETE FROM empresa_usuarios WHERE empresa_id = :e', ['e' => $id]);
            Database::run("UPDATE acessos_log SET email = NULL, ip = NULL, agente = NULL WHERE perfil = 'empresa' AND usuario_id = :e", ['e' => $id]);
            foreach ($emails as $email) {
                Database::run('UPDATE acessos_log SET email = NULL, ip = NULL, agente = NULL WHERE LOWER(email) = :m', ['m' => $email]);
                Database::run("UPDATE emails_log SET destinatario = '(removido)' WHERE LOWER(destinatario) = :m", ['m' => $email]);
            }
            Database::run("UPDATE historico SET detalhe = NULL WHERE entidade = 'empresa' AND entidade_id = :e AND acao LIKE 'usuario_%'", ['e' => $id]);
            Database::run("DELETE FROM notificacoes WHERE tipo = 'membro' AND referencia_id = :e", ['e' => $id]);
            Database::update('empresas', $id, [
                'cnpj' => null, 'razao_social' => null, 'descricao' => null, 'endereco' => '', 'cep' => null, 'logradouro' => null, 'numero' => null,
                'complemento' => null, 'bairro' => null, 'responsavel' => '', 'cargo' => null, 'telefone' => '', 'celular' => null, 'email' => null,
                'email_contato' => null, 'site' => null, 'linkedin' => null, 'logo' => null, 'banner' => null, 'senha_hash' => '', 'termo_arquivo' => null,
                'convenio_arquivo' => null, 'convenio_assinado_em' => null, 'motivo' => null, 'status' => Status::E_ENCERRADA, 'encerrada_em' => Database::now(),
            ]);
            AuditLog::registrar('empresa', $id, 'parceria_encerrada', $company['status'], Status::E_ENCERRADA, 'empresa', $id, $openJobs ? $openJobs . ' vaga(s) retiradas do mural' : null);
            $pdo->commit();
        } catch (Throwable $t) {
            $pdo->rollBack();
            throw $t;
        }
        foreach ($files as $f) if (url_upload($f)) @unlink(ROOT_PATH . '/' . $f);
        if ($term) @unlink(ROOT_PATH . '/storage/termos/' . basename($term));
        if ($agreement) @unlink(ROOT_PATH . self::AGREEMENT_DIR . basename($agreement));
        return ['nome' => $company['nome'], 'emails' => $emails, 'vagas' => $openJobs];
    }

    public const AGREEMENT_DIR = '/storage/convenios/';

    public static function anexarConvenio(int $id, string $file, int $adminId): void
    {
        $previous = Database::val('SELECT convenio_arquivo FROM empresas WHERE id = :i', ['i' => $id]);
        Database::update('empresas', $id, ['convenio_arquivo' => $file, 'convenio_assinado_em' => Database::now()]);
        if ($previous && $previous !== $file) @unlink(ROOT_PATH . self::AGREEMENT_DIR . basename((string) $previous));
        AuditLog::registrar('empresa', $id, 'convenio_assinado', null, null, 'admin', $adminId, $previous ? 'nova versão' : null);
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

    /** Só publica vagas a empresa aprovada cujo convênio assinado por todas as partes já foi anexado pela Coordenação. */
    public static function podePublicar(array $company): bool
    {
        return ($company['status'] ?? '') === Status::E_APROVADA && !empty($company['convenio_arquivo']);
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
