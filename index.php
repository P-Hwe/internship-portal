<?php
require_once __DIR__ . '/config.php';

Auth::normalize();
header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

$rotas = [

    'home'              => ['JobController',            'index'],
    'vaga'              => ['JobController',            'mostrar'],
    'encerradas'        => ['JobController',            'encerradas'],
    'tv'                => ['JobController',            'tv'],
    'empresas'          => ['PartnersController',       'index'],
    'empresa'           => ['PartnersController',       'perfil'],
    'externo'           => ['PartnersController',       'externo'],

    'empresa-login'     => ['AuthController',          'empresaLogin'],
    'admin-login'       => ['AuthController',          'adminLogin'],
    'sair'              => ['AuthController',          'sair'],
    'senha-esqueci'     => ['AuthController',          'senhaEsqueci'],
    'senha-redefinir'   => ['AuthController',          'senhaRedefinir'],
    'empresa-cadastro'  => ['CompanyController',        'cadastro'],
    'empresa-concluido' => ['CompanyController',        'concluido'],

    'painel'            => ['CompanyAreaController',    'painel'],
    'empresa-vagas'     => ['CompanyAreaController',    'vagas'],
    'empresa-vaga'      => ['CompanyAreaController',    'vaga'],
    'empresa-perfil'    => ['CompanyAreaController',    'perfil'],
    'vaga-cadastro'     => ['CompanyAreaController',    'formVaga'],
    'vaga-editar'       => ['CompanyAreaController',    'formVaga'],
    'vaga-encerrar'     => ['CompanyAreaController',    'encerrar'],
    'aviso-dispensar'   => ['CompanyAreaController',    'dispensarAviso'],

    'admin'             => ['AdminController',         'painel'],
    'admin-vagas'       => ['AdminController',         'vagas'],
    'admin-vaga'        => ['AdminController',         'vaga'],
    'admin-empresas'    => ['AdminController',         'empresas'],
    'admin-empresa'     => ['AdminController',         'empresa'],
    'admin-notificacao' => ['AdminController',         'notificacao'],
    'admin-senha'       => ['AdminController',         'senha'],
    'admin-atividade'   => ['AdminController',         'atividade'],
    'admin-aprovacoes'  => ['AdminController',         'aprovacoes'],
    'admin-usuarios'    => ['AdminController',         'usuarios'],
    'admin-desempenho'  => ['AdminController',         'desempenho'],
    'admin-relatorios'  => ['AdminController',         'relatorios'],
    'admin-acao'        => ['AdminController',         'acao'],
    'admin-termo'       => ['AdminController',         'termo'],
    'admin-exportar'    => ['AdminController',         'exportar'],
];

$pagina = $_GET['p'] ?? null;
if ($pagina === null) {
    $r = Router::resolver(Router::caminhoAtual());
    if ($r) {
        $pagina = $r[0];
        foreach ($r[1] as $k => $v) $_GET[$k] = $v;
    } else {
        $pagina = '404';
    }
}
if (isset($_GET['id']) && !is_scalar($_GET['id'])) {
    unset($_GET['id']);
}
if (!is_string($pagina) || !isset($rotas[$pagina])) {
    pagina_404();
    exit;
}

try {
    Bootstrap::ensure();
    [$classe, $metodo] = $rotas[$pagina];
    (new $classe())->$metodo();
} catch (Throwable $t) {
    error_log($t);
    @file_put_contents(ROOT_PATH . '/storage/erro.log', date('c') . ' ' . $t . "\n\n", FILE_APPEND);
    http_response_code(500);
    $hint = Bootstrap::hint($t);
    echo env('APP_DEBUG') ? '<pre>' . e((string) $t) . '</pre>' : 'Erro interno. ' . ($hint ?: 'Detalhes em storage/erro.log.');
}