<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $dsn = 'mysql:host=' . env('DB_HOST', 'localhost') . ';port=' . env('DB_PORT', 3306) . ';dbname=' . env('DB_NAME', 'vagas') . ';charset=utf8mb4';
        self::$pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);
        self::$pdo->exec("SET time_zone = '-03:00'");
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
        self::run("INSERT INTO $tabela (" . implode(',', $cols) . ') VALUES (:' . implode(',:', $cols) . ')', $dados);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $tabela, int $id, array $dados): void
    {
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($dados)));
        self::run("UPDATE $tabela SET $set WHERE id = :_id", $dados + ['_id' => $id]);
    }

    public static function agora(): string { return date('Y-m-d H:i:s'); }
}