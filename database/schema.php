<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/helpers.php';
$db = Database::pdo();

function migrar()
{
    global $db;

    $migracoes = [
        '000_tabelas_iniciais',
        '001_endereco_estruturado',
        '002_refinamento_empresas_vagas',
        '003_banner_empresa_e_visitas',
        '004_admin_principal_e_troca_senha',
        '005_senha_resets',
    ];

    foreach ($migracoes as $nome) {
        $arquivo = __DIR__ . '/migrations/' . $nome . '.php';
        if (file_exists($arquivo)) {
            require_once $arquivo;
        }
    }
}

echo "✅ Banco de dados conectado!\n";
echo "📦 Migrando...\n";

migrar();
echo "✅ Migrações concluídas!\n";
