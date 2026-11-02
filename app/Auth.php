<?php
class Auth
{
    private const MAX_ATTEMPTS      = 5;
    private const MAX_IP_ATTEMPTS   = 30;
    private const RATE_WINDOW_MIN   = 15;
    private const ABSOLUTE_LIMIT    = 86400;
    private const DUMMY_HASH        = '$2y$12$897Wv//ow2tzz0QODCVNGu44bCLcQSqDsCpHC7jI1aR.FVArSya86';

    private static function idleTimeout(string $profile): int
    {
        return (int) env('SESSION_IDLE_' . strtoupper($profile), $profile === 'admin' ? 1800 : 7200);
    }

    public static function log(string $profile, ?int $uid, ?string $email, string $event): void
    {
        try {
            Database::insert('acessos_log', [
                'perfil'     => $profile,
                'usuario_id' => $uid,
                'email'      => $email ? mb_substr($email, 0, 120) : null,
                'evento'     => $event,
                'ip'         => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'), 0, 45),
                'agente'     => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 160),
                'criado_em'  => Database::now(),
            ]);
        } catch (Throwable $e) { }
    }

    public static function empresa(): ?array
    {
        $id = (int) ($_SESSION['empresa_id'] ?? 0);
        if (!$id) return null;
        $company = Company::porId($id);
        if (!$company || $company['status'] === Status::E_BLOQUEADA) {
            unset($_SESSION['empresa_id']);
            return null;
        }
        return $company;
    }

    public static function admin(): ?array
    {
        $id = (int) ($_SESSION['admin_id'] ?? 0);
        if (!$id) return null;
        $admin = Admin::findById($id);
        if (!$admin || !(int) $admin['ativo']) {
            unset($_SESSION['admin_id']);
            return null;
        }
        return $admin;
    }

    public static function role(): ?string
    {
        return self::admin() ? 'admin' : (self::empresa() ? 'empresa' : null);
    }

    /** @deprecated Use role() */
    public static function perfil(): ?string { return self::role(); }

    public static function normalize(): void
    {
        if (!empty($_SESSION['admin_id']) && !empty($_SESSION['empresa_id'])) {
            unset($_SESSION['empresa_id']);
        }
        $profile = !empty($_SESSION['admin_id']) ? 'admin' : (!empty($_SESSION['empresa_id']) ? 'empresa' : null);
        if (!$profile) return;
        $now          = time();
        $lastActivity = (int) ($_SESSION['_ultima'] ?? $now);
        $sessionStart = (int) ($_SESSION['_inicio'] ?? $now);
        if ($now - $lastActivity > self::idleTimeout($profile) || $now - $sessionStart > self::ABSOLUTE_LIMIT) {
            self::log($profile, (int) $_SESSION[$profile . '_id'], null, 'timeout');
            unset($_SESSION['admin_id'], $_SESSION['empresa_id'], $_SESSION['_ultima'], $_SESSION['_inicio']);
            session_regenerate_id(true);
            flash('info', 'Sua sessão expirou. Entre novamente.');
            return;
        }
        $_SESSION['_ultima'] = $now;
        $_SESSION['_inicio'] = $sessionStart;
    }

    /** @deprecated Use normalize() */
    public static function normalizar(): void { self::normalize(); }

    public static function blockIfWrongRole(string $wanted): void
    {
        $current = self::role();
        if ($current && $current !== $wanted) {
            self::log($current, (int) ($_SESSION[$current . '_id'] ?? 0), null, 'negado');
            redirect($wanted === 'admin' ? 'admin-login' : 'empresa-login');
        }
    }

    /** @deprecated Use blockIfWrongRole() */
    public static function bloquearSeOutroPerfil(string $wanted): void { self::blockIfWrongRole($wanted); }

    public static function requireCompany(): array
    {
        self::blockIfWrongRole('empresa');
        $company = self::empresa();
        if (!$company) {
            flash('info', 'Entre com a conta da empresa para continuar.');
            redirect('empresa-login', ['next' => $_GET['p'] ?? 'painel']);
        }
        return $company;
    }

    /** @deprecated Use requireCompany() */
    public static function exigirEmpresa(): array { return self::requireCompany(); }

    public static function requireAdmin(bool $passwordChangePage = false): array
    {
        self::blockIfWrongRole('admin');
        $admin = self::admin();
        if (!$admin) {
            flash('info', 'Acesso restrito à Coordenação/Secretaria.');
            redirect('admin-login');
        }
        if ((int) ($admin['trocar_senha'] ?? 0) && !$passwordChangePage) {
            redirect('admin-senha');
        }
        return $admin;
    }

    /** @deprecated Use requireAdmin() */
    public static function exigirAdmin(bool $paginaDeTroca = false): array { return self::requireAdmin($paginaDeTroca); }

    public static function deny(string $resource = 'resource'): void
    {
        $role = self::role() ?? 'anonimo';
        self::log($role, (int) ($_SESSION[$role . '_id'] ?? 0) ?: null, null, 'negado');
        http_response_code(404);
        $currentRole = self::role();
        render('shared/error', ['titulo' => 'Não encontrado', 'msg' => 'O item não existe ou você não tem acesso a ele.', 'area' => $currentRole ?? 'empresa', 'secao' => '', 'emp' => self::empresa() ?? [], 'adm' => self::admin() ?? []], $currentRole ? 'app' : 'auth');
        exit;
    }

    /** @deprecated Use deny() */
    public static function recusar(string $motivo = 'recurso'): void { self::deny($motivo); }

    public static function login(string $profile, string $email, string $password): array
    {
        $current = self::role();
        $email = mb_strtolower(trim($email));
        $ip    = (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli');
        $key   = hash('sha256', $profile . '|' . $email . '|' . $ip);
        $ipKey = hash('sha256', 'ip|' . $ip);
        if (self::loginAttempts($key) >= self::MAX_ATTEMPTS || self::loginAttempts($ipKey) >= self::MAX_IP_ATTEMPTS) {
            self::log($profile, null, $email, 'bloqueado');
            return [null, 'Muitas tentativas. Aguarde ' . self::RATE_WINDOW_MIN . ' minutos e tente de novo.'];
        }
        if (mb_strlen($password) > 4096) {
            return [null, 'E-mail ou senha incorretos.'];
        }
        $user = $profile === 'admin' ? Admin::findByEmail($email) : Company::porEmail($email);
        $hash = $user['senha_hash'] ?? self::DUMMY_HASH;
        if (!password_verify($password, $hash) || !$user) {
            Database::insert('login_tentativas', ['chave' => $key, 'criado_em' => Database::now()]);
            Database::insert('login_tentativas', ['chave' => $ipKey, 'criado_em' => Database::now()]);
            self::log($profile, $user ? (int) $user['id'] : null, $email, 'login_falha');
            return [null, 'E-mail ou senha incorretos.'];
        }
        if ($profile === 'admin' && !(int) $user['ativo']) {
            self::log($profile, (int) $user['id'], $email, 'negado');
            return [null, 'Esta conta está desativada. Fale com a Coordenação.'];
        }
        if ($profile === 'empresa' && $user['status'] === Status::E_BLOQUEADA) {
            self::log($profile, (int) $user['id'], $email, 'negado');
            return [null, 'Esta conta está bloqueada. Fale com a Coordenação.'];
        }
        Database::run('DELETE FROM login_tentativas WHERE chave = :c', ['c' => $key]);
        if (password_needs_rehash($user['senha_hash'], PASSWORD_DEFAULT)) {
            Database::update($profile === 'admin' ? 'admins' : 'empresas', (int) $user['id'], ['senha_hash' => self::hash($password)]);
        }
        if ($current && $current !== $profile) {
            self::logout($current);
        }
        $previousAccess = $user['ultimo_acesso'];
        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
        $_SESSION[$profile === 'admin' ? 'admin_id' : 'empresa_id'] = (int) $user['id'];
        $_SESSION['_ultima'] = $_SESSION['_inicio'] = time();
        Database::update($profile === 'admin' ? 'admins' : 'empresas', (int) $user['id'], ['ultimo_acesso' => Database::now()]);
        if ($profile === 'empresa' && $user['status'] === Status::E_APROVADA && !(int) $user['aprovacao_vista'] && $previousAccess && $user['avaliado_em'] && $previousAccess >= $user['avaliado_em']) {
            Database::update('empresas', (int) $user['id'], ['aprovacao_vista' => 1]);
        }
        self::log($profile, (int) $user['id'], $email, 'login_ok');
        return [$user, null];
    }

    /** @deprecated Use login() */
    public static function entrar(string $perfil, string $email, string $senha): array { return self::login($perfil, $email, $senha); }

    public static function startSession(string $profile, int $id, ?string $email = null): void
    {
        session_regenerate_id(true);
        $_SESSION[$profile === 'admin' ? 'admin_id' : 'empresa_id'] = $id;
        $_SESSION['_ultima'] = $_SESSION['_inicio'] = time();
        Database::update($profile === 'admin' ? 'admins' : 'empresas', $id, ['ultimo_acesso' => Database::now()]);
        self::log($profile, $id, $email, 'login_ok');
    }

    /** @deprecated Use startSession() */
    public static function iniciarSessao(string $perfil, int $id, ?string $email = null): void { self::startSession($perfil, $id, $email); }

    public static function logout(string $profile): void
    {
        self::log($profile, (int) ($_SESSION[$profile . '_id'] ?? 0) ?: null, null, 'logout');
        unset($_SESSION[$profile === 'admin' ? 'admin_id' : 'empresa_id'], $_SESSION['_ultima'], $_SESSION['_inicio']);
        session_regenerate_id(true);
    }

    /** @deprecated Use logout() */
    public static function sair(string $perfil): void { self::logout($perfil); }

    private static function loginAttempts(string $key): int
    {
        $since = date('Y-m-d H:i:s', time() - self::RATE_WINDOW_MIN * 60);
        return (int) Database::val('SELECT COUNT(*) FROM login_tentativas WHERE chave = :c AND criado_em >= :d', ['c' => $key, 'd' => $since]);
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function temporaryPassword(int $length = 14): string
    {
        $letters = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $all = $letters . $digits;
        $chars = [$letters[random_int(0, strlen($letters) - 1)], $digits[random_int(0, strlen($digits) - 1)]];
        while (count($chars) < $length) $chars[] = $all[random_int(0, strlen($all) - 1)];
        for ($i = count($chars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }
        return implode('', $chars);
    }
}
