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
                    'senha_hash' => Auth::hash($senha), 'criado_em' => Database::agora()]);
            }
        }
    }

    public static function dica(Throwable $t): string
    {
        $m = $t->getMessage();
        if ($t instanceof PDOException || str_contains($m, 'could not find driver')) {
            if (str_contains($m, 'could not find driver')) {
                return 'Falta a extensão PDO do banco no PHP (pdo_sqlite, pdo_mysql ou pdo_pgsql). No cPanel: "Select PHP Version" > Extensions.';
            }
            if (str_contains($m, 'unable to open database') || str_contains($m, 'readonly')) {
                return 'Sem permissão de escrita na pasta storage/. Dê permissão 755/775 a storage/.';
            }
            return 'Não foi possível usar o banco de dados. Confira DB_* no arquivo .env.';
        }
        return '';
    }
}
