<?php
return [
    'up' => function (PDO $pdo) {
        if (!tabela_existe('empresa_usuarios')) {
            $pdo->exec('CREATE TABLE empresa_usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY, empresa_id INT NOT NULL, nome VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE,
                senha_hash VARCHAR(255) NOT NULL, papel VARCHAR(12) NOT NULL DEFAULT \'membro\', ativo SMALLINT NOT NULL DEFAULT 1,
                trocar_senha SMALLINT NOT NULL DEFAULT 0, desativado_por VARCHAR(10) NULL, criado_por INT NULL, criado_em DATETIME NOT NULL,
                ultimo_acesso DATETIME NULL, excluido_em DATETIME NULL, FOREIGN KEY (empresa_id) REFERENCES empresas(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $pdo->exec('CREATE INDEX idx_empresa_usuarios_empresa ON empresa_usuarios (empresa_id)');
        }
        CompanyMember::backfillPrincipals();
    },
    'down' => function (PDO $pdo) {
        $pdo->exec('DROP TABLE IF EXISTS empresa_usuarios');
    },
];
