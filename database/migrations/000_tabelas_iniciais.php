<?php
return [
    'up' => function (PDO $pdo) {
        if (tabela_existe('migracoes')) return;
        $dt = 'DATETIME';
        $pk = 'INT AUTO_INCREMENT PRIMARY KEY';
        $fim = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        $pdo->exec("CREATE TABLE migracoes (id $pk, nome VARCHAR(50) NOT NULL, aplicado_em $dt NOT NULL, aplicou_se BOOLEAN NOT NULL DEFAULT 1)$fim");
        $migracoes = [
            '001_endereco_estruturado',
            '002_refinamento_empresas_vagas',
            '003_banner_empresa_e_visitas',
            '004_admin_principal_e_troca_senha',
            '005_senha_resets',
        ];
        foreach ($migracoes as $nome) {
            $pdo->exec("INSERT INTO migracoes (nome, aplicado_em) VALUES ('$nome', NOW())");
        }
    },
    'down' => function (PDO $pdo) {
        $pdo->exec('DROP TABLE IF EXISTS migracoes');
    },
];
