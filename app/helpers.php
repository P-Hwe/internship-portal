<?php
if (!function_exists('tabela_existe')) {
    function tabela_existe(string $table): bool
    {
        try { Database::pdo()->query("SELECT 1 FROM $table WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('table_exists')) {
    function table_exists(string $table): bool { return tabela_existe($table); }
}

if (!function_exists('coluna_existe')) {
    function coluna_existe(string $table, string $column): bool
    {
        try { Database::pdo()->query("SELECT $column FROM $table WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('column_exists')) {
    function column_exists(string $table, string $column): bool { return coluna_existe($table, $column); }
}

if (!function_exists('add_coluna')) {
    function add_coluna(string $table, string $column, string $definition): void
    {
        if (!coluna_existe($table, $column)) Database::pdo()->exec("ALTER TABLE $table ADD COLUMN $column $definition");
    }
}
if (!function_exists('add_column')) {
    function add_column(string $table, string $column, string $definition): void { add_coluna($table, $column, $definition); }
}

if (!function_exists('indice_existe')) {
    function indice_existe(string $table, string $name): bool
    {
        try {
            return (bool) Database::pdo()->query("SHOW INDEXES FROM `$table` WHERE Key_name = '$name'")->fetchColumn();
        } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('index_exists')) {
    function index_exists(string $table, string $name): bool { return indice_existe($table, $name); }
}

if (!function_exists('criar_indice')) {
    function criar_indice(string $table, string $name, string $columns): void
    {
        if (!indice_existe($table, $name)) Database::pdo()->exec("CREATE INDEX `$name` ON `$table` (`$columns`)");
    }
}
if (!function_exists('create_index')) {
    function create_index(string $table, string $name, string $columns): void { criar_indice($table, $name, $columns); }
}

if (!function_exists('migrations')) {
    function migrations(): array
    {
        $list = [];
        foreach (glob(__DIR__ . '/../database/migrations/*.php') as $file) {
            $migration = require $file;
            $migration['nome'] = basename($file, '.php');
            $list[] = $migration;
        }
        usort($list, fn($a, $b) => strcmp($a['nome'], $b['nome']));
        return $list;
    }
}

if (!function_exists('desfazer_ultima')) {
    function desfazer_ultima(): ?string
    {
        $last = Database::one('SELECT * FROM migracoes ORDER BY id DESC LIMIT 1');
        if (!$last) return null;
        foreach (migrations() as $migration) {
            if ($migration['nome'] !== $last['nome']) continue;
            if (empty($migration['down'])) throw new RuntimeException("Migration {$migration['nome']} has no rollback.");
            ($migration['down'])(Database::pdo());
            Database::run('DELETE FROM migracoes WHERE id = :i', ['i' => $last['id']]);
            return $migration['nome'];
        }
        return null;
    }
}
if (!function_exists('rollback_last')) {
    function rollback_last(): ?string { return desfazer_ultima(); }
}
