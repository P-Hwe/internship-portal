<?php
class AdminController
{
    private function renderPage(string $view, array $data, string $section, string $title): void
    {
        $data += ['adm' => Auth::admin()];
        render($view, $data + ['titulo' => $title, 'area' => 'admin', 'secao' => $section, 'pendentes' => (int) Database::val("SELECT COUNT(*) FROM empresas WHERE status = 'pendente'") + (int) Database::val("SELECT COUNT(*) FROM vagas WHERE status = 'aguardando_aprovacao'")], 'app');
    }
    private function paramLista(): array { return [trim((string) ($_GET['q'] ?? '')), max(1, (int) ($_GET['pg'] ?? 1))]; }
    private function periodo(): string  { return isset(Visits::PERIODS[$_GET['periodo'] ?? '']) ? $_GET['periodo'] : 'semana'; }

    public function painel(): void
    {
        $admin = Auth::requireAdmin();
        $emp = Stats::empresas(); $vag = Vaga::contagens();
        $pend = (int) ($emp['pendente'] ?? 0) + (int) ($vag['aguardando'] ?? 0);
        $lead = $pend ? 'Tem ' . (int) ($emp['pendente'] ?? 0) . ' empresa(s) e ' . (int) ($vag['aguardando'] ?? 0) . ' vaga(s) esperando a sua análise.' : 'Nada esperando análise por enquanto.';
        $this->renderPage('admin/dashboard', [
            'emp' => $emp, 'vag' => $vag, 'periodo' => $this->periodo(), 'resumo' => Visits::summary($this->periodo()), 'lead' => $lead, 'pend' => $pend,
            'atividade' => AuditLog::recentes(10), 'empresasRecentes' => Stats::empresasRecentes(5),
            'naoLidas' => Notification::naoLidas(), 'notifs' => Notification::recentes(8),
        ], 'dashboard', saudacao() . ', ' . primeiro_nome($admin['nome']));
    }

    public function desempenho(): void
    {
        Auth::requireAdmin();
        $p = $this->periodo();
        $this->renderPage('admin/performance', ['periodo' => $p, 'resumo' => Visits::summary($p), 'vagasTop' => Visits::jobRanking($p, 10), 'empresasTop' => Visits::companyRanking($p, 10)], 'desempenho', 'Desempenho');
    }

    public function vagas(): void
    {
        Auth::requireAdmin();
        $counts = Vaga::contagens();
        $tab = $_GET['aba'] ?? 'todas';
        if (!in_array($tab, array_merge(['todas', 'destaque'], Status::SITUACOES), true)) $tab = 'todas';
        [$q, $pg] = $this->paramLista();
        $type = (string) ($_GET['tipo'] ?? ''); $order = (string) ($_GET['ord'] ?? 'recentes'); $view = ($_GET['vista'] ?? 'cards') === 'tabela' ? 'tabela' : 'cards';
        [$list, $total] = Vaga::listar(null, $tab, $q, $type, $order, $pg);
        [, $pg, $pages] = Paginator::calc($total, $pg, POR_PAGINA);
        $this->renderPage('admin/job-listings', ['cont' => $counts, 'aba' => $tab, 'q' => $q, 'tipo' => $type, 'ord' => $order, 'vista' => $view, 'lista' => $list, 'total' => $total, 'pg' => $pg, 'paginas' => $pages], 'vagas', 'Vagas');
    }

    public function vaga(): void
    {
        Auth::requireAdmin();
        $job = Vaga::porId((int) ($_GET['id'] ?? 0));
        if (!$job) { http_response_code(404); render('shared/error', ['titulo' => 'Não encontrada', 'msg' => 'Vaga não encontrada.', 'area' => 'admin', 'secao' => 'vagas'], 'app'); return; }
        $this->renderPage('admin/job-detail', ['v' => $job, 'hist' => AuditLog::da('vaga', (int) $job['id']), 'desemp' => Visits::detail('vaga', (int) $job['id'])], 'vagas', $job['titulo']);
    }

    public function empresas(): void
    {
        Auth::requireAdmin();
        $abas = Company::abasAdmin();
        $tab = array_key_exists($_GET['aba'] ?? '', $abas) ? $_GET['aba'] : ($abas['pendentes'][1] ? 'pendentes' : 'todas');
        [$q, $pg] = $this->paramLista();
        [$list, $total] = Company::listarAdmin($tab, $q, $pg);
        [, $pg, $pages] = Paginator::calc($total, $pg, POR_PAGINA);
        $this->renderPage('admin/companies', ['abas' => $abas, 'aba' => $tab, 'q' => $q, 'lista' => $list, 'total' => $total, 'pg' => $pg, 'paginas' => $pages], 'empresas', 'Empresas');
    }

