<?php
return [
    'up' => function (PDO $pdo) {
        add_coluna('admins', 'excluido_em', 'DATETIME NULL');
    },
    'down' => function (PDO $pdo) {
        $pdo->exec('ALTER TABLE admins DROP COLUMN excluido_em');
    },
];
