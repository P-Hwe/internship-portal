<?php
/** Área da empresa: só enxerga e altera o que é dela (ownership conferido em toda ação). */
class CompanyAreaController
{
    private function page(string $view, array $data, string $section, string $title): void
    {
        render($view, $data + ['titulo' => $title, 'area' => 'empresa', 'secao' => $section, 'emp' => $data['emp']], 'app');
    }

    /** GET /painel */
    public function painel(): void
    {
        $emp  = Auth::requireCompany();
        $cont = Vaga::contagens((int) $emp['id']);
        [$recentes] = Vaga::listar((int) $emp['id'], 'todas', '', '', 'recentes', 1, 6);
        $resumo = CompanyMember::canSeeDashboard($emp['usuario']) ? Visits::companyOverview((int) $emp['id'], 'semana') : null;
        $vencendo = array_values(array_filter(
            Vaga::todasDaEmpresa((int) $emp['id']),
            fn($v) => $v['situacao'] === 'publicada' && $v['dias'] <= DIAS_VAGA_VENCENDO
        ));
        $this->page('company/dashboard', [
            'emp'      => $emp,
            'cont'     => $cont,
            'recentes' => $recentes,
            'vencendo' => $vencendo,
            'resumo'   => $resumo,
            'aviso'    => Company::mostrarAvisoAprovacao($emp),
            'lead'     => saudacao() . ', ' . primeiro_nome((string) ($emp['usuario']['nome'] ?? $emp['responsavel'])) . '. '
                        . ((int) ($cont['publicada'] ?? 0)
                            ? 'Você tem ' . (int) $cont['publicada'] . ' vaga(s) no ar.'
                            : 'Sua empresa ainda não tem vagas no ar.'),
        ], 'painel', 'Visão geral');
    }

    /** GET /painel/vagas */
    public function vagas(): void
    {
        $emp  = Auth::requireCompany();
        $cont = Vaga::contagens((int) $emp['id']);
        $aba  = $_GET['aba'] ?? 'todas';
        if (!in_array($aba, array_merge(['todas', 'destaque'], Status::SITUACOES), true)) $aba = 'todas';
        $q  = trim((string) ($_GET['q'] ?? ''));
        $pg = max(1, (int) ($_GET['pg'] ?? 1));
        $vista = ($_GET['vista'] ?? 'cards') === 'lista' ? 'lista' : 'cards';
        [$lista, $total] = Vaga::listar((int) $emp['id'], $aba, $q, '', 'recentes', $pg);
        [, $pg, $paginas] = Paginator::calc($total, $pg, POR_PAGINA);
        $this->page('company/job-listings', compact('emp', 'cont', 'aba', 'q', 'vista', 'lista', 'total', 'pg', 'paginas'), 'vagas', 'Minhas vagas');
    }

