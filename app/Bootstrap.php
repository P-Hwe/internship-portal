<?php

class Bootstrap
{
    public static function garantir(): void
    {
        $pdo = Database::pdo();
        require_once ROOT_PATH . '/database/schema.php';

        $precisa = true;
        try {
            $pdo->query('SELECT 1 FROM migracoes WHERE 1 = 0');
            $ap = array_column(Database::all('SELECT nome FROM migracoes'), 'nome');
            $precisa = (bool) array_diff(array_column(migrations(), 'nome'), $ap);
        } catch (PDOException $e) {
            $precisa = true;
        }
        if ($precisa) {
            migrar();
        }

        if (!Database::val('SELECT 1 FROM admins LIMIT 1')) {
            $senha = (string) env('ADMIN_PASSWORD', '');
            if (strlen($senha) >= 8) {
                Database::insert('admins', ['nome' => 'Coordenação', 'email' => mb_strtolower((string) env('ADMIN_EMAIL', 'admin@ifsp.local')),
                    'senha_hash' => Auth::hash($senha), 'papel' => 'raiz', 'criado_em' => Database::agora()]);
            }
        }
    }

    public static function dica(Throwable $t): string
    {
        $m = $t->getMessage();
        if ($t instanceof PDOException || str_contains($m, 'could not find driver')) return 'Falta a extensão PDO do banco no PHP (pdo_mysql). No cPanel: "Select PHP Version" > Extensions.';
        if (str_contains($m, 'Access denied')) return 'O MySQL recusou o usuário ou a senha. Confira DB_USER e DB_PASS no arquivo .env.';
        if (str_contains($m, 'Unknown database')) return 'O banco informado em DB_NAME não existe. Crie o banco no MySQL (ou no cPanel) e confira o .env.';
        return 'Não foi possível usar o banco de dados. Confira DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS no arquivo .env.';
    }
}
