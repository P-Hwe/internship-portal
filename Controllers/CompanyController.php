<?php
class CompanyController
{
    public function cadastro(): void
    {
        Auth::blockIfWrongRole('empresa');
        if (Auth::empresa()) redirect('painel');
        $errors = []; $formData = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $formData = limpar_post($_POST);
            [$data, $errors] = CompanyForm::validar($formData, $_FILES, 'cadastro');
            if (!$errors) {
                $id = Company::criar($data);
                Notifier::empresaCadastrada(Company::porId($id));
                Auth::startSession('empresa', $id, $data['email']);
                $_SESSION['concluido'] = $id;
                redirect('empresa-concluido');
            }
        }
        render('company/register', ['titulo' => 'Cadastro de empresa', 'ambiente' => 'empresa', 'erros' => $errors, 'old' => $formData], 'auth');
    }

    public function concluido(): void
    {
        $emp = Auth::requireCompany();
        if ((int) ($_SESSION['concluido'] ?? 0) !== (int) $emp['id']) redirect('painel');
        unset($_SESSION['concluido']);
        render('company/register-success', ['titulo' => 'Cadastro enviado', 'ambiente' => 'empresa', 'emp' => $emp], 'auth');
    }
}

class_alias('CompanyController', 'EmpresaController');
