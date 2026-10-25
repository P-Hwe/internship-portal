<?php
// 🔒 PROTEÇÃO: Só roda em ambiente de desenvolvimento e localhost
session_start();

$env = getenv('APP_ENV') ?: $_SERVER['APP_ENV'] ?? 'prod';

// Bloqueia em produção
if ($env !== 'dev' && $env !== 'local') {
    die("❌ Este script de seed só pode ser executado em ambiente de desenvolvimento (APP_ENV=dev ou local).\n");
}

// 🔒 Só permite acesso de localhost
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die("❌ Acesso não autorizado. Este script só funciona em localhost.\n");
}

// 🔒 Só via CLI ou com parâmetro ?force
if (php_sapi_name() !== 'cli' && !isset($_GET['force'])) {
    die("❌ Este script deve ser executado via linha de comando ou com parâmetro ?force\n");
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';

$sql = file_get_contents(__DIR__ . '/mysql.sql');

if (!$sql) {
    die("❌ Erro: Não foi possível ler o arquivo mysql.sql\n");
}

try {
    $db = Database::pdo();

    echo "📦 Criando tabelas iniciais...\n\n";

    $sql = preg_replace('/CREATE INDEX .*/', '', $sql);
    $sql = preg_replace('/^INSERT.*$/m', '', $sql);

    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $created = [];
    $skipped = [];

    foreach ($statements as $stmt) {
        if (empty($stmt) || strpos($stmt, 'CREATE TABLE') !== 0) {
            continue;
        }

        preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $stmt, $matches);
        if (!isset($matches[1])) {
            continue;
        }

        $table = $matches[1];

        if ($db->query("SHOW TABLES LIKE '$table'")->fetchColumn()) {
            echo "⏭️  Pula: $table (já existe)\n";
            continue;
        }

        try {
            $db->exec($stmt);
            echo "✅ Criada: $table\n";
            $created[] = $table;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                throw $e;
            }
            echo "⏭️  Pula: $table (criada por migração)\n";
        }
    }

    // List all tables
    $all_tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    sort($all_tables);

    echo "\n📋 Todas as tabelas no banco:\n";
    foreach ($all_tables as $table) {
        echo "   - $table\n";
    }

    if (empty($created)) {
        echo "\n✅ Todas as tabelas já existem!\n";
    } else {
        echo "\n🎉 Banco de dados pronto!\n";
    }

    // Registra as migrações no banco (para migrate.php saber quais rodar)
    try {
        $db->exec("INSERT INTO migracoes (nome, aplicada_em) VALUES
            ('001_endereco_estruturado', NOW()),
            ('002_refinamento_empresas_vagas', NOW()),
            ('003_banner_empresa_e_visitas', NOW()),
            ('004_admin_principal_e_troca_senha', NOW()),
            ('005_senha_resets', NOW())
        ON DUPLICATE KEY UPDATE aplicada_em = NOW()");
        echo "✅ Migrações registradas!\n";
    } catch (PDOException $e) {
        // Ignora se já existe
    }

} catch (PDOException $e) {
    echo "\n❌ Erro ao criar tabelas: " . $e->getMessage() . "\n";
    exit(1);
}