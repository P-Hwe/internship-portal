<?php
return [
    'up' => function (PDO $pdo) {
        add_coluna('empresas', 'banner', 'VARCHAR(200) NULL');
        if (!tabela_existe('visitas')) {
            $pdo->exec('CREATE TABLE visitas (id INT AUTO_INCREMENT PRIMARY KEY, tipo VARCHAR(10) NOT NULL, ref_id INT NULL, visitante CHAR(24) NOT NULL, criado_em DATETIME NOT NULL)'
                . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        }
        foreach (['idx_visitas_data ON visitas (criado_em)', 'idx_visitas_ref ON visitas (tipo, ref_id, criado_em)'] as $ix) {
            try { $pdo->exec("CREATE INDEX $ix"); } catch (PDOException $e) { /* já existe */ }
        }
    },
    'down' => function (PDO $pdo) {
        $pdo->exec('DROP TABLE IF EXISTS visitas');
        $pdo->exec('ALTER TABLE empresas DROP COLUMN banner');
    },
];