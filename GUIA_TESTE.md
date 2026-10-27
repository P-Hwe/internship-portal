# 🚀 Guia de Instalação e Teste - Internship Portal

Este guia mostra como rodar o projeto em ambiente de teste (hml ou develop) com Docker.

---

## 📋 Pré-requisitos

- **Docker Desktop** instalado e rodando
- **Git** instalado
- **PHP 8.1+** (opcional, para rodar scripts manualmente)

---

## 🎯 Passo a Passo Completo

### 1️⃣ Iniciar Docker

**Opção A: Usar script automático (recomendado)**
```powershell
# Windows - Script completo
.\scripts\start-docker-full.ps1

# Linux/Mac
./scripts/start-docker.sh
```

**Opção B: Manual**
```bash
# Verificar se o Docker está rodando
docker --version

# Iniciar o Docker Desktop (se necessário)
# No Windows: Abra o Docker Desktop pelo menu Iniciar
```

### 2️⃣ Configurar Variáveis de Ambiente

```bash
# Copiar o arquivo .env.example para .env
copy .env.example .env

# Editar o arquivo .env com suas configurações
notepad .env
```

**Configurações necessárias no `.env`:**

```env
APP_ENV=dev
APP_URL=http://localhost:8000
PRETTY_URLS=1

# Banco de dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=vagas
DB_PASSWORD=sua_senha_ou_vazia  # Pode deixar vazio para teste

# E-mail (opcional - use log para teste)
MAIL_DRIVER=log
ADMIN_EMAIL=teste@seudominio.edu.br
ADMIN_PASSWORD=TroqueEstaSenha123
```

### 3️⃣ Iniciar o Banco de Dados

```bash
# Iniciar os containers Docker
docker-compose up -d

# Verificar se está rodando
docker-compose ps

# Ver logs do banco (opcional)
docker-compose logs mysql-vagas
```

**Saída esperada:**
```
mysql-vagas    | [Warning] [MY-010453] [Server] query cache is disabled
mysql-vagas    | mysql: 8.0.34-0ubuntu0.22.04.1 [(Ubuntu)]
mysql-vagas    | 2026-10-27T01:30:00.000000Z 0 [System] [MY-010931] [Server] /usr/sbin/mysqld: ready for connections.
```

### 4️⃣ Verificar Arquivos do Projeto

```bash
# Listar estrutura do projeto
tree /F

# Verificar arquivos importantes
dir database\
dir public\
dir views\
```

**Arquivos essenciais que devem existir:**
- ✅ `database/mysql.sql` - Estrutura do banco
- ✅ `database/schema.php` - Classe Database
- ✅ `database/seed.php` - Script de seed
- ✅ `config.php` - Configuração do projeto
- ✅ `public/index.php` - Entry point

### 5️⃣ Rodar o Seed (Preencher Banco com Dados)

**Opção A: Usar script automático (recomendado)**
```powershell
# Windows - Script de seed
.\scripts\seed-local.ps1

# Linux/Mac
./scripts/seed-local.sh
```

**Opção B: Via linha de comando**
```bash
php database\seed.php
```

**Opção C: Via navegador**
```
http://localhost:8000/database/seed.php?force=1
```

**Saída esperada:**
```
📦 Criando tabelas iniciais...
✅ Criada: migracoes
✅ Criada: admins
✅ Criada: empresas
✅ Criada: vagas
✅ Criada: areas
✅ Criada: historico
✅ Criada: acessos_log
✅ Criada: notificacoes
✅ Criada: emails_log
✅ Criada: visitas
✅ Criada: login_tentativas

📋 Todas as tabelas no banco:
   - acessos_log
   - admins
   - areas
   - emails_log
   - empresas
   - historico
   - login_tentativas
   - migracoes
   - notificacoes
   - vagas
   - visitas

✅ Migrações registradas!
🎉 Banco de dados pronto!
```

### 6️⃣ Verificar o Banco de Dados

