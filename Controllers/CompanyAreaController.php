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
        [$recentes] = Vaga::listar((int) $emp['id'], 'todas', '', '', 'recentes', 1, 3);
        $vencendo = array_values(array_filter(
            Vaga::todasDaEmpresa((int) $emp['id']),
            fn($v) => $v['situacao'] === 'publicada' && $v['dias'] <= DIAS_VAGA_VENCENDO
        ));
        $this->page('company/dashboard', [
            'emp'      => $emp,
            'cont'     => $cont,
            'recentes' => $recentes,
            'vencendo' => $vencendo,
            'aviso'    => Company::mostrarAvisoAprovacao($emp),
            'lead'     => saudacao() . ', ' . primeiro_nome((string) $emp['responsavel']) . '. '
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
        [$lista, $total] = Vaga::listar((int) $emp['id'], $aba, $q, '', 'recentes', $pg);
        [, $pg, $paginas] = Paginator::calc($total, $pg, POR_PAGINA);
        $this->page('company/job-listings', compact('emp', 'cont', 'aba', 'q', 'lista', 'total', 'pg', 'paginas'), 'vagas', 'Minhas vagas');
    }

    /** GET /painel/vagas/{id} */
    public function vaga(): void
    {
        $emp = Auth::requireCompany();
        $v   = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_GET['id'] ?? 0));
        if (!$v) Auth::deny('vaga');
        $this->page('company/job-detail', ['emp' => $emp, 'v' => $v, 'hist' => AuditLog::da('vaga', (int) $v['id'])], 'vagas', $v['titulo']);
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
