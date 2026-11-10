<?php
return [
    'up' => function (PDO $pdo) {
        add_coluna('empresas', 'convenio_arquivo', 'VARCHAR(80) NULL');
        add_coluna('empresas', 'convenio_assinado_em', 'DATETIME NULL');
    },
    'down' => function (PDO $pdo) {
        $pdo->exec('ALTER TABLE empresas DROP COLUMN convenio_arquivo');
        $pdo->exec('ALTER TABLE empresas DROP COLUMN convenio_assinado_em');
    },
];
