<?php

class AdminController
{
    private function pagina(string $view, array $dados, string $secao, string $titulo): void
    {
        $dados += ['adm' => Auth::admin()];
        render($view, $dados + ['titulo' => $titulo, 'area' => 'admin', 'secao' => $secao, 'pendentes' => (int) Database::val("SELECT COUNT(*) FROM empresas WHERE status = 'pendente'") + (int) Database::val("SELECT COUNT(*) FROM vagas WHERE status = 'aguardando_aprovacao'")], 'app');
    }
    private function paramLista(): array
    {
        return [trim((string) ($_GET['q'] ?? '')), max(1, (int) ($_GET['pg'] ?? 1))];
    }

    public function painel(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_dashboard', [
            'emp' => Stats::empresas(),
            'vag' => Vaga::contagens(),
            'semanas' => Stats::porSemana(8),
            'acessos' => Stats::acessosResumo(7),
            'logins' => Stats::loginsPorDia(7),
            'atividade' => Historico::recentes(8),
            'empresasRecentes' => Stats::empresasRecentes(5),
            'naoLidas' => Notificacao::naoLidas(),
            'notifs' => Notificacao::recentes(6),
        ], 'dashboard', 'Dashboard');
    }

    public function vagas(): void
    {
        Auth::exigirAdmin();
        $cont = Vaga::contagens();
        $aba = $_GET['aba'] ?? 'todas';
        if (!in_array($aba, array_merge(['todas', 'destaque'], Estado::SITUACOES), true)) {
            $aba = 'todas';
        }
        [$q, $pg] = $this->paramLista();
        $tipo = (string) ($_GET['tipo'] ?? '');
        $ord = (string) ($_GET['ord'] ?? 'recentes');
        $vista = ($_GET['vista'] ?? 'cards') === 'tabela' ? 'tabela' : 'cards';
        [$lista, $total] = Vaga::listar(null, $aba, $q, $tipo, $ord, $pg);
        [, $pg, $paginas] = Paginador::calc($total, $pg, POR_PAGINA);
        $this->pagina('admin_vagas', compact('cont', 'aba', 'q', 'tipo', 'ord', 'vista', 'lista', 'total', 'pg', 'paginas'), 'vagas', 'Vagas');
    }

    public function vaga(): void
    {
        Auth::exigirAdmin();
        $v = Vaga::porId((int) ($_GET['id'] ?? 0));
        if (!$v) {
            http_response_code(404);
            render('erro', ['titulo' => 'Não encontrada', 'msg' => 'Vaga não encontrada.', 'area' => 'admin', 'secao' => 'vagas'], 'app');
            return;
        }
        $this->pagina('admin_vaga', ['v' => $v, 'hist' => Historico::da('vaga', (int) $v['id'])], 'vagas', $v['titulo']);
    }

    public function empresas(): void
    {
        Auth::exigirAdmin();
        $abas = Empresa::abasAdmin();
        $aba = array_key_exists($_GET['aba'] ?? '', $abas) ? $_GET['aba'] : ($abas['pendentes'][1] ? 'pendentes' : 'todas');
        [$q, $pg] = $this->paramLista();
        [$lista, $total] = Empresa::listarAdmin($aba, $q, $pg);
        [, $pg, $paginas] = Paginador::calc($total, $pg, POR_PAGINA);
        $this->pagina('admin_empresas', compact('abas', 'aba', 'q', 'lista', 'total', 'pg', 'paginas'), 'empresas', 'Empresas');
    }

    public function empresa(): void
    {
        Auth::exigirAdmin();
        $e = Empresa::comContagem((int) ($_GET['id'] ?? 0));
        if (!$e) {
            http_response_code(404);
            render('erro', ['titulo' => 'Não encontrada', 'msg' => 'Empresa não encontrada.', 'area' => 'admin', 'secao' => 'empresas'], 'app');
            return;
        }
        $this->pagina('admin_empresa', ['e' => $e, 'vagas' => Vaga::todasDaEmpresa((int) $e['id']), 'hist' => Historico::da('empresa', (int) $e['id'])], 'empresas', $e['nome']);
    }

    public function aprovacoes(): void
    {
        Auth::exigirAdmin();
        [$vp] = Vaga::listar(null, 'aguardando', '', '', 'recentes', 1, 50);
        $this->pagina('admin_aprovacoes', ['empPend' => Empresa::pendentes(), 'vagasPend' => $vp], 'aprovacoes', 'Aprovações');
    }

    public function usuarios(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_usuarios', ['admins' => Admin::todos(), 'erros' => $_SESSION['erros_admin'] ?? [], 'old' => $_SESSION['old_admin'] ?? []], 'usuarios', 'Usuários');
        unset($_SESSION['erros_admin'], $_SESSION['old_admin']);
    }

    public function acesso(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_acesso', [
            'resumo' => Stats::acessosResumo(7),
            'resumo30' => Stats::acessosResumo(30),
            'bloqueios' => Stats::bloqueiosAtivos(),
            'contas' => Stats::contasRestritas(),
            'ultAdmins' => Stats::ultimosAcessos('admin'),
            'ultEmpresas' => Stats::ultimosAcessos('empresa'),
            'suspeitos' => Stats::eventosRecentes(12, true),
            'eventos' => Stats::eventosRecentes(15),
            'logins' => Stats::loginsPorDia(7),
        ], 'acesso', 'Controle de acesso');
    }

    public function relatorios(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_relatorios', ['vag' => Vaga::contagens(), 'emp' => Stats::empresas()], 'relatorios', 'Relatórios');
    }

    public function config(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_config', ['emails' => Mailer::recentes(8), 'mailModo' => Mailer::modo()], 'config', 'Configurações');
    }

    private const RETORNOS = ['admin', 'admin-vagas', 'admin-vaga', 'admin-empresas', 'admin-empresa', 'admin-aprovacoes', 'admin-usuarios', 'admin-config'];

    public function acao(): void
    {
        $adm = Auth::exigirAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin');
        }
        Csrf::exigir();
        $tipo = (string) ($_POST['tipo'] ?? '');
        $acao = (string) ($_POST['acao'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim((string) ($_POST['motivo'] ?? ''));
        $ator = ['admin', (int) $adm['id']];
        $ret = in_array($_POST['ret'] ?? '', self::RETORNOS, true) ? $_POST['ret'] : 'admin';
        $retParams = ((int) ($_POST['ret_id'] ?? 0)) ? ['id' => (int) $_POST['ret_id']] : [];
        $voltar = function () use (&$ret, &$retParams) {
            redirect($ret, $retParams);
        };

        switch ($tipo) {
            case 'notificacoes':
                Notificacao::marcarLidas();
                $voltar();

                // no break
            case 'email-teste':
                $ok = Mailer::enviar($adm['email'], 'Teste de e-mail do sistema de vagas', Mailer::layout('Teste de e-mail', '<p>Se você está lendo isto, o envio de e-mails está funcionando.</p>'));
                if (!$ok) {
                    flash('erro', 'Falha ao enviar: ' . (Mailer::$ultimoErro ?: 'erro desconhecido'));
                } elseif (Mailer::modo() === 'log') {
                    flash('info', 'Modo de teste (MAIL_DRIVER=log): nada foi enviado de verdade. A mensagem foi gravada em storage/mail. Configure SMTP no .env para receber na caixa de entrada.');
                } else {
                    flash('ok', 'E-mail de teste enviado para ' . $adm['email'] . '. Confira a caixa de entrada e o spam.');
                }
                redirect('admin-config');

            case 'empresa':
                $emp = Empresa::porId($id);
                if (!$emp) {
                    flash('erro', 'Empresa não encontrada.');
                    $voltar();
                }
                $para = ['aprovar' => Estado::E_APROVADA, 'reprovar' => Estado::E_REPROVADA, 'bloquear' => Estado::E_BLOQUEADA, 'desbloquear' => Estado::E_APROVADA][$acao] ?? null;
                if (!$para) {
                    $voltar();
                }
                if (in_array($acao, ['reprovar', 'bloquear'], true) && mb_strlen($motivo) < 5) {
                    flash('erro', 'Informe o motivo (ele é enviado à empresa).');
                    $voltar();
                }
                if (!Empresa::mudarStatus($id, $para, $motivo ?: null, $ator[0], $ator[1])) {
                    flash('erro', 'Essa ação não é permitida para a situação atual da empresa.');
                    $voltar();
                }
                $nova = Empresa::porId($id);
                if ($acao !== 'desbloquear') {
                    Notificador::empresaAvaliada($nova);
                }
                flash('ok', $emp['nome'] . ': ' . ['aprovar' => 'aprovada', 'reprovar' => 'reprovada', 'bloquear' => 'bloqueada', 'desbloquear' => 'desbloqueada'][$acao] . '. ' . ($acao === 'desbloquear' ? '' : 'A empresa foi avisada por e-mail.'));
                break;

            case 'vaga':
                $v = Vaga::porId($id);
                if (!$v) {
                    flash('erro', 'Vaga não encontrada.');
                    $voltar();
                }
                if ($acao === 'destaque') {
                    Vaga::alternarDestaque($id, $ator[0], $ator[1]);
                    flash('ok', 'Destaque atualizado.');
                    break;
                }
                $para = ['aprovar' => Estado::V_APROVADA, 'rejeitar' => Estado::V_REJEITADA, 'encerrar' => Estado::V_ENCERRADA][$acao] ?? null;
                if (!$para) {
                    $voltar();
                }
                if ($acao === 'aprovar' && $v['empresa']['status'] !== Estado::E_APROVADA) {
                    flash('erro', 'Aprove a empresa antes de aprovar a vaga.');
                    $voltar();
                }
                if ($acao === 'rejeitar' && mb_strlen($motivo) < 5) {
                    flash('erro', 'Informe o motivo (ele é enviado à empresa).');
                    $voltar();
                }
                if (!Vaga::mudarStatus($id, $para, $motivo ?: null, $ator[0], $ator[1])) {
                    flash('erro', 'Essa ação não é permitida para a situação atual da vaga (' . Estado::rotuloSituacao($v['situacao']) . ').');
                    $voltar();
                }
                Notificador::vagaAvaliada(Vaga::porId($id), $v['empresa']);
                flash('ok', 'Vaga ' . ['aprovar' => 'aprovada', 'rejeitar' => 'rejeitada', 'encerrar' => 'encerrada'][$acao] . '. A empresa foi avisada por e-mail.');
                break;

            case 'admin':
                if ($acao === 'criar') {
                    $d = clean_post($_POST);
                    $er = [];
                    if (mb_strlen($d['nome'] ?? '') < 3) {
                        $er['nome'] = 'Informe o nome.';
                    }
                    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                        $er['email'] = 'E-mail inválido.';
                    } elseif (Admin::porEmail($d['email'])) {
                        $er['email'] = 'Já existe um administrador com este e-mail.';
                    }
                    if (!EmpresaForm::senhaForte($_POST['senha'] ?? '')) {
                        $er['senha'] = 'A senha precisa ter 8 ou mais caracteres, com letras e números.';
                    }
                    if ($er) {
                        $_SESSION['erros_admin'] = $er;
                        $_SESSION['old_admin'] = $d;
                        $voltar();
                    }
                    $novo = Admin::criar($d['nome'], $d['email'], $_POST['senha']);
                    Historico::registrar('admin', $novo, 'cadastro', null, null, $ator[0], $ator[1], $d['email']);
                    flash('ok', 'Administrador criado.');
                } elseif (in_array($acao, ['ativar', 'desativar'], true)) {
                    if ($id === (int) $adm['id']) {
                        flash('erro', 'Você não pode desativar a própria conta.');
                        $voltar();
                    }
                    if ($acao === 'desativar' && Stats::contasRestritas()['admins_ativos'] <= 1) {
                        flash('erro', 'É preciso manter ao menos um administrador ativo.');
                        $voltar();
                    }
                    Admin::definirAtivo($id, $acao === 'ativar');
                    Historico::registrar('admin', $id, $acao === 'ativar' ? 'desbloqueada' : 'bloqueada', null, null, $ator[0], $ator[1]);
                    flash('ok', 'Conta ' . ($acao === 'ativar' ? 'ativada' : 'desativada') . '.');
                }
                break;
        }
        if (Mailer::$falhas > 0) {
            flash('erro', 'A ação foi feita, mas o e-mail NÃO foi enviado: ' . (Mailer::$ultimoErro ?: 'erro desconhecido') . ' (veja Configurações > Diagnóstico de e-mail).');
        }
        $voltar();
    }

    public function termo(): void
    {
        Auth::exigirAdmin();
        $emp = Empresa::porId((int) ($_GET['id'] ?? 0));
        $arq = $emp ? ROOT_PATH . '/storage/termos/' . basename((string) $emp['termo_arquivo']) : '';
        if (!$emp || !$emp['termo_arquivo'] || !is_file($arq)) {
            http_response_code(404);
            exit('Arquivo não encontrado.');
        }
        header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($arq));
        header('Content-Disposition: inline; filename="termo-' . preg_replace('/\W/', '', $emp['cnpj']) . '.' . pathinfo($arq, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($arq);
        exit;
    }

    public function exportar(): void
    {
        Auth::exigirAdmin();
        $tipo = ($_GET['tipo'] ?? '') === 'empresas' ? 'empresas' : 'vagas';
        $esc = function ($v) {
            $v = (string) $v;
            return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
        };
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $tipo . '-' . date('Ymd') . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $o = fopen('php://output', 'w');
        fwrite($o, "\xEF\xBB\xBF");
        if ($tipo === 'vagas') {
            fputcsv($o, ['Código', 'Título', 'Tipo', 'Empresa', 'Área', 'Modalidade', 'Local', 'Remuneração', 'Situação', 'Publicação', 'Data limite', 'Criada em'], ';');
            [$l] = Vaga::listar(null, 'todas', '', '', 'recentes', 1, 100000);
            foreach ($l as $v) {
                fputcsv($o, array_map($esc, [$v['codigo'], $v['titulo'], $v['tipo'], $v['empresa']['nome'], $v['area'], $v['modalidade'], $v['local'], $v['bolsa_txt'], Estado::rotuloSituacao($v['situacao']), data_br($v['data_publicacao']), data_br($v['data_limite']), data_br(substr($v['criado_em'], 0, 10))]), ';');
            }
        } else {
            fputcsv($o, ['CNPJ', 'Razão social', 'Nome fantasia', 'Segmento', 'Cidade/UF', 'Responsável', 'E-mail', 'Situação', 'Vagas', 'Cadastro'], ';');
            [$l] = Empresa::listarAdmin('todas', '', 1, 100000);
            foreach ($l as $e) {
                fputcsv($o, array_map($esc, [Cnpj::formatar($e['cnpj']), $e['razao_social'], $e['nome'], $e['segmento'], trim(($e['cidade'] ?? '') . '/' . ($e['uf'] ?? ''), '/'), $e['responsavel'], $e['email'], Estado::STATUS_EMPRESA[$e['status']][0] ?? $e['status'], $e['total_vagas'], data_br(substr($e['criado_em'], 0, 10))]), ';');
            }
        }
        fclose($o);
        exit;
    }
}
