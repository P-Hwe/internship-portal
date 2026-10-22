<?php
require_once __DIR__ . '/config.php';

Auth::normalizar();
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// ?p=<rota> => [Controller, método]
$rotas = [
    // público 
    'home'              => ['VagaController',          'index'],
    'vaga'              => ['VagaController',          'mostrar'],
    'encerradas'        => ['VagaController',          'encerradas'],
    'tv'                => ['VagaController',          'tv'],
    'empresas'          => ['ParceirasController',     'index'],
    'empresa'           => ['ParceirasController',     'perfil'],
    'externo'           => ['ParceirasController',     'externo'],
    // autenticação e cadastro 
    'empresa-login'     => ['AuthController',          'empresaLogin'],
    'admin-login'       => ['AuthController',          'adminLogin'],
    'sair'              => ['AuthController',          'sair'],
    'empresa-cadastro'  => ['EmpresaController',       'cadastro'],
    'empresa-concluido' => ['EmpresaController',       'concluido'],
    // área da empresa
    'painel'            => ['EmpresaAreaController',   'painel'],
    'empresa-vagas'     => ['EmpresaAreaController',   'vagas'],
    'empresa-vaga'      => ['EmpresaAreaController',   'vaga'],
    'empresa-perfil'    => ['EmpresaAreaController',   'perfil'],
    'vaga-cadastro'     => ['EmpresaAreaController',   'formVaga'],
    'vaga-editar'       => ['EmpresaAreaController',   'formVaga'],
    'vaga-encerrar'     => ['EmpresaAreaController',   'encerrar'],
    'aviso-dispensar'   => ['EmpresaAreaController',   'dispensarAviso'],
    // administração
    'admin'             => ['AdminController',         'painel'],
    'admin-vagas'       => ['AdminController',         'vagas'],
    'admin-vaga'        => ['AdminController',         'vaga'],
    'admin-empresas'    => ['AdminController',         'empresas'],
    'admin-empresa'     => ['AdminController',         'empresa'],
    'admin-aprovacoes'  => ['AdminController',         'aprovacoes'],
    'admin-usuarios'    => ['AdminController',         'usuarios'],
    'admin-acesso'      => ['AdminController',         'acesso'],
    'admin-relatorios'  => ['AdminController',         'relatorios'],
    'admin-config'      => ['AdminController',         'config'],
    'admin-acao'        => ['AdminController',         'acao'],
    'admin-termo'       => ['AdminController',         'termo'],
    'admin-exportar'    => ['AdminController',         'exportar'],
];

$pagina = $_GET['p'] ?? 'home';
if (isset($_GET['id']) && !is_scalar($_GET['id'])) { unset($_GET['id']); }
if (!is_string($pagina) || !isset($rotas[$pagina])) { http_response_code(404); $pagina = 'home'; }

try {
    Bootstrap::garantir();
    [$classe, $metodo] = $rotas[$pagina];
    (new $classe())->$metodo(); // @phpstan-ignore class.notFound
} catch (Throwable $t) {
    error_log($t);
    @file_put_contents(ROOT_PATH . '/storage/erro.log', date('c') . ' ' . $t . "\n\n", FILE_APPEND);
    http_response_code(500);
    $dica = Bootstrap::dica($t); // @phpstan-ignore class.notFound
    echo env('APP_DEBUG') ? '<pre>' . e((string) $t) . '</pre>' : 'Erro interno. ' . ($dica ?: 'Detalhes em storage/erro.log.'); // @phpstan-ignore binaryOp.invalid
}