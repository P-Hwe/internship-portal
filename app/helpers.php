<?php
// Fallbacks para scripts CLI que não incluem database/schema.php diretamente.
// Os guards em schema.php garantem que não há redeclaração em nenhuma ordem de include.

if (!function_exists('tabela_existe')) {
    function tabela_existe(string $t): bool
    {
        try { Database::pdo()->query("SELECT 1 FROM $t WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('coluna_existe')) {
    function coluna_existe(string $t, string $c): bool
    {
        try { Database::pdo()->query("SELECT $c FROM $t WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('add_coluna')) {
    function add_coluna(string $t, string $c, string $def): void
    {
        if (!coluna_existe($t, $c)) Database::pdo()->exec("ALTER TABLE $t ADD COLUMN $c $def");
    }
}
if (!function_exists('indice_existe')) {
    function indice_existe(string $tabela, string $nome): bool
    {
        try {
            return (bool) Database::pdo()->query("SHOW INDEXES FROM `$tabela` WHERE Key_name = '$nome'")->fetchColumn();
        } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('criar_indice')) {
    function criar_indice(string $tabela, string $nome, string $colunas): void
    {
        if (!indice_existe($tabela, $nome)) {
            Database::pdo()->exec("CREATE INDEX `$nome` ON `$tabela` (`$colunas`)");
        }
    }
}
if (!function_exists('migrations')) {
    function migrations(): array
    {
        $l = [];
        foreach (glob(__DIR__ . '/../database/migrations/*.php') as $f) {
            $m = require $f; $m['nome'] = basename($f, '.php'); $l[] = $m;
        }
        usort($l, fn($a, $b) => strcmp($a['nome'], $b['nome']));
        return $l;
    }
}
if (!function_exists('desfazer_ultima')) {
    function desfazer_ultima(): ?string
    {
        $u = Database::one('SELECT * FROM migracoes ORDER BY id DESC LIMIT 1');
        if (!$u) return null;
        foreach (migrations() as $m) {
            if ($m['nome'] !== $u['nome']) continue;
            if (empty($m['down'])) throw new RuntimeException("A migration {$m['nome']} não possui rollback.");
            ($m['down'])(Database::pdo());
            Database::run('DELETE FROM migracoes WHERE id = :i', ['i' => $u['id']]);
            return $m['nome'];
        }
        return null;
    }
}
