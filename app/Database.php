```php
<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function driver(): string { return strtolower((string) env('DB_DRIVER', 'sqlite')); }

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $d = self::driver();
        $host = env('DB_HOST', 'localhost'); $nome = env('DB_NAME', 'vagas');
        if ($d === 'mysql') {
            $dsn = "mysql:host=$host;port=" . env('DB_PORT', 3306) . ";dbname=$nome;charset=utf8mb4";
        } elseif ($d === 'pgsql') {
            $dsn = "pgsql:host=$host;port=" . env('DB_PORT', 5432) . ";dbname=$nome";
        } else {
            $arq = env('DB_SQLITE', ROOT_PATH . '/storage/database.sqlite');
            $dsn = 'sqlite:' . $arq;
        }
        self::$pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES => $d === 'mysql',
        ]);
        if ($d === 'sqlite') self::$pdo->exec('PRAGMA foreign_keys = ON');
        return self::$pdo;
    }

    public static function reset(): void { self::$pdo = null; }

    public static function run(string $sql, array $p = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql); $st->execute($p); return $st;
    }
    public static function all(string $sql, array $p = []): array { return self::run($sql, $p)->fetchAll(); }
    public static function one(string $sql, array $p = []): ?array { $r = self::run($sql, $p)->fetch(); return $r ?: null; }
    public static function val(string $sql, array $p = []) { $r = self::run($sql, $p)->fetchColumn(); return $r === false ? null : $r; }

    public static function insert(string $tabela, array $dados): int
    {
        $cols = array_keys($dados);
        $sql = "INSERT INTO $tabela (" . implode(',', $cols) . ') VALUES (:' . implode(',:', $cols) . ')';
        if (self::driver() === 'pgsql') {
            return (int) self::val($sql . ' RETURNING id', $dados);
        }
        self::run($sql, $dados);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $tabela, int $id, array $dados): void
    {
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($dados)));
        self::run("UPDATE $tabela SET $set WHERE id = :_id", $dados + ['_id' => $id]);
    }

    public static function agora(): string { return date('Y-m-d H:i:s'); }
}
```
