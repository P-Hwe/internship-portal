<?php

/** Verifica se uma tabela existe */
function tabela_existe(string $nome): bool
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    return (bool) $db->query("SHOW TABLES LIKE '$nome'")->fetchColumn();
}

/** Adiciona uma coluna a uma tabela */
function add_coluna(string $tabela, string $coluna, string $tipo): void
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    // Verifica se a coluna já existe
    try {
        $existe = (bool) $db->query("SHOW COLUMNS FROM `$tabela` LIKE '`$coluna`")->fetchColumn();
        if (!$existe) {
            $db->exec("ALTER TABLE `$tabela` ADD COLUMN `$coluna` $tipo");
        }
    } catch (PDOException $e) {
        // Ignora erros de permissão ou outras exceções
    }
}

/** Verifica se um índice existe */
function indice_existe(string $tabela, string $nome): bool
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    try {
        return (bool) $db->query("SHOW INDEXES FROM `$tabela` WHERE Key_name = '`$nome`")->fetchColumn();
    } catch (PDOException) {
        return false;
    }
}

/** Cria um índice em uma tabela */
function criar_indice(string $tabela, string $nome, string $colunas): void
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    if (!indice_existe($tabela, $nome)) {
        $db->exec("CREATE INDEX `$nome` ON `$tabela` (`$colunas`)");
    }
}

/** Retorna lista de migrações disponíveis */
function migrations(): array
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    try {
        return array_map(fn($r) => ['nome' => $r['nome']], $db->query("SELECT nome FROM migracoes ORDER BY id")->fetchAll());
    } catch (PDOException) {
        return [];
    }
}

/** Desfaz a última migração */
function desfazer_ultima(): ?string
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    try {
        $ultima = $db->query("SELECT nome FROM migracoes ORDER BY id DESC LIMIT 1")->fetch();
        if ($ultima) {
            $f = __DIR__ . '/../database/migrations/' . $ultima['nome'] . '.php';
            if (is_file($f)) {
                require_once $f;
                $migracao = ['up' => null, 'down' => null];
                if (isset($migracao['down'])) {
                    $migracao['down']($db->pdo);
                }
                $db->query("DELETE FROM migracoes WHERE nome = '{$ultima['nome']}'");
                return $ultima['nome'];
            }
        }
    } catch (PDOException) {
        // Ignora erros
    }
    return null;
}
