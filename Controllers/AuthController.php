<?php
class AuthController
{
    private function destino(): string
    {
        $n = $_GET['next'] ?? $_POST['next'] ?? '';
        return in_array($n, ['painel', 'vaga-cadastro', 'empresa-vagas', 'empresa-perfil'], true) ? $n : 'painel';
    }

    public function empresaLogin(): void
    {
        Auth::blockIfWrongRole('empresa');
        if (Auth::empresa()) redirect('painel');
        $error = null; $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $error] = Auth::login('empresa', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect($this->destino());
        }
        render('company/login', ['titulo' => 'Entrar - Empresa', 'ambiente' => 'empresa', 'erro' => $error, 'email' => $email, 'next' => $this->destino(), 'modal' => $this->modalSenha('empresa')], 'auth');
    }

    public function adminLogin(): void
    {
        Auth::blockIfWrongRole('admin');
        if (Auth::admin()) redirect('admin');
        $error = null; $email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $error] = Auth::login('admin', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect('admin');
        }
        render('admin/login', ['titulo' => 'Entrar - Administração', 'ambiente' => 'admin', 'erro' => $error, 'email' => $email, 'modal' => $this->modalSenha('admin')], 'auth');
    }

    public function sair(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('home');
        Csrf::exigir();
        $profile = ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'empresa';
        if (Auth::role() === $profile) Auth::logout($profile);
        flash('ok', 'Você saiu da conta.');
        redirect($profile === 'admin' ? 'admin-login' : 'home');
    }

    private function perfilSenha(): string { return ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'empresa'; }

    private function modalSenha(string $profile): ?array
    {
        $modal = $_SESSION['reset_modal'] ?? null; unset($_SESSION['reset_modal']);
        if (is_array($modal) && ($modal['perfil'] ?? '') === $profile) return $modal;
        if (!empty($_GET['redefinir'])) {
            $tokenStr = (string) $_GET['redefinir'];
            $reset = PasswordReset::findByToken($profile, $tokenStr);
            if ($reset) return ['perfil' => $profile, 'passo' => 2, 'email' => $reset['email'], 'token' => $tokenStr, 'erro' => null];
            return ['perfil' => $profile, 'passo' => 1, 'erro' => 'Este link venceu ou já foi usado. Peça um novo por e-mail.'];
        }
        if (!empty($_GET['esqueci'])) return ['perfil' => $profile, 'passo' => 1, 'erro' => null];
        return null;
    }

    private function voltarLogin(string $profile): void { redirect($profile === 'admin' ? 'admin-login' : 'empresa-login'); }

    public function senhaEsqueci(): void
    {
        $profile = $this->perfilSenha();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->voltarLogin($profile);
        Csrf::exigir();
        $email   = trim((string) ($_POST['email'] ?? ''));
        $request = PasswordReset::request($profile, $email);
        if ($request) Notifier::senhaRedefinicao($profile, $request['usuario'], $request['token'], $request['codigo']);
        $_SESSION['reset_modal'] = ['perfil' => $profile, 'passo' => 2, 'email' => mb_strtolower($email), 'token' => '', 'erro' => null,
            'aviso' => 'Se este e-mail tiver cadastro, enviamos um link e um código de 6 dígitos. Eles valem ' . PasswordReset::VALIDADE_MIN . ' minutos.'];
        $this->voltarLogin($profile);
    }

    public function senhaRedefinir(): void
    {
        $profile = $this->perfilSenha();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->voltarLogin($profile);
        Csrf::exigir();
        $token       = (string) ($_POST['token'] ?? '');
        $email       = trim((string) ($_POST['email'] ?? ''));
        $code        = preg_replace('/\D/', '', (string) ($_POST['codigo'] ?? ''));
        $newPassword = (string) ($_POST['nova'] ?? '');
        $confirm     = (string) ($_POST['confirma'] ?? '');
        $reset = $token !== '' ? PasswordReset::findByToken($profile, $token) : PasswordReset::findByCode($profile, $email, $code);
        $error = null;
        if (!$reset)                                 $error = $token !== '' ? 'Este link venceu ou já foi usado. Peça um novo.' : 'Código incorreto ou vencido. Confira o e-mail ou peça um novo código.';
        elseif (!CompanyForm::passwordStrong($newPassword)) $error = 'A nova senha precisa ter 8 ou mais caracteres, com letras e números.';
        elseif ($newPassword !== $confirm)           $error = 'A confirmação não é igual à nova senha.';
        if ($error) {
            $_SESSION['reset_modal'] = ['perfil' => $profile, 'passo' => $reset || $token === '' ? 2 : 1, 'email' => mb_strtolower($email), 'token' => $reset ? $token : '', 'erro' => $error];
            $this->voltarLogin($profile);
        }
        PasswordReset::complete($reset, $newPassword);
        Notifier::senhaAlterada($profile, ['email' => $reset['email'], 'nome' => $reset['nome'], 'responsavel' => $reset['nome']]);
        flash('ok', 'Senha alterada. Entre com a nova senha.');
        $this->voltarLogin($profile);
    }
}