    public function empresa(): void
    {
        Auth::requireAdmin();
        $company = Company::comContagem((int) ($_GET['id'] ?? 0));
        if (!$company) { http_response_code(404); render('shared/error', ['titulo' => 'Não encontrada', 'msg' => 'Empresa não encontrada.', 'area' => 'admin', 'secao' => 'empresas'], 'app'); return; }
        $this->renderPage('admin/company-detail', ['e' => $company, 'vagas' => Vaga::todasDaEmpresa((int) $company['id']), 'hist' => AuditLog::da('empresa', (int) $company['id']), 'desemp' => Visits::detail('empresa', (int) $company['id'])], 'empresas', $company['nome']);
    }

    public function aprovacoes(): void
    {
        Auth::requireAdmin();
        [$pendingJobs] = Vaga::listar(null, 'aguardando', '', '', 'recentes', 1, 50);
        $this->renderPage('admin/approvals', ['empPend' => Company::pendentes(), 'vagasPend' => $pendingJobs], 'aprovacoes', 'Aprovações');
    }

    public function notificacao(): void
    {
        Auth::requireAdmin();
        $n = Notification::porId((int) ($_GET['id'] ?? 0));
        if (!$n) { flash('erro', 'Notificação não encontrada.'); redirect('admin'); }
        Notification::marcarLida((int) $n['id']);
        [$pg, $par] = Notification::destino($n);
        $existe = $n['tipo'] === 'vaga' ? Vaga::existe($par['id']) : (bool) Database::val('SELECT 1 FROM empresas WHERE id = :i', ['i' => $par['id']]);
        if (!$existe) { flash('erro', 'Esta solicitação não existe mais.'); redirect('admin-aprovacoes'); }
        redirect($pg, $par);
    }

