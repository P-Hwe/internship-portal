<?php
class Bootstrap
{
    public static function ensure(): void
    {
        $pdo = Database::pdo();
        require_once ROOT_PATH . '/database/schema.php';

        $needsMigration = true;
        try {
            $pdo->query('SELECT 1 FROM migracoes WHERE 1 = 0');
            $applied        = array_column(Database::all('SELECT nome FROM migracoes'), 'nome');
            $needsMigration = (bool) array_diff(array_column(migrations(), 'nome'), $applied);
        } catch (PDOException $e) {
            $needsMigration = true;
        }
        if ($needsMigration) {
            migrar();
        }

        if (!Database::val('SELECT 1 FROM admins LIMIT 1')) {
            $password = (string) env('ADMIN_PASSWORD', '');
            if (strlen($password) >= 8) {
                Database::insert('admins', [
                    'nome'       => 'Coordenação',
                    'email'      => mb_strtolower((string) env('ADMIN_EMAIL', 'admin@ifsp.local')),
                    'senha_hash' => Auth::hash($password),
                    'papel'      => 'raiz',
                    'criado_em'  => Database::now(),
                ]);
            }
        }
    }

    public static function hint(Throwable $t): string
    {
        $msg = $t->getMessage();
        if (str_contains($msg, 'could not find driver'))
            return 'Falta a extensão PDO do banco no PHP (pdo_mysql). No cPanel: "Select PHP Version" > Extensions.';
        if (str_contains($msg, 'Access denied'))
            return 'O MySQL recusou o usuário ou a senha. Confira DB_USER e DB_PASSWORD no arquivo .env.';
        if (str_contains($msg, 'Unknown database'))
            return 'O banco informado em DB_NAME não existe. Crie o banco no MySQL (ou no cPanel) e confira o .env.';
        if ($t instanceof PDOException)
            return 'Erro de banco de dados: ' . $msg;
        return 'Não foi possível usar o banco de dados. Confira DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASSWORD no arquivo .env.';
    }

    // ── backwards-compatible aliases ──
    /** @deprecated Use ensure() */
    public static function garantir(): void { self::ensure(); }
    /** @deprecated Use hint() */
    public static function dica(Throwable $t): string { return self::hint($t); }
}
