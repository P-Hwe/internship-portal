<?php

return [
    'up' => function (PDO $pdo, string $driver) {
        $dt = $driver === 'pgsql' ? 'TIMESTAMP' : 'DATETIME';
        $pk = ['sqlite' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'mysql' => 'INT AUTO_INCREMENT PRIMARY KEY', 'pgsql' => 'SERIAL PRIMARY KEY'][$driver];
        $fim = $driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
        foreach ([
            'razao_social' => 'VARCHAR(150) NULL', 'descricao' => 'TEXT NULL', 'segmento' => 'VARCHAR(60) NULL', 'porte' => 'VARCHAR(20) NULL',
            'cargo' => 'VARCHAR(60) NULL', 'celular' => 'VARCHAR(20) NULL', 'email_contato' => 'VARCHAR(120) NULL', 'linkedin' => 'VARCHAR(200) NULL',
            'link_principal' => "VARCHAR(10) NOT NULL DEFAULT 'site'", 'logo' => 'VARCHAR(200) NULL', 'termos_aceitos_em' => "$dt NULL",
            'aprovacao_vista' => 'SMALLINT NOT NULL DEFAULT 0',
        ] as $c => $d) {
            add_coluna('empresas', $c, $d);
        }
        foreach ([
            'carga_horas_dia' => 'SMALLINT NULL', 'carga_dias' => 'VARCHAR(30) NULL', 'carga_turno' => 'VARCHAR(12) NULL', 'requisitos_desejaveis' => 'TEXT NULL',
            'link_site' => 'VARCHAR(200) NULL', 'link_linkedin' => 'VARCHAR(200) NULL', 'link_candidatura' => 'VARCHAR(250) NULL', 'link_plataforma' => 'VARCHAR(12) NULL',
            'versao' => 'SMALLINT NOT NULL DEFAULT 1', 'atualizado_em' => "$dt NULL",
        ] as $c => $d) {
            add_coluna('vagas', $c, $d);
        }
        add_coluna('admins', 'papel', "VARCHAR(20) NOT NULL DEFAULT 'admin'");
        add_coluna('admins', 'ativo', 'SMALLINT NOT NULL DEFAULT 1');
        if (!tabela_existe('historico')) {
            $pdo->exec("CREATE TABLE historico (id $pk, entidade VARCHAR(10) NOT NULL, entidade_id INT NOT NULL, acao VARCHAR(30) NOT NULL, de VARCHAR(25) NULL, para VARCHAR(25) NULL, ator_tipo VARCHAR(10) NOT NULL, ator_id INT NULL, detalhe VARCHAR(300) NULL, criado_em $dt NOT NULL)$fim");
        }
        if (!tabela_existe('acessos_log')) {
            $pdo->exec("CREATE TABLE acessos_log (id $pk, perfil VARCHAR(10) NOT NULL, usuario_id INT NULL, email VARCHAR(120) NULL, evento VARCHAR(15) NOT NULL, ip VARCHAR(45) NULL, agente VARCHAR(160) NULL, criado_em $dt NOT NULL)$fim");
        }
        $pdo->exec("UPDATE vagas SET status = 'aguardando_aprovacao' WHERE status = 'pendente'");
        $pdo->exec("UPDATE vagas SET status = 'rejeitada' WHERE status = 'reprovada'");
        $pdo->exec("UPDATE empresas SET aprovacao_vista = 1 WHERE status = 'aprovada'");
        $pdo->exec("UPDATE empresas SET razao_social = nome WHERE razao_social IS NULL");
    },
    'down' => function (PDO $pdo, string $driver) {
        $pdo->exec("UPDATE vagas SET status = 'pendente' WHERE status = 'aguardando_aprovacao'");
        $pdo->exec("UPDATE vagas SET status = 'reprovada' WHERE status = 'rejeitada'");
        $pdo->exec('DROP TABLE IF EXISTS historico');
        $pdo->exec('DROP TABLE IF EXISTS acessos_log');
        foreach (['papel', 'ativo'] as $c) {
            $pdo->exec("ALTER TABLE admins DROP COLUMN $c");
        }
        foreach (['carga_horas_dia', 'carga_dias', 'carga_turno', 'requisitos_desejaveis', 'link_site', 'link_linkedin', 'link_candidatura', 'link_plataforma', 'versao', 'atualizado_em'] as $c) {
            $pdo->exec("ALTER TABLE vagas DROP COLUMN $c");
        }
        foreach (['razao_social', 'descricao', 'segmento', 'porte', 'cargo', 'celular', 'email_contato', 'linkedin', 'link_principal', 'logo', 'termos_aceitos_em', 'aprovacao_vista'] as $c) {
            $pdo->exec("ALTER TABLE empresas DROP COLUMN $c");
        }
    },
];
