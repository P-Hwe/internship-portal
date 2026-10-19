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
        render('empresa_login', ['titulo' => 'Entrar - Empresa', 'ambiente' => 'empresa', 'erro' => $erro, 'email' => $email, 'next' => $this->destino()], 'auth');
    }

    public function adminLogin(): void
    {
        Auth::bloquearSeOutroPerfil('admin');
        if (Auth::admin()) redirect('admin');
        $erro = null; $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $erro] = Auth::entrar('admin', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect('admin');
        }
        render('admin_login', ['titulo' => 'Entrar - Administração', 'ambiente' => 'admin', 'erro' => $erro, 'email' => $email], 'auth');
    }

    public function sair(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('home');
        Csrf::exigir();
        $perfil = ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'empresa';
        if (Auth::perfil() === $perfil) Auth::sair($perfil);   // só encerra o perfil que de fato está conectado
        flash('ok', 'Você saiu da conta.');
        redirect($perfil === 'admin' ? 'admin-login' : 'home');
    }
}
