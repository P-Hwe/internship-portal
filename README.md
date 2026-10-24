# ESTAGIA+ — Vagas de Estágio do IFSP Guarulhos

Plataforma PHP (monorepo: front + back + banco) com **três ambientes independentes**: estudante (público), empresa e administração.

| Documento | Para quê |
|---|---|
| `docs/GUIA_DESENVOLVIMENTO.md` | Instalação, ordem de desenvolvimento do zero, banco/migrations, testes, deploy |
| `docs/ARQUITETURA.md` | Camadas, máquina de estados, autorização, banco, hospedagem |
| `docs/ROTAS_E_CONTRATOS.md` | Rotas, campos e dados (o que o front espera e o back entrega) |
| `docs/EQUIPE_E_FLUXO_GIT.md` | Quem faz o quê, dependências, branches, PR e merge |

Início rápido: `cp .env.example .env && php database/seed.php --demo && php -S localhost:8000 router.php` (admin em `/?p=admin-login`). Testes: `tests/run.sh`.

---

# Vagas de Estágio - IFSP Guarulhos (monorepo PHP: front + back + banco)

Front (Views/CSS/JS) e back (Controllers/Models/app) vivem **no mesmo projeto e na mesma hospedagem**. PHP 8.0+ com PDO.

## Rodar local (sem instalar banco)
No primeiro acesso o sistema cria sozinho as tabelas e o administrador (usa ADMIN_EMAIL/ADMIN_PASSWORD do `.env`; sem `.env`, o admin não é criado: copie o `.env.example`). Erros ficam em `storage/erro.log`.

    cp .env.example .env            # ajuste ADMIN_EMAIL / ADMIN_PASSWORD
    php database/seed.php --demo    # cria tabelas (SQLite), admin e dados de exemplo
    php -S localhost:8000 router.php
Admin: e-mail/senha do `.env`. Empresas de demonstração: `rh@technova.example` / `demo12345`.
E-mails em desenvolvimento (`MAIL_DRIVER=log`) são gravados em `storage/mail/*.html`.

## Telas e acesso
| Rota (?p=) | Quem | O quê |
|---|---|---|
| home, vaga&id=N, tv | público | destaque + carrosséis (novas, recrutador ativo, melhores salários, jovem aprendiz/profissional, trainee, estágio, encerrando) e lista com filtros |
| empresa-login / empresa-cadastro | empresa | páginas separadas de entrada e cadastro |
| painel, vaga-cadastro | empresa logada | situação do cadastro e das vagas; publicar vaga só com empresa aprovada |
| admin-login | administrador | página própria de login |
| admin | administrador logado | aprovar/reprovar empresas e vagas, destaque, encerrar, notificações; o link só aparece para admin logado |

## Login único por vez
Admin e empresa são exclusivos: com um perfil conectado, as páginas e o menu do outro não abrem (o sistema avisa e leva de volta à área do perfil atual). Para trocar de perfil, use **Sair**. O menu do administrador mostra só itens de administração; o da empresa, só itens de empresa.

## E-mails: como receber de verdade
Com `MAIL_DRIVER=log` (padrão do `.env.example`) **nada é enviado**: as mensagens só são gravadas em `storage/mail/`. Para receber na caixa de entrada:
1. No `.env`: `MAIL_DRIVER=smtp` e preencha `SMTP_HOST/PORT/SECURE/USER/PASS` e `MAIL_FROM` (veja os exemplos de Gmail, cPanel e Mailtrap no `.env.example`). `MAIL_FROM` deve ser igual a `SMTP_USER`.
2. Defina quem recebe os avisos de novas empresas/vagas em `ADMIN_NOTIFY_EMAIL` (aceita vários separados por vírgula).
3. Aprove uma empresa ou vaga de teste e confira a caixa de entrada (e o spam) do e-mail cadastrado.
4. Se a ação de aprovar/reprovar não conseguir avisar a empresa, o painel mostra o erro na hora.
Testado contra um servidor SMTP de teste (`tests/smtp_falso.py`); o envio por Gmail/cPanel depende das suas credenciais.

## Fluxos (tudo funcional)
1. Empresa se cadastra em 4 etapas (Empresa, Endereço com preenchimento automático pelo CEP via ViaCEP, Contato e acesso, Termo) -> fica *pendente* -> e-mail ao admin + aviso no sino do painel + e-mail de confirmação à empresa.
2. Admin aprova/reprova (reprovar exige motivo) -> e-mail à empresa.
3. Empresa aprovada publica vaga (tipo, área, datas...) -> pendente -> admin aprova -> e-mail à empresa dizendo que está no ar **e até quando** (data limite).
4. A vaga aparece e some sozinha pelas datas (a consulta filtra por data). `cron/rotina.php` (1x/dia) marca como encerrada e avisa a empresa ("encerra em 3 dias" e "encerrada").
5. Breve descrição do card = 2 a 3 primeiras linhas da descrição (não existe mais campo próprio).
6. Área "Outra...": cria a área; ela só vira filtro se tiver `AREA_MIN_VAGAS` vagas no ar (config.php), senão entra em "Outras".
7. Selos no banner do card: tipo, Nova, Encerra em X dias, Destaque, Melhor salário, Recrutador ativo.

