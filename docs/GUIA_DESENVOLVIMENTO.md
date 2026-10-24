# Guia de desenvolvimento do ESTAGIA+ (do zero, na ordem das histórias e sprints)

Este guia permite reconstruir e evoluir o sistema seguindo o backlog da planilha (`Planilha_Gestao_Scrum_EstagiaMais.xlsx`, abas Product_Backlog/Cronograma). Os códigos `NNN.NN` são as tarefas da planilha. A implementação de referência está neste repositório; cada tarefa indica o arquivo correspondente.

## 1. Requisitos
PHP 8.0+ (extensões `pdo_sqlite` ou `pdo_mysql` ou `pdo_pgsql`, `mbstring`, `fileinfo`, `curl` opcional), Apache com `mod_rewrite` (cPanel/XAMPP) ou o servidor embutido do PHP. Git. Node/Python **não** são necessários para rodar (só para testes de navegador opcionais).

## 2. Instalação
```bash
git clone <repo> && cd projeto-estagio
cp .env.example .env            # edite ADMIN_EMAIL e ADMIN_PASSWORD (8+ caracteres, letras e números)
php database/seed.php --demo    # cria o banco, o administrador e dados de exemplo (opcional)
php -S localhost:8000 router.php
```
Admin: `http://localhost:8000/?p=admin-login`. Empresas de demonstração: `rh@technova.example` / `demo12345`.
XAMPP: coloque em `htdocs/projeto-estagio`, use `DB_DRIVER=mysql` (ou sqlite) e abra `http://localhost/projeto-estagio/`.

## 3. Configuração do ambiente (`.env`)
`APP_URL`, banco (`DB_*`), e-mail (`MAIL_*`, `SMTP_*`), `ADMIN_*`, `PRETTY_URLS`, `SESSION_IDLE_*`. **O `.env` nunca vai para o Git.** E-mail: `MAIL_DRIVER=log` só grava em `storage/mail/`; para enviar de verdade use `smtp` (Mailtrap para teste; conta `email-automation@…` em produção). Diagnóstico em *Admin > Configurações*.

## 4. Configuração do PHP
No XAMPP/cPanel confirme as extensões `pdo_mysql` (ou `pdo_sqlite`), `mbstring`, `fileinfo`. `upload_max_filesize` ≥ 5M. `date.timezone` é definido pelo sistema (America/Sao_Paulo).

## 5. Dependências
Nenhuma via Composer/NPM. Única biblioteca de terceiros: `public/js/vendor/qrcode.js` (qrcode-generator 1.4.4, MIT), copiada localmente (o modo TV funciona sem internet). A API ViaCEP é consultada pelo navegador.

## 6. Estrutura de pastas
Ver `docs/ARQUITETURA.md` §2.

## 7. Ordem correta de desenvolvimento (dependências)
A ordem completa, branch por branch, com o **código a copiar em cada uma**, está em **`docs/GUIA_PASSO_A_PASSO.md`** (14 branches, cada uma com um dono). Resumo da ordem de merge:
```
1 architecture (PH) → 2 security (PM) + 3 database-company (RQ) + 4 backend-core (JR) → 5 design-system (PH) + 6 notifications (PM)
→ 7 backend-auth (JR) → 8 backend-jobs (JR) → 9 frontend-company (RQ) + 10 frontend-admin (PH) → 11 access-control (PM)
→ 12 frontend-jobs (AR) → 13 tv-mode (PM) → 14 qa (PM)
```
Os códigos das tarefas (`NNN.NN`) são os da planilha; o prefixo é o produto do catálogo (aba Produtos).

## 8. Banco de dados e migrations (113.*)
1. **Modelagem:** tabelas e relacionamentos em `docs/ARQUITETURA.md` §5; esquema em `database/schema.php` (função `schema_sql()`).
2. **Instalação nova:** `php database/migrate.php` cria o esquema final e registra as migrations como aplicadas.
3. **Nova mudança de banco = nova migration:** crie `database/migrations/003_nome.php` retornando `['up' => fn(PDO $pdo, string $driver) => ..., 'down' => fn(...) => ...]`. Use `add_coluna()`, `coluna_existe()`, `tabela_existe()` (idempotentes). **Atualize também `schema_sql()`** (instalação nova) e regenere os SQL: `php database/gerar_sql.php mysql > database/mysql.sql` (e `pgsql > postgres.sql`).
4. **Aplicar/consultar/desfazer:** `php database/migrate.php` · `... status` · `... down`. Em produção o sistema aplica pendentes sozinho no primeiro acesso após o deploy (`Bootstrap`).
5. **Importar manualmente (cPanel/phpMyAdmin):** importe `database/mysql.sql` num banco vazio.
6. **Teste obrigatório:** `php tests/migracao.php` (rollback + reaplicação preservando dados).
Regras: nunca editar uma migration já aplicada; colunas novas devem ser `NULL` ou ter `DEFAULT`; dados antigos são convertidos na própria migration.

