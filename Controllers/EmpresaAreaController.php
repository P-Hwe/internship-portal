<?php
class EmpresaAreaController
{
    private function aprovada(array $emp): void
    {
        if ($emp['status'] !== Estado::E_APROVADA) { flash('info', 'Sua empresa ainda não foi aprovada pela Coordenação. Assim que for, você poderá publicar vagas.'); redirect('painel'); }
    }
    private function pagina(string $view, array $dados, string $secao, string $titulo): void
    {
        render($view, $dados + ['titulo' => $titulo, 'area' => 'empresa', 'secao' => $secao, 'emp' => $dados['emp']], 'app');
    }

    public function painel(): void
    {
        $emp = Auth::exigirEmpresa();
        $cont = Vaga::contagens((int) $emp['id']);
        [$recentes] = Vaga::listar((int) $emp['id'], 'todas', '', '', 'recentes', 1, 3);
        $vencendo = array_values(array_filter(Vaga::todasDaEmpresa((int) $emp['id']), fn($v) => $v['situacao'] === 'publicada' && $v['dias'] <= DIAS_VAGA_VENCENDO));
        $this->pagina('empresa_painel', ['emp' => $emp, 'cont' => $cont, 'recentes' => $recentes, 'vencendo' => $vencendo, 'aviso' => Empresa::mostrarAvisoAprovacao($emp), 'lead' => saudacao() . ', ' . primeiro_nome((string) $emp['responsavel']) . '. ' . ((int) ($cont['publicada'] ?? 0) ? 'Você tem ' . (int) $cont['publicada'] . ' vaga(s) no ar.' : 'Sua empresa ainda não tem vagas no ar.')], 'painel', 'Visão geral');
    }

    public function dispensarAviso(): void
    {
        $emp = Auth::exigirEmpresa();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { Csrf::exigir(); Empresa::dispensarAviso((int) $emp['id']); }
        redirect('painel');
    }

    public function vagas(): void
    {
        $emp = Auth::exigirEmpresa();
        $cont = Vaga::contagens((int) $emp['id']);
        $aba = $_GET['aba'] ?? 'todas';
        if (!in_array($aba, array_merge(['todas', 'destaque'], Estado::SITUACOES), true)) $aba = 'todas';
        $q = trim((string) ($_GET['q'] ?? '')); $pg = (int) ($_GET['pg'] ?? 1);
        [$lista, $total] = Vaga::listar((int) $emp['id'], $aba, $q, '', 'recentes', $pg);
        [, $pg, $paginas] = Paginador::calc($total, $pg, POR_PAGINA);
        $this->pagina('empresa_vagas', compact('emp', 'cont', 'aba', 'q', 'lista', 'total', 'pg', 'paginas'), 'vagas', 'Minhas vagas');
    }

    public function vaga(): void
    {
        $emp = Auth::exigirEmpresa();
        $v = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_GET['id'] ?? 0));
        if (!$v) Auth::recusar('vaga');
        $this->pagina('empresa_vaga', ['emp' => $emp, 'v' => $v, 'hist' => Historico::da('vaga', (int) $v['id'])], 'vagas', $v['titulo']);
    }

    public function perfil(): void
    {
        $emp = Auth::exigirEmpresa();
        $erros = []; $old = $emp;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            if (($_POST['acao'] ?? '') === 'reenviar') {
                if (Empresa::mudarStatus((int) $emp['id'], Estado::E_PENDENTE, null, 'empresa', (int) $emp['id'])) {
                    Notificador::empresaCadastrada(Empresa::porId((int) $emp['id']));
                    flash('ok', 'Cadastro reenviado para análise.');
                }
                redirect('painel');
            }
            $old = limpar_post($_POST) + ['logo' => $emp['logo'], 'banner' => $emp['banner']];
            [$dados, $erros] = EmpresaForm::validar($old, $_FILES, 'perfil', $emp);
            if (!$erros) {
                if (empty($dados['logo'])) $dados['logo'] = $emp['logo'];
                elseif ($emp['logo'] && $dados['logo'] !== $emp['logo']) @unlink(ROOT_PATH . '/' . $emp['logo']);
                if (empty($dados['banner'])) $dados['banner'] = $emp['banner'];
                elseif ($emp['banner'] && $dados['banner'] !== $emp['banner']) @unlink(ROOT_PATH . '/' . $emp['banner']);
                Empresa::atualizarPerfil((int) $emp['id'], $dados);
                flash('ok', 'Perfil atualizado.');
                redirect('empresa-perfil');
            }
        }
        $this->pagina('empresa_perfil', ['emp' => $emp, 'old' => $old, 'erros' => $erros, 'cont' => Vaga::contagens((int) $emp['id'])], 'perfil', 'Perfil da empresa');
    }

    public function formVaga(): void
    {
        $emp = Auth::exigirEmpresa(); $this->aprovada($emp);
        $id = (int) ($_GET['id'] ?? 0); $atual = null;
        if ($id) {
            $atual = Vaga::daEmpresaPorId((int) $emp['id'], $id);
            if (!$atual) Auth::recusar('vaga');
            if ($atual['status'] === Estado::V_ENCERRADA) { flash('info', 'Vagas encerradas não podem ser editadas.'); redirect('empresa-vagas'); }
        }
        $erros = []; $old = $atual ? VagaForm::paraFormulario($atual) : ['remuneracao_tipo' => 'valor', 'data_publicacao' => date('Y-m-d'), 'contato' => $emp['email_contato'] ?: $emp['email'], 'dias' => ['seg', 'ter', 'qua', 'qui', 'sex']];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $old = limpar_post($_POST);
            [$dados, $erros] = VagaForm::validar($old, $_FILES, $atual);
            if (!$erros) {
                if ($atual) {
                    [$mudou, $para] = Vaga::atualizar($id, $dados, (int) $emp['id']);
                    $v = Vaga::porId($id);
                    if ($mudou && $para === Estado::V_AGUARDANDO) {
                        Notificador::vagaReenviada($v, $emp);
                        flash('ok', 'Alterações salvas. A vaga saiu do ar e volta depois de nova aprovação da Coordenação.');
                    } else flash('ok', $mudou ? 'Alterações salvas.' : 'Nada foi alterado.');
                    redirect('empresa-vaga', ['id' => $id]);
                }
                $novo = Vaga::criar($dados, (int) $emp['id']);
                Notificador::vagaEnviada(Vaga::porId($novo), $emp);
                flash('ok', 'Vaga enviada para análise. Você receberá um e-mail com o resultado e o período em que ficará no ar.');
                redirect('empresa-vaga', ['id' => $novo]);
            }
        }
        $this->pagina('vaga_form', ['emp' => $emp, 'atual' => $atual, 'old' => $old, 'erros' => $erros, 'areas' => Vaga::areas()], 'vagas', $atual ? 'Editar vaga' : 'Publicar vaga');
    }

    public function encerrar(): void
    {
        $emp = Auth::exigirEmpresa();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('empresa-vagas');
        Csrf::exigir();
        $v = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_POST['id'] ?? 0));
        if (!$v) Auth::recusar('vaga');
        if (Vaga::mudarStatus((int) $v['id'], Estado::V_ENCERRADA, null, 'empresa', (int) $emp['id'])) flash('ok', 'Vaga encerrada. Ela deixou de aparecer para os estudantes e continua no seu histórico.');
        else flash('erro', 'Só vagas aprovadas podem ser encerradas.');
        redirect('empresa-vagas');
    }
}