## CNPJ alfanumérico
`app/Cnpj.php` e `public/js/vagas.js` seguem a IN RFB 2.229/2024: 12 primeiros caracteres A-Z/0-9, 2 últimos dígitos, módulo 11 com valor = ASCII - 48. CNPJ numérico antigo continua válido. Coluna `cnpj VARCHAR(14)`. Teste: `12.ABC.345/01DE-35`.

## Banco: qual escolher
Troque só o `.env` (`DB_DRIVER=mysql|pgsql|sqlite`) e rode `php database/migrate.php`.
**Recomendação para cPanel: MySQL/MariaDB** (já vem incluso e tem phpMyAdmin/backup). PostgreSQL só se o plano oferecer. SQLite serve para desenvolvimento.

## Hospedagem: separar front e back ou monorepo?
Recomendado: **manter tudo junto (monorepo) em uma única pasta no cPanel**, porque:
- É PHP renderizado no servidor: não existe "build" de front separado, e o cPanel roda PHP nativamente (sem Setup Node.js App).
- Mesma origem: sem CORS, a sessão por cookie e o CSRF funcionam sem configuração extra, e as rotas de admin ficam protegidas no servidor.
- Um deploy só, um `.env` só.
Separar só compensaria com um front SPA (React/Vue) + API; aqui o ganho seria nenhum e os riscos (CORS, tokens, dois deploys) aumentam.

## Criar o banco MySQL você mesmo (cPanel / phpMyAdmin)
1. cPanel > MySQL Databases: crie o banco e um usuário, e dê TODAS as permissões do usuário sobre o banco.
2. phpMyAdmin > selecione o banco > aba **Importar** > envie `database/mysql.sql` (cria as 7 tabelas e as áreas padrão).
3. No `.env` do servidor: `DB_DRIVER=mysql`, `DB_HOST=localhost`, `DB_NAME`, `DB_USER`, `DB_PASS` (o cPanel prefixa com o usuário da conta, ex.: `conta_vagas`), mais `ADMIN_EMAIL` e `ADMIN_PASSWORD` (8+ caracteres).
4. Abra o site: o administrador é criado no primeiro acesso. Nenhum comando de terminal é necessário.
Para regerar os SQL: `php database/gerar_sql.php mysql > database/mysql.sql` (ou `pgsql > database/postgres.sql`).
Testado com a suíte completa (91 verificações) em MariaDB (MySQL), PostgreSQL 16 e SQLite.

## PostgreSQL (se o seu servidor oferecer)
1. Crie o banco em UTF8 e um usuário: `CREATE DATABASE vagas ENCODING 'UTF8' TEMPLATE template0;` (no cPanel: *PostgreSQL Databases*).
2. Importe `database/postgres.sql` (phpPgAdmin ou `psql -f`).
3. `.env`: `DB_DRIVER=pgsql`, `DB_HOST=localhost`, `DB_PORT=5432`, `DB_NAME`, `DB_USER`, `DB_PASS`, mais `ADMIN_EMAIL` e `ADMIN_PASSWORD`.
4. Exige a extensão PHP `pdo_pgsql` (cPanel > Select PHP Version > Extensions). Abra o site: o admin é criado no primeiro acesso.

## Deploy no cPanel
1. Envie o conteúdo para a pasta do domínio (ou subpasta; BASE_URL é detectado). Ideal: apontar o domínio para a pasta do projeto.
2. Crie o banco MySQL + usuário no cPanel e preencha `.env` (DB_*, APP_URL, MAIL_*, ADMIN_*).
3. Terminal do cPanel ou "Executar script PHP": `php database/seed.php` (cria tabelas e o admin).
4. Cron diário: `php /home/USUARIO/pasta/cron/rotina.php`.
5. E-mail: `MAIL_DRIVER=mail` funciona na maioria; para melhor entrega use `smtp` com uma conta do domínio (SPF/DKIM ativos).
6. `.htaccess` já bloqueia app/, Controllers/, Models/, Views/, database/, storage/ e .env. Confirme acessando /.env (deve dar 403).
7. Permissão de escrita: `storage/` e `public/uploads/banners/`.

## Segurança já implementada
Senhas com bcrypt/`password_hash`; sessão com cookie HttpOnly/SameSite e regeneração no login; limite de 5 tentativas/15 min; CSRF em todo POST; consultas preparadas (PDO); saída escapada; upload validado pelo conteúdo real do arquivo; termo de cooperação fora da pasta pública (só admin baixa); empresa só encerra a própria vaga.

## Testes
    DB_SQLITE=/tmp/t.sqlite php database/seed.php --demo
    DB_SQLITE=/tmp/t.sqlite php -S 127.0.0.1:8123 router.php &
    DB_SQLITE=/tmp/t.sqlite php tests/fluxo.php http://127.0.0.1:8123     # 91 verificações