```bash
# Conectar ao banco via MySQL Workbench, DBeaver ou phpMyAdmin
mysql -h localhost -P 3306 -u root -p vagas

# Ou usar DBeaver:
# Host: localhost
# Port: 3306
# Database: vagas
# User: root
# Password: (vazio ou a senha definida)

# Verificar tabelas
SHOW TABLES;

# Verificar admins cadastrados
SELECT * FROM admins;

# Verificar áreas cadastradas
SELECT * FROM areas;
```

### 7️⃣ Acessar o Portal

```bash
# Abrir no navegador
http://localhost:8000

# Ou se estiver usando Apache/Nginx
http://localhost/projeto-estagio
```

**Primeiro acesso:**
- **Admin:** `teste@seudominio.edu.br` / `TroqueEstaSenha123`
- A senha será solicitada na primeira entrada

### 8️⃣ Verificar Logs

```bash
# Logs do Docker
docker-compose logs -f

# Logs do PHP (se houver)
tail -f storage/erro.log
tail -f storage/access.log
```

---

## 🔧 Comandos Úteis

### Iniciar/Parar Containers

```bash
# Iniciar tudo (script automático)
.\scripts\start-docker-full.ps1  # Windows
./scripts/start-docker.sh        # Linux/Mac

# Iniciar apenas o banco
docker-compose up -d mysql-vagas

# Parar tudo
docker-compose down

# Parar e remover volumes (reinstala tudo)
docker-compose down -v
```

### Seed Automático

```bash
# Windows
.\scripts\seed-local.ps1

# Linux/Mac
./scripts/seed-local.sh
```

### Verificar Status

```bash
# Status dos containers
docker-compose ps

# Logs em tempo real
docker-compose logs -f

# Verificar espaço em disco
docker system df
```

### Reset Completo

```bash
# Remover tudo e começar do zero
docker-compose down -v
rm -rf storage/*
php database\seed.php
```

---

## 🐛 Solução de Problemas

### Problema: "Container não inicia"

**Solução:**
```bash
# Verificar erros no log
docker-compose logs mysql-vagas

# Recriar containers
docker-compose down
docker-compose up -d --build
```

### Problema: "Erro de conexão com banco"

**Solução:**
```bash
# Verificar se o banco está rodando
docker-compose ps

# Reiniciar apenas o banco
docker-compose restart mysql-vagas

# Verificar logs do banco
docker-compose logs mysql-vagas
```

### Problema: "Erro ao rodar seed.php"

**Solução:**
```bash
# Verificar versão do PHP
php -v

# Verificar se há erros de sintaxe
php -l database/seed.php

# Limpar cache de opcache
opcache_reset
```

### Problema: "Links quebrados no modo TV"

**Solução:**
```env
# No arquivo .env
APP_URL=http://localhost:8000
PRETTY_URLS=1
```

---

## 📊 Dados de Teste

O seed.php cria automaticamente:

### Admin Inicial
- **Email:** `teste@seudominio.edu.br`
- **Senha:** `TroqueEstaSenha123`
- **Papel:** admin

### Áreas Cadastradas
- Administração, Engenharia, Logística, Marketing, Qualidade
- Tecnologia da Informação, Recursos Humanos, Financeiro
- Comercial, Saúde, Educação, Jurídico

### Configurações Padrão
- **Sessão Admin:** 1800 segundos (30 min)
- **Sessão Empresa:** 7200 segundos (2 horas)
- **Upload Max:** 5 MB
- **Vagas por página:** 9

---

## 🎯 Próximos Passos

1. ✅ Banco de dados criado e populado
2. ✅ Admin inicial cadastrado
3. ✅ Áreas padrão cadastradas
4. ⏭️ **Criar empresas de teste** (via painel admin)
5. ⏭️ **Criar vagas de teste** (via painel empresa)
6. ⏭️ **Testar funcionalidades** (modo TV, modo finish, etc.)

---

## 📞 Suporte

Se encontrar problemas:
1. Verifique os logs: `docker-compose logs -f`
2. Confira o `.env` está configurado corretamente
3. Verifique se o Docker Desktop está rodando
4. Reinicie o Docker Desktop

---

**Boa sorte com os testes! 🎉**