## 9. Backend (106.*, 107.01, 108.*, 109.*)
Padrão: **Controller** (autoriza + valida) → **Form** (`EmpresaForm`, `VagaForm`) → **Model** (regra + SQL) → **Historico** (auditoria) → **Notificador** (e-mails).
- Toda mudança de estado passa por `Estado` (transições) e `Model::mudarStatus()`; nunca `UPDATE status` solto.
- Listagens por situação usam `Estado::sqlSituacao()`; **proibido** decidir expiração no JavaScript.
- Edição de vaga aprovada: `Vaga::atualizar()` compara campos (`camposAlterados`) e, se mudou, volta para `aguardando_aprovacao`.
- Para criar uma rota nova: método no Controller → linha na tabela de `index.php` → View → (se for admin/empresa) `render('view', [...], 'app')`.

## 10. Autenticação (106.*)
Dois contextos exclusivos (`Auth`). Login: `Auth::entrar()`. Proteção de rota: primeira linha da ação, `Auth::exigirEmpresa()` ou `Auth::exigirAdmin()`. Para qualquer acesso a recurso por ID, **sempre** use o método com ownership (`daEmpresaPorId`) ou `Auth::recusar()`.

## 11. Frontend (107.*, 108.*, 110.*, 111.*, 112.*)
- Telas logadas usam `public/css/app.css` + `public/js/app.js`; público usa `vagas.css` + `vagas.js`.
- Formulário em etapas: `<form data-form data-wiz>` + `<fieldset class="ui-fieldset ui-step" data-step="N" data-title="...">`; a revisão final lista os campos com `data-rev="Rótulo"`.
- Campos: `Ui::campo([...], $old, $erros)`. Máscaras: `data-mask="cnpj|tel|cep|money|int"`. ViaCEP: `data-viacep` + `data-cep-fill`.
- Modal de confirmação: `Ui::formConfirmar(...)`; modal com motivo: `Ui::formMotivo(...)`. **Não use `alert()`/`confirm()`.**
- Modo noturno: usar só variáveis (`var(--surface)`, `var(--text)`…). Cor fixa em componente é bug.
- Sem emojis na interface. Ícones: `icon('nome')` (SVG embutido).

## 12. Integração
O front consome apenas o que está em `docs/ROTAS_E_CONTRATOS.md`. Mudou campo/rota? Atualize o contrato **no mesmo PR**.

## 13. Testes (113.06–113.08)
```bash
tests/run.sh            # SQLite (padrão): 185+ verificações + migrações
tests/run.sh mysql      # exige DB_* no ambiente e o banco criado
```
Cobrem: empresa (cadastro, login, edição, vaga, reaprovação, encerramento), admin, estudante, **segurança** (empresa→admin, admin→empresa, IDOR entre empresas, ID manipulado, sessão inválida/expirada, CSRF, SQLi, XSS, brute force), modo TV/QR e migrações. Antes de abrir PR: `tests/run.sh` verde. Teste de navegador (wizard, modais, QR) é manual/Playwright opcional.

## 14. Git, branches, PR e merge
Ver `docs/GUIA_PASSO_A_PASSO.md` (fluxo por branch) e `docs/EQUIPE_E_FLUXO_GIT.md`.

## 15. Deploy (cPanel)
1. Envie os arquivos (ideal: domínio apontando para a pasta). 2. Crie banco MySQL + usuário; importe `database/mysql.sql` (ou deixe o sistema criar). 3. `.env` de produção (`APP_URL`, `DB_*`, `MAIL_*`, `ADMIN_*`, `PRETTY_URLS=1`). 4. Permissão de escrita em `storage/` e `public/uploads/`. 5. Cron diário: `php /home/USUARIO/pasta/cron/rotina.php`. 6. Confirme que `/.env`, `/storage/` e `/docs/` dão 403. 7. *Admin > Configurações > Enviar e-mail de teste*.
