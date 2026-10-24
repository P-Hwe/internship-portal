<?php
return [
    'up' => function (PDO $pdo) {
        if (tabela_existe('senha_resets')) return;
        $pk = 'INT AUTO_INCREMENT PRIMARY KEY'; $dt = 'DATETIME'; $fim = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        $pdo->exec("CREATE TABLE senha_resets (id $pk, perfil VARCHAR(10) NOT NULL, usuario_id INT NOT NULL, token_hash CHAR(64) NOT NULL, codigo_hash CHAR(64) NOT NULL, expira_em $dt NOT NULL, usado_em $dt NULL, tentativas SMALLINT NOT NULL DEFAULT 0, criado_em $dt NOT NULL)$fim");
        $pdo->exec('CREATE INDEX idx_senha_resets_token ON senha_resets (token_hash)');
        $pdo->exec('CREATE INDEX idx_senha_resets_conta ON senha_resets (perfil, usuario_id)');
    },
    'down' => function (PDO $pdo) { $pdo->exec('DROP TABLE IF EXISTS senha_resets'); },
];