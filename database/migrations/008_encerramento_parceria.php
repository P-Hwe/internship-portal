<?php
return [
    'up' => function (PDO $pdo) {
        $pdo->exec('ALTER TABLE empresas MODIFY cnpj VARCHAR(14) NULL');
        $pdo->exec('ALTER TABLE empresas MODIFY email VARCHAR(120) NULL');
        add_coluna('empresas', 'encerrada_em', 'DATETIME NULL');
    },
    'down' => function (PDO $pdo) {
        $pdo->exec("UPDATE empresas SET cnpj = LPAD(id, 14, '0') WHERE cnpj IS NULL");
        $pdo->exec("UPDATE empresas SET email = CONCAT('encerrada-', id, '@removido.invalid') WHERE email IS NULL");
        $pdo->exec('ALTER TABLE empresas MODIFY cnpj VARCHAR(14) NOT NULL');
        $pdo->exec('ALTER TABLE empresas MODIFY email VARCHAR(120) NOT NULL');
        $pdo->exec('ALTER TABLE empresas DROP COLUMN encerrada_em');
    },
];
