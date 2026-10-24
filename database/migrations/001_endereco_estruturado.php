<?php
return [
    'up' => function (PDO $pdo) {
        foreach (['cep' => 8, 'logradouro' => 120, 'numero' => 10, 'complemento' => 60, 'bairro' => 60, 'cidade' => 60, 'uf' => 2] as $c => $n) add_coluna('empresas', $c, "VARCHAR($n) NULL");
    },
    'down' => function (PDO $pdo) {
        foreach (['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'] as $c) $pdo->exec("ALTER TABLE empresas DROP COLUMN $c");
    },
];