    public function senha(): void
    {
        $admin = Auth::requireAdmin(true);
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $atual = (string) ($_POST['atual'] ?? ''); $nova = (string) ($_POST['nova'] ?? ''); $conf = (string) ($_POST['confirma'] ?? '');
            if (!password_verify($atual, $admin['senha_hash'])) $error = 'A senha atual (inicial) não confere.';
            elseif (!CompanyForm::passwordStrong($nova)) $error = 'A nova senha precisa ter 8 ou mais caracteres, com letras e números.';
            elseif ($nova === $atual) $error = 'A nova senha precisa ser diferente da senha inicial.';
            elseif ($nova !== $conf)  $error = 'A confirmação não é igual à nova senha.';
            else { Admin::changePassword((int) $admin['id'], $nova); flash('ok', 'Senha criada. Bem-vindo(a)!'); redirect('admin'); }
        }
        render('admin/change-password', ['titulo' => 'Crie a sua senha', 'ambiente' => 'admin', 'erro' => $error, 'forcada' => (bool) (int) $admin['trocar_senha'], 'adm' => $admin], 'auth');
    }

    public function atividade(): void
    {
        Auth::requireAdmin();
        $f = $_GET['filtro'] ?? 'tudo'; $day = (string) ($_GET['desde'] ?? '');
        $today = date('Y-m-d');
        $start = ['semana' => date('Y-m-d', strtotime('-6 days')), 'mes' => date('Y-m-d', strtotime('-29 days')), 'ano' => date('Y-m-d', strtotime('-364 days'))][$f] ?? null;
        if ($f === 'dia') {
            $d = DateTime::createFromFormat('Y-m-d', $day);
            if ($d && $d->format('Y-m-d') === $day && $day <= $today) $start = $day;
            else { $f = 'tudo'; $day = ''; flash('erro', 'Escolha uma data válida (até hoje).'); }
        } elseif (!in_array($f, ['tudo', 'semana', 'mes', 'ano'], true)) $f = 'tudo';
        $pg = max(1, (int) ($_GET['pg'] ?? 1));
        $total = AuditLog::contar($start);
        [$off, $pg, $pages] = Paginator::calc($total, $pg, 30);
        $this->renderPage('admin/activity-log', ['lista' => AuditLog::listar($start, 30, $off), 'total' => $total, 'filtro' => $f, 'desde' => $day, 'hoje' => $today, 'pg' => $pg, 'paginas' => $pages, 'ini' => $start], 'dashboard', 'Histórico de atividades');
    }

    public function usuarios(): void
    {
        Auth::requireAdmin();
        $this->renderPage('admin/users', ['admins' => Admin::all(), 'erros' => $_SESSION['erros_admin'] ?? [], 'old' => $_SESSION['old_admin'] ?? []], 'usuarios', 'Usuários');
        unset($_SESSION['erros_admin'], $_SESSION['old_admin']);
    }

    public function relatorios(): void
    {
        Auth::requireAdmin();
        $this->renderPage('admin/reports', ['vag' => Vaga::contagens(), 'emp' => Stats::empresas()], 'relatorios', 'Relatórios');
    }

    private const RETORNOS = ['admin', 'admin-vagas', 'admin-vaga', 'admin-empresas', 'admin-empresa', 'admin-aprovacoes', 'admin-usuarios', 'admin-atividade'];

    public function acao(): void
    {
        $admin = Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin');
        Csrf::exigir();
        $type = (string) ($_POST['tipo'] ?? ''); $action = (string) ($_POST['acao'] ?? ''); $id = (int) ($_POST['id'] ?? 0);
        $reason = trim((string) ($_POST['motivo'] ?? ''));
        $actor = ['admin', (int) $admin['id']];
        $returnRoute  = in_array($_POST['ret'] ?? '', self::RETORNOS, true) ? $_POST['ret'] : 'admin';
        $returnParams = ((int) ($_POST['ret_id'] ?? 0)) ? ['id' => (int) $_POST['ret_id']] : [];
        $goBack = function () use (&$returnRoute, &$returnParams) { redirect($returnRoute, $returnParams); };

        switch ($type) {
            case 'notificacoes': Notification::marcarLidas(); $goBack();

            case 'empresa':
                $company = Company::porId($id);
                if (!$company) { flash('erro', 'Empresa não encontrada.'); $goBack(); }
                $newStatus = ['aprovar' => Status::E_APROVADA, 'reprovar' => Status::E_REPROVADA, 'bloquear' => Status::E_BLOQUEADA, 'desbloquear' => Status::E_APROVADA][$action] ?? null;
                if (!$newStatus) $goBack();
                if (in_array($action, ['reprovar', 'bloquear'], true) && mb_strlen($reason) < 5) { flash('erro', 'Informe o motivo (ele é enviado à empresa).'); $goBack(); }
                if (!Company::mudarStatus($id, $newStatus, $reason ?: null, $actor[0], $actor[1])) { flash('erro', 'Essa ação não é permitida para a situação atual da empresa.'); $goBack(); }
                $nova = Company::porId($id);
                if ($action !== 'desbloquear') Notifier::empresaAvaliada($nova);
                flash('ok', $company['nome'] . ': ' . ['aprovar' => 'aprovada', 'reprovar' => 'reprovada', 'bloquear' => 'bloqueada', 'desbloquear' => 'desbloqueada'][$action] . '. ' . ($action === 'desbloquear' ? '' : 'A empresa foi avisada por e-mail.'));
                break;

            case 'vaga':
                $job = Vaga::porId($id);
                if (!$job) { flash('erro', 'Vaga não encontrada.'); $goBack(); }
                if ($action === 'destaque') { Vaga::alternarDestaque($id, $actor[0], $actor[1]); flash('ok', 'Destaque atualizado.'); break; }
                $newStatus = ['aprovar' => Status::V_APROVADA, 'rejeitar' => Status::V_REJEITADA, 'encerrar' => Status::V_ENCERRADA][$action] ?? null;
                if (!$newStatus) $goBack();
                if ($action === 'aprovar' && $job['empresa']['status'] !== Status::E_APROVADA) { flash('erro', 'Aprove a empresa antes de aprovar a vaga.'); $goBack(); }
                if ($action === 'rejeitar' && mb_strlen($reason) < 5) { flash('erro', 'Informe o motivo (ele é enviado à empresa).'); $goBack(); }
                if (!Vaga::mudarStatus($id, $newStatus, $reason ?: null, $actor[0], $actor[1])) { flash('erro', 'Essa ação não é permitida para a situação atual da vaga (' . Status::rotuloSituacao($job['situacao']) . ').'); $goBack(); }
                Notifier::vagaAvaliada(Vaga::porId($id), $job['empresa']);
                flash('ok', 'Vaga ' . ['aprovar' => 'aprovada', 'rejeitar' => 'rejeitada', 'encerrar' => 'encerrada'][$action] . '. A empresa foi avisada por e-mail.');
                break;

            case 'admin':
                if (!Admin::isRoot($admin)) { flash('erro', 'Somente o administrador principal pode criar ou desativar administradores.'); $goBack(); }
                if ($action === 'criar') {
                    $formData = limpar_post($_POST); $errors = [];
                    if (mb_strlen($formData['nome'] ?? '') < 3) $errors['nome'] = 'Informe o nome.';
                    if (!filter_var($formData['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'] = 'E-mail inválido.';
                    elseif (Admin::findByEmail($formData['email'])) $errors['email'] = 'Já existe um administrador com este e-mail.';
                    if (!CompanyForm::passwordStrong($_POST['senha'] ?? '')) $errors['senha'] = 'A senha precisa ter 8 ou mais caracteres, com letras e números.';
                    if ($errors) { $_SESSION['erros_admin'] = $errors; $_SESSION['old_admin'] = $formData; $goBack(); }
                    $newAdmin = Admin::create($formData['nome'], $formData['email'], $_POST['senha']);
                    AuditLog::registrar('admin', $newAdmin, 'cadastro', null, null, $actor[0], $actor[1], $formData['nome']);
                    Notifier::adminCriado(Admin::findById($newAdmin), $admin);
                    flash('ok', 'Administrador criado. Ele foi avisado por e-mail e vai trocar a senha inicial no primeiro acesso.');
                } elseif (in_array($action, ['ativar', 'desativar'], true)) {
                    if ($id === (int) $admin['id']) { flash('erro', 'Você não pode desativar a própria conta.'); $goBack(); }
                    if (!Admin::isRoot(Admin::findById($id))) { flash('erro', 'A conta do administrador principal não pode ser desativada.'); $goBack(); }
                    if ($action === 'desativar' && Stats::contasRestritas()['admins_ativos'] <= 1) { flash('erro', 'É preciso manter ao menos um administrador ativo.'); $goBack(); }
                    Admin::setActive($id, $action === 'ativar');
                    AuditLog::registrar('admin', $id, $action === 'ativar' ? 'desbloqueada' : 'bloqueada', null, null, $actor[0], $actor[1]);
                    flash('ok', 'Conta ' . ($action === 'ativar' ? 'ativada' : 'desativada') . '.');
                }
                break;
        }
        if (Mailer::$failures > 0) flash('erro', 'A ação foi feita, mas o e-mail NÃO foi enviado: ' . (Mailer::$lastError ?: 'erro desconhecido') . ' (veja os logs de e-mail em storage/mail).');
        $goBack();
    }

    public function termo(): void
    {
        Auth::requireAdmin();
        $company = Company::porId((int) ($_GET['id'] ?? 0));
        $file = $company ? ROOT_PATH . '/storage/termos/' . basename((string) $company['termo_arquivo']) : '';
        if (!$company || !$company['termo_arquivo'] || !is_file($file)) { http_response_code(404); exit('Arquivo não encontrado.'); }
        header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($file));
        header('Content-Disposition: inline; filename="termo-' . preg_replace('/\W/', '', $company['cnpj']) . '.' . pathinfo($file, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, no-store');
        readfile($file); exit;
    }

    public function exportar(): void
    {
        Auth::requireAdmin();
        $type = ($_GET['tipo'] ?? '') === 'empresas' ? 'empresas' : 'vagas';
        $escape = function ($v) { $v = (string) $v; return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v; };
        header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="' . $type . '-' . date('Ymd') . '.csv"'); header('X-Content-Type-Options: nosniff');
        $output = fopen('php://output', 'w'); fwrite($output, "\xEF\xBB\xBF");
        if ($type === 'vagas') {
            fputcsv($output, ['Código', 'Título', 'Tipo', 'Empresa', 'Área', 'Modalidade', 'Local', 'Remuneração', 'Situação', 'Publicação', 'Data limite', 'Criada em'], ';');
            [$rows] = Vaga::listar(null, 'todas', '', '', 'recentes', 1, 100000);
            foreach ($rows as $job) fputcsv($output, array_map($escape, [$job['codigo'], $job['titulo'], $job['tipo'], $job['empresa']['nome'], $job['area'], $job['modalidade'], $job['local'], $job['bolsa_txt'], Status::rotuloSituacao($job['situacao']), data_br($job['data_publicacao']), data_br($job['data_limite']), data_br(substr($job['criado_em'], 0, 10))]), ';');
        } else {
            fputcsv($output, ['CNPJ', 'Razão social', 'Nome fantasia', 'Segmento', 'Cidade/UF', 'Responsável', 'E-mail', 'Situação', 'Vagas', 'Cadastro'], ';');
            [$rows] = Company::listarAdmin('todas', '', 1, 100000);
            foreach ($rows as $company) fputcsv($output, array_map($escape, [Cnpj::format($company['cnpj']), $company['razao_social'], $company['nome'], $company['segmento'], trim(($company['cidade'] ?? '') . '/' . ($company['uf'] ?? ''), '/'), $company['responsavel'], $company['email'], Status::STATUS_EMPRESA[$company['status']][0] ?? $company['status'], $company['total_vagas'], data_br(substr($company['criado_em'], 0, 10))]), ';');
        }
        fclose($output); exit;
    }
}
