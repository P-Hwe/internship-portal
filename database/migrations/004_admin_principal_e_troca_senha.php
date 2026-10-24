<?php
return [
    'up' => function (PDO $pdo) {
        add_coluna('admins', 'trocar_senha', 'SMALLINT NOT NULL DEFAULT 0');
        if (!(int) $pdo->query("SELECT COUNT(*) FROM admins WHERE papel = 'raiz'")->fetchColumn()) {
            $id = (int) $pdo->query('SELECT MIN(id) FROM admins')->fetchColumn();
            if ($id) $pdo->exec("UPDATE admins SET papel = 'raiz' WHERE id = $id");
        }
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("UPDATE admins SET papel = 'admin' WHERE papel = 'raiz'");
        $pdo->exec('ALTER TABLE admins DROP COLUMN trocar_senha');
    },
];