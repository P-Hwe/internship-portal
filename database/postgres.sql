-- Vagas de Estágio - IFSP Guarulhos | esquema para PGSQL
-- Importe no phpMyAdmin (aba Importar) com o banco já selecionado.
-- O administrador é criado no primeiro acesso ao site, a partir de ADMIN_EMAIL/ADMIN_PASSWORD do .env.

CREATE TABLE IF NOT EXISTS migracoes (id SERIAL PRIMARY KEY, nome VARCHAR(80) NOT NULL UNIQUE, aplicada_em TIMESTAMP NOT NULL);

CREATE TABLE IF NOT EXISTS admins ( id SERIAL PRIMARY KEY, nome VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE, senha_hash VARCHAR(255) NOT NULL, papel VARCHAR(20) NOT NULL DEFAULT 'admin', ativo SMALLINT NOT NULL DEFAULT 1, criado_em TIMESTAMP NOT NULL, ultimo_acesso TIMESTAMP NULL);

CREATE TABLE IF NOT EXISTS empresas ( id SERIAL PRIMARY KEY, cnpj VARCHAR(14) NOT NULL UNIQUE, razao_social VARCHAR(150) NULL, nome VARCHAR(120) NOT NULL, descricao TEXT NULL, segmento VARCHAR(60) NULL, porte VARCHAR(20) NULL, endereco VARCHAR(200) NOT NULL, cep VARCHAR(8) NULL, logradouro VARCHAR(120) NULL, numero VARCHAR(10) NULL, complemento VARCHAR(60) NULL, bairro VARCHAR(60) NULL, cidade VARCHAR(60) NULL, uf VARCHAR(2) NULL, responsavel VARCHAR(100) NOT NULL, cargo VARCHAR(60) NULL, telefone VARCHAR(20) NOT NULL, celular VARCHAR(20) NULL, email VARCHAR(120) NOT NULL UNIQUE, email_contato VARCHAR(120) NULL, site VARCHAR(200) NULL, linkedin VARCHAR(200) NULL, link_principal VARCHAR(10) NOT NULL DEFAULT 'site', logo VARCHAR(200) NULL, senha_hash VARCHAR(255) NOT NULL, termo_arquivo VARCHAR(80) NULL, termos_aceitos_em TIMESTAMP NULL, status VARCHAR(12) NOT NULL DEFAULT 'pendente', motivo TEXT NULL, aprovacao_vista SMALLINT NOT NULL DEFAULT 0, criado_em TIMESTAMP NOT NULL, avaliado_em TIMESTAMP NULL, ultimo_acesso TIMESTAMP NULL);

CREATE TABLE IF NOT EXISTS areas (id SERIAL PRIMARY KEY, nome VARCHAR(60) NOT NULL UNIQUE, padrao SMALLINT NOT NULL DEFAULT 1);

CREATE TABLE IF NOT EXISTS vagas ( id SERIAL PRIMARY KEY, codigo VARCHAR(20) NOT NULL UNIQUE, empresa_id INT NOT NULL, titulo VARCHAR(80) NOT NULL, tipo VARCHAR(30) NOT NULL DEFAULT 'Estágio', area VARCHAR(60) NOT NULL, modalidade VARCHAR(20) NOT NULL, local VARCHAR(80) NOT NULL, bolsa DECIMAL(10,2) NULL, carga_horaria VARCHAR(60) NULL, carga_horas_dia SMALLINT NULL, carga_dias VARCHAR(30) NULL, carga_turno VARCHAR(12) NULL, descricao TEXT NOT NULL, atividades TEXT NULL, requisitos TEXT NULL, requisitos_desejaveis TEXT NULL, beneficios TEXT NULL, data_publicacao DATE NOT NULL, data_limite DATE NOT NULL, contato VARCHAR(120) NOT NULL, imagem VARCHAR(200) NULL, link_site VARCHAR(200) NULL, link_linkedin VARCHAR(200) NULL, link_candidatura VARCHAR(250) NULL, link_plataforma VARCHAR(12) NULL, destaque SMALLINT NOT NULL DEFAULT 0, status VARCHAR(25) NOT NULL DEFAULT 'aguardando_aprovacao', motivo TEXT NULL, versao SMALLINT NOT NULL DEFAULT 1, criado_em TIMESTAMP NOT NULL, atualizado_em TIMESTAMP NULL, avaliado_em TIMESTAMP NULL, aviso_vencendo SMALLINT NOT NULL DEFAULT 0, aviso_encerrada SMALLINT NOT NULL DEFAULT 0, FOREIGN KEY (empresa_id) REFERENCES empresas(id));

CREATE TABLE IF NOT EXISTS historico ( id SERIAL PRIMARY KEY, entidade VARCHAR(10) NOT NULL, entidade_id INT NOT NULL, acao VARCHAR(30) NOT NULL, de VARCHAR(25) NULL, para VARCHAR(25) NULL, ator_tipo VARCHAR(10) NOT NULL, ator_id INT NULL, detalhe VARCHAR(300) NULL, criado_em TIMESTAMP NOT NULL);

CREATE TABLE IF NOT EXISTS acessos_log ( id SERIAL PRIMARY KEY, perfil VARCHAR(10) NOT NULL, usuario_id INT NULL, email VARCHAR(120) NULL, evento VARCHAR(15) NOT NULL, ip VARCHAR(45) NULL, agente VARCHAR(160) NULL, criado_em TIMESTAMP NOT NULL);

CREATE TABLE IF NOT EXISTS notificacoes (id SERIAL PRIMARY KEY, tipo VARCHAR(12) NOT NULL, referencia_id INT NOT NULL, mensagem VARCHAR(200) NOT NULL, lida SMALLINT NOT NULL DEFAULT 0, criado_em TIMESTAMP NOT NULL);

CREATE TABLE IF NOT EXISTS emails_log (id SERIAL PRIMARY KEY, destinatario VARCHAR(120) NOT NULL, assunto VARCHAR(200) NOT NULL, status VARCHAR(10) NOT NULL, erro VARCHAR(255) NULL, criado_em TIMESTAMP NOT NULL);

CREATE TABLE IF NOT EXISTS login_tentativas (id SERIAL PRIMARY KEY, chave VARCHAR(64) NOT NULL, criado_em TIMESTAMP NOT NULL);

CREATE INDEX idx_vagas_status ON vagas (status, data_limite);

CREATE INDEX idx_vagas_empresa ON vagas (empresa_id);

CREATE INDEX idx_login_chave ON login_tentativas (chave);

CREATE INDEX idx_hist_ent ON historico (entidade, entidade_id);

CREATE INDEX idx_acessos_data ON acessos_log (criado_em);

CREATE INDEX idx_empresas_status ON empresas (status);

-- Registra as migrations já contempladas por este esquema (assim o sistema não tenta reaplicá-las)
INSERT INTO migracoes (nome, aplicada_em) VALUES
  ('001_endereco_estruturado', CURRENT_TIMESTAMP),
  ('002_refinamento_empresas_vagas', CURRENT_TIMESTAMP);

INSERT INTO areas (nome, padrao) VALUES
  ('Administração', 1),
  ('Engenharia', 1),
  ('Logística', 1),
  ('Marketing', 1),
  ('Qualidade', 1),
  ('Tecnologia da Informação', 1),
  ('Recursos Humanos', 1),
  ('Financeiro', 1),
  ('Comercial', 1),
  ('Saúde', 1),
  ('Educação', 1),
  ('Jurídico', 1);