    /** GET /painel/vagas/{id} */
    public function vaga(): void
    {
        $emp = Auth::requireCompany();
        $v   = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_GET['id'] ?? 0));
        if (!$v) Auth::deny('vaga');
        $this->page('company/job-detail', ['emp' => $emp, 'v' => $v, 'hist' => AuditLog::da('vaga', (int) $v['id'])], 'vagas', $v['titulo']);
    }

    /** GET /painel/desempenho */
    public function desempenho(): void
    {
        $emp = Auth::requireCompany();
        if (!CompanyMember::canSeeDashboard($emp['usuario'])) Auth::deny('desempenho');
        $periodo = isset(Visits::PERIODS[$_GET['periodo'] ?? '']) ? $_GET['periodo'] : 'semana';
        $this->page('company/performance', ['emp' => $emp, 'periodo' => $periodo, 'resumo' => Visits::companyOverview((int) $emp['id'], $periodo)], 'desempenho', 'Desempenho');
    }

    /** GET /painel/encerrar-parceria  POST /painel/encerrar-parceria */
    public function encerrarParceria(): void
    {
        $emp = Auth::requireCompany();
        if (!CompanyMember::isPrincipal($emp['usuario'])) Auth::deny('encerrar');
        $erro = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            if (Cnpj::clean((string) ($_POST['cnpj'] ?? '')) !== (string) $emp['cnpj']) $erro = 'O CNPJ digitado não é o da empresa.';
            elseif (!password_verify((string) ($_POST['senha'] ?? ''), (string) $emp['usuario']['senha_hash'])) $erro = 'Senha incorreta.';
            elseif (empty($_POST['confirmo'])) $erro = 'Marque a confirmação para continuar.';
            else {
                $former = Company::encerrarParceria((int) $emp['id']);
                if ($former) Notifier::parceriaEncerrada((int) $emp['id'], $former);
                Auth::logout('empresa');
                flash('ok', 'Parceria encerrada. As vagas saíram do mural e os dados da empresa foram apagados do portal. Para voltar, faça um novo cadastro.');
                redirect('home');
            }
        }
        $cont = Vaga::contagens((int) $emp['id']);
        $this->page('company/end-partnership', ['emp' => $emp, 'erro' => $erro, 'cont' => $cont,
            'membros' => (int) Database::val('SELECT COUNT(*) FROM empresa_usuarios WHERE empresa_id = :e AND excluido_em IS NULL', ['e' => $emp['id']])], 'perfil', 'Encerrar parceria');
    }

    /** GET /painel/perfil  POST /painel/perfil */
    public function perfil(): void
    {
        $emp   = Auth::requireCompany();
        $erros = [];
        $old   = $emp;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            if (($_POST['acao'] ?? '') === 'reenviar') {
                if (Company::mudarStatus((int) $emp['id'], Status::E_PENDENTE, null, 'empresa', (int) $emp['id'])) {
                    Notifier::empresaCadastrada(Company::porId((int) $emp['id']));
                    flash('ok', 'Cadastro reenviado para análise.');
                }
                redirect('painel');
            }
            $old = limpar_post($_POST) + ['logo' => $emp['logo'], 'banner' => $emp['banner']];
            [$dados, $erros] = CompanyForm::validar($old, $_FILES, 'perfil', $emp);
            if (!$erros) {
                if (empty($dados['logo']))   $dados['logo']   = $emp['logo'];
                elseif ($emp['logo']   && $dados['logo']   !== $emp['logo'])   @unlink(ROOT_PATH . '/' . $emp['logo']);
                if (empty($dados['banner'])) $dados['banner'] = $emp['banner'];
                elseif ($emp['banner'] && $dados['banner'] !== $emp['banner']) @unlink(ROOT_PATH . '/' . $emp['banner']);
                Company::atualizarPerfil((int) $emp['id'], $dados);
                flash('ok', 'Perfil atualizado.');
                redirect('empresa-perfil', ['saved' => '1']);
            }
        }
        $this->page('company/profile', [
            'emp'   => $emp,
            'old'   => $old,
            'erros' => $erros,
            'cont'  => Vaga::contagens((int) $emp['id']),
        ], 'perfil', 'Perfil da empresa');
    }

    /** GET /painel/vagas/nova  GET /painel/vagas/{id}/editar  POST (both) */
    public function formVaga(): void
    {
        $emp = Auth::requireCompany();
        if ($emp['status'] !== Status::E_APROVADA) {
            flash('info', 'Sua empresa ainda não foi aprovada pela Coordenação. Assim que for, você poderá publicar vagas.');
            redirect('painel');
        }
        $id    = (int) ($_GET['id'] ?? 0);
        $atual = null;
        if ($id) {
            $atual = Vaga::daEmpresaPorId((int) $emp['id'], $id);
            if (!$atual) Auth::deny('vaga');
            if ($atual['status'] === Status::V_ENCERRADA) {
                flash('info', 'Vagas encerradas não podem ser editadas.');
                redirect('empresa-vagas');
            }
        }
        $erros = [];
        $old   = $atual
            ? JobForm::paraFormulario($atual)
            : ['remuneracao_tipo' => 'valor', 'data_publicacao' => date('Y-m-d'),
               'contato' => $emp['email_contato'] ?? $emp['email'],
               'dias' => ['seg', 'ter', 'qua', 'qui', 'sex']];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $old = limpar_post($_POST);
            [$dados, $erros] = JobForm::validar($old, $_FILES, $atual);
            if (!$erros) {
                if ($atual) {
                    [$mudou, $para] = Vaga::atualizar($id, $dados, (int) $emp['id']);
                    $v = Vaga::porId($id);
                    if ($mudou && $para === Status::V_AGUARDANDO) {
                        Notifier::vagaReenviada($v, $emp);
                        flash('ok', 'Alterações salvas. A vaga saiu do ar e volta depois de nova aprovação da Coordenação.');
                    } else {
                        flash('ok', $mudou ? 'Alterações salvas.' : 'Nada foi alterado.');
                    }
                    redirect('empresa-vaga', ['id' => $id]);
                }
                $novo = Vaga::criar($dados, (int) $emp['id']);
                Notifier::vagaEnviada(Vaga::porId($novo), $emp);
                flash('ok', 'Vaga enviada para análise. Você receberá um e-mail com o resultado e o período em que ficará no ar.');
                redirect('empresa-vaga', ['id' => $novo]);
            }
        }
        $this->page('company/job-form', [
            'emp'   => $emp,
            'atual' => $atual,
            'old'   => $old,
            'erros' => $erros,
            'areas' => Vaga::areas(),
        ], $atual ? 'vagas' : 'publicar', $atual ? 'Editar vaga' : 'Publicar vaga');
    }

    /** POST /painel/vagas/{id}/encerrar */
    public function encerrar(): void
    {
        $emp = Auth::requireCompany();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('empresa-vagas');
        Csrf::exigir();
        $v = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_POST['id'] ?? ($_GET['id'] ?? 0)));
        if (!$v) Auth::deny('vaga');
        if (Vaga::mudarStatus((int) $v['id'], Status::V_ENCERRADA, null, 'empresa', (int) $emp['id'])) {
            flash('ok', 'Vaga encerrada. Ela deixou de aparecer para os estudantes e continua no seu histórico.');
        } else {
            flash('erro', 'Só vagas aprovadas podem ser encerradas.');
        }
        redirect('empresa-vagas');
    }

    private const MEMBERS_PER_PAGE = 12;

    /** GET /painel/usuarios  POST /painel/usuarios */
    public function usuarios(): void
    {
        $emp = Auth::requireCompany();
        $actor = $emp['usuario'];
        if (!CompanyMember::canManageUsers($actor)) Auth::deny('usuarios');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $action = (string) ($_POST['acao'] ?? '');
            $page = max(1, (int) ($_POST['pg'] ?? 1));
            if ($action === 'criar') {
                [$member, $errors] = MemberActions::create($emp, $actor, limpar_post($_POST));
                if ($errors) {
                    $_SESSION['erros_membro'] = $errors;
                    $_SESSION['old_membro'] = limpar_post($_POST);
                } else {
                    flash('ok', $member['nome'] . ' foi adicionado. A senha provisória e o link para criar a senha foram enviados só para o e-mail dele.');
                }
                redirect('empresa-usuarios');
            }
            $target = CompanyMember::find((int) ($_POST['id'] ?? 0));
            if (!$target || (int) $target['empresa_id'] !== (int) $emp['id']) Auth::deny('usuario');
            $error = $action === 'papel'
                ? MemberActions::changeRole($emp, $actor, $target, (string) ($_POST['papel'] ?? ''))
                : MemberActions::changeStatus($emp, $target, $action, 'empresa', $actor, $actor['nome']);
            if ($error) flash('erro', $error);
            else flash('ok', ['papel' => 'Permissão atualizada.', 'desativar' => 'Acesso desativado.', 'ativar' => 'Acesso reativado.', 'excluir' => 'Usuário removido.'][$action] . ' ' . $target['nome'] . ' foi avisado por e-mail.');
            if (Mailer::$failures > 0) flash('erro', 'A ação foi feita, mas o e-mail NÃO foi enviado: ' . (Mailer::$lastError ?: 'erro desconhecido') . '.');
            redirect('empresa-usuarios', $page > 1 ? ['pg' => $page] : []);
        }
        [$members, $total, $pg, $pages] = CompanyMember::ofCompany((int) $emp['id'], max(1, (int) ($_GET['pg'] ?? 1)), self::MEMBERS_PER_PAGE);
        $this->page('company/users', [
            'emp'     => $emp,
            'membros' => $members,
            'total'   => $total,
            'pg'      => $pg,
            'paginas' => $pages,
            'erros'   => $_SESSION['erros_membro'] ?? [],
            'old'     => $_SESSION['old_membro'] ?? [],
        ], 'usuarios', 'Usuários da empresa');
        unset($_SESSION['erros_membro'], $_SESSION['old_membro']);
    }

    /** GET /painel/senha  POST /painel/senha */
    public function senha(): void
    {
        $emp = Auth::requireCompany(true);
        $member = $emp['usuario'];
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $current = (string) ($_POST['atual'] ?? '');
            $new = (string) ($_POST['nova'] ?? '');
            if (!password_verify($current, $member['senha_hash'])) $error = 'A senha atual (provisória) não confere.';
            elseif (!CompanyForm::passwordStrong($new)) $error = 'A nova senha precisa ter 8 ou mais caracteres, com letras e números.';
            elseif ($new === $current) $error = 'A nova senha precisa ser diferente da senha provisória.';
            elseif ($new !== (string) ($_POST['confirma'] ?? '')) $error = 'A confirmação não é igual à nova senha.';
            else {
                CompanyMember::changePassword((int) $member['id'], $new);
                flash('ok', 'Senha criada. Bem-vindo(a)!');
                redirect('painel');
            }
        }
        render('company/change-password', ['titulo' => 'Crie a sua senha', 'ambiente' => 'empresa', 'erro' => $error, 'forcada' => (bool) (int) $member['trocar_senha'], 'emp' => $emp], 'auth');
    }

    /** POST /painel/aviso-dispensar */
    public function dispensarAviso(): void
    {
        $emp = Auth::requireCompany();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            Company::dispensarAviso((int) $emp['id']);
        }
        redirect('painel');
    }
}
