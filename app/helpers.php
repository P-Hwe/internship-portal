<?php

function tabela_existe(string $nome): bool
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    return (bool) $db->query("SHOW TABLES LIKE '$nome'")->fetchColumn();
}

function add_coluna(string $tabela, string $coluna, string $tipo): void
{
    global $db;
    if (!$db) {
        $db = Database::pdo();
    }
    try {
        $existe = (bool) $db->query("SHOW COLUMNS FROM `$tabela` LIKE '`$coluna`")->fetchColumn();
        if (!$existe) {
            $db->exec("ALTER TABLE `$tabela` ADD COLUMN `$coluna` $tipo");
        }
    } catch (PDOException $e) {
    }
}

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
    }
    return null;
}
