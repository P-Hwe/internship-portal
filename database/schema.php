<?php

use App\Database;

/** @var Database $db */
$db = Database::getInstance();

function migrar()
{
    global $db;

    $migracoes = [
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

if (!$db->pdo) {
    echo "⚠️  Banco de dados não conectado. Configure o .env primeiro.\n";
    echo "💡 Dica: Use SOLUÇÃO 1 para configurar MySQL/MariaDB local\n";
    exit(1);
}

echo "✅ Banco de dados conectado!\n";
echo "📦 Migrando...\n";

try {
    migrar();
    echo "✅ Migrações concluídas!\n";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
