<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $dsn = 'mysql:host=' . env('DB_HOST', 'localhost') . ';port=' . env('DB_PORT', 3306)
             . ';dbname=' . env('DB_NAME', 'vagas_dev') . ';charset=utf8mb4';
        self::$pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]);
        self::$pdo->exec("SET time_zone = '-03:00'");
        return self::$pdo;
    }

    public static function reset(): void { self::$pdo = null; }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function val(string $sql, array $params = [])
    {
        $value = self::run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        self::run(
            "INSERT INTO $table (" . implode(',', $columns) . ') VALUES (:' . implode(',:', $columns) . ')',
            $data
        );
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, int $id, array $data): void
    {
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
        self::run("UPDATE $table SET $set WHERE id = :_id", $data + ['_id' => $id]);
    }

    public static function now(): string { return date('Y-m-d H:i:s'); }

    /** @deprecated Use now() — kept because it appears in many DB insert arrays */
    public static function agora(): string { return self::now(); }
}
