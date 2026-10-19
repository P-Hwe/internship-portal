<?php
class EmpresaController
{
    public function cadastro(): void
    {
        Auth::bloquearSeOutroPerfil('empresa');
        if (Auth::empresa()) redirect('painel');
        $erros = []; $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $old = clean_post($_POST);
            [$dados, $erros] = EmpresaForm::validar($old, $_FILES, 'cadastro');
            if (!$erros) {
                $id = Empresa::criar($dados);
                Notificador::empresaCadastrada(Empresa::porId($id));
                Auth::iniciarSessao('empresa', $id, $dados['email']);
                $_SESSION['concluido'] = $id;
                redirect('empresa-concluido');
            }
        }
        render('empresa_cadastro', ['titulo' => 'Cadastro de empresa', 'ambiente' => 'empresa', 'erros' => $erros, 'old' => $old], 'auth');
    }

    public function concluido(): void
    {
        $emp = Auth::exigirEmpresa();
        if ((int) ($_SESSION['concluido'] ?? 0) !== (int) $emp['id']) redirect('painel');
        unset($_SESSION['concluido']);
        render('empresa_concluido', ['titulo' => 'Cadastro enviado', 'ambiente' => 'empresa', 'emp' => $emp], 'auth');
    }
}
