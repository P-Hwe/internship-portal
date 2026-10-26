<?php

class Auth
{
    private const MAX_TENTATIVAS = 5;
    private const JANELA_MIN = 15;
    private static function ocioso(string $perfil): int { return (int) env('SESSION_IDLE_' . strtoupper($perfil), $perfil === 'admin' ? 1800 : 7200); }
    private const ABSOLUTO = 43200;
    public static function log(string $perfil, ?int $uid, ?string $email, string $evento): void
    {
        try {
            Database::insert('acessos_log', ['perfil' => $perfil, 'usuario_id' => $uid, 'email' => $email ? mb_substr($email, 0, 120) : null, 'evento' => $evento,
                'ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'), 0, 45), 'agente' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 160), 'criado_em' => Database::agora()]);
        } catch (Throwable $t) {  }
    }

    public static function empresa(): ?array
    {
        $id = (int) ($_SESSION['empresa_id'] ?? 0);
        if (!$id) return null;
        $e = Empresa::porId($id);
        if (!$e || $e['status'] === Estado::E_BLOQUEADA) { unset($_SESSION['empresa_id']); return null; }   // bloqueada no meio da sessão: cai na hora
        return $e;
    }
    public static function admin(): ?array
    {
        $id = (int) ($_SESSION['admin_id'] ?? 0);
        if (!$id) return null;
        $a = Admin::porId($id);
        if (!$a || !(int) $a['ativo']) { unset($_SESSION['admin_id']); return null; }
        return $a;
    }
    public static function perfil(): ?string { return self::admin() ? 'admin' : (self::empresa() ? 'empresa' : null); }

    public static function normalizar(): void
    {
        if (!empty($_SESSION['admin_id']) && !empty($_SESSION['empresa_id'])) unset($_SESSION['empresa_id']);
        $perfil = !empty($_SESSION['admin_id']) ? 'admin' : (!empty($_SESSION['empresa_id']) ? 'empresa' : null);
        if (!$perfil) return;
        $agora = time(); $ult = (int) ($_SESSION['_ultima'] ?? $agora); $ini = (int) ($_SESSION['_inicio'] ?? $agora);
        if ($agora - $ult > self::ocioso($perfil) || $agora - $ini > self::ABSOLUTO) {
            self::log($perfil, (int) $_SESSION[$perfil . '_id'], null, 'timeout');
            unset($_SESSION['admin_id'], $_SESSION['empresa_id'], $_SESSION['_ultima'], $_SESSION['_inicio']);
            session_regenerate_id(true);
            flash('info', 'Sua sessão expirou. Entre novamente.');
            return;
        }
        $_SESSION['_ultima'] = $agora; $_SESSION['_inicio'] = $ini;
    }

    public static function bloquearSeOutroPerfil(string $quero): void
    {
        $atual = self::perfil();
        if ($atual && $atual !== $quero) {
            self::log($atual, (int) ($_SESSION[$atual . '_id'] ?? 0), null, 'negado');
            flash('info', $atual === 'admin'
                ? 'Você está conectado como administrador. Saia da conta para entrar ou cadastrar como empresa.'
                : 'Você está conectado como empresa. Saia da conta para entrar como administrador.');
            redirect($atual === 'admin' ? 'admin' : 'painel');
        }
    }
    public static function exigirEmpresa(): array
    {
        self::bloquearSeOutroPerfil('empresa');
        $e = self::empresa();
        if (!$e) { flash('info', 'Entre com a conta da empresa para continuar.'); redirect('empresa-login', ['next' => $_GET['p'] ?? 'painel']); }
        return $e;
    }
    public static function exigirAdmin(bool $paginaDeTroca = false): array
    {
        self::bloquearSeOutroPerfil('admin');
        $a = self::admin();
        if (!$a) { flash('info', 'Acesso restrito à Coordenação/Secretaria.'); redirect('admin-login'); }
        if ((int) ($a['trocar_senha'] ?? 0) && !$paginaDeTroca) redirect('admin-senha');
        return $a;
    }
    public static function recusar(string $motivo = 'recurso'): void
    {
        $p = self::perfil() ?? 'anonimo';
        self::log($p, (int) ($_SESSION[$p . '_id'] ?? 0) ?: null, null, 'negado');
        http_response_code(404);
        $perfil = self::perfil();
        render('erro', ['titulo' => 'Não encontrado', 'msg' => 'O item não existe ou você não tem acesso a ele.', 'area' => $perfil ?? 'empresa', 'secao' => '', 'emp' => self::empresa() ?? [], 'adm' => self::admin() ?? []], $perfil ? 'app' : 'auth');
        exit;
    }

    public static function entrar(string $perfil, string $email, string $senha): array
    {
        $atual = self::perfil();
        if ($atual && $atual !== $perfil) return [null, 'Já existe um login ativo como ' . ($atual === 'admin' ? 'administrador' : 'empresa') . '. Saia primeiro.'];
        $email = mb_strtolower(trim($email));
        $chave = hash('sha256', $perfil . '|' . $email . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
        if (self::tentativas($chave) >= self::MAX_TENTATIVAS) {
            self::log($perfil, null, $email, 'bloqueado');
            return [null, 'Muitas tentativas. Aguarde ' . self::JANELA_MIN . ' minutos e tente de novo.'];
        }
        $u = $perfil === 'admin' ? Admin::porEmail($email) : Empresa::porEmail($email);
        $hash = $u['senha_hash'] ?? '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyzABCDE';
        if (!$u || !password_verify($senha, $hash)) {
            Database::insert('login_tentativas', ['chave' => $chave, 'criado_em' => Database::agora()]);
            self::log($perfil, $u ? (int) $u['id'] : null, $email, 'login_falha');
            return [null, 'E-mail ou senha incorretos.'];
        }
        if ($perfil === 'admin' && !(int) $u['ativo']) { self::log($perfil, (int) $u['id'], $email, 'negado'); return [null, 'Esta conta está desativada. Fale com a Coordenação.']; }
        if ($perfil === 'empresa' && $u['status'] === Estado::E_BLOQUEADA) { self::log($perfil, (int) $u['id'], $email, 'negado'); return [null, 'Esta conta está bloqueada. Fale com a Coordenação.']; }

        Database::run('DELETE FROM login_tentativas WHERE chave = :c', ['c' => $chave]);
        $anterior = $u['ultimo_acesso'];
        session_regenerate_id(true);
        $_SESSION[$perfil === 'admin' ? 'admin_id' : 'empresa_id'] = (int) $u['id'];
        $_SESSION['_ultima'] = $_SESSION['_inicio'] = time();
        Database::update($perfil === 'admin' ? 'admins' : 'empresas', (int) $u['id'], ['ultimo_acesso' => Database::agora()]);

        if ($perfil === 'empresa' && $u['status'] === Estado::E_APROVADA && !(int) $u['aprovacao_vista'] && $anterior && $u['avaliado_em'] && $anterior >= $u['avaliado_em']) {
            Database::update('empresas', (int) $u['id'], ['aprovacao_vista' => 1]);
        }
        self::log($perfil, (int) $u['id'], $email, 'login_ok');
        return [$u, null];
    }

    public static function iniciarSessao(string $perfil, int $id, ?string $email = null): void
    {
        session_regenerate_id(true);
        $_SESSION[$perfil === 'admin' ? 'admin_id' : 'empresa_id'] = $id;
        $_SESSION['_ultima'] = $_SESSION['_inicio'] = time();
        Database::update($perfil === 'admin' ? 'admins' : 'empresas', $id, ['ultimo_acesso' => Database::agora()]);
        self::log($perfil, $id, $email, 'login_ok');
    }

    public static function sair(string $perfil): void
    {
        self::log($perfil, (int) ($_SESSION[$perfil . '_id'] ?? 0) ?: null, null, 'logout');
        unset($_SESSION[$perfil === 'admin' ? 'admin_id' : 'empresa_id'], $_SESSION['_ultima'], $_SESSION['_inicio']);
        session_regenerate_id(true);
    }
    private static function tentativas(string $chave): int
    {
        $desde = date('Y-m-d H:i:s', time() - self::JANELA_MIN * 60);
        return (int) Database::val('SELECT COUNT(*) FROM login_tentativas WHERE chave = :c AND criado_em >= :d', ['c' => $chave, 'd' => $desde]);
    }
    public static function hash(string $senha): string { return password_hash($senha, PASSWORD_DEFAULT); }
}