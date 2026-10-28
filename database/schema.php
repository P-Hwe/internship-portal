<?php

function schema_sql(): array
{
    $pk  = 'INT AUTO_INCREMENT PRIMARY KEY';
    $dt  = 'DATETIME';
    $fim = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $t = [];
    $t[] = "CREATE TABLE IF NOT EXISTS migracoes (id $pk, nome VARCHAR(80) NOT NULL UNIQUE, aplicada_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS admins (
        id $pk, nome VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE, senha_hash VARCHAR(255) NOT NULL,
        papel VARCHAR(20) NOT NULL DEFAULT 'admin', ativo SMALLINT NOT NULL DEFAULT 1, trocar_senha SMALLINT NOT NULL DEFAULT 0,
        criado_em $dt NOT NULL, ultimo_acesso $dt NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS empresas (
        id $pk, cnpj VARCHAR(14) NOT NULL UNIQUE, razao_social VARCHAR(150) NULL, nome VARCHAR(120) NOT NULL,
        descricao TEXT NULL, segmento VARCHAR(60) NULL, porte VARCHAR(20) NULL,
        endereco VARCHAR(200) NOT NULL,
        cep VARCHAR(8) NULL, logradouro VARCHAR(120) NULL, numero VARCHAR(10) NULL, complemento VARCHAR(60) NULL, bairro VARCHAR(60) NULL, cidade VARCHAR(60) NULL, uf VARCHAR(2) NULL,
        responsavel VARCHAR(100) NOT NULL, cargo VARCHAR(60) NULL, telefone VARCHAR(20) NOT NULL, celular VARCHAR(20) NULL,
        email VARCHAR(120) NOT NULL UNIQUE, email_contato VARCHAR(120) NULL,
        site VARCHAR(200) NULL, linkedin VARCHAR(200) NULL, link_principal VARCHAR(10) NOT NULL DEFAULT 'site', logo VARCHAR(200) NULL, banner VARCHAR(200) NULL,
        senha_hash VARCHAR(255) NOT NULL, termo_arquivo VARCHAR(80) NULL, termos_aceitos_em $dt NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'pendente', motivo TEXT NULL, aprovacao_vista SMALLINT NOT NULL DEFAULT 0,
        criado_em $dt NOT NULL, avaliado_em $dt NULL, ultimo_acesso $dt NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS areas (id $pk, nome VARCHAR(60) NOT NULL UNIQUE, padrao SMALLINT NOT NULL DEFAULT 1)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS vagas (
        id $pk, codigo VARCHAR(20) NOT NULL UNIQUE, empresa_id INT NOT NULL,
        titulo VARCHAR(80) NOT NULL, tipo VARCHAR(30) NOT NULL DEFAULT 'Estágio', area VARCHAR(60) NOT NULL,
        modalidade VARCHAR(20) NOT NULL, local VARCHAR(80) NOT NULL, bolsa DECIMAL(10,2) NULL, carga_horaria VARCHAR(60) NULL,
        carga_horas_dia SMALLINT NULL, carga_dias VARCHAR(30) NULL, carga_turno VARCHAR(12) NULL,
        descricao TEXT NOT NULL, atividades TEXT NULL, requisitos TEXT NULL, requisitos_desejaveis TEXT NULL, beneficios TEXT NULL,
        data_publicacao DATE NOT NULL, data_limite DATE NOT NULL, contato VARCHAR(120) NOT NULL, imagem VARCHAR(200) NULL,
        link_site VARCHAR(200) NULL, link_linkedin VARCHAR(200) NULL, link_candidatura VARCHAR(250) NULL, link_plataforma VARCHAR(12) NULL,
        destaque SMALLINT NOT NULL DEFAULT 0, status VARCHAR(25) NOT NULL DEFAULT 'aguardando_aprovacao', motivo TEXT NULL, versao SMALLINT NOT NULL DEFAULT 1,
        criado_em $dt NOT NULL, atualizado_em $dt NULL, avaliado_em $dt NULL,
        aviso_vencendo SMALLINT NOT NULL DEFAULT 0, aviso_encerrada SMALLINT NOT NULL DEFAULT 0,
        FOREIGN KEY (empresa_id) REFERENCES empresas(id))$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS historico (
        id $pk, entidade VARCHAR(10) NOT NULL, entidade_id INT NOT NULL, acao VARCHAR(30) NOT NULL, de VARCHAR(25) NULL, para VARCHAR(25) NULL,
        ator_tipo VARCHAR(10) NOT NULL, ator_id INT NULL, detalhe VARCHAR(300) NULL, criado_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS acessos_log (
        id $pk, perfil VARCHAR(10) NOT NULL, usuario_id INT NULL, email VARCHAR(120) NULL, evento VARCHAR(15) NOT NULL,
        ip VARCHAR(45) NULL, agente VARCHAR(160) NULL, criado_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS notificacoes (id $pk, tipo VARCHAR(12) NOT NULL, referencia_id INT NOT NULL, mensagem VARCHAR(200) NOT NULL, lida SMALLINT NOT NULL DEFAULT 0, criado_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS emails_log (id $pk, destinatario VARCHAR(120) NOT NULL, assunto VARCHAR(200) NOT NULL, status VARCHAR(10) NOT NULL, erro VARCHAR(255) NULL, criado_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS visitas (
        id $pk, tipo VARCHAR(10) NOT NULL, ref_id INT NULL, visitante CHAR(24) NOT NULL, criado_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS senha_resets (id $pk, perfil VARCHAR(10) NOT NULL, usuario_id INT NOT NULL, token_hash CHAR(64) NOT NULL, codigo_hash CHAR(64) NOT NULL, expira_em $dt NOT NULL, usado_em $dt NULL, tentativas SMALLINT NOT NULL DEFAULT 0, criado_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS login_tentativas (id $pk, chave VARCHAR(64) NOT NULL, criado_em $dt NOT NULL)$fim";
    foreach ([
        'idx_vagas_status ON vagas (status, data_limite)', 'idx_vagas_empresa ON vagas (empresa_id)', 'idx_login_chave ON login_tentativas (chave)',
        'idx_hist_ent ON historico (entidade, entidade_id)', 'idx_visitas_data ON visitas (criado_em)', 'idx_senha_resets_token ON senha_resets (token_hash)', 'idx_senha_resets_conta ON senha_resets (perfil, usuario_id)', 'idx_visitas_ref ON visitas (tipo, ref_id, criado_em)', 'idx_acessos_data ON acessos_log (criado_em)', 'idx_empresas_status ON empresas (status)',
    ] as $ix) $t[] = "CREATE INDEX $ix";
    return $t;
}

const AREAS_PADRAO = ['Administração', 'Engenharia', 'Logística', 'Marketing', 'Qualidade', 'Tecnologia da Informação', 'Recursos Humanos', 'Financeiro', 'Comercial', 'Saúde', 'Educação', 'Jurídico'];

if (!function_exists('tabela_existe')) {
    function tabela_existe(string $t): bool
    {
        try { Database::pdo()->query("SELECT 1 FROM $t WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('coluna_existe')) {
    function coluna_existe(string $t, string $c): bool
    {
        try { Database::pdo()->query("SELECT $c FROM $t WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
    }
}
if (!function_exists('add_coluna')) {
    function add_coluna(string $t, string $c, string $def): void
    {
        if (!coluna_existe($t, $c)) Database::pdo()->exec("ALTER TABLE $t ADD COLUMN $c $def");
    }
}

if (!function_exists('migrations')) {
    function migrations(): array
    {
        $l = [];
        foreach (glob(__DIR__ . '/migrations/*.php') as $f) { $m = require $f; $m['nome'] = basename($f, '.php'); $l[] = $m; }
        usort($l, fn($a, $b) => strcmp($a['nome'], $b['nome']));
        return $l;
    }
}

function migrar(): array
{
    $pdo = Database::pdo(); $feitas = [];
    $novo = !tabela_existe('empresas');
    foreach (schema_sql() as $sql) {
        try { $pdo->exec($sql); } catch (PDOException $e) { if (stripos($sql, 'CREATE INDEX') !== 0) throw $e; }
    }
    $aplicadas = tabela_existe('migracoes') ? array_column(Database::all('SELECT nome FROM migracoes'), 'nome') : [];
    foreach (migrations() as $m) {
        if (in_array($m['nome'], $aplicadas, true)) continue;
        if (!$novo) { ($m['up'])($pdo); $feitas[] = $m['nome']; }   // banco antigo: executa a migration
        Database::insert('migracoes', ['nome' => $m['nome'], 'aplicada_em' => Database::agora()]);   // banco novo: esquema final já contempla
    }
    foreach (schema_sql() as $sql) {
        if (stripos($sql, 'CREATE INDEX') === 0) { try { $pdo->exec($sql); } catch (PDOException $e) { /* já existe */ } }
    }
    foreach (AREAS_PADRAO as $a) {
        if (!Database::val('SELECT 1 FROM areas WHERE nome = :n', ['n' => $a])) Database::insert('areas', ['nome' => $a, 'padrao' => 1]);
    }
    return $feitas;
}

if (!function_exists('desfazer_ultima')) {
    function desfazer_ultima(): ?string
    {
        $u = Database::one('SELECT * FROM migracoes ORDER BY id DESC LIMIT 1');
        if (!$u) return null;
        foreach (migrations() as $m) {
            if ($m['nome'] !== $u['nome']) continue;
            if (empty($m['down'])) throw new RuntimeException("A migration {$m['nome']} não possui rollback.");
            ($m['down'])(Database::pdo());
            Database::run('DELETE FROM migracoes WHERE id = :i', ['i' => $u['id']]);
            return $m['nome'];
        }
        return null;
    }
}
