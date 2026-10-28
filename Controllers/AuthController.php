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
        Auth::bloquearSeOutroPerfil('empresa');
        if (Auth::empresa()) redirect('painel');
        $erro = null; $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $erro] = Auth::entrar('empresa', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect($this->destino());
        }
        render('empresa_login', ['titulo' => 'Entrar - Empresa', 'ambiente' => 'empresa', 'erro' => $erro, 'email' => $email, 'next' => $this->destino(), 'modal' => $this->modalSenha('empresa')], 'auth');
    }

    public function adminLogin(): void
    {
        Auth::bloquearSeOutroPerfil('admin');
        if (Auth::admin()) redirect('admin');
        $erro = null; $email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $erro] = Auth::entrar('admin', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect('admin');
        }
        render('admin_login', ['titulo' => 'Entrar - Administração', 'ambiente' => 'admin', 'erro' => $erro, 'email' => $email, 'modal' => $this->modalSenha('admin')], 'auth');
    }

    public function sair(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('home');
        Csrf::exigir();
        $perfil = ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'empresa';
        if (Auth::perfil() === $perfil) Auth::sair($perfil);
        flash('ok', 'Você saiu da conta.');
        redirect($perfil === 'admin' ? 'admin-login' : 'home');
    }

    // ---------- "esqueci a senha" ----------
    private function perfilSenha(): string { return ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'empresa'; }

    private function modalSenha(string $perfil): ?array
    {
        $m = $_SESSION['reset_modal'] ?? null; unset($_SESSION['reset_modal']);
        if (is_array($m) && ($m['perfil'] ?? '') === $perfil) return $m;
        if (!empty($_GET['redefinir'])) {
            $t = (string) $_GET['redefinir']; $r = SenhaReset::porToken($perfil, $t);
            if ($r) return ['perfil' => $perfil, 'passo' => 2, 'email' => $r['email'], 'token' => $t, 'erro' => null];
            return ['perfil' => $perfil, 'passo' => 1, 'erro' => 'Este link venceu ou já foi usado. Peça um novo por e-mail.'];
        }
        if (!empty($_GET['esqueci'])) return ['perfil' => $perfil, 'passo' => 1, 'erro' => null];
        return null;
    }
    private function voltarLogin(string $perfil): void { redirect($perfil === 'admin' ? 'admin-login' : 'empresa-login'); }

    public function senhaEsqueci(): void
    {
        $perfil = $this->perfilSenha();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->voltarLogin($perfil);
        Csrf::exigir();
        $email = trim((string) ($_POST['email'] ?? ''));
        $pedido = SenhaReset::solicitar($perfil, $email);
        if ($pedido) Notificador::senhaRedefinicao($perfil, $pedido['usuario'], $pedido['token'], $pedido['codigo']);
        $_SESSION['reset_modal'] = ['perfil' => $perfil, 'passo' => 2, 'email' => mb_strtolower($email), 'token' => '', 'erro' => null,
            'aviso' => 'Se este e-mail tiver cadastro, enviamos um link e um código de 6 dígitos. Eles valem ' . SenhaReset::VALIDADE_MIN . ' minutos.'];
        $this->voltarLogin($perfil);
    }

    public function senhaRedefinir(): void
    {
        $perfil = $this->perfilSenha();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->voltarLogin($perfil);
        Csrf::exigir();
        $token = (string) ($_POST['token'] ?? ''); $email = trim((string) ($_POST['email'] ?? '')); $codigo = preg_replace('/\D/', '', (string) ($_POST['codigo'] ?? ''));
        $nova = (string) ($_POST['nova'] ?? ''); $conf = (string) ($_POST['confirma'] ?? '');
        $r = $token !== '' ? SenhaReset::porToken($perfil, $token) : SenhaReset::porCodigo($perfil, $email, $codigo);
        $erro = null;
        if (!$r) $erro = $token !== '' ? 'Este link venceu ou já foi usado. Peça um novo.' : 'Código incorreto ou vencido. Confira o e-mail ou peça um novo código.';
        elseif (!EmpresaForm::senhaForte($nova)) $erro = 'A nova senha precisa ter 8 ou mais caracteres, com letras e números.';
        elseif ($nova !== $conf) $erro = 'A confirmação não é igual à nova senha.';
        if ($erro) {
            $_SESSION['reset_modal'] = ['perfil' => $perfil, 'passo' => $r || $token === '' ? 2 : 1, 'email' => mb_strtolower($email), 'token' => $r ? $token : '', 'erro' => $erro];
            $this->voltarLogin($perfil);
        }
        SenhaReset::concluir($r, $nova);
        Notificador::senhaAlterada($perfil, ['email' => $r['email'], 'nome' => $r['nome'], 'responsavel' => $r['nome']]);
        flash('ok', 'Senha alterada. Entre com a nova senha.');
        $this->voltarLogin($perfil);
    }
}
