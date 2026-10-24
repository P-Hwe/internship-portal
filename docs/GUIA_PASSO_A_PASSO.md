# ESTAGIA+ — Guia passo a passo: tarefas e branches por pessoa

Este guia reconstrói o ESTAGIA+ (plataforma PHP de vagas de estágio do IFSP Guarulhos) **do zero**, em **14 branches**, cada uma com **um dono**, na **ordem em que devem ser mescladas**. Em cada branch você encontra: as tarefas da planilha, o código para copiar (arquivo por arquivo), como verificar e como fechar. O código é o do projeto testado (185 verificações automáticas); seguindo a ordem, o resultado é idêntico.

## 1. O que muda em relação ao projeto original
| Antes | Agora |
|---|---|
| Login, cadastro e painéis dentro do cabeçalho do IFSP | Login, cadastro e painéis em layouts independentes (`layouts/auth` e `layouts/app`) |
| Empresa e administrador logados ao mesmo tempo | Login único por vez, exigido no backend |
| Tabelas e formulários gigantes | Cards, abas, modais e formulários em etapas |
| Estados soltos (aprovada some das listas) | Máquina de estados + situação calculada pelas datas |
| Sem histórico e sem migrations | Tabela `historico`, `acessos_log` e migrations com rollback |

O que **não** muda: o visual do cabeçalho, rodapé e carrosséis do portal do IF (`Views/header.php`, `Views/footer.php`).

## 2. Fluxo de trabalho com Git: uma branch por etapa
Cada branch sai de `develop`, é revisada por outra pessoa em Pull Request e volta para `develop`. Quando uma branch depende de outra, espere o merge dela antes de começar (coluna *Depende de*). `main` só recebe `develop` no fim, com os testes verdes.

| Ordem | Branch | Dono | Revisor | Depende de | Pode andar em paralelo com |
|---|---|---|---|---|---|
| 1 | `feature/architecture` | Pedro Henri | Rodrigo | — | feature/security, feature/database-company, feature/backend-core (depois que esta for mesclada) |
| 2 | `feature/security` | Pedro Miguel | Pedro Henri | `feature/architecture` | feature/database-company, feature/backend-core |
| 3 | `feature/database-company` | Rodrigo | José Roberto | `feature/architecture` | feature/security, feature/backend-core |
| 4 | `feature/backend-core` | José Roberto | Pedro Miguel | `feature/architecture` | feature/security, feature/database-company |
| 5 | `feature/design-system` | Pedro Henri | Rodrigo | `feature/backend-core` | feature/notifications |
| 6 | `feature/notifications` | Pedro Miguel | Pedro Henri | `feature/database-company`, `feature/backend-core` | feature/design-system |
| 7 | `feature/backend-auth` | José Roberto | Pedro Miguel | `feature/security`, `feature/database-company`, `feature/backend-core`, `feature/notifications` | — |
| 8 | `feature/backend-jobs` | José Roberto | Pedro Miguel | `feature/backend-auth` | — |
| 9 | `feature/frontend-company` | Rodrigo | José Roberto | `feature/design-system`, `feature/backend-jobs` | feature/frontend-admin |
| 10 | `feature/frontend-admin` | Pedro Henri | Rodrigo | `feature/design-system`, `feature/backend-jobs` | feature/frontend-company |
| 11 | `feature/access-control` | Pedro Miguel | Pedro Henri | `feature/frontend-admin` | feature/frontend-jobs |
| 12 | `feature/frontend-jobs` | Arthur | Pedro Henri | `feature/frontend-company`, `feature/frontend-admin` | feature/access-control |
| 13 | `feature/tv-mode` | Pedro Miguel | Arthur | `feature/frontend-jobs` | feature/qa (testes podem começar antes) |
| 14 | `feature/qa` | Pedro Miguel | José Roberto | `feature/tv-mode`, `feature/access-control` | — |

Fluxo de cada branch (os comandos exatos estão no fim de cada seção):
```bash
git checkout develop && git pull
git checkout -b feature/<nome>
# ...siga os passos da branch...
git add . && git commit -m "<mensagem indicada>"
git push -u origin feature/<nome>
# abra o Pull Request para develop; o revisor aprova; depois:
git checkout develop && git pull   # com o merge feito
```
Regras: commits pequenos e descritivos; nada de `.env`, `storage/*` ou uploads no commit; antes do PR rode a verificação da branch.

## 3. Roteiro de cada pessoa
Cada pessoa vê abaixo as suas branches, em ordem, e as tarefas da planilha (aba Product_Backlog) que cada uma cobre, com a sprint proposta no Cronograma. As datas e durações são estimativas a validar no Planning.

### José Roberto
- **4. `feature/backend-core`** — Regras centrais: máquina de estados, auditoria, CNPJ alfanumérico e paginação. Tarefas: `111.01`, `105.20`. Sprints propostas: 1 a 5.
- **7. `feature/backend-auth`** — Autenticação: contextos empresa × admin exclusivos, sessão, limite de tentativas, cadastro da empresa e administrador inicial. Tarefas: `105.01`, `106.02`, `106.03`, `106.04`, `106.05`, `105.02`, `105.10`, `105.11`. Sprints propostas: 1 a 8.
- **8. `feature/backend-jobs`** — Backend das vagas e dos painéis: modelo de vaga, validação, estatísticas e controllers (público, empresa, administração). Tarefas: `111.02`, `111.04`, `111.05`, `109.04`, `111.03`, `108.01`, `105.12`, `108.13`, `108.07`, `105.15`. Sprints propostas: 3 a 9.

### Rodrigo
- **3. `feature/database-company`** — Banco de dados: conexão, esquema final, migrations versionadas com rollback e geração dos SQL de instalação. Tarefas: `105.17`, `105.18`, `105.19`, `105.21`. Sprints propostas: 1 a 6.
- **9. `feature/frontend-company`** — Telas da empresa: login, cadastro em 5 etapas com ViaCEP, perfil e lista de vagas em cards. Tarefas: `106.06`, `105.03`, `105.04`, `105.05`, `105.06`, `105.07`, `105.08`, `105.09`, `108.03`, `105.16`, `109.02`. Sprints propostas: 2 a 8.

### Pedro Miguel
- **2. `feature/security`** — Peças de segurança reutilizadas por todos: CSRF, upload seguro e validação de links externos. Tarefas: `109.01`, `108.06`. Sprints propostas: 2 a 5.
- **6. `feature/notifications`** — E-mails: remetente único (log, mail ou SMTP), avisos ao administrador e à empresa, notificações do painel. Tarefas: `107.01`, `107.02`, `107.03`, `107.04`. Sprints propostas: 1 a 10.
- **11. `feature/access-control`** — Usuários administradores e controle de acesso (logins, falhas, acessos negados, bloqueios). Tarefas: `111.13`, `111.14`. Sprints propostas: 4 a 4.
- **13. `feature/tv-mode`** — Modo TV com QR Code individual por vaga (biblioteca local, sem internet). Tarefas: `110.02`, `110.04`. Sprints propostas: 6 a 7.
- **14. `feature/qa`** — Testes automatizados de fluxo, segurança, QR e migrações. Tarefas: `105.24`, `105.23`, `105.25`, `111.06`, `105.22`, `105.27`. Sprints propostas: 2 a 9.

### Pedro Henri
- **1. `feature/architecture`** — Esqueleto do projeto: configuração, roteador, regras de acesso do servidor, modelo de PR. Tarefas: `105.26`, `106.01`, `110.01`. Sprints propostas: 1 a 6.
- **5. `feature/design-system`** — Design system das áreas logadas: tokens com modo noturno, layouts independentes, componentes PHP e núcleo do JavaScript. Tarefas: `106.08`, `104.01`, `104.02`. Sprints propostas: 2 a 10.
- **10. `feature/frontend-admin`** — Telas da administração: login, dashboard, aprovações, vagas, empresas, relatórios e configurações. Tarefas: `106.07`, `111.07`, `111.11`, `111.15`, `111.16`, `111.09`, `111.10`, `107.05`, `111.08`, `111.12`. Sprints propostas: 2 a 8.

### Arthur
- **12. `feature/frontend-jobs`** — Site público (vagas, carrosséis, empresas parceiras), formulário de vaga em 8 etapas e telas da empresa para vagas. Tarefas: `108.05`, `108.10`, `108.11`, `108.12`, `108.02`, `108.04`, `105.13`, `105.14`, `108.08`, `108.09`, `110.03`, `109.03`, `104.03`, `104.04`. Sprints propostas: 1 a 12.

## 4. Preparação (uma vez só, feita pelo Pedro Henri)
```bash
mkdir projeto-estagio && cd projeto-estagio
git init -b main
```
Copie do **zip original do projeto** (`projeto-estagio.zip`) estes itens, **sem alterar**:
- `Views/header.php` e `Views/footer.php` (cabeçalho e rodapé do portal do IF)
- `images/if.png`
- `public/uploads/banners/vaga-01.jpg` a `vaga-07.jpg` (banners dos dados de exemplo)
- `database/seed/empresas.json` e `database/seed/vagas.json` (dados de exemplo, opcionais)
- a pasta `docs/` e o `README.md` (documentação)

```bash
mkdir -p Views database/seed images public/uploads/banners docs
# (copie os arquivos acima do zip para estes lugares)
git add . && git commit -m "chore: arquivos originais do portal e documentação"
git branch develop
```
**Requisitos:** PHP 8.0+ com `pdo_sqlite` (ou `pdo_mysql`/`pdo_pgsql`), `mbstring` e `fileinfo`; Apache com `mod_rewrite` (ou o servidor embutido do PHP); Node.js só para baixar o QR Code (uma vez); Python 3 só para os testes de e-mail (opcional).

## 5. Como rodar (depois de mesclar as branches 1 a 8)
```bash
cp .env.example .env     # edite ADMIN_EMAIL e ADMIN_PASSWORD
php database/seed.php --demo    # cria o banco, o administrador e dados de exemplo
php -S localhost:8000 router.php
```
Admin: `http://localhost:8000/?p=admin-login`. Empresa de exemplo: `rh@technova.example` / `demo12345`.

---

## 🌿 Branch 1: `feature/architecture` — dono: Pedro Henri

Esqueleto do projeto: configuração, roteador, regras de acesso do servidor, modelo de PR.

**Revisor do PR:** Rodrigo. **Depende de:** nada (é a primeira).

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `105.26` | Estrutura de pastas, padrões, contratos e guia da equipe | 3 | 1 |
| `106.01` | Definir os contextos de autenticação (empresa × administrador) e a regra de login único por vez | 1 | 1 |
| `110.01` | URL pública por vaga (link curto /vaga/ID) | 1 | 6 |

```bash
git checkout develop && git pull
git checkout -b feature/architecture
```

### Passo 1 — Estrutura de pastas

Dentro do repositório (já criado na Preparação):

```bash
mkdir -p app Models Controllers Views/layouts Views/partials database/migrations database/seed cron scripts tests docs .github
mkdir -p public/css public/js/vendor public/uploads/banners public/uploads/logos storage/mail storage/termos
printf 'Require all denied\n' > storage/.htaccess
touch storage/mail/.gitkeep storage/termos/.gitkeep
```

### Passo 2 — .gitignore

#### Arquivo: `.gitignore` — CRIAR

```ini
.env
storage/database.sqlite
storage/mail/*
storage/termos/*
public/uploads/banners/*
!public/uploads/banners/vaga-0*.jpg
```

### Passo 3 — .env.example

#### Arquivo: `.env.example` — CRIAR
Depois: `cp .env.example .env` e edite `ADMIN_EMAIL` e `ADMIN_PASSWORD` (8+ caracteres, letras e números).

```ini
# Copie para .env e ajuste. Nunca versione o .env.
APP_URL=http://localhost:8000
# Site em subpasta? (usado só por cron/linha de comando para montar links nos e-mails). Ex.: /estagio  | vazio = raiz
APP_BASE_PATH=
# 1 = links curtos /vaga/123 (usados no QR Code do modo TV). Exige o .htaccess (Apache) ou o router.php em desenvolvimento.
PRETTY_URLS=0
# Expiração de sessão por inatividade, em segundos (padrão: admin 1800, empresa 7200)
SESSION_IDLE_ADMIN=1800
SESSION_IDLE_EMPRESA=7200

# Banco: sqlite (dev) | mysql (cPanel / MariaDB) | pgsql
DB_DRIVER=sqlite
DB_HOST=localhost
DB_PORT=3306
DB_NAME=vagas
DB_USER=
DB_PASS=

# ===== E-mail =====
# MAIL_DRIVER=log  -> NÃO envia nada: só grava em storage/mail/*.html (bom só para ver o layout)
# MAIL_DRIVER=smtp -> envio de verdade (recomendado, também no seu computador)
# MAIL_DRIVER=mail -> função mail() do PHP (só funciona em hospedagem com envio local, como muitos cPanel; NÃO funciona no XAMPP)
MAIL_DRIVER=log
# Remetente ÚNICO de todas as mensagens (admins e empresas recebem deste endereço).
# Em SMTP, normalmente MAIL_FROM = SMTP_USER; peça ao TI as credenciais dessa conta.
MAIL_FROM=email-automation@ifsp.edu.br
MAIL_FROM_NAME=Vagas de Estágio - IFSP Guarulhos
# Opcional: para onde vão as respostas (a caixa de automação ninguém lê)
MAIL_REPLY_TO=
SMTP_HOST=
SMTP_PORT=587
# tls = porta 587 | ssl = porta 465 | none = sem criptografia (porta 25, só testes)
SMTP_SECURE=tls
SMTP_USER=email-automation@ifsp.edu.br
SMTP_PASS=
# Se der erro de certificado SSL no XAMPP/Windows, use 0 só para testar localmente
SMTP_VERIFY_TLS=1
# Exemplos:
#  Gmail (senha de app, exige verificação em 2 etapas): SMTP_HOST=smtp.gmail.com SMTP_PORT=587 SMTP_SECURE=tls SMTP_USER=voce@gmail.com SMTP_PASS=<senha de app de 16 letras> MAIL_FROM=voce@gmail.com
#  cPanel (e-mail do domínio): SMTP_HOST=mail.seudominio.com.br SMTP_PORT=465 SMTP_SECURE=ssl SMTP_USER=vagas@seudominio.com.br SMTP_PASS=... MAIL_FROM=vagas@seudominio.com.br
#  Mailtrap (caixa de teste, não entrega de verdade): SMTP_HOST=sandbox.smtp.mailtrap.io SMTP_PORT=2525 SMTP_SECURE=tls

# Administrador inicial (php database/seed.php) e quem recebe os avisos de novas empresas/vagas
ADMIN_EMAIL=coordenacao@seudominio.edu.br
ADMIN_PASSWORD=TroqueEstaSenha123
ADMIN_NOTIFY_EMAIL=coordenacao@seudominio.edu.br
```

### Passo 4 — .htaccess

#### Arquivo: `.htaccess` — CRIAR

```ini
# Apache/cPanel: bloqueia código, banco, segredos e arquivos privados
Options -Indexes
DirectoryIndex index.php
<IfModule mod_rewrite.c>
  RewriteEngine On
  # URL curta da vaga (usada no QR Code do modo TV quando PRETTY_URLS=1)
  RewriteRule ^vaga/([0-9]+)/?$ index.php?p=vaga&id=$1 [L,QSA]
  RewriteRule ^(app|Controllers|Models|Views|database|cron|storage|tests|docs)(/|$) - [F,L]
</IfModule>
<FilesMatch "^(\.env.*|\.git.*|.*\.sqlite|.*\.sql|README\.md|router\.php)$">
  Require all denied
</FilesMatch>
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
</IfModule>
```

### Passo 5 — .htaccess

#### Arquivo: `public/uploads/.htaccess` — CRIAR
Copie também para a pasta de logos: `cp public/uploads/.htaccess public/uploads/logos/.htaccess` e `cp public/uploads/.htaccess public/uploads/banners/.htaccess`.

```ini
# Uploads públicos (banners): nunca executar código aqui
<FilesMatch "\.(php|phtml|phar|php[0-9])$">
  Require all denied
</FilesMatch>
<IfModule mod_php.c>
  php_flag engine off
</IfModule>
```

### Passo 6 — config.php

#### Arquivo: `config.php` — CRIAR
Constantes de negócio, helpers (`url`, `e`, `render`, `url_vaga`), ícones e autoload. Todo o time usa este arquivo: depois desta branch, só altere com aviso.

```php
<?php
// ---------- Ambiente (.env) ----------
define('ROOT_PATH', __DIR__);
(function () {
    $f = ROOT_PATH . '/.env';
    if (!is_file($f)) return;
    foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        if ($l[0] === '#' || strpos($l, '=') === false) continue;
        [$k, $v] = explode('=', $l, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
})();
/** Variável de ambiente real tem prioridade sobre o arquivo .env (assim testes e servidores podem sobrescrever sem editar o .env). */
function env(string $k, $padrao = null)
{
    $v = getenv($k);
    if ($v !== false && $v !== '') return $v;
    return $_ENV[$k] ?? $padrao;
}

// URL base detectada automaticamente (raiz ou subpasta: XAMPP/WAMP/cPanel)
// Em linha de comando (cron, testes) não há URL: usa APP_BASE_PATH (ex.: /estagio se o site estiver em subpasta; vazio = raiz)
$dir = PHP_SAPI === 'cli' ? rtrim((string) env('APP_BASE_PATH', ''), '/') : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
define('BASE_URL', $dir . '/');

// Regras de negócio (ajuste aqui)
define('CARROSSEL_MAX', 5);       // vagas no banner grande de destaques
define('DIAS_VAGA_NOVA', 7);      // selo "Nova"
define('DIAS_VAGA_VENCENDO', 7);  // selo "Encerra em X dias"
define('AREA_MIN_VAGAS', 3);      // área criada por empresa só vira filtro se tiver ao menos N vagas; senão entra em "Outras"
define('DIAS_RECRUTADOR_ATIVO', 7); // empresa que acessou o painel nos últimos N dias
define('UPLOAD_MAX_MB', 5);
define('TIPOS_VAGA', ['Estágio', 'Trainee', 'Jovem Aprendiz', 'Jovem Profissional']);
define('MODALIDADES', ['Presencial', 'Híbrido', 'Remoto']);
define('AREA_OUTRA', '__outra__');
define('SEGMENTOS', ['Tecnologia da Informação', 'Indústria', 'Comércio', 'Serviços', 'Educação', 'Saúde', 'Logística e Transporte', 'Financeiro', 'Marketing e Comunicação', 'Construção', 'Alimentação', 'Agronegócio', 'Outros']);
define('PORTES', ['MEI', 'Microempresa', 'Pequena empresa', 'Média empresa', 'Grande empresa']);
define('TURNOS', ['manha' => 'Manhã', 'tarde' => 'Tarde', 'noite' => 'Noite', 'integral' => 'Integral', 'flexivel' => 'Horário flexível']);
define('DIAS_SEMANA', ['seg' => 'Seg', 'ter' => 'Ter', 'qua' => 'Qua', 'qui' => 'Qui', 'sex' => 'Sex', 'sab' => 'Sáb']);
define('POR_PAGINA', 9);
define('UFS', ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO']);

date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

spl_autoload_register(function ($c) {
    foreach (['app', 'Models', 'Controllers'] as $d) {
        $f = ROOT_PATH . "/$d/$c.php";
        if (is_file($f)) { require_once $f; return; }
    }
});

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('vagas_sid');
    session_set_cookie_params(['lifetime' => 0, 'path' => BASE_URL, 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---------- Helpers ----------
function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function url(string $pagina = 'home', array $params = []): string
{
    $params = array_merge(['p' => $pagina], $params);
    if ($pagina === 'home') { unset($params['p']); }
    return BASE_URL . ($params ? '?' . http_build_query($params) : '');
}

/** URL absoluta (e-mails). Defina APP_URL no .env em produção. */
function url_abs(string $pagina = 'home', array $params = []): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    if (!$base) {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $base = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . url($pagina, $params);
}

function redirect(string $pagina, array $params = []): void { header('Location: ' . url($pagina, $params)); exit; }

function data_br(?string $iso): string { return $iso ? date('d/m/Y', strtotime($iso)) : ''; }

function dias_restantes(string $limite): int
{
    return (int) floor((strtotime($limite . ' 23:59:59') - time()) / 86400);
}

function dinheiro($v): string { return $v === null || $v === '' ? 'A combinar' : 'R$ ' . number_format((float) $v, 2, ',', '.'); }

/** "1.500,50" ou "1500" -> 1500.5 ; vazio -> null */
function parse_dinheiro(string $s): ?float
{
    $s = trim(str_replace(['R$', ' '], '', $s));
    if ($s === '') return null;
    $s = str_contains($s, ',') ? str_replace(['.', ','], ['', '.'], $s) : $s;
    return is_numeric($s) && $s >= 0 ? round((float) $s, 2) : null;
}

/** Telefone só com números -> "11 90000-0000" (sem parênteses). */
function fone_fmt(string $d): string
{
    $d = preg_replace('/\D/', '', $d);
    if (strlen($d) === 11) return substr($d, 0, 2) . ' ' . substr($d, 2, 5) . '-' . substr($d, 7);
    if (strlen($d) === 10) return substr($d, 0, 2) . ' ' . substr($d, 2, 4) . '-' . substr($d, 6);
    return $d;
}

/** Breve descrição = 2 ou 3 primeiras linhas da descrição completa (limitada a ~220 caracteres). */
function resumo_da_descricao(string $desc, int $linhas = 3, int $max = 220): string
{
    $partes = array_values(array_filter(array_map('trim', preg_split('/\R/u', $desc)), 'strlen'));
    $txt = implode(' ', array_slice($partes, 0, $linhas));
    if (mb_strlen($txt) <= $max) return $txt;
    $corte = mb_substr($txt, 0, $max);
    $esp = mb_strrpos($corte, ' ');
    return rtrim(mb_substr($corte, 0, $esp ?: $max), " ,.;:") . '…';
}

function vaga_imagem(array $v, string $classe = ''): string
{
    $emp = $v['empresa']['nome'] ?? 'Empresa';
    if (!empty($v['imagem'])) {
        return '<img class="' . e($classe) . '" src="' . e(BASE_URL . $v['imagem']) . '" alt="Banner da vaga ' . e($v['titulo']) . ' - ' . e($emp) . '" loading="lazy">';
    }
    return '<div class="vg-fallback vg-fb-' . ((int) ($v['empresa_id'] ?? 0) % 6) . ' ' . e($classe) . '" role="img" aria-label="Vaga ' . e($v['titulo']) . ' - ' . e($emp) . '"><span>' . e(iniciais($emp)) . '</span><small>' . e($emp) . '</small></div>';
}

function iniciais(string $nome): string
{
    $i = '';
    foreach (array_slice(preg_split('/\s+/', trim($nome)), 0, 2) as $p) { if (preg_match('/./u', $p, $m)) $i .= mb_strtoupper($m[0]); }
    return $i;
}

function selo_prazo(array $v): array
{
    $d = dias_restantes($v['data_limite']);
    if ($d <= 0) return ['Encerra hoje', 'vg-badge-warn'];
    if ($d <= DIAS_VAGA_VENCENDO) return ['Encerra em ' . $d . ($d === 1 ? ' dia' : ' dias'), 'vg-badge-warn'];
    return ['Até ' . date('d/m', strtotime($v['data_limite'])), 'vg-badge-soft'];
}

function vaga_e_nova(array $v): bool { return (time() - strtotime($v['data_publicacao'])) <= DIAS_VAGA_NOVA * 86400; }

/** Ícones SVG embutidos (o portal do IF usa Font Awesome 3, que não tem as classes "fa fa-*"). */
function icon(string $nome, int $tam = 18): string
{
    static $p = [
        'chevron-left' => '<path d="M15 18l-6-6 6-6"/>', 'chevron-right' => '<path d="M9 18l6-6-6-6"/>',
        'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7"/>', 'arrow-left' => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'building' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M12 6h.01M16 6h.01M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/>',
        'plus' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>',
        'tv' => '<rect x="2" y="7" width="20" height="15" rx="2"/><path d="m17 2-5 5-5-5"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'money' => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'check' => '<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="m22 4-10 10-3-3"/>',
        'copy' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
        'edit' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M7 14l4-4 4 3 5-6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>', 'check-s' => '<path d="m5 12 5 5 9-10"/>', 'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.7 4H7.3a2 2 0 0 0-1.8 1.1z"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'star' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'flame' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.4-.5-2-1-3-1.1-2.1-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.2.4-2.4 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'trending' => '<path d="m22 7-8.5 8.5-5-5L2 17M16 7h6v6"/>',
    ];
    return '<svg class="vg-ico" width="' . $tam . '" height="' . $tam . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($p[$nome] ?? '') . '</svg>';
}

function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">'; }

function flash(string $tipo, string $msg): void { $_SESSION['flash'][] = [$tipo, $msg]; }

/** Página pública (usa o header/footer do IFSP dentro da própria view). */
function view(string $nome, array $dados = []): void
{
    extract($dados, EXTR_SKIP);
    require ROOT_PATH . "/Views/$nome.php";
}
/** Página independente: layout 'app' (menu lateral: admin/empresa) ou 'auth' (login/cadastro). Sem header/footer do IFSP. */
function render(string $nome, array $dados, string $layout): void
{
    extract($dados, EXTR_SKIP);
    ob_start(); require ROOT_PATH . "/Views/$nome.php"; $conteudo = ob_get_clean();
    require ROOT_PATH . "/Views/layouts/$layout.php";
}
/** URL pública da vaga (o QR Code do modo TV usa esta). PRETTY_URLS=1 exige o .htaccess (/vaga/123). */
function url_vaga(int $id): string { return env('PRETTY_URLS') && env('PRETTY_URLS') !== '0' ? BASE_URL . 'vaga/' . $id : url('vaga', ['id' => $id]); }
function url_vaga_abs(int $id): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    if (!$base) { $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'; $base = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'); }
    return $base . url_vaga($id);
}
/** Trim em strings; mantém arrays de strings (checkboxes). */
function limpar_post(array $p): array
{
    $o = [];
    foreach ($p as $k => $v) $o[$k] = is_array($v) ? array_values(array_filter(array_map(fn($x) => is_string($x) ? trim($x) : '', $v), 'strlen')) : (is_string($v) ? trim($v) : '');
    return $o;
}
function dias_txt(int $d): string { return $d < 0 ? 'Encerrada há ' . abs($d) . ($d === -1 ? ' dia' : ' dias') : ($d === 0 ? 'Último dia' : $d . ($d === 1 ? ' dia restante' : ' dias restantes')); }
```

### Passo 7 — index.php

#### Arquivo: `index.php` — CRIAR
Tabela de rotas. As rotas das telas aparecem aqui desde já; cada controller/tela chega na sua branch.

```php
<?php
require_once __DIR__ . '/config.php';

Auth::normalizar();
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// ?p=<rota> => [Controller, método]
$rotas = [
    // público (estudante)
    'home'              => ['VagaController',          'index'],
    'vaga'              => ['VagaController',          'mostrar'],
    'encerradas'        => ['VagaController',          'encerradas'],
    'tv'                => ['VagaController',          'tv'],
    'empresas'          => ['ParceirasController',     'index'],
    'empresa'           => ['ParceirasController',     'perfil'],
    'externo'           => ['ParceirasController',     'externo'],
    // autenticação e cadastro (layout independente)
    'empresa-login'     => ['AuthController',          'empresaLogin'],
    'admin-login'       => ['AuthController',          'adminLogin'],
    'sair'              => ['AuthController',          'sair'],
    'empresa-cadastro'  => ['EmpresaController',       'cadastro'],
    'empresa-concluido' => ['EmpresaController',       'concluido'],
    // área da empresa
    'painel'            => ['EmpresaAreaController',   'painel'],
    'empresa-vagas'     => ['EmpresaAreaController',   'vagas'],
    'empresa-vaga'      => ['EmpresaAreaController',   'vaga'],
    'empresa-perfil'    => ['EmpresaAreaController',   'perfil'],
    'vaga-cadastro'     => ['EmpresaAreaController',   'formVaga'],
    'vaga-editar'       => ['EmpresaAreaController',   'formVaga'],
    'vaga-encerrar'     => ['EmpresaAreaController',   'encerrar'],
    'aviso-dispensar'   => ['EmpresaAreaController',   'dispensarAviso'],
    // administração
    'admin'             => ['AdminController',         'painel'],
    'admin-vagas'       => ['AdminController',         'vagas'],
    'admin-vaga'        => ['AdminController',         'vaga'],
    'admin-empresas'    => ['AdminController',         'empresas'],
    'admin-empresa'     => ['AdminController',         'empresa'],
    'admin-aprovacoes'  => ['AdminController',         'aprovacoes'],
    'admin-usuarios'    => ['AdminController',         'usuarios'],
    'admin-acesso'      => ['AdminController',         'acesso'],
    'admin-relatorios'  => ['AdminController',         'relatorios'],
    'admin-config'      => ['AdminController',         'config'],
    'admin-acao'        => ['AdminController',         'acao'],
    'admin-termo'       => ['AdminController',         'termo'],
    'admin-exportar'    => ['AdminController',         'exportar'],
];

$pagina = $_GET['p'] ?? 'home';
if (isset($_GET['id']) && !is_scalar($_GET['id'])) { unset($_GET['id']); }
if (!is_string($pagina) || !isset($rotas[$pagina])) { http_response_code(404); $pagina = 'home'; }

try {
    Bootstrap::garantir();
    [$classe, $metodo] = $rotas[$pagina];
    (new $classe())->$metodo();
} catch (Throwable $t) {
    error_log($t);
    @file_put_contents(ROOT_PATH . '/storage/erro.log', date('c') . ' ' . $t . "\n\n", FILE_APPEND);
    http_response_code(500);
    $dica = Bootstrap::dica($t);
    echo env('APP_DEBUG') ? '<pre>' . e((string) $t) . '</pre>' : 'Erro interno. ' . ($dica ?: 'Detalhes em storage/erro.log.');
}
```

### Passo 8 — router.php

#### Arquivo: `router.php` — CRIAR

```php
<?php
// Só para desenvolvimento:  php -S localhost:8000 router.php   (em produção o .htaccess faz esse papel)
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(app|Controllers|Models|Views|database|cron|storage|tests|docs)(/|$)|^/\.env|/router\.php$#', $p)) { http_response_code(403); exit('Acesso negado'); }
if (preg_match('#^/vaga/(\d+)/?$#', $p, $m)) { $_GET['p'] = 'vaga'; $_GET['id'] = $m[1]; require __DIR__ . '/index.php'; return; }
if ($p !== '/' && is_file(__DIR__ . $p) && !preg_match('/\.php$/', $p)) return false;
require __DIR__ . '/index.php';
```

### Passo 9 — git-setup.sh

#### Arquivo: `scripts/git-setup.sh` — CRIAR

```bash
#!/usr/bin/env bash
# Cria a estrutura de branches do projeto. Uso: scripts/git-setup.sh   (dentro do repositório já iniciado)
set -e
git rev-parse --git-dir >/dev/null 2>&1 || { git init -b main && git add -A && git commit -m "chore: versão inicial do ESTAGIA+"; }
git branch -M main
git branch develop 2>/dev/null || true
for b in architecture design-system database-company backend-auth backend-jobs frontend-company frontend-admin frontend-jobs tv-mode security qa; do
  git branch "feature/$b" develop 2>/dev/null || true
done
echo "Branches criadas:"; git branch --list
echo "Proteja main e develop no GitHub/GitLab (PR + 1 aprovação + testes)."
```

### Passo 10 — pull_request_template.md

#### Arquivo: `.github/pull_request_template.md` — CRIAR

```markdown
## O que muda
<!-- 1–3 linhas. Cite o código da tarefa da planilha (ex.: 108.06). -->

## Tarefa / História
- Tarefa: `NNN.NN`   - Branch: `feature/...`   - Depende de: #

## Checklist
- [ ] `tests/run.sh` verde (e `tests/run.sh mysql` se mexeu em SQL)
- [ ] Autorização conferida **no backend** (não só ocultei o botão)
- [ ] Recursos por ID conferem a empresa dona (ownership)
- [ ] Mudou banco? Criei migration + atualizei `schema_sql()` + regenerei `mysql.sql`/`postgres.sql`
- [ ] Mudou rota/campo? Atualizei `docs/ROTAS_E_CONTRATOS.md`
- [ ] Sem cor fixa nos componentes (modo noturno) e sem emojis
- [ ] Sem `.env`, `storage/*` ou uploads no commit
```

### Passo 11 — Verificar a branch

```bash
php -l config.php && php -l index.php && php -l router.php
```

### Passo 12 — Fechar a branch

```bash
git add .
git commit -m "feat(arquitetura): esqueleto do projeto, config, roteador e padrões da equipe"
git push -u origin feature/architecture
```
Abra o Pull Request `feature/architecture` → `develop` e peça revisão a **Rodrigo**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 2: `feature/security` — dono: Pedro Miguel

Peças de segurança reutilizadas por todos: CSRF, upload seguro e validação de links externos.

**Revisor do PR:** Pedro Henri. **Depende de:** `feature/architecture`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `109.01` | Upload seguro (tipo real, tamanho, nome aleatório, destino fora da execução) | 2 | 2 |
| `108.06` | Validação de links externos (http/https, domínio por plataforma, sem credenciais) | 1 | 5 |

```bash
git checkout develop && git pull
git checkout -b feature/security
```

### Passo 13 — Csrf.php

#### Arquivo: `app/Csrf.php` — CRIAR

```php
<?php
class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }
    /** Exige token válido em todo POST. */
    public static function exigir(): void
    {
        $t = $_POST['_csrf'] ?? '';
        if (!is_string($t) || !hash_equals(self::token(), $t)) {
            http_response_code(419);
            exit('Sessão expirada. Volte, recarregue a página e tente novamente.');
        }
    }
}
```

### Passo 14 — Upload.php

#### Arquivo: `app/Upload.php` — CRIAR
Valida o tipo REAL do arquivo (finfo), o tamanho e usa nome aleatório.

```php
<?php
/** Upload seguro: confere o tipo real do arquivo (não a extensão), limita tamanho e gera nome aleatório. */
class Upload
{
    private const EXT = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

    /** @return array [nomeSalvo|null, erro|null] */
    public static function salvar(?array $f, array $mimes, string $destino, bool $obrigatorio, ?int $maxMb = null): array
    {
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, $obrigatorio ? 'Anexe um arquivo.' : null];
        }
        if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Falha no envio do arquivo. Tente novamente.'];
        $lim = $maxMb ?? UPLOAD_MAX_MB;
        if ($f['size'] > $lim * 1048576) return [null, 'Arquivo maior que ' . $lim . ' MB.'];
        if (!is_uploaded_file($f['tmp_name']) && !(defined('TESTING') && TESTING)) return [null, 'Arquivo inválido.'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!in_array($mime, $mimes, true) || !isset(self::EXT[$mime])) {
            return [null, 'Formato não permitido. Envie ' . implode(', ', array_map(fn($m) => strtoupper(self::EXT[$m]), $mimes)) . '.'];
        }
        if (!is_dir($destino)) mkdir($destino, 0755, true);
        $nome = bin2hex(random_bytes(12)) . '.' . self::EXT[$mime];
        $ok = defined('TESTING') && TESTING ? copy($f['tmp_name'], "$destino/$nome") : move_uploaded_file($f['tmp_name'], "$destino/$nome");
        return $ok ? [$nome, null] : [null, 'Não foi possível salvar o arquivo.'];
    }
}
```

### Passo 15 — Link.php

#### Arquivo: `app/Link.php` — CRIAR

```php
<?php
/** Links externos: validação mínima e segura (só http/https, host com ponto, sem credenciais), checagem por plataforma. */
class Link
{
    public const PLATAFORMAS = ['gupy' => 'Gupy', 'linkedin' => 'LinkedIn', 'site' => 'Site da empresa', 'email' => 'E-mail', 'outro' => 'Outro sistema'];

    /** "empresa.com.br" -> "https://empresa.com.br"; vazio -> null. */
    public static function normalizar(?string $u): ?string
    {
        $u = trim((string) $u);
        if ($u === '') return null;
        if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $u)) $u = 'https://' . $u;
        return $u;
    }
    public static function valido(?string $u, ?string $plataforma = null): bool
    {
        if ($u === null || $u === '' || strlen($u) > 250 || preg_match('/[\s<>"\']/', $u)) return false;
        $p = parse_url($u);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true) || empty($p['host']) || isset($p['user']) || isset($p['pass'])) return false;
        $h = strtolower($p['host']);
        if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $h)) return false;
        if ($plataforma === 'gupy') return (bool) preg_match('/(^|\.)gupy\.io$/', $h);
        if ($plataforma === 'linkedin') return (bool) preg_match('/(^|\.)linkedin\.com$/', $h);
        return true;
    }
    public static function dominio(string $u): string { return strtolower((string) parse_url($u, PHP_URL_HOST)); }
}
```

### Passo 16 — Verificar a branch

```bash
php -r 'require "config.php"; var_dump(Link::valido("https://empresa.gupy.io/jobs/1","gupy"), Link::valido("https://evil.com/gupy.io","gupy"), Link::valido("javascript:alert(1)"));'
```

Resultado esperado:
- `php -r 'require "config.php"; var_dump(Link::valido("https://empresa.g…` → bool(true) bool(false) bool(false)

### Passo 17 — Fechar a branch

```bash
git add .
git commit -m "feat(seguranca): CSRF, upload validado pelo conteúdo e validação de links externos"
git push -u origin feature/security
```
Abra o Pull Request `feature/security` → `develop` e peça revisão a **Pedro Henri**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 3: `feature/database-company` — dono: Rodrigo

Banco de dados: conexão, esquema final, migrations versionadas com rollback e geração dos SQL de instalação.

**Revisor do PR:** José Roberto. **Depende de:** `feature/architecture`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `105.17` | Revisar o banco atual (tabelas, chaves, índices, status) e registrar inconsistências | 2 | 1 |
| `105.18` | Sistema de migrations versionadas com rollback | 3 | 1 |
| `105.19` | Migration do refinamento (perfil da empresa, vaga estruturada, histórico, acessos, administradores) | 3 | 2 |
| `105.21` | Suporte a MySQL/MariaDB, PostgreSQL e SQLite e geração dos SQL de instalação | 2 | 6 |

```bash
git checkout develop && git pull
git checkout -b feature/database-company
```

### Passo 18 — Database.php

#### Arquivo: `app/Database.php` — CRIAR

```php
<?php
/** Conexão PDO. Suporta sqlite (desenvolvimento), mysql/mariadb (cPanel) e pgsql, só mudando o .env. */
class Database
{
    private static ?PDO $pdo = null;

    public static function driver(): string { return strtolower((string) env('DB_DRIVER', 'sqlite')); }

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $d = self::driver();
        $host = env('DB_HOST', 'localhost'); $nome = env('DB_NAME', 'vagas');
        if ($d === 'mysql') {
            $dsn = "mysql:host=$host;port=" . env('DB_PORT', 3306) . ";dbname=$nome;charset=utf8mb4";
        } elseif ($d === 'pgsql') {
            $dsn = "pgsql:host=$host;port=" . env('DB_PORT', 5432) . ";dbname=$nome";
        } else {
            $arq = env('DB_SQLITE', ROOT_PATH . '/storage/database.sqlite');
            $dsn = 'sqlite:' . $arq;
        }
        self::$pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // MySQL nativo não aceita o mesmo :parametro repetido na consulta; com emulação ligada aceita.
            PDO::ATTR_EMULATE_PREPARES => $d === 'mysql',
        ]);
        if ($d === 'sqlite') self::$pdo->exec('PRAGMA foreign_keys = ON');
        return self::$pdo;
    }

    public static function reset(): void { self::$pdo = null; }

    public static function run(string $sql, array $p = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql); $st->execute($p); return $st;
    }
    public static function all(string $sql, array $p = []): array { return self::run($sql, $p)->fetchAll(); }
    public static function one(string $sql, array $p = []): ?array { $r = self::run($sql, $p)->fetch(); return $r ?: null; }
    public static function val(string $sql, array $p = []) { $r = self::run($sql, $p)->fetchColumn(); return $r === false ? null : $r; }

    /** INSERT que devolve o id gerado em qualquer driver. */
    public static function insert(string $tabela, array $dados): int
    {
        $cols = array_keys($dados);
        $sql = "INSERT INTO $tabela (" . implode(',', $cols) . ') VALUES (:' . implode(',:', $cols) . ')';
        if (self::driver() === 'pgsql') {
            return (int) self::val($sql . ' RETURNING id', $dados);
        }
        self::run($sql, $dados);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $tabela, int $id, array $dados): void
    {
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($dados)));
        self::run("UPDATE $tabela SET $set WHERE id = :_id", $dados + ['_id' => $id]);
    }

    public static function agora(): string { return date('Y-m-d H:i:s'); }
}
```

### Passo 19 — schema.php

#### Arquivo: `database/schema.php` — CRIAR
Esquema FINAL (instalação nova) + executor de migrations.

```php
<?php
/**
 * Esquema FINAL (instalação nova) + executor de migrations (atualização de bancos existentes).
 *  - Banco vazio  : cria o esquema final e marca todas as migrations como aplicadas.
 *  - Banco antigo : aplica só as migrations pendentes, em ordem (database/migrations/NNN_nome.php).
 *  php database/migrate.php            aplica pendentes
 *  php database/migrate.php status     lista
 *  php database/migrate.php down       desfaz a última (quando a migration tem rollback)
 */
function schema_sql(string $driver): array
{
    $pk  = ['sqlite' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'mysql' => 'INT AUTO_INCREMENT PRIMARY KEY', 'pgsql' => 'SERIAL PRIMARY KEY'][$driver];
    $dt  = $driver === 'pgsql' ? 'TIMESTAMP' : 'DATETIME';
    $fim = $driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $t = [];
    $t[] = "CREATE TABLE IF NOT EXISTS migracoes (id $pk, nome VARCHAR(80) NOT NULL UNIQUE, aplicada_em $dt NOT NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS admins (
        id $pk, nome VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL UNIQUE, senha_hash VARCHAR(255) NOT NULL,
        papel VARCHAR(20) NOT NULL DEFAULT 'admin', ativo SMALLINT NOT NULL DEFAULT 1,
        criado_em $dt NOT NULL, ultimo_acesso $dt NULL)$fim";
    $t[] = "CREATE TABLE IF NOT EXISTS empresas (
        id $pk, cnpj VARCHAR(14) NOT NULL UNIQUE, razao_social VARCHAR(150) NULL, nome VARCHAR(120) NOT NULL,
        descricao TEXT NULL, segmento VARCHAR(60) NULL, porte VARCHAR(20) NULL,
        endereco VARCHAR(200) NOT NULL,
        cep VARCHAR(8) NULL, logradouro VARCHAR(120) NULL, numero VARCHAR(10) NULL, complemento VARCHAR(60) NULL, bairro VARCHAR(60) NULL, cidade VARCHAR(60) NULL, uf VARCHAR(2) NULL,
        responsavel VARCHAR(100) NOT NULL, cargo VARCHAR(60) NULL, telefone VARCHAR(20) NOT NULL, celular VARCHAR(20) NULL,
        email VARCHAR(120) NOT NULL UNIQUE, email_contato VARCHAR(120) NULL,
        site VARCHAR(200) NULL, linkedin VARCHAR(200) NULL, link_principal VARCHAR(10) NOT NULL DEFAULT 'site', logo VARCHAR(200) NULL,
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
    $t[] = "CREATE TABLE IF NOT EXISTS login_tentativas (id $pk, chave VARCHAR(64) NOT NULL, criado_em $dt NOT NULL)$fim";
    foreach ([
        'idx_vagas_status ON vagas (status, data_limite)', 'idx_vagas_empresa ON vagas (empresa_id)', 'idx_login_chave ON login_tentativas (chave)',
        'idx_hist_ent ON historico (entidade, entidade_id)', 'idx_acessos_data ON acessos_log (criado_em)', 'idx_empresas_status ON empresas (status)',
    ] as $ix) $t[] = "CREATE INDEX $ix";
    return $t;
}

const AREAS_PADRAO = ['Administração', 'Engenharia', 'Logística', 'Marketing', 'Qualidade', 'Tecnologia da Informação', 'Recursos Humanos', 'Financeiro', 'Comercial', 'Saúde', 'Educação', 'Jurídico'];

function tabela_existe(string $t): bool
{
    try { Database::pdo()->query("SELECT 1 FROM $t WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
}
function coluna_existe(string $t, string $c): bool
{
    try { Database::pdo()->query("SELECT $c FROM $t WHERE 1 = 0"); return true; } catch (PDOException $e) { return false; }
}
function add_coluna(string $t, string $c, string $def): void
{
    if (!coluna_existe($t, $c)) Database::pdo()->exec("ALTER TABLE $t ADD COLUMN $c $def");
}

function migrations(): array
{
    $l = [];
    foreach (glob(__DIR__ . '/migrations/*.php') as $f) { $m = require $f; $m['nome'] = basename($f, '.php'); $l[] = $m; }
    usort($l, fn($a, $b) => strcmp($a['nome'], $b['nome']));
    return $l;
}

function migrar(): array
{
    $pdo = Database::pdo(); $driver = Database::driver(); $feitas = [];
    $novo = !tabela_existe('empresas');
    foreach (schema_sql($driver) as $sql) {
        try { $pdo->exec($sql); } catch (PDOException $e) { if (stripos($sql, 'CREATE INDEX') !== 0) throw $e; }
    }
    $aplicadas = tabela_existe('migracoes') ? array_column(Database::all('SELECT nome FROM migracoes'), 'nome') : [];
    foreach (migrations() as $m) {
        if (in_array($m['nome'], $aplicadas, true)) continue;
        if (!$novo) { ($m['up'])($pdo, $driver); $feitas[] = $m['nome']; }   // banco antigo: executa a migration
        Database::insert('migracoes', ['nome' => $m['nome'], 'aplicada_em' => Database::agora()]);   // banco novo: esquema final já contempla
    }
    // índices que dependem de colunas criadas por migrations
    foreach (schema_sql($driver) as $sql) {
        if (stripos($sql, 'CREATE INDEX') === 0) { try { $pdo->exec($sql); } catch (PDOException $e) { /* já existe */ } }
    }
    foreach (AREAS_PADRAO as $a) {
        if (!Database::val('SELECT 1 FROM areas WHERE nome = :n', ['n' => $a])) Database::insert('areas', ['nome' => $a, 'padrao' => 1]);
    }
    return $feitas;
}

function desfazer_ultima(): ?string
{
    $u = Database::one('SELECT * FROM migracoes ORDER BY id DESC LIMIT 1');
    if (!$u) return null;
    foreach (migrations() as $m) {
        if ($m['nome'] !== $u['nome']) continue;
        if (empty($m['down'])) throw new RuntimeException("A migration {$m['nome']} não possui rollback.");
        ($m['down'])(Database::pdo(), Database::driver());
        Database::run('DELETE FROM migracoes WHERE id = :i', ['i' => $u['id']]);
        return $m['nome'];
    }
    return null;
}
```

### Passo 20 — 001_endereco_estruturado.php

#### Arquivo: `database/migrations/001_endereco_estruturado.php` — CRIAR

```php
<?php
// Endereço da empresa em campos separados (CEP, rua, número...). Bancos criados antes desta versão recebem as colunas.
return [
    'up' => function (PDO $pdo, string $driver) {
        foreach (['cep' => 8, 'logradouro' => 120, 'numero' => 10, 'complemento' => 60, 'bairro' => 60, 'cidade' => 60, 'uf' => 2] as $c => $n) add_coluna('empresas', $c, "VARCHAR($n) NULL");
    },
    'down' => function (PDO $pdo, string $driver) {
        foreach (['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'] as $c) $pdo->exec("ALTER TABLE empresas DROP COLUMN $c");
    },
];
```

### Passo 21 — 002_refinamento_empresas_vagas.php

#### Arquivo: `database/migrations/002_refinamento_empresas_vagas.php` — CRIAR

```php
<?php
// Perfil completo da empresa, estados de vaga coerentes, vaga estruturada (requisitos, carga horária, links),
// histórico (auditoria), log de acessos e controle de administradores.
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
        ] as $c => $d) add_coluna('empresas', $c, $d);
        foreach ([
            'carga_horas_dia' => 'SMALLINT NULL', 'carga_dias' => 'VARCHAR(30) NULL', 'carga_turno' => 'VARCHAR(12) NULL', 'requisitos_desejaveis' => 'TEXT NULL',
            'link_site' => 'VARCHAR(200) NULL', 'link_linkedin' => 'VARCHAR(200) NULL', 'link_candidatura' => 'VARCHAR(250) NULL', 'link_plataforma' => 'VARCHAR(12) NULL',
            'versao' => 'SMALLINT NOT NULL DEFAULT 1', 'atualizado_em' => "$dt NULL",
        ] as $c => $d) add_coluna('vagas', $c, $d);
        add_coluna('admins', 'papel', "VARCHAR(20) NOT NULL DEFAULT 'admin'");
        add_coluna('admins', 'ativo', 'SMALLINT NOT NULL DEFAULT 1');
        if (!tabela_existe('historico')) $pdo->exec("CREATE TABLE historico (id $pk, entidade VARCHAR(10) NOT NULL, entidade_id INT NOT NULL, acao VARCHAR(30) NOT NULL, de VARCHAR(25) NULL, para VARCHAR(25) NULL, ator_tipo VARCHAR(10) NOT NULL, ator_id INT NULL, detalhe VARCHAR(300) NULL, criado_em $dt NOT NULL)$fim");
        if (!tabela_existe('acessos_log')) $pdo->exec("CREATE TABLE acessos_log (id $pk, perfil VARCHAR(10) NOT NULL, usuario_id INT NULL, email VARCHAR(120) NULL, evento VARCHAR(15) NOT NULL, ip VARCHAR(45) NULL, agente VARCHAR(160) NULL, criado_em $dt NOT NULL)$fim");
        // nomes de estado coerentes
        $pdo->exec("UPDATE vagas SET status = 'aguardando_aprovacao' WHERE status = 'pendente'");
        $pdo->exec("UPDATE vagas SET status = 'rejeitada' WHERE status = 'reprovada'");
        // empresas que já estavam aprovadas não precisam ver o aviso "empresa aprovada"
        $pdo->exec("UPDATE empresas SET aprovacao_vista = 1 WHERE status = 'aprovada'");
        $pdo->exec("UPDATE empresas SET razao_social = nome WHERE razao_social IS NULL");
    },
    'down' => function (PDO $pdo, string $driver) {
        $pdo->exec("UPDATE vagas SET status = 'pendente' WHERE status = 'aguardando_aprovacao'");
        $pdo->exec("UPDATE vagas SET status = 'reprovada' WHERE status = 'rejeitada'");
        $pdo->exec('DROP TABLE IF EXISTS historico'); $pdo->exec('DROP TABLE IF EXISTS acessos_log');
        foreach (['papel', 'ativo'] as $c) $pdo->exec("ALTER TABLE admins DROP COLUMN $c");
        foreach (['carga_horas_dia', 'carga_dias', 'carga_turno', 'requisitos_desejaveis', 'link_site', 'link_linkedin', 'link_candidatura', 'link_plataforma', 'versao', 'atualizado_em'] as $c) $pdo->exec("ALTER TABLE vagas DROP COLUMN $c");
        foreach (['razao_social', 'descricao', 'segmento', 'porte', 'cargo', 'celular', 'email_contato', 'linkedin', 'link_principal', 'logo', 'termos_aceitos_em', 'aprovacao_vista'] as $c) $pdo->exec("ALTER TABLE empresas DROP COLUMN $c");
    },
];
```

### Passo 22 — migrate.php

#### Arquivo: `database/migrate.php` — CRIAR

```php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
$cmd = $argv[1] ?? 'up';
if ($cmd === 'status') {
    $ap = tabela_existe('migracoes') ? array_column(Database::all('SELECT nome FROM migracoes'), 'nome') : [];
    foreach (migrations() as $m) echo (in_array($m['nome'], $ap, true) ? '[x] ' : '[ ] ') . $m['nome'] . "\n";
} elseif ($cmd === 'down') {
    $n = desfazer_ultima(); echo $n ? "Desfeita: $n\n" : "Nada para desfazer.\n";
} else {
    $f = migrar(); echo $f ? "Migrations aplicadas: " . implode(', ', $f) . "\n" : "Banco atualizado (" . Database::driver() . ").\n";
}
```

### Passo 23 — gerar_sql.php

#### Arquivo: `database/gerar_sql.php` — CRIAR

```php
<?php
// php database/gerar_sql.php mysql > database/mysql.sql   (também aceita pgsql / sqlite)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
$d = $argv[1] ?? 'mysql';
echo "-- Vagas de Estágio - IFSP Guarulhos | esquema para " . strtoupper($d) . "\n-- Importe no phpMyAdmin (aba Importar) com o banco já selecionado.\n";
echo "-- O administrador é criado no primeiro acesso ao site, a partir de ADMIN_EMAIL/ADMIN_PASSWORD do .env.\n\n";
if ($d === 'mysql') echo "SET NAMES utf8mb4;\n\n";
foreach (schema_sql($d) as $sql) echo preg_replace('/\s+/', ' ', trim($sql)) . ";\n\n";
echo "-- Registra as migrations já contempladas por este esquema (assim o sistema não tenta reaplicá-las)\nINSERT INTO migracoes (nome, aplicada_em) VALUES\n";
$ms = array_map(fn($f) => basename($f, '.php'), glob(__DIR__ . '/migrations/*.php')); sort($ms);
echo implode(",\n", array_map(fn($n) => "  ('$n', " . ($d === 'sqlite' ? "datetime('now')" : 'CURRENT_TIMESTAMP') . ")", $ms)) . ";\n\n";
echo "INSERT INTO areas (nome, padrao) VALUES\n";
$a = AREAS_PADRAO;
echo implode(",\n", array_map(fn($n) => "  ('" . str_replace("'", "''", $n) . "', 1)", $a)) . ";\n";
```

### Passo 24 — Arquivo `.env`

Se ainda não fez: `cp .env.example .env` (mantém `DB_DRIVER=sqlite` para desenvolver sem instalar banco).

### Passo 25 — Verificar a branch

```bash
php database/migrate.php
php database/migrate.php status
php database/migrate.php down && php database/migrate.php
php database/gerar_sql.php mysql > database/mysql.sql && php database/gerar_sql.php pgsql > database/postgres.sql
```

Resultado esperado:
- `php database/migrate.php…` → Banco atualizado (sqlite).
- `php database/migrate.php status…` → [x] 001... [x] 002...
- `php database/migrate.php down && php database/migrate.php…` → Desfeita: 002... depois Migrations aplicadas: 002...
- `php database/gerar_sql.php mysql > database/mysql.sql && php database/…` → gera os SQL para importar no phpMyAdmin

### Passo 26 — Fechar a branch

```bash
git add .
git commit -m "feat(banco): esquema, migrations com rollback e suporte a MySQL, PostgreSQL e SQLite"
git push -u origin feature/database-company
```
Abra o Pull Request `feature/database-company` → `develop` e peça revisão a **José Roberto**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 4: `feature/backend-core` — dono: José Roberto

Regras centrais: máquina de estados, auditoria, CNPJ alfanumérico e paginação.

**Revisor do PR:** Pedro Miguel. **Depende de:** `feature/architecture`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `111.01` | Modelar a máquina de estados (persistidos × situação calculada) de vaga e empresa | 3 | 1 |
| `105.20` | Tabela de auditoria (historico) e registro das mudanças de estado | 2 | 5 |

```bash
git checkout develop && git pull
git checkout -b feature/backend-core
```

### Passo 27 — Estado.php

#### Arquivo: `app/Estado.php` — CRIAR
A regra mais importante do sistema: estados persistidos, transições e situação calculada pelas datas.

```php
<?php
/**
 * Máquina de estados (única fonte de verdade). Estados PERSISTIDOS e transições permitidas:
 *
 * VAGA     aguardando_aprovacao -> aprovada | rejeitada
 *          aprovada             -> encerrada | aguardando_aprovacao (empresa editou: reaprovação)
 *          rejeitada            -> aguardando_aprovacao (empresa corrigiu e reenviou)
 *          encerrada            -> (final)
 * EMPRESA  pendente -> aprovada | reprovada ; aprovada <-> bloqueada ; reprovada -> pendente (reenvio)
 *
 * SITUAÇÃO da vaga (calculada pelas datas, no backend/SQL; nunca pelo JavaScript):
 *   aguardando | rejeitada | encerrada | agendada | publicada | expirada
 * "aprovada" vira agendada (antes da data inicial), publicada (dentro do período) ou expirada (depois da data final).
 */
class Estado
{
    public const V_AGUARDANDO = 'aguardando_aprovacao', V_APROVADA = 'aprovada', V_REJEITADA = 'rejeitada', V_ENCERRADA = 'encerrada';
    public const E_PENDENTE = 'pendente', E_APROVADA = 'aprovada', E_REPROVADA = 'reprovada', E_BLOQUEADA = 'bloqueada';

    private const TV = [
        self::V_AGUARDANDO => [self::V_APROVADA, self::V_REJEITADA],
        self::V_APROVADA   => [self::V_ENCERRADA, self::V_AGUARDANDO],
        self::V_REJEITADA  => [self::V_AGUARDANDO],
        self::V_ENCERRADA  => [],
    ];
    private const TE = [
        self::E_PENDENTE  => [self::E_APROVADA, self::E_REPROVADA],
        self::E_APROVADA  => [self::E_BLOQUEADA],
        self::E_BLOQUEADA => [self::E_APROVADA],
        self::E_REPROVADA => [self::E_PENDENTE],
    ];
    public static function vagaPode(string $de, string $para): bool { return in_array($para, self::TV[$de] ?? [], true); }
    public static function empresaPode(string $de, string $para): bool { return in_array($para, self::TE[$de] ?? [], true); }

    public const SITUACOES = ['aguardando', 'agendada', 'publicada', 'expirada', 'encerrada', 'rejeitada'];
    private const ROTULO = ['aguardando' => 'Em análise', 'agendada' => 'Agendada', 'publicada' => 'Publicada', 'expirada' => 'Expirada', 'encerrada' => 'Encerrada', 'rejeitada' => 'Rejeitada'];
    private const TOM = ['aguardando' => 'warn', 'agendada' => 'info', 'publicada' => 'ok', 'expirada' => 'mute', 'encerrada' => 'mute', 'rejeitada' => 'danger'];

    public static function situacaoVaga(array $v, ?string $hoje = null): string
    {
        $hoje = $hoje ?? date('Y-m-d');
        switch ($v['status']) {
            case self::V_AGUARDANDO: return 'aguardando';
            case self::V_REJEITADA:  return 'rejeitada';
            case self::V_ENCERRADA:  return 'encerrada';
        }
        if ($hoje < $v['data_publicacao']) return 'agendada';
        if ($hoje > $v['data_limite']) return 'expirada';
        return 'publicada';
    }
    public static function rotuloSituacao(string $s): string { return self::ROTULO[$s] ?? $s; }
    public static function tomSituacao(string $s): string { return self::TOM[$s] ?? 'mute'; }

    /** Condição SQL equivalente a situacaoVaga(), para filtrar/contar no banco. @return array [sql, params] */
    public static function sqlSituacao(string $sit, string $v = 'v'): array
    {
        $h = ['hoje' => date('Y-m-d')];
        switch ($sit) {
            case 'aguardando': return ["$v.status = '" . self::V_AGUARDANDO . "'", []];
            case 'rejeitada':  return ["$v.status = '" . self::V_REJEITADA . "'", []];
            case 'encerrada':  return ["$v.status = '" . self::V_ENCERRADA . "'", []];
            case 'agendada':   return ["($v.status = '" . self::V_APROVADA . "' AND $v.data_publicacao > :hoje)", $h];
            case 'expirada':   return ["($v.status = '" . self::V_APROVADA . "' AND $v.data_limite < :hoje)", $h];
            case 'publicada':  return ["($v.status = '" . self::V_APROVADA . "' AND $v.data_publicacao <= :hoje AND $v.data_limite >= :hoje)", $h];
        }
        return ['1 = 1', []];
    }

    public const STATUS_EMPRESA = ['pendente' => ['Pendente', 'warn'], 'aprovada' => ['Aprovada', 'ok'], 'reprovada' => ['Reprovada', 'danger'], 'bloqueada' => ['Bloqueada', 'mute']];
}
```

### Passo 28 — Historico.php

#### Arquivo: `app/Historico.php` — CRIAR

```php
<?php
/** Auditoria: toda mudança de estado e ação sensível fica registrada (quem, quando, de/para, detalhe). */
class Historico
{
    public static function registrar(string $entidade, int $id, string $acao, ?string $de, ?string $para, string $atorTipo, ?int $atorId, ?string $detalhe = null): void
    {
        Database::insert('historico', ['entidade' => $entidade, 'entidade_id' => $id, 'acao' => $acao, 'de' => $de, 'para' => $para,
            'ator_tipo' => $atorTipo, 'ator_id' => $atorId, 'detalhe' => $detalhe !== null ? mb_substr($detalhe, 0, 300) : null, 'criado_em' => Database::agora()]);
    }
    public static function da(string $entidade, int $id, int $n = 30): array
    {
        return Database::all('SELECT * FROM historico WHERE entidade = :e AND entidade_id = :i ORDER BY id DESC LIMIT ' . (int) $n, ['e' => $entidade, 'i' => $id]);
    }
    public static function recentes(int $n = 10): array
    {
        return Database::all("SELECT h.*, v.titulo AS v_titulo, e.nome AS e_nome FROM historico h
            LEFT JOIN vagas v ON (h.entidade = 'vaga' AND v.id = h.entidade_id) LEFT JOIN empresas e ON (h.entidade = 'empresa' AND e.id = h.entidade_id)
            ORDER BY h.id DESC LIMIT " . (int) $n);
    }
    /** Ator atual da requisição, no formato [tipo, id]. */
    public static function ator(): array
    {
        if (!empty($_SESSION['admin_id'])) return ['admin', (int) $_SESSION['admin_id']];
        if (!empty($_SESSION['empresa_id'])) return ['empresa', (int) $_SESSION['empresa_id']];
        return ['sistema', null];
    }
    private const ROT = ['criada' => 'Enviada para análise', 'aprovada' => 'Aprovada', 'rejeitada' => 'Rejeitada', 'reprovada' => 'Reprovada', 'encerrada' => 'Encerrada',
        'editada' => 'Editada (voltou para análise)', 'reenviada' => 'Reenviada para análise', 'destaque' => 'Destaque alterado', 'bloqueada' => 'Bloqueada', 'desbloqueada' => 'Desbloqueada',
        'cadastro' => 'Cadastro realizado', 'perfil' => 'Perfil atualizado', 'expirada' => 'Prazo encerrado'];
    public static function rotulo(array $h): string { return self::ROT[$h['acao']] ?? $h['acao']; }
}
```

### Passo 29 — Cnpj.php

#### Arquivo: `app/Cnpj.php` — CRIAR
CNPJ numérico e alfanumérico (IN RFB 2.229/2024).

```php
<?php
/**
 * CNPJ numérico e ALFANUMÉRICO (IN RFB 2.229/2024, em vigor a partir de julho/2026).
 * 12 primeiros caracteres: letras A-Z e dígitos 0-9; os 2 últimos são sempre dígitos verificadores.
 * Cálculo: módulo 11, pesos 2..9, valor de cada caractere = código ASCII - 48 (dígitos seguem valendo 0-9).
 * CNPJs numéricos antigos continuam válidos com o mesmo algoritmo.
 */
class Cnpj
{
    /** Remove máscara e põe em maiúsculas: "12.abc.345/01de-35" -> "12ABC34501DE35". */
    public static function limpar(string $v): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $v));
    }

    public static function formatar(string $v): string
    {
        $v = self::limpar($v);
        if (strlen($v) !== 14) return $v;
        return substr($v, 0, 2) . '.' . substr($v, 2, 3) . '.' . substr($v, 5, 3) . '/' . substr($v, 8, 4) . '-' . substr($v, 12, 2);
    }

    private static function dv(string $base): int
    {
        $n = strlen($base);
        $pesos = [];
        for ($i = 0, $p = 2; $i < $n; $i++, $p = $p === 9 ? 2 : $p + 1) { $pesos[$n - 1 - $i] = $p; }
        $soma = 0;
        for ($i = 0; $i < $n; $i++) { $soma += (ord($base[$i]) - 48) * $pesos[$i]; }
        $r = $soma % 11;
        return $r < 2 ? 0 : 11 - $r;
    }

    public static function valido(string $v): bool
    {
        $c = self::limpar($v);
        if (!preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $c)) return false;
        if (preg_match('/^(.)\1{13}$/', $c)) return false;
        $d1 = self::dv(substr($c, 0, 12));
        $d2 = self::dv(substr($c, 0, 12) . $d1);
        return $c[12] === (string) $d1 && $c[13] === (string) $d2;
    }

    /** Gera um CNPJ válido a partir de 12 caracteres (usado nos dados de exemplo e testes). */
    public static function completar(string $base12): string
    {
        $b = self::limpar($base12);
        $d1 = self::dv($b);
        return $b . $d1 . self::dv($b . $d1);
    }
}
```

### Passo 30 — Paginador.php

#### Arquivo: `app/Paginador.php` — CRIAR

```php
<?php
class Paginador
{
    /** @return array [offset, paginaAtual, totalPaginas] */
    public static function calc(int $total, int $pagina, int $por): array
    {
        $paginas = max(1, (int) ceil($total / $por));
        $pagina = max(1, min($pagina, $paginas));
        return [($pagina - 1) * $por, $pagina, $paginas];
    }
}
```

### Passo 31 — Verificar a branch

```bash
php -r 'require "config.php"; var_dump(Estado::vagaPode("aguardando_aprovacao","aprovada"), Estado::vagaPode("encerrada","aprovada"), Cnpj::valido("12.ABC.345/01DE-35"));'
```

Resultado esperado:
- `php -r 'require "config.php"; var_dump(Estado::vagaPode("aguardando_ap…` → bool(true) bool(false) bool(true)

### Passo 32 — Fechar a branch

```bash
git add .
git commit -m "feat(dominio): máquina de estados, historico, CNPJ alfanumérico e paginação"
git push -u origin feature/backend-core
```
Abra o Pull Request `feature/backend-core` → `develop` e peça revisão a **Pedro Miguel**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 5: `feature/design-system` — dono: Pedro Henri

Design system das áreas logadas: tokens com modo noturno, layouts independentes, componentes PHP e núcleo do JavaScript.

**Revisor do PR:** Rodrigo. **Depende de:** `feature/backend-core`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `106.08` | Layouts independentes: autenticação (minimalista) e área logada (menu lateral) | 3 | 2 |
| `104.01` | Design tokens e componentes reutilizáveis | 5 | 9 |
| `104.02` | Modo noturno por tokens nas áreas da empresa e da administração, com preferência salva | 3 | 10 |

```bash
git checkout develop && git pull
git checkout -b feature/design-system
```

### Passo 33 — app.css

#### Arquivo: `public/css/app.css` — CRIAR
Todas as cores vêm de variáveis. O modo noturno só redefine as variáveis.

```css
/* ESTAGIA+ - design system das áreas independentes (login, empresa, administração).
   Tudo usa tokens (variáveis). O modo noturno só redefine os tokens: nenhum componente tem cor própria para o escuro. */
:root{
  --bg:#f3f6f4;--surface:#fff;--surface-2:#eef3ef;--text:#1b241e;--muted:#5a695f;--border:#dbe4de;--border-strong:#c3d0c7;
  --primary:#1f7a35;--primary-h:#17622a;--on-primary:#fff;--primary-soft:#e4f3e8;--focus:#c77d00;
  --ok:#14702a;--ok-bg:#e3f4e7;--warn:#8a5200;--warn-bg:#fff2d9;--danger:#b42318;--danger-bg:#fdebe9;--info:#0b5cad;--info-bg:#e6f0fb;--mute:#4b5750;--mute-bg:#e8ede9;
  --shadow:0 1px 2px rgba(16,32,22,.06),0 4px 14px rgba(16,32,22,.06);--r:12px;--side:248px;
  --chart-1:#1f7a35;--chart-2:#0b5cad;--chart-3:#c77d00;--chart-4:#b42318;--chart-5:#6b7a70;
  color-scheme:light;
}
[data-theme=dark]{
  --bg:#0e1411;--surface:#161f1a;--surface-2:#1c2822;--text:#e5eee8;--muted:#9fb1a5;--border:#2a3831;--border-strong:#3a4b42;
  --primary:#4cc263;--primary-h:#6dd681;--on-primary:#052010;--primary-soft:#173424;--focus:#f2b24c;
  --ok:#74dd8a;--ok-bg:#16331f;--warn:#f4bb62;--warn-bg:#392b10;--danger:#ff9a90;--danger-bg:#3d1916;--info:#86bdf7;--info-bg:#112a42;--mute:#a9b9ae;--mute-bg:#232f28;
  --shadow:0 1px 2px rgba(0,0,0,.4),0 4px 14px rgba(0,0,0,.35);
  --chart-1:#4cc263;--chart-2:#6aaef0;--chart-3:#f2b24c;--chart-4:#ff8f85;--chart-5:#8fa196;
  color-scheme:dark;
}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body.app-body{margin:0;background:var(--bg);color:var(--text);font:15px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;min-height:100vh}
a{color:var(--primary)}a:hover{color:var(--primary-h)}
h1,h2,h3{margin:0;line-height:1.25;color:var(--text)}
:focus-visible{outline:3px solid var(--focus);outline-offset:2px;border-radius:6px}
.ui-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
.vg-ico{display:inline-block;vertical-align:-3px;flex:none}
[hidden]{display:none!important}

/* ===== shell (empresa/admin) ===== */
.app-shell{display:grid;grid-template-columns:var(--side) minmax(0,1fr);min-height:100vh}
.app-side{background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;padding:18px 14px;gap:6px}
.app-brand{display:flex;align-items:center;gap:10px;padding:4px 8px 14px;font-weight:800;font-size:17px;color:var(--text);text-decoration:none}
.app-brand-mark{width:34px;height:34px;border-radius:9px;background:var(--primary);color:var(--on-primary);display:grid;place-items:center;font-weight:800}
.app-brand small{display:block;font-weight:600;font-size:11px;color:var(--muted);letter-spacing:.04em;text-transform:uppercase}
.app-nav{display:flex;flex-direction:column;gap:2px}
.app-nav a{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;color:var(--text);text-decoration:none;font-weight:600;font-size:14.5px}
.app-nav a:hover{background:var(--surface-2)}
.app-nav a.is-active{background:var(--primary-soft);color:var(--primary)}
.app-nav .ui-count{margin-left:auto}
.app-nav-sep{height:1px;background:var(--border);margin:8px 4px}
.app-side-foot{margin-top:auto;display:flex;flex-direction:column;gap:6px;font-size:13px;color:var(--muted);padding:8px}
.app-main{min-width:0;display:flex;flex-direction:column}
.app-top{display:flex;align-items:center;gap:12px;padding:12px 28px;border-bottom:1px solid var(--border);background:var(--surface);position:sticky;top:0;z-index:20}
.app-top h1{font-size:19px;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.app-icon-btn.app-menu-btn{display:none}
.app-icon-btn{width:38px;height:38px;border-radius:50%;border:1px solid var(--border);background:var(--surface);color:var(--text);display:grid;place-items:center;cursor:pointer;padding:0}
.app-icon-btn:hover{background:var(--surface-2)}
.app-user{font-size:13px;color:var(--muted);text-align:right;line-height:1.25}.app-user strong{display:block;color:var(--text);font-size:13.5px}
.app-content{padding:24px 28px 48px;max-width:1240px;width:100%;margin:0 auto}
.app-scrim{display:none}

/* ===== auth (login/cadastro) ===== */
.auth-body{min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:28px 16px;background:var(--bg)}
.auth-top{width:100%;max-width:980px;display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.auth-wrap{width:100%;max-width:440px}.auth-wrap.is-wide{max-width:760px}
.auth-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);padding:28px}
.auth-env{display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft);color:var(--primary);font-weight:700;font-size:12.5px;padding:4px 12px;border-radius:99px;margin-bottom:12px;text-transform:uppercase;letter-spacing:.05em}
.auth-card h1{font-size:23px;margin-bottom:4px}.auth-card .lead{color:var(--muted);margin:0 0 18px}
.auth-links{display:flex;flex-direction:column;gap:6px;margin-top:16px;font-size:14px;text-align:center}

/* ===== componentes ===== */
.ui-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:9px;border:1px solid var(--border-strong);background:var(--surface);color:var(--text);font:inherit;font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;line-height:1.2}
.ui-btn:hover{background:var(--surface-2);color:var(--text)}
.ui-btn-primary{background:var(--primary);border-color:var(--primary);color:var(--on-primary)}.ui-btn-primary:hover{background:var(--primary-h);border-color:var(--primary-h);color:var(--on-primary)}
.ui-btn-danger{background:var(--danger);border-color:var(--danger);color:#fff}.ui-btn-danger:hover{filter:brightness(.92);background:var(--danger);color:#fff}
.ui-btn-danger-o{color:var(--danger);border-color:var(--danger)}.ui-btn-danger-o:hover{background:var(--danger-bg);color:var(--danger)}
.ui-btn-ok-o{color:var(--ok);border-color:var(--ok)}.ui-btn-ok-o:hover{background:var(--ok-bg);color:var(--ok)}
.ui-btn-sm{padding:6px 11px;font-size:13px}.ui-btn-lg{padding:12px 22px;font-size:15px}.ui-btn-block{width:100%}
.ui-btn[disabled]{opacity:.55;cursor:not-allowed}
.ui-link-btn{background:none;border:0;color:var(--primary);font:inherit;font-weight:700;cursor:pointer;padding:0}.ui-link-btn:hover{text-decoration:underline}
.ui-badge{display:inline-flex;align-items:center;font-size:12px;font-weight:700;padding:2px 10px;border-radius:99px;line-height:1.5;white-space:nowrap}
.ui-badge-ok{background:var(--ok-bg);color:var(--ok)}.ui-badge-warn{background:var(--warn-bg);color:var(--warn)}.ui-badge-danger{background:var(--danger-bg);color:var(--danger)}
.ui-badge-info{background:var(--info-bg);color:var(--info)}.ui-badge-mute{background:var(--mute-bg);color:var(--mute)}
.ui-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow);padding:18px 20px}
.ui-card>h2,.ui-card-h{font-size:16px;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.ui-section{margin-bottom:26px}.ui-section>h2{font-size:17px;margin-bottom:12px}
.ui-sub{color:var(--muted);font-size:14px;margin:-6px 0 14px}
.ui-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px}
.ui-grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.ui-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.ui-metrics{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:22px}
.ui-metric{display:grid;grid-template-columns:auto 1fr;grid-template-areas:"i n" "i l";column-gap:12px;align-items:center;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;text-decoration:none;color:var(--text);box-shadow:var(--shadow)}
a.ui-metric:hover{border-color:var(--primary)}
.ui-metric-ico{grid-area:i;width:40px;height:40px;border-radius:10px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center}
.ui-metric-ico.warn{background:var(--warn-bg);color:var(--warn)}.ui-metric-ico.danger{background:var(--danger-bg);color:var(--danger)}.ui-metric-ico.info{background:var(--info-bg);color:var(--info)}.ui-metric-ico.mute{background:var(--mute-bg);color:var(--mute)}
.ui-metric-n{grid-area:n;font-size:26px;font-weight:800;line-height:1.1}.ui-metric-l{grid-area:l;font-size:13px;color:var(--muted)}
.ui-tabs{display:flex;flex-wrap:wrap;gap:6px;border-bottom:1px solid var(--border);margin-bottom:16px}
.ui-tabs a{display:inline-flex;align-items:center;gap:7px;padding:9px 13px;border-bottom:3px solid transparent;color:var(--muted);text-decoration:none;font-weight:700;font-size:14px;margin-bottom:-1px}
.ui-tabs a:hover{color:var(--text)}.ui-tabs a.is-active{color:var(--primary);border-color:var(--primary)}
.ui-count{background:var(--surface-2);color:var(--muted);border-radius:99px;font-size:12px;padding:0 8px;line-height:1.7;font-weight:700}
.is-active>.ui-count,a.is-active .ui-count{background:var(--primary-soft);color:var(--primary)}
.ui-toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:16px}
.ui-toolbar form{display:flex;flex-wrap:wrap;gap:10px;flex:1;align-items:center}
.ui-input,.ui-select,.ui-textarea{width:100%;padding:9px 12px;border:1px solid var(--border-strong);border-radius:9px;background:var(--surface);color:var(--text);font:inherit;font-size:14.5px}
.ui-select{height:40px}.ui-textarea{resize:vertical;min-height:90px}
.ui-toolbar .ui-input{flex:1;min-width:200px;width:auto}.ui-toolbar .ui-select{width:auto}
.ui-input::placeholder,.ui-textarea::placeholder{color:var(--muted);opacity:.8}
.ui-pages{display:flex;gap:6px;justify-content:center;margin-top:22px;flex-wrap:wrap}
.ui-pages a,.ui-pages span{padding:6px 12px;border-radius:8px;border:1px solid var(--border);text-decoration:none;font-weight:700;font-size:14px;color:var(--text);background:var(--surface)}
.ui-pages .is-cur{background:var(--primary);border-color:var(--primary);color:var(--on-primary)}.ui-pages .is-off{opacity:.45}
.ui-empty{text-align:center;padding:38px 20px;background:var(--surface);border:1px dashed var(--border-strong);border-radius:var(--r);color:var(--muted)}
.ui-empty strong{display:block;color:var(--text);font-size:16px;margin-bottom:4px}.ui-empty p{margin:0 0 14px}
.ui-alert{display:flex;gap:12px;align-items:flex-start;padding:13px 16px;border-radius:var(--r);margin-bottom:18px;border:1px solid transparent}
.ui-alert strong{display:block}.ui-alert p{margin:2px 0 0;font-size:14px}.ui-alert>div{flex:1}
.ui-alert-ok{background:var(--ok-bg);color:var(--ok);border-color:color-mix(in srgb,var(--ok) 30%,transparent)}
.ui-alert-warn,.ui-alert-info{background:var(--info-bg);color:var(--info)}.ui-alert-warn{background:var(--warn-bg);color:var(--warn)}
.ui-alert-danger,.ui-alert-erro{background:var(--danger-bg);color:var(--danger)}
.ui-alert a{color:inherit;font-weight:700}
.ui-logo{width:44px;height:44px;border-radius:10px;object-fit:cover;background:var(--surface-2);border:1px solid var(--border);flex:none}
.ui-logo-ini{display:grid;place-items:center;font-weight:800;color:var(--primary);background:var(--primary-soft);font-size:15px}
.ui-kv{display:grid;grid-template-columns:150px 1fr;gap:6px 14px;margin:0;font-size:14.5px}.ui-kv dt{color:var(--muted);font-weight:600}.ui-kv dd{margin:0;word-break:break-word}
.ui-list{margin:0;padding-left:20px}.ui-list li{margin-bottom:3px}
.ui-pills{display:flex;flex-wrap:wrap;gap:8px;list-style:none;margin:0;padding:0}.ui-pills li{background:var(--primary-soft);color:var(--primary);padding:3px 12px;border-radius:99px;font-weight:600;font-size:13.5px}
.ui-timeline{list-style:none;margin:0;padding:0 0 0 18px;border-left:2px solid var(--border)}
.ui-timeline li{position:relative;padding:0 0 14px 14px;font-size:14px}.ui-timeline li::before{content:"";position:absolute;left:-24px;top:5px;width:10px;height:10px;border-radius:50%;background:var(--primary);border:2px solid var(--surface)}
.ui-timeline small{display:block;color:var(--muted)}
.ui-table-wrap{overflow-x:auto;border:1px solid var(--border);border-radius:var(--r);background:var(--surface)}
.ui-table{width:100%;border-collapse:collapse;font-size:14px}
.ui-table th,.ui-table td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);vertical-align:top}.ui-table tr:last-child td{border-bottom:0}
.ui-table th{background:var(--surface-2);font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)}
.ui-table small{color:var(--muted)}
.ui-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.ui-actions form{margin:0}
.ui-row-between{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.ui-seg{display:inline-flex;border:1px solid var(--border-strong);border-radius:9px;overflow:hidden}.ui-seg a{padding:7px 12px;text-decoration:none;font-weight:700;font-size:13px;color:var(--muted);background:var(--surface)}.ui-seg a.is-active{background:var(--primary-soft);color:var(--primary)}

/* cards de vaga e de empresa (área logada) */
.ui-job,.ui-comp{display:flex;flex-direction:column;gap:10px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:16px;box-shadow:var(--shadow);position:relative}
.ui-job:hover,.ui-comp:hover{border-color:var(--primary)}
.ui-job-top{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
.ui-job h3,.ui-comp h3{font-size:16px}.ui-job h3 a,.ui-comp h3 a{color:var(--text);text-decoration:none}.ui-job h3 a:hover,.ui-comp h3 a:hover{color:var(--primary)}
.ui-job-co{font-size:13.5px;color:var(--muted);display:flex;align-items:center;gap:8px}
.ui-job-meta{display:flex;flex-direction:column;gap:3px;font-size:13.5px;color:var(--muted);margin:0;padding:0;list-style:none}.ui-job-meta li{display:flex;gap:7px;align-items:center}
.ui-job-days{font-weight:700}.ui-job-days.is-warn{color:var(--warn)}.ui-job-days.is-off{color:var(--muted)}
.ui-job-foot{margin-top:auto;display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding-top:10px;border-top:1px solid var(--border)}
.ui-job-flag{font-size:13px;color:var(--warn);background:var(--warn-bg);border-radius:8px;padding:6px 10px}
.ui-comp-head{display:flex;gap:12px;align-items:center}.ui-comp-head>div{min-width:0}
.ui-comp-head .ui-logo{width:52px;height:52px}
.ui-comp p{margin:0;color:var(--muted);font-size:13.5px}

/* formulários */
.ui-form{max-width:780px}
.ui-fieldset{border:0;padding:0;margin:0 0 6px;min-width:0}
.ui-fieldset>legend{font-size:18px;font-weight:800;margin-bottom:4px;padding:0}
.ui-step-sub{color:var(--muted);margin:0 0 16px;font-size:14px}
.ui-field{margin-bottom:15px}
.ui-field>label,.ui-label{display:block;font-weight:700;font-size:14px;margin-bottom:5px}
.ui-req{color:var(--danger)}
.ui-hint{display:block;color:var(--muted);font-size:12.5px;margin-top:4px}.ui-hint.is-ok{color:var(--ok);font-weight:600}.ui-hint.is-warn{color:var(--warn);font-weight:600}
.ui-err{display:block;color:var(--danger);font-size:12.5px;margin-top:4px;font-weight:700}.ui-err:empty{display:none}
.ui-field .is-invalid,.is-invalid{border-color:var(--danger)!important;background:var(--danger-bg)}
.ui-cols{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}.ui-cols-3{grid-template-columns:1fr 1fr 1fr}.ui-cols-num{grid-template-columns:150px 1fr}.ui-cols-uf{grid-template-columns:1fr 120px}
.ui-checks{display:flex;flex-wrap:wrap;gap:8px}.ui-check{display:inline-flex;align-items:center;gap:8px;padding:7px 12px;border:1px solid var(--border-strong);border-radius:9px;cursor:pointer;font-weight:600;font-size:14px;background:var(--surface)}
.ui-check:has(input:checked){background:var(--primary-soft);border-color:var(--primary);color:var(--primary)}.ui-check input{accent-color:var(--primary)}
.ui-consent{display:flex;gap:10px;align-items:flex-start;margin:10px 0;font-size:14px}.ui-consent input{margin-top:4px;accent-color:var(--primary)}
.ui-terms{max-height:150px;overflow:auto;border:1px solid var(--border);border-radius:9px;padding:12px 14px;font-size:13px;color:var(--muted);background:var(--surface-2);margin-bottom:10px}.ui-terms h3{font-size:13.5px;color:var(--text);margin-bottom:4px}.ui-terms p{margin:0 0 8px}
.ui-money{position:relative}.ui-money span{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-weight:700}.ui-money input{padding-left:40px}
.ui-rules{list-style:none;margin:8px 0 0;padding:0;font-size:13px;color:var(--muted)}.ui-rules li::before{content:"○ ";}.ui-rules li.is-ok{color:var(--ok)}.ui-rules li.is-ok::before{content:"● "}
.ui-period{margin-top:6px;padding:12px 14px;border-radius:10px;background:var(--surface-2);font-size:14px}.ui-period-bar{height:8px;border-radius:99px;background:var(--border);margin-top:8px;overflow:hidden}.ui-period-bar i{display:block;height:100%;background:var(--primary)}
.ui-logo-pick{display:flex;gap:14px;align-items:center}.ui-logo-pick .ui-logo{width:64px;height:64px}
.ui-chips{display:none;flex-wrap:wrap;gap:8px;border:1px solid var(--border-strong);border-radius:10px;padding:8px;background:var(--surface)}
.is-enhanced .ui-chips{display:flex}.is-enhanced textarea{display:none}
.ui-chip{display:inline-flex;align-items:center;gap:6px;background:var(--primary-soft);color:var(--primary);padding:4px 6px 4px 12px;border-radius:99px;font-weight:600;font-size:13.5px}
.ui-chip button{border:0;background:none;color:inherit;cursor:pointer;width:20px;height:20px;border-radius:50%;display:grid;place-items:center;padding:0}.ui-chip button:hover{background:rgba(0,0,0,.12)}
.ui-chips input{flex:1;min-width:180px;border:0;background:transparent;color:var(--text);font:inherit;padding:5px 6px}.ui-chips input:focus{outline:none}
.ui-chips:focus-within{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
.ui-quick{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}.ui-quick button{border:1px dashed var(--border-strong);background:none;color:var(--muted);border-radius:99px;padding:3px 10px;font:inherit;font-size:12.5px;cursor:pointer}.ui-quick button:hover{color:var(--primary);border-color:var(--primary)}

/* stepper (cadastros em etapas) */
.ui-stepper{display:none;list-style:none;margin:0 0 22px;padding:0;gap:6px}
.ui-wiz-on .ui-stepper{display:flex}
.ui-stepper li{flex:1;min-width:0}
.ui-stepper button{width:100%;display:flex;align-items:center;gap:9px;padding:9px 10px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--muted);font:inherit;font-weight:700;font-size:13px;cursor:pointer;text-align:left}
.ui-stepper button:disabled{cursor:default;opacity:.6}
.ui-stepper .n{flex:none;width:26px;height:26px;border-radius:50%;background:var(--surface-2);display:grid;place-items:center;font-size:12.5px;color:var(--text)}
.ui-stepper .t{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ui-stepper .is-current button{border-color:var(--primary);background:var(--primary-soft);color:var(--primary)}.ui-stepper .is-current .n{background:var(--primary);color:var(--on-primary)}
.ui-stepper .is-done button{color:var(--primary)}.ui-stepper .is-done .n{background:var(--primary);color:var(--on-primary);font-size:0}.ui-stepper .is-done .n::before{content:"\2713";font-size:14px}
.ui-stepper .has-error button{border-color:var(--danger)}
.ui-wiz-on .ui-step:not(.is-active){display:none}
.ui-wiz-on .ui-step{animation:uifade .22s}@keyframes uifade{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
.ui-wiz-nav{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-top:20px;padding-top:18px;border-top:1px solid var(--border)}
.ui-wiz-nav .ui-spacer{flex:1}
.ui-progress{height:6px;background:var(--border);border-radius:99px;margin:-8px 0 20px;overflow:hidden}.ui-progress i{display:block;height:100%;background:var(--primary);transition:width .25s}
.ui-review{background:var(--surface-2);border-radius:var(--r);padding:16px 18px;margin-bottom:14px}.ui-review h3{font-size:15px;margin-bottom:8px}
.ui-review dl{margin:0;display:grid;gap:7px}.ui-review dl>div{display:grid;grid-template-columns:150px 1fr;gap:10px;font-size:14px}.ui-review dt{color:var(--muted);font-weight:600}.ui-review dd{margin:0;word-break:break-word}

.ui-stepper.is-many li{flex:0 0 auto}.ui-stepper.is-many .t{display:none}.ui-stepper.is-many .is-current{flex:1}.ui-stepper.is-many .is-current .t{display:inline}

/* modal */
.ui-modal{border:1px solid var(--border);border-radius:16px;padding:0;max-width:460px;width:calc(100% - 32px);background:var(--surface);color:var(--text);box-shadow:0 20px 60px rgba(0,0,0,.35)}
.ui-modal::backdrop{background:rgba(8,16,11,.55)}
.ui-modal-in{padding:22px}.ui-modal h2{font-size:18px;margin-bottom:8px}.ui-modal p{margin:0 0 14px;color:var(--muted)}
.ui-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px}
.ui-modal .ui-textarea{min-height:90px}

/* gráficos (SVG gerado no servidor, com tokens) */
.ui-chart{width:100%;height:auto;display:block}.ui-chart text{fill:var(--muted);font:11px system-ui,sans-serif}.ui-chart .grid{stroke:var(--border);stroke-width:1}
.ui-legend{display:flex;flex-wrap:wrap;gap:12px;font-size:13px;color:var(--muted);margin-top:8px}.ui-legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px}
.ui-donut-wrap{display:flex;gap:18px;align-items:center;flex-wrap:wrap}.ui-donut-wrap ul{list-style:none;margin:0;padding:0;font-size:14px;display:grid;gap:5px}.ui-donut-wrap li i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:8px}
.ui-bell{position:relative}.ui-bell>summary{list-style:none;cursor:pointer}.ui-bell>summary::-webkit-details-marker{display:none}
.ui-bell-n{position:absolute;top:-5px;right:-5px;background:var(--danger);color:#fff;border-radius:99px;font-size:11px;font-weight:800;min-width:19px;height:19px;display:grid;place-items:center;padding:0 5px}
.ui-pop{position:absolute;right:0;top:46px;width:min(340px,86vw);background:var(--surface);border:1px solid var(--border);border-radius:var(--r);box-shadow:0 12px 34px rgba(0,0,0,.25);padding:14px;z-index:30}
.ui-pop p{margin:8px 0 0;padding:8px 10px;border-radius:8px;background:var(--surface-2);font-size:13.5px;display:flex;flex-direction:column}.ui-pop p.is-new{background:var(--warn-bg);color:var(--warn);font-weight:700}.ui-pop small{color:var(--muted);font-weight:400}
.ui-code{font-family:ui-monospace,Menlo,Consolas,monospace;background:var(--surface-2);padding:2px 6px;border-radius:5px;font-size:13px}

@media (max-width:980px){
  .app-shell{grid-template-columns:1fr}
  .app-side{position:fixed;inset:0 auto 0 0;width:min(280px,86vw);z-index:60;transform:translateX(-102%);transition:transform .2s;box-shadow:0 0 40px rgba(0,0,0,.3)}
  .app-side.is-open{transform:none}.app-scrim.is-open{display:block;position:fixed;inset:0;background:rgba(8,16,11,.5);z-index:50}
  .app-icon-btn.app-menu-btn{display:grid}.app-top{padding:10px 16px}.app-content{padding:18px 16px 40px}.app-user{display:none}
}
@media (max-width:700px){
  .ui-grid-2,.ui-grid-3,.ui-cols,.ui-cols-3,.ui-cols-num,.ui-cols-uf{grid-template-columns:1fr}
  .ui-kv,.ui-review dl>div{grid-template-columns:1fr;gap:0}.ui-kv dd{margin-bottom:8px}
  .ui-stepper .t{display:none}.ui-stepper .is-current .t{display:inline}.ui-stepper li{flex:0 0 auto}.ui-stepper .is-current{flex:1}
  .auth-card{padding:20px}.ui-wiz-nav .ui-btn{flex:1}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
```

### Passo 34 — head.php

#### Arquivo: `Views/layouts/head.php` — CRIAR

```php
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<script>try{var t=localStorage.getItem('vg-theme');if(!t)t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
<link rel="stylesheet" href="<?= e(BASE_URL) ?>public/css/app.css?v=<?= (int) @filemtime(ROOT_PATH . '/public/css/app.css') ?>">
```

### Passo 35 — auth.php

#### Arquivo: `Views/layouts/auth.php` — CRIAR

```php
<!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? 'ESTAGIA+') ?> · ESTAGIA+</title></head>
<body class="auth-body">
  <div class="auth-top">
    <a class="app-brand" href="<?= url() ?>"><span class="app-brand-mark">E+</span><span>ESTAGIA+<small>IFSP Guarulhos</small></span></a>
    <div class="ui-actions">
      <a class="ui-btn ui-btn-sm" href="<?= url() ?>">Ver vagas</a>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
    </div>
  </div>
  <main class="auth-wrap <?= !empty($larga) ? 'is-wide' : '' ?>">
    <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="ui-alert ui-alert-<?= e($t) ?>" role="status"><div><?= e($m) ?></div></div><?php endforeach; unset($_SESSION['flash']); ?>
    <?= $conteudo ?>
  </main>
  <script src="<?= e(BASE_URL) ?>public/js/app.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/app.js') ?>"></script>
</body></html>
```

### Passo 36 — app.php

#### Arquivo: `Views/layouts/app.php` — CRIAR

```php
<?php
$ehAdmin = $area === 'admin';
$nav = $ehAdmin ? [
    ['admin', 'Dashboard', 'grid', 'dashboard'], ['admin-aprovacoes', 'Aprovações', 'check', 'aprovacoes'], ['admin-vagas', 'Vagas', 'briefcase', 'vagas'],
    ['admin-empresas', 'Empresas', 'building', 'empresas'], ['admin-usuarios', 'Usuários', 'users', 'usuarios'], ['admin-acesso', 'Controle de acesso', 'shield', 'acesso'],
    ['admin-relatorios', 'Relatórios', 'chart', 'relatorios'], ['admin-config', 'Configurações', 'settings', 'config'],
] : array_values(array_filter([
    ['painel', 'Visão geral', 'grid', 'painel'], ['empresa-vagas', 'Minhas vagas', 'briefcase', 'vagas'],
    ($emp['status'] ?? '') === 'aprovada' ? ['vaga-cadastro', 'Publicar vaga', 'plus', 'publicar'] : null, ['empresa-perfil', 'Perfil da empresa', 'building', 'perfil'],
]));
$pessoa = $ehAdmin ? ($adm['nome'] ?? 'Administrador') : ($emp['nome'] ?? 'Empresa');
$sub = $ehAdmin ? 'Administração' : 'Área da empresa';
?><!DOCTYPE html>
<html lang="pt-br"><head><?php require __DIR__ . '/head.php'; ?><title><?= e($titulo ?? '') ?> · <?= e($sub) ?> · ESTAGIA+</title></head>
<body class="app-body">
<div class="app-shell">
  <aside class="app-side" id="menu" aria-label="Menu principal">
    <a class="app-brand" href="<?= url($ehAdmin ? 'admin' : 'painel') ?>"><span class="app-brand-mark">E+</span><span>ESTAGIA+<small><?= e($sub) ?></small></span></a>
    <nav class="app-nav">
      <?php foreach ($nav as [$rota, $rot, $ic, $chave]): $on = ($secao ?? '') === $chave || ($chave === 'publicar' && ($secao ?? '') === 'publicar'); ?>
        <a href="<?= url($rota) ?>" class="<?= $on ? 'is-active' : '' ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= icon($ic, 18) ?> <?= e($rot) ?>
          <?php if ($chave === 'aprovacoes' && !empty($pendentes)): ?><span class="ui-count"><?= (int) $pendentes ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <div class="app-nav-sep"></div>
      <a href="<?= url() ?>"><?= icon('external', 18) ?> Ver site de vagas</a>
    </nav>
    <div class="app-side-foot">
      <strong style="color:var(--text)"><?= e($pessoa) ?></strong>
      <form method="post" action="<?= url('sair') ?>"><?= csrf_field() ?><input type="hidden" name="perfil" value="<?= $ehAdmin ? 'admin' : 'empresa' ?>">
        <button class="ui-btn ui-btn-sm ui-btn-block" type="submit"><?= icon('logout', 16) ?> Sair</button></form>
    </div>
  </aside>
  <div class="app-scrim"></div>
  <div class="app-main">
    <header class="app-top">
      <button type="button" class="app-icon-btn app-menu-btn" data-menu-open aria-controls="menu" aria-label="Abrir menu"><?= icon('menu', 20) ?></button>
      <h1><?= e($titulo ?? '') ?></h1>
      <?php if ($ehAdmin && isset($naoLidas)): ?>
        <details class="ui-bell"><summary class="app-icon-btn" aria-label="Notificações: <?= (int) $naoLidas ?> novas"><?= icon('bell', 18) ?><?php if ($naoLidas): ?><span class="ui-bell-n"><?= (int) $naoLidas ?></span><?php endif; ?></summary>
          <div class="ui-pop"><strong>Notificações</strong>
            <?php foreach ($notifs ?? [] as $n): ?><p class="<?= $n['lida'] ? '' : 'is-new' ?>"><span><?= e($n['mensagem']) ?></span><small><?= e(date('d/m H:i', strtotime($n['criado_em']))) ?></small></p><?php endforeach; ?>
            <?php if (empty($notifs)): ?><p>Nada por aqui ainda.</p><?php endif; ?>
            <?php if ($naoLidas): ?><form method="post" action="<?= url('admin-acao') ?>" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="tipo" value="notificacoes"><button class="ui-link-btn" type="submit">Marcar todas como lidas</button></form><?php endif; ?></div></details>
      <?php endif; ?>
      <button type="button" class="app-icon-btn" data-theme-toggle aria-label="Ativar modo noturno"><?= icon('moon', 18) ?></button>
      <div class="app-user"><strong><?= e($pessoa) ?></strong><?= $ehAdmin ? e($adm['email'] ?? '') : e($emp['email'] ?? '') ?></div>
    </header>
    <main class="app-content" id="conteudo">
      <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="ui-alert ui-alert-<?= e($t) ?>" role="status"><div><?= e($m) ?></div></div><?php endforeach; unset($_SESSION['flash']); ?>
      <?= $conteudo ?>
    </main>
  </div>
</div>
<dialog id="ui-dialog" class="ui-modal" aria-labelledby="ui-dlg-title"><div class="ui-modal-in">
  <h2 id="ui-dlg-title"></h2><p id="ui-dlg-text"></p>
  <textarea id="ui-dlg-motivo" class="ui-textarea" rows="3" hidden placeholder="Descreva o motivo" aria-label="Motivo"></textarea><small class="ui-err" id="ui-dlg-err"></small>
  <div class="ui-modal-actions"><button type="button" class="ui-btn" id="ui-dlg-cancel">Cancelar</button><button type="button" class="ui-btn ui-btn-primary" id="ui-dlg-ok">Confirmar</button></div>
</div></dialog>
<script src="<?= e(BASE_URL) ?>public/js/app.js?v=<?= (int) @filemtime(ROOT_PATH . '/public/js/app.js') ?>"></script>
</body></html>
```

### Passo 37 — erro.php

#### Arquivo: `Views/erro.php` — CRIAR

```php
<div class="ui-empty"><strong><?= e($titulo ?? 'Erro') ?></strong><p><?= e($msg ?? '') ?></p><a class="ui-btn ui-btn-primary" href="javascript:history.back()">Voltar</a></div>
```

### Passo 38 — Ui.php

#### Arquivo: `app/Ui.php` — CRIAR
Componentes: badge, abas, paginação, métricas, campo, modais, gráficos SVG.

```php
<?php
/** Componentes de interface reutilizáveis (HTML escapado). Usados por empresa, admin e login. */
class Ui
{
    public static function badge(string $texto, string $tom = 'mute'): string { return '<span class="ui-badge ui-badge-' . e($tom) . '">' . e($texto) . '</span>'; }
    public static function situacao(string $s): string { return self::badge(Estado::rotuloSituacao($s), Estado::tomSituacao($s)); }
    public static function statusEmpresa(string $s): string { [$t, $c] = Estado::STATUS_EMPRESA[$s] ?? [$s, 'mute']; return self::badge($t, $c); }

    public static function vazio(string $titulo, string $texto = '', ?array $acao = null): string
    {
        return '<div class="ui-empty"><strong>' . e($titulo) . '</strong>' . ($texto ? '<p>' . e($texto) . '</p>' : '')
            . ($acao ? '<a class="ui-btn ui-btn-primary" href="' . e($acao[1]) . '">' . e($acao[0]) . '</a>' : '') . '</div>';
    }
    /** Abas por link (estado na URL). $abas = [chave => [rótulo, contagem|null]]. */
    public static function abas(array $abas, string $ativa, string $pagina, array $params = [], string $chave = 'aba'): string
    {
        $h = '<nav class="ui-tabs" aria-label="Filtrar por situação">';
        foreach ($abas as $k => [$rot, $n]) {
            $u = url($pagina, array_merge($params, [$chave => $k]));
            $h .= '<a href="' . e($u) . '" class="' . ($k === $ativa ? 'is-active' : '') . '"' . ($k === $ativa ? ' aria-current="page"' : '') . '>' . e($rot) . ($n !== null ? ' <span class="ui-count">' . (int) $n . '</span>' : '') . '</a>';
        }
        return $h . '</nav>';
    }
    public static function paginacao(int $pagina, int $paginas, string $rota, array $params = []): string
    {
        if ($paginas <= 1) return '';
        $l = fn(int $p, string $t, bool $on = true, string $c = '') => $on ? '<a class="' . $c . '" href="' . e(url($rota, array_merge($params, ['pg' => $p]))) . '">' . $t . '</a>' : '<span class="is-off ' . $c . '">' . $t . '</span>';
        $h = '<nav class="ui-pages" aria-label="Paginação">' . $l($pagina - 1, 'Anterior', $pagina > 1);
        for ($p = max(1, $pagina - 2); $p <= min($paginas, $pagina + 2); $p++) $h .= $l($p, (string) $p, $p !== $pagina, $p === $pagina ? 'is-cur' : '');
        return $h . $l($pagina + 1, 'Próxima', $pagina < $paginas) . '</nav>';
    }
    public static function metrica(string $rotulo, $valor, string $icone = 'briefcase', ?string $href = null, string $tom = ''): string
    {
        $in = '<span class="ui-metric-ico ' . e($tom) . '">' . icon($icone, 20) . '</span><span class="ui-metric-n">' . e((string) $valor) . '</span><span class="ui-metric-l">' . e($rotulo) . '</span>';
        return $href ? '<a class="ui-metric" href="' . e($href) . '">' . $in . '</a>' : '<div class="ui-metric">' . $in . '</div>';
    }
    public static function logo(array $emp, int $px = 44): string
    {
        if (!empty($emp['logo'])) return '<img class="ui-logo" width="' . $px . '" height="' . $px . '" src="' . e(BASE_URL . $emp['logo']) . '" alt="Logo de ' . e($emp['nome']) . '" loading="lazy">';
        return '<span class="ui-logo ui-logo-ini" style="width:' . $px . 'px;height:' . $px . 'px" aria-hidden="true">' . e(iniciais($emp['nome'])) . '</span>';
    }
    /** Botão que abre o modal de confirmação (o <form> carrega os textos em data-*). */
    public static function formConfirmar(string $acao, array $campos, string $botao, string $titulo, string $texto, string $tom = 'danger', string $classe = 'ui-btn ui-btn-sm'): string
    {
        $h = '<form method="post" action="' . e($acao) . '" data-confirm="' . e($texto) . '" data-confirm-title="' . e($titulo) . '" data-confirm-ok="' . e($botao) . '" data-confirm-tone="' . e($tom) . '">' . csrf_field();
        foreach ($campos as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e((string) $v) . '">';
        return $h . '<button class="' . e($classe) . '" type="submit">' . e($botao) . '</button></form>';
    }
    /** Botão que abre o modal pedindo o motivo (rejeitar/bloquear). */
    public static function formMotivo(string $acao, array $campos, string $botao, string $titulo, string $classe = 'ui-btn ui-btn-sm ui-btn-danger-o'): string
    {
        $h = '<form method="post" action="' . e($acao) . '" data-motivo="' . e($titulo) . '" data-confirm-ok="' . e($botao) . '">' . csrf_field();
        foreach ($campos as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e((string) $v) . '">';
        return $h . '<input type="hidden" name="motivo" value=""><button class="' . e($classe) . '" type="submit">' . e($botao) . '</button></form>';
    }

    /** Campo de formulário padrão (label, controle, dica e erro). $o: name,label,type,req,hint,attrs[],opcoes[]|[valor=>rótulo],id */
    public static function campo(array $o, array $old = [], array $erros = []): string
    {
        $n = $o['name']; $t = $o['type'] ?? 'text'; $v = $old[$n] ?? ($o['value'] ?? ''); if (is_array($v)) $v = '';
        $id = $o['id'] ?? $n; $err = $erros[$n] ?? ''; $req = !empty($o['req']);
        $attr = ''; foreach (($o['attrs'] ?? []) as $k => $x) $attr .= is_int($k) ? ' ' . e($x) : ' ' . e($k) . '="' . e($x) . '"';
        $cls = ($t === 'select' ? 'ui-select' : ($t === 'textarea' ? 'ui-textarea' : 'ui-input')) . ($err ? ' is-invalid' : '');
        $h = '<div class="ui-field"' . (!empty($o['when']) ? ' data-when="' . e($o['when']) . '"' : '') . '><label for="' . e($id) . '">' . e($o['label']) . ($req ? ' <span class="ui-req" aria-hidden="true">*</span>' : '') . '</label>';
        $base = 'id="' . e($id) . '" name="' . e($n) . '" class="' . $cls . '"' . ($req ? ' required' : '') . $attr . ($err ? ' aria-invalid="true"' : '');
        if ($t === 'select') {
            $h .= '<select ' . $base . '><option value="">' . e($o['vazio'] ?? 'Selecione') . '</option>';
            foreach ($o['opcoes'] as $k => $r) { $val = array_is_list($o['opcoes']) ? $r : $k; $h .= '<option value="' . e($val) . '"' . ((string) $v === (string) $val ? ' selected' : '') . '>' . e($r) . '</option>'; }
            $h .= '</select>';
        } elseif ($t === 'textarea') { $h .= '<textarea ' . $base . ' rows="' . (int) ($o['rows'] ?? 4) . '">' . e($v) . '</textarea>'; }
        else { $h .= '<input type="' . e($t) . '" ' . $base . ' value="' . e($v) . '">'; }
        if (!empty($o['hint'])) $h .= '<small class="ui-hint">' . e($o['hint']) . '</small>';
        return $h . '<small class="ui-err" data-err>' . e($err) . '</small></div>';
    }

    /** Ações do administrador sobre uma vaga, conforme a situação (todas em modal; o backend revalida cada uma). */
    public static function acoesVagaAdmin(array $v, string $ret = 'admin-vagas', ?int $retId = null): string
    {
        $c = ['tipo' => 'vaga', 'id' => $v['id'], 'ret' => $ret] + ($retId ? ['ret_id' => $retId] : []); $a = url('admin-acao'); $h = '<div class="ui-actions">';
        if ($v['status'] === Estado::V_AGUARDANDO) {
            $h .= self::formConfirmar($a, $c + ['acao' => 'aprovar'], 'Aprovar', 'Aprovar esta vaga?', 'A vaga "' . $v['titulo'] . '" será publicada de ' . data_br($v['data_publicacao']) . ' até ' . data_br($v['data_limite']) . ' e a empresa receberá um e-mail.', 'primary', 'ui-btn ui-btn-sm ui-btn-primary');
            $h .= self::formMotivo($a, $c + ['acao' => 'rejeitar'], 'Rejeitar', 'Rejeitar esta vaga');
        } elseif ($v['status'] === Estado::V_APROVADA) {
            if ($v['situacao'] === 'publicada') $h .= '<form method="post" action="' . e($a) . '">' . csrf_field() . '<input type="hidden" name="acao" value="destaque">' . implode('', array_map(fn($k, $x) => '<input type="hidden" name="' . e($k) . '" value="' . e((string) $x) . '">', array_keys($c), $c)) . '<button class="ui-btn ui-btn-sm" type="submit">' . ($v['destaque'] ? 'Tirar do destaque' : 'Destacar') . '</button></form>';
            $h .= self::formConfirmar($a, $c + ['acao' => 'encerrar'], 'Encerrar', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de ser exibida aos estudantes, continua no histórico e a empresa será avisada.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o');
        }
        return $h . '</div>';
    }
    /** Ações do administrador sobre uma empresa. */
    public static function acoesEmpresaAdmin(array $e, string $ret = 'admin-empresas', ?int $retId = null): string
    {
        $c = ['tipo' => 'empresa', 'id' => $e['id'], 'ret' => $ret] + ($retId ? ['ret_id' => $retId] : []); $a = url('admin-acao'); $h = '<div class="ui-actions">';
        if ($e['status'] === Estado::E_PENDENTE) {
            $h .= self::formConfirmar($a, $c + ['acao' => 'aprovar'], 'Aprovar', 'Aprovar a empresa?', $e['nome'] . ' poderá publicar vagas e receberá um e-mail de confirmação.', 'primary', 'ui-btn ui-btn-sm ui-btn-primary');
            $h .= self::formMotivo($a, $c + ['acao' => 'reprovar'], 'Reprovar', 'Reprovar o cadastro');
        } elseif ($e['status'] === Estado::E_APROVADA) {
            $h .= self::formMotivo($a, $c + ['acao' => 'bloquear'], 'Bloquear', 'Bloquear a empresa', 'ui-btn ui-btn-sm ui-btn-danger-o');
        } elseif ($e['status'] === Estado::E_BLOQUEADA) {
            $h .= self::formConfirmar($a, $c + ['acao' => 'desbloquear'], 'Desbloquear', 'Desbloquear a empresa?', 'A empresa volta a acessar a plataforma e as vagas aprovadas voltam ao ar nas datas previstas.', 'primary', 'ui-btn ui-btn-sm');
        }
        return $h . '</div>';
    }

    /** Rosca em SVG. $itens = [[rótulo, valor, 'var(--chart-1)']]. Sem dados: estado vazio (nada inventado). */
    public static function donut(array $itens, string $centro = ''): string
    {
        $total = array_sum(array_column($itens, 1));
        if ($total <= 0) return self::vazio('Sem dados ainda', 'Os números aparecem quando houver registros.');
        $r = 42; $c = 2 * M_PI * $r; $off = 0; $arcos = '';
        foreach ($itens as [$rot, $v, $cor]) {
            if ($v <= 0) continue; $len = $v / $total * $c;
            $arcos .= '<circle cx="60" cy="60" r="' . $r . '" fill="none" stroke="' . e($cor) . '" stroke-width="16" stroke-dasharray="' . round($len, 2) . ' ' . round($c - $len, 2) . '" stroke-dashoffset="' . round(-$off, 2) . '" transform="rotate(-90 60 60)"/>';
            $off += $len;
        }
        $li = ''; foreach ($itens as [$rot, $v, $cor]) $li .= '<li><i style="background:' . e($cor) . '"></i>' . e($rot) . ': <strong>' . (int) $v . '</strong></li>';
        return '<div class="ui-donut-wrap"><svg viewBox="0 0 120 120" width="150" height="150" role="img" aria-label="Gráfico de rosca: ' . e(implode(', ', array_map(fn($i) => $i[0] . ' ' . (int) $i[1], $itens))) . '">' . $arcos
            . '<text x="60" y="58" text-anchor="middle" style="font:700 20px system-ui;fill:var(--text)">' . (int) $total . '</text><text x="60" y="73" text-anchor="middle" style="font:11px system-ui;fill:var(--muted)">' . e($centro) . '</text></svg><ul>' . $li . '</ul></div>';
    }
    /** Barras agrupadas em SVG. $linhas = [['rot'=>'01/10','a'=>2,'b'=>1]]; $series = [[chave, rótulo, cor]]. */
    public static function barras(array $linhas, array $series, string $descricao): string
    {
        $max = 0; foreach ($linhas as $l) foreach ($series as [$k]) $max = max($max, (int) ($l[$k] ?? 0));
        if ($max <= 0) return self::vazio('Sem dados no período', 'Os gráficos aparecem quando houver registros.');
        $W = 560; $H = 200; $pt = 10; $pb = 26; $pl = 28; $n = count($linhas); $gw = ($W - $pl) / $n; $bw = min(22, ($gw - 8) / count($series)); $ih = $H - $pt - $pb; $svg = '';
        $topo = max(4, (int) ceil($max / 4) * 4);
        for ($i = 0; $i <= 4; $i++) { $y = $pt + $ih - $ih * $i / 4; $svg .= '<line class="grid" x1="' . $pl . '" x2="' . $W . '" y1="' . $y . '" y2="' . $y . '"/><text x="' . ($pl - 6) . '" y="' . ($y + 4) . '" text-anchor="end">' . round($topo * $i / 4) . '</text>'; }
        foreach ($linhas as $i => $l) {
            $x0 = $pl + $i * $gw + ($gw - $bw * count($series)) / 2;
            foreach ($series as $j => [$k, $rot, $cor]) { $v = (int) ($l[$k] ?? 0); $h = $ih * $v / $topo; $svg .= '<rect x="' . round($x0 + $j * $bw, 1) . '" y="' . round($pt + $ih - $h, 1) . '" width="' . round($bw - 2, 1) . '" height="' . round($h, 1) . '" rx="3" fill="' . e($cor) . '"><title>' . e($rot . ' ' . $l['rot'] . ': ' . $v) . '</title></rect>'; }
            $svg .= '<text x="' . round($pl + $i * $gw + $gw / 2, 1) . '" y="' . ($H - 8) . '" text-anchor="middle">' . e($l['rot']) . '</text>';
        }
        $lg = implode('', array_map(fn($s) => '<span><i style="background:' . e($s[2]) . '"></i>' . e($s[1]) . '</span>', $series));
        return '<svg class="ui-chart" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="' . e($descricao) . '">' . $svg . '</svg><div class="ui-legend">' . $lg . '</div>';
    }
}
```

### Passo 39 — app.js

#### Arquivo: `public/js/app.js` — parte 1 — CRIAR
**Onde:** criar o arquivo. Parte 1 (núcleo): tema, menu, modais, máscaras e validação. O arquivo termina com `})();`. As partes 2 e 3 serão coladas ANTES dessa última linha, em outras branches.

```javascript
/* ESTAGIA+ - JS das áreas independentes (login, empresa, administração). Sem dependências.
   O backend valida tudo de novo: este arquivo só melhora a experiência. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduz = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- modo noturno (tokens: só troca data-theme) ---------- */
  function tema(t) {
    document.documentElement.setAttribute('data-theme', t);
    try { localStorage.setItem('vg-theme', t); } catch (e) {}
    $$('[data-theme-toggle]').forEach(function (b) { b.setAttribute('aria-pressed', t === 'dark'); b.setAttribute('aria-label', t === 'dark' ? 'Ativar modo claro' : 'Ativar modo noturno'); });
  }
  $$('[data-theme-toggle]').forEach(function (b) {
    b.addEventListener('click', function () { tema(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); });
  });
  tema(document.documentElement.getAttribute('data-theme') || 'light');

  /* ---------- menu lateral (mobile) ---------- */
  var side = $('.app-side'), scrim = $('.app-scrim');
  function menu(a) { if (!side) return; side.classList.toggle('is-open', a); scrim.classList.toggle('is-open', a); }
  $$('[data-menu-open]').forEach(function (b) { b.addEventListener('click', function () { menu(true); }); });
  if (scrim) scrim.addEventListener('click', function () { menu(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') menu(false); });

  /* ---------- modais de confirmação (no lugar do alert/confirm do navegador) ---------- */
  var dlg = $('#ui-dialog');
  function dialogo(o) {
    return new Promise(function (resolve) {
      if (!dlg || !dlg.showModal) {   // navegador sem <dialog>: cai no confirm/prompt nativo
        if (o.motivo) { var m = window.prompt(o.titulo + '\n\nInforme o motivo (mínimo 5 caracteres):'); resolve({ ok: !!m && m.trim().length >= 5, motivo: (m || '').trim() }); }
        else resolve({ ok: window.confirm(o.titulo + '\n\n' + (o.texto || '')), motivo: '' });
        return;
      }
      $('#ui-dlg-title').textContent = o.titulo; $('#ui-dlg-text').textContent = o.texto || '';
      var ta = $('#ui-dlg-motivo'), er = $('#ui-dlg-err'), ok = $('#ui-dlg-ok');
      ta.hidden = !o.motivo; ta.value = ''; er.textContent = ''; ta.classList.remove('is-invalid');
      ok.textContent = o.ok || 'Confirmar'; ok.className = 'ui-btn ' + (o.tom === 'danger' ? 'ui-btn-danger' : 'ui-btn-primary');
      var fim = function (v) { dlg.removeEventListener('close', aoFechar); ok.onclick = null; dlg.close(); resolve(v); };
      var aoFechar = function () { resolve({ ok: false, motivo: '' }); };
      dlg.addEventListener('close', aoFechar, { once: true });
      ok.onclick = function () {
        if (o.motivo && ta.value.trim().length < 5) { er.textContent = 'Informe o motivo (mínimo de 5 caracteres).'; ta.classList.add('is-invalid'); ta.focus(); return; }
        dlg.removeEventListener('close', aoFechar); dlg.close(); resolve({ ok: true, motivo: ta.value.trim() });
      };
      $('#ui-dlg-cancel').onclick = function () { fim({ ok: false, motivo: '' }); };
      dlg.showModal(); (o.motivo ? ta : ok).focus();
    });
  }
  document.addEventListener('submit', function (ev) {
    var f = ev.target;
    if (!f.matches || !(f.matches('form[data-confirm]') || f.matches('form[data-motivo]')) || f.dataset.ok === '1') return;
    ev.preventDefault();
    var motivo = f.hasAttribute('data-motivo');
    dialogo({ titulo: f.dataset.confirmTitle || f.dataset.motivo || 'Confirmar ação', texto: f.dataset.confirm || 'O motivo será enviado à empresa por e-mail.', ok: f.dataset.confirmOk, tom: f.dataset.confirmTone || (motivo ? 'danger' : 'primary'), motivo: motivo })
      .then(function (r) {
        if (!r.ok) return;
        if (motivo) { var h = f.querySelector('input[name=motivo]'); if (h) h.value = r.motivo; }
        f.dataset.ok = '1'; f.submit();
      });
  }, true);

  /* ---------- fechar popovers ao clicar fora ---------- */
  document.addEventListener('click', function (e) { $$('details.ui-bell[open]').forEach(function (d) { if (!d.contains(e.target)) d.open = false; }); });

  /* ---------- copiar ---------- */
  $$('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.dataset.copy, o = b.innerHTML, ok = function () { b.textContent = 'Copiado'; setTimeout(function () { b.innerHTML = o; }, 1500); };
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(t).then(ok);
    });
  });

  /* ---------- máscaras ---------- */
  function maskCnpj(v) {   // CNPJ alfanumérico (IN RFB 2.229/2024): 12 primeiros letras/números, 2 últimos dígitos
    v = v.replace(/[^0-9A-Za-z]/g, '').toUpperCase(); var base = '';
    for (var k = 0; k < v.length && base.length < 14; k++) { if (base.length >= 12 && !/\d/.test(v[k])) continue; base += v[k]; }
    var o = ''; for (var i = 0; i < base.length; i++) { if (i === 2 || i === 5) o += '.'; if (i === 8) o += '/'; if (i === 12) o += '-'; o += base[i]; } return o;
  }
  function maskTel(v) { var d = v.replace(/\D/g, '').slice(0, 11), cut = d.length > 10 ? 5 : 4, r = d.slice(2); return d.slice(0, 2) + (r ? ' ' + r.slice(0, cut) : '') + (r.length > cut ? '-' + r.slice(cut) : ''); }
  function maskMoney(v) { v = v.replace(/[^\d,]/g, ''); var p = v.split(','); return p.length > 1 ? p[0] + ',' + p.slice(1).join('').slice(0, 2) : p[0]; }
  function maskCep(v) { var d = v.replace(/\D/g, '').slice(0, 8); return d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d; }
  function maskInt(v) { return v.replace(/\D/g, '').slice(0, 2); }
  var MASC = { cnpj: maskCnpj, tel: maskTel, money: maskMoney, cep: maskCep, int: maskInt };
  $$('[data-mask]').forEach(function (i) { var f = MASC[i.dataset.mask]; i.addEventListener('input', function () { i.value = f(i.value); }); if (i.value) i.value = f(i.value); });
  function cnpjValido(v) {
    var c = v.replace(/[^0-9A-Za-z]/g, '').toUpperCase();
    if (!/^[A-Z0-9]{12}[0-9]{2}$/.test(c) || /^(.)\1{13}$/.test(c)) return false;
    function dv(b) { var s = 0, p = 2; for (var i = b.length - 1; i >= 0; i--) { s += (b.charCodeAt(i) - 48) * p; p = p === 9 ? 2 : p + 1; } var r = s % 11; return r < 2 ? 0 : 11 - r; }
    var d1 = dv(c.slice(0, 12)); return d1 === +c[12] && dv(c.slice(0, 12) + d1) === +c[13];
  }
  function urlOk(v, host) {
    v = v.trim(); if (!v) return true; if (!/^[a-z][a-z0-9+.-]*:/i.test(v)) v = 'https://' + v;
    try { var u = new URL(v); if (!/^https?:$/.test(u.protocol) || !/^[a-z0-9.-]+\.[a-z]{2,}$/i.test(u.hostname) || u.username) return false; if (host && !new RegExp('(^|\\.)' + host.replace('.', '\\.') + '$', 'i').test(u.hostname)) return false; return true; } catch (e) { return false; }
  }
  var senhaForte = function (s) { return s.length >= 8 && /[A-Za-z]/.test(s) && /\d/.test(s); };

  /* ---------- validação ---------- */
  function wrapErr(c) { var b = c.closest('.ui-field') || c.parentNode; return { box: b, err: b && b.querySelector('[data-err]') }; }
  function erro(c, msg) {
    var w = wrapErr(c), alvo = c.matches('textarea[data-chips-required]') ? $('.ui-chips', w.box) : c;
    if (alvo) alvo.classList.toggle('is-invalid', !!msg); c.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (w.err) w.err.textContent = msg || ''; return !msg;
  }
  function valida(c) {
    if (c.type === 'hidden' || c.disabled || c.closest('[hidden]')) return true;
    var v = (c.value || '').trim();
    if (c.type === 'checkbox' && c.hasAttribute('data-need-one')) return true;
    if (c.type === 'checkbox') return erro(c, c.required && !c.checked ? 'Confirme para continuar.' : '');
    if (c.type === 'radio') return true;
    if (c.type === 'file') {
      var f = c.files[0]; if (!f) return erro(c, c.required ? 'Anexe um arquivo.' : '');
      var ok = c.hasAttribute('data-img-only') ? ['image/jpeg', 'image/png'] : ['application/pdf', 'image/jpeg', 'image/png'];
      if (ok.indexOf(f.type) < 0) return erro(c, 'Formato não permitido.');
      if (f.size > (+c.dataset.max || 5) * 1048576) return erro(c, 'Arquivo maior que ' + (c.dataset.max || 5) + ' MB.'); return erro(c, '');
    }
    if (c.required && !v) return erro(c, c.tagName === 'SELECT' ? 'Selecione uma opção.' : 'Preencha este campo.');
    if (v && c.minLength > 0 && v.length < c.minLength && c.name !== 'senha') return erro(c, 'Mínimo de ' + c.minLength + ' caracteres.');
    if (c.dataset.mask === 'cnpj' && v && !cnpjValido(v)) return erro(c, 'CNPJ inválido. Confira os 14 caracteres.');
    if (c.dataset.mask === 'tel' && v && v.replace(/\D/g, '').length < 10) return erro(c, 'Telefone incompleto.');
    if (c.dataset.mask === 'cep' && v && v.replace(/\D/g, '').length !== 8) return erro(c, 'CEP incompleto. Informe os 8 números.');
    if (c.type === 'email' && v && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v)) return erro(c, 'E-mail inválido.');
    if (c.hasAttribute('data-url') && v) {   // domínio exigido: fixo (data-host) ou pela plataforma escolhida (data-host-from)
      var plat = c.dataset.hostFrom ? ($('[name=' + c.dataset.hostFrom + ']', c.form) || {}).value : '';
      var host = c.dataset.host || ({ gupy: 'gupy.io', linkedin: 'linkedin.com' }[plat] || '');
      if (!urlOk(v, host)) return erro(c, host ? 'Use um endereço de ' + host + '.' : 'Endereço inválido. Exemplo: https://www.empresa.com.br');
      if (c.dataset.hostFrom && !plat) return erro(c, 'Escolha onde o processo seletivo acontece.');
    }
    if (c.hasAttribute('data-pwd') && v && !senhaForte(v)) return erro(c, 'Use 8 ou mais caracteres, com letras e números.');
    if (c.name === 'senha2' && v !== ($('[name=senha]', c.form).value)) return erro(c, 'As senhas não conferem.');
    if (c.dataset.after) { var o = $('[name=' + c.dataset.after + ']', c.form); if (o && o.value && v && v <= o.value) return erro(c, 'A data final deve ser depois da inicial.'); }
    if (c.dataset.minDate && v && v < c.dataset.minDate) return erro(c, 'Escolha uma data de hoje em diante.');
    if (c.dataset.max && c.type === 'text' && c.dataset.mask === 'int' && v && (+v < +c.dataset.min || +v > +c.dataset.max)) return erro(c, 'Informe de ' + c.dataset.min + ' a ' + c.dataset.max + '.');
    if (c.hasAttribute('data-chips-required') && !v) return erro(c, 'Adicione ao menos um item.');
    if (c.dataset.moneyReq !== undefined) { var tipo = $('[name=remuneracao_tipo]:checked', c.form); if (tipo && tipo.value === 'valor' && (!v || parseFloat(v.replace(',', '.')) <= 0)) return erro(c, 'Informe o valor mensal ou escolha "A combinar".'); }
    return erro(c, '');
  }
  function validaGrupos(raiz) {
    var ok = true;
    $$('[data-need-one]', raiz).forEach(function (g) {
      if (g.closest('[hidden]')) return;
      var tem = $$('input[type=checkbox]', g).some(function (i) { return i.checked; }), e = g.parentNode.querySelector('[data-err]');
      if (e) e.textContent = tem ? '' : (g.dataset.needOne || 'Marque ao menos uma opção.'); if (!tem) ok = false;
    });
    return ok;
  }
  function foca(c) { var w = c.closest && c.closest('[data-chips]'); var t = c.matches && c.matches('textarea') && w ? $('.ui-chips input', w) : c; if (t && t.focus) t.focus(); }
  function camposDe(raiz) { return $$('input:not([type=hidden]):not([type=submit]),select,textarea', raiz); }
  $$('[data-form]').forEach(function (form) {
    camposDe(form).forEach(function (c) {
      c.addEventListener('blur', function () { valida(c); });
      c.addEventListener('input', function () { if (c.classList.contains('is-invalid') || (c.closest('.ui-field') || {}).querySelector && c.closest('.ui-field').querySelector('.is-invalid')) valida(c); });   // erro some enquanto digita (sem mudar o layout no clique)
      c.addEventListener('change', function () { if (c.classList.contains('is-invalid') || (c.parentNode.querySelector('[data-err]') || {}).textContent) valida(c); });
    });
    form.addEventListener('submit', function (ev) {
      if (form.dataset.ok === '1') return;
      var primeiro = null;
      camposDe(form).forEach(function (c) { if (!valida(c) && !primeiro) primeiro = c; });
      if (!validaGrupos(form) && !primeiro) primeiro = $('[data-need-one] input', form);
      if (primeiro) { ev.preventDefault(); if (form._wizGo) form._wizGo(primeiro); else foca(primeiro); return; }
      var b = $('button[type=submit]:not([hidden])', form); if (b) { b.disabled = true; b.textContent = 'Enviando...'; }
    });
  });

})();
```

### Passo 40 — timeline.php

#### Arquivo: `Views/partials/timeline.php` — CRIAR

```php
<?php /** Histórico (auditoria). Espera $hist. */
$quem = ['admin' => 'Coordenação', 'empresa' => 'Empresa', 'sistema' => 'Sistema']; ?>
<?php if (!$hist): ?><p class="ui-sub">Sem registros ainda.</p><?php else: ?>
<ol class="ui-timeline"><?php foreach ($hist as $h): ?>
  <li><strong><?= e(Historico::rotulo($h)) ?></strong><small><?= e($quem[$h['ator_tipo']] ?? $h['ator_tipo']) ?> · <?= e(date('d/m/Y H:i', strtotime($h['criado_em']))) ?></small><?php if ($h['detalhe']): ?><small style="color:var(--text)"><?= e($h['detalhe']) ?></small><?php endif; ?></li>
<?php endforeach; ?></ol><?php endif; ?>
```

### Passo 41 — vaga_detalhe.php

#### Arquivo: `Views/partials/vaga_detalhe.php` — CRIAR

```php
<?php /** Dados completos da vaga (empresa e admin). Espera $v. */ ?>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Informações</h2><dl class="ui-kv">
    <dt>Código</dt><dd><span class="ui-code"><?= e($v['codigo']) ?></span> <small class="ui-hint" style="display:inline">versão <?= (int) $v['versao'] ?></small></dd>
    <dt>Tipo</dt><dd><?= e($v['tipo']) ?></dd><dt>Área</dt><dd><?= e($v['area']) ?></dd><dt>Modalidade</dt><dd><?= e($v['modalidade']) ?></dd><dt>Local</dt><dd><?= e($v['local']) ?></dd>
    <dt>Remuneração</dt><dd><?= e($v['bolsa_txt']) ?></dd><dt>Carga horária</dt><dd><?= e($v['carga_txt'] ?: 'Não informada') ?></dd>
    <dt>Período</dt><dd><?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?> <small class="ui-hint" style="display:inline">(<?= e(dias_txt($v['dias'])) ?>)</small></dd>
  </dl></div>
  <div class="ui-card"><h2>Candidatura e links</h2><dl class="ui-kv">
    <dt>Processo seletivo</dt><dd><?= e(Link::PLATAFORMAS[$v['link_plataforma']] ?? 'Por e-mail') ?></dd>
    <dt>Link de candidatura</dt><dd><?= $v['link_candidatura'] ? '<a href="' . e($v['link_candidatura']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($v['link_candidatura'])) . '</a>' : '-' ?></dd>
    <dt>E-mail</dt><dd><?= e($v['contato'] ?: '-') ?></dd>
    <dt>Site</dt><dd><?= $v['link_site'] ? '<a href="' . e($v['link_site']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($v['link_site'])) . '</a>' : '-' ?></dd>
    <dt>LinkedIn</dt><dd><?= $v['link_linkedin'] ? '<a href="' . e($v['link_linkedin']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($v['link_linkedin'])) . '</a>' : '-' ?></dd>
  </dl></div>
</div>
<div class="ui-card" style="margin-top:16px"><h2>Descrição</h2><p style="margin:0"><?= nl2br(e($v['descricao'])) ?></p></div>
<div class="ui-grid-2" style="margin-top:16px">
  <div class="ui-card"><h2>Requisitos obrigatórios</h2><?php if ($v['requisitos']): ?><ul class="ui-list"><?php foreach ($v['requisitos'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php else: ?><p class="ui-sub">Não informado.</p><?php endif; ?>
    <?php if ($v['requisitos_desejaveis']): ?><h2 style="margin-top:14px">Desejáveis</h2><ul class="ui-list"><?php foreach ($v['requisitos_desejaveis'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?></div>
  <div class="ui-card"><h2>Benefícios</h2><?php if ($v['beneficios']): ?><ul class="ui-pills"><?php foreach ($v['beneficios'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php else: ?><p class="ui-sub">Não informado.</p><?php endif; ?>
    <?php if ($v['atividades']): ?><h2 style="margin-top:14px">Atividades</h2><ul class="ui-list"><?php foreach ($v['atividades'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?></div>
</div>
```

### Passo 42 — vaga_card_app.php

#### Arquivo: `Views/partials/vaga_card_app.php` — CRIAR

```php
<?php
/** Card de vaga na área logada. Espera $v e $ctx ('empresa'|'admin'); admin: $ret (rota de retorno das ações). */
$ctx = $ctx ?? 'empresa'; $ret = $ret ?? 'admin-vagas'; $sit = $v['situacao'];
$ver = $ctx === 'admin' ? url('admin-vaga', ['id' => $v['id']]) : url('empresa-vaga', ['id' => $v['id']]);
$cls = $sit === 'publicada' ? ($v['dias'] <= DIAS_VAGA_VENCENDO ? 'is-warn' : '') : 'is-off';
?>
<article class="ui-job">
  <div class="ui-job-top"><?= Ui::situacao($sit) ?><span class="ui-badge ui-badge-info"><?= e($v['tipo']) ?></span></div>
  <div><h3><a href="<?= e($ver) ?>"><?= e($v['titulo']) ?></a></h3>
    <?php if ($ctx === 'admin'): ?><div class="ui-job-co"><?= Ui::logo($v['empresa'], 22) ?> <a href="<?= url('admin-empresa', ['id' => $v['empresa_id']]) ?>" style="color:inherit"><?= e($v['empresa']['nome']) ?></a></div><?php endif; ?></div>
  <ul class="ui-job-meta">
    <li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
    <li><?= icon('money', 15) ?> <?= e($v['bolsa_txt']) ?></li>
    <li><?= icon('clock', 15) ?> <?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?></li>
    <?php if (in_array($sit, ['publicada', 'expirada'], true)): ?><li class="ui-job-days <?= $cls ?>"><?= icon('trending', 15) ?> <?= e(dias_txt($v['dias'])) ?></li><?php endif; ?>
    <?php if ($sit === 'agendada'): ?><li class="ui-job-days"><?= icon('clock', 15) ?> Entra no ar em <?= data_br($v['data_publicacao']) ?></li><?php endif; ?>
  </ul>
  <?php if ($sit === 'rejeitada' && $v['motivo']): ?><div class="ui-job-flag">Motivo: <?= e($v['motivo']) ?></div><?php endif; ?>
  <?php if (!empty($v['destaque']) && $sit === 'publicada'): ?><span class="ui-badge ui-badge-ok" style="align-self:flex-start">Em destaque</span><?php endif; ?>
  <div class="ui-job-foot">
    <a class="ui-btn ui-btn-sm" href="<?= e($ver) ?>"><?= icon('eye', 15) ?> Ver</a>
    <?php if ($ctx === 'empresa'): ?>
      <?php if ($v['status'] !== 'encerrada'): ?><a class="ui-btn ui-btn-sm" href="<?= url('vaga-editar', ['id' => $v['id']]) ?>"><?= icon('edit', 15) ?> Editar</a><?php endif; ?>
      <?php if ($v['status'] === 'aprovada'): echo Ui::formConfirmar(url('vaga-encerrar'), ['id' => $v['id']], 'Encerrar vaga', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de aparecer para os estudantes imediatamente. Ela continua guardada no seu histórico e não será excluída.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o'); endif; ?>
    <?php else: echo Ui::acoesVagaAdmin($v, $ret); endif; ?>
  </div>
</article>
```

### Passo 43 — empresa_card_app.php

#### Arquivo: `Views/partials/empresa_card_app.php` — CRIAR

```php
<?php /** Card de empresa no admin. Espera $e (com total_vagas/vagas_abertas). */ ?>
<article class="ui-comp ui-job">
  <div class="ui-comp-head"><?= Ui::logo($e, 52) ?><div><h3><a href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a></h3><p><?= e(Empresa::segmentoTxt($e)) ?></p></div></div>
  <div class="ui-actions"><?= Ui::statusEmpresa($e['status']) ?><?php if ($e['cidade']): ?><span class="ui-badge ui-badge-mute"><?= e($e['cidade'] . '/' . $e['uf']) ?></span><?php endif; ?></div>
  <ul class="ui-job-meta"><li><?= icon('briefcase', 15) ?> <?= (int) $e['total_vagas'] ?> vaga(s), <?= (int) $e['vagas_abertas'] ?> no ar</li><li><?= icon('clock', 15) ?> Cadastrada em <?= data_br(substr($e['criado_em'], 0, 10)) ?></li>
    <li><?= icon('user', 15) ?> <?= e($e['responsavel']) ?></li></ul>
  <div class="ui-job-foot"><a class="ui-btn ui-btn-sm" href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= icon('eye', 15) ?> Abrir</a><?= Ui::acoesEmpresaAdmin($e, 'admin-empresas') ?></div>
</article>
```

### Passo 44 — Verificar a branch

```bash
php -r 'require "config.php"; echo Ui::badge("Teste","ok"), "\n";'
for f in Views/layouts/*.php Views/partials/*.php Views/erro.php app/Ui.php; do php -l $f; done
```

Resultado esperado:
- `php -r 'require "config.php"; echo Ui::badge("Teste","ok"), "\n";'…` → <span class="ui-badge ui-badge-ok">Teste</span>

### Passo 45 — Fechar a branch

```bash
git add .
git commit -m "feat(design-system): tokens, modo noturno, layouts auth/app, componentes e núcleo do app.js"
git push -u origin feature/design-system
```
Abra o Pull Request `feature/design-system` → `develop` e peça revisão a **Rodrigo**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 6: `feature/notifications` — dono: Pedro Miguel

E-mails: remetente único (log, mail ou SMTP), avisos ao administrador e à empresa, notificações do painel.

**Revisor do PR:** Pedro Henri. **Depende de:** `feature/database-company`, `feature/backend-core`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `107.01` | Configurar o envio de e-mail (log, mail ou SMTP) com remetente único | 2 | 1 |
| `107.02` | Avisos ao administrador: nova empresa e nova vaga (e-mail e notificação no painel) | 2 | 5 |
| `107.03` | E-mails à empresa: recebimento, aprovação, reprovação com motivo e bloqueio | 2 | 9 |
| `107.04` | E-mails de vaga: aprovada (com período no ar), rejeitada, vencendo, expirada e editada em reaprovação | 3 | 10 |

```bash
git checkout develop && git pull
git checkout -b feature/notifications
```

### Passo 46 — Mailer.php

#### Arquivo: `app/Mailer.php` — CRIAR
Modo `log` só grava em `storage/mail/`; para enviar de verdade use `MAIL_DRIVER=smtp` (veja o `.env.example`).

```php
<?php
/**
 * Envio de e-mail. MAIL_DRIVER no .env:
 *  log  -> não envia; grava em storage/mail/*.html (desenvolvimento)
 *  mail -> função mail() do PHP (funciona na maioria dos cPanel)
 *  smtp -> SMTP próprio (SMTP_HOST/PORT/USER/PASS/SECURE=ssl|tls|none)
 * Toda tentativa fica registrada na tabela emails_log. Falha de e-mail nunca derruba a ação do usuário.
 */
class Mailer
{
    public static int $falhas = 0;          // quantos envios falharam nesta requisição
    public static ?string $ultimoErro = null;

    public static function modo(): string { return strtolower((string) env('MAIL_DRIVER', 'log')); }

    public static function recentes(int $n = 10): array { return Database::all('SELECT * FROM emails_log ORDER BY id DESC LIMIT ' . (int) $n); }

    public static function enviar(string $para, string $assunto, string $html): bool
    {
        $driver = strtolower((string) env('MAIL_DRIVER', 'log'));
        $de = (string) env('MAIL_FROM', 'vagas@localhost');
        $nomeDe = (string) env('MAIL_FROM_NAME', 'Vagas de Estágio - IFSP Guarulhos');
        $erro = null; $ok = false;
        try {
            if ($driver === 'smtp') { $ok = self::smtp($para, $assunto, $html, $de, $nomeDe); }
            elseif ($driver === 'mail') {
                $h = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: " . self::nome($nomeDe) . " <$de>\r\n" . self::replyTo();
                $ok = @mail($para, self::assunto($assunto), $html, $h);
                if (!$ok) $erro = 'mail() falhou: este servidor não tem envio local configurado. Use MAIL_DRIVER=smtp.';
            } else {
                if (!is_dir(ROOT_PATH . '/storage/mail')) mkdir(ROOT_PATH . '/storage/mail', 0755, true);
                $arq = ROOT_PATH . '/storage/mail/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.html';
                file_put_contents($arq, "<!-- Para: $para | Assunto: $assunto -->\n$html");
                $ok = true;
            }
        } catch (Throwable $t) { $erro = $t->getMessage(); }
        if (!$ok) { self::$falhas++; self::$ultimoErro = $erro; }
        try {
            Database::insert('emails_log', ['destinatario' => $para, 'assunto' => $assunto,
                'status' => $ok ? ($driver === 'log' ? 'simulado' : 'enviado') : 'falha', 'erro' => $erro ? mb_substr($erro, 0, 250) : null, 'criado_em' => Database::agora()]);
        } catch (Throwable $t) { /* log é best-effort */ }
        return $ok;
    }

    /** Layout padrão dos e-mails (verde do IF). $botao = [texto, url] */
    public static function layout(string $titulo, string $corpoHtml, ?array $botao = null): string
    {
        $b = $botao ? '<p style="margin:22px 0"><a href="' . e($botao[1]) . '" style="background:#2f9e41;color:#fff;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:8px;display:inline-block">' . e($botao[0]) . '</a></p>' : '';
        return '<!DOCTYPE html><html lang="pt-br"><body style="margin:0;background:#f3f6f3;font-family:Arial,Helvetica,sans-serif;color:#222">'
            . '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">'
            . '<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #dfe7e1">'
            . '<tr><td style="background:#1f6f2d;color:#fff;padding:18px 24px;font-size:13px;letter-spacing:.04em">IFSP CAMPUS GUARULHOS · VAGAS</td></tr>'
            . '<tr><td style="padding:24px"><h1 style="font-size:20px;margin:0 0 14px;color:#1f6f2d">' . e($titulo) . '</h1>'
            . '<div style="font-size:15px;line-height:1.55">' . $corpoHtml . '</div>' . $b . '</td></tr>'
            . '<tr><td style="padding:14px 24px;background:#f6f8f6;font-size:12px;color:#5d6b62">Mensagem automática do sistema de vagas. Não responda a este e-mail.</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

    /** Cabeçalho Reply-To (opcional): MAIL_REPLY_TO=coordenacao.gru@ifsp.edu.br */
    private static function replyTo(): string
    {
        $r = trim((string) env('MAIL_REPLY_TO', ''));
        return filter_var($r, FILTER_VALIDATE_EMAIL) ? "Reply-To: <$r>\r\n" : '';
    }

    private static function nome(string $s): string { return '=?UTF-8?B?' . base64_encode($s) . '?='; }
    private static function assunto(string $s): string { return '=?UTF-8?B?' . base64_encode($s) . '?='; }

    private static function smtp(string $para, string $assunto, string $html, string $de, string $nomeDe): bool
    {
        $host = (string) env('SMTP_HOST'); $porta = (int) env('SMTP_PORT', 587); $sec = strtolower((string) env('SMTP_SECURE', 'tls'));
        if ($host === '') throw new RuntimeException('SMTP_HOST não está configurado no .env');
        // SMTP_VERIFY_TLS=0 desliga a checagem do certificado (só para teste local no Windows/XAMPP sem bundle de CAs)
        $verifica = !in_array(strtolower((string) env('SMTP_VERIFY_TLS', '1')), ['0', 'false', 'no'], true);
        $ctx = stream_context_create(['ssl' => ['verify_peer' => $verifica, 'verify_peer_name' => $verifica, 'allow_self_signed' => !$verifica]]);
        $s = @stream_socket_client(($sec === 'ssl' ? 'ssl://' : 'tcp://') . "$host:$porta", $en, $es, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$s) throw new RuntimeException("SMTP: não conectou em $host:$porta ($es). Confira SMTP_HOST, SMTP_PORT e SMTP_SECURE.");
        stream_set_timeout($s, 15);
        $ler = function () use ($s) { $r = ''; while (($l = fgets($s, 515)) !== false) { $r .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $r; };
        $cmd = function (string $c, string $esp) use ($s, $ler) {
            fwrite($s, $c . "\r\n"); $r = $ler();
            if (strpos($r, $esp) !== 0) throw new RuntimeException('SMTP: ' . trim($r));
        };
        if (strpos($ler(), '220') !== 0) throw new RuntimeException('SMTP: servidor não respondeu');
        $ehlo = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
        $cmd("EHLO $ehlo", '250');
        if ($sec === 'tls') {
            $cmd('STARTTLS', '220');
            if (!stream_socket_enable_crypto($s, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('SMTP: TLS falhou');
            $cmd("EHLO $ehlo", '250');
        }
        if (env('SMTP_USER')) {
            $cmd('AUTH LOGIN', '334'); $cmd(base64_encode(env('SMTP_USER')), '334'); $cmd(base64_encode((string) env('SMTP_PASS')), '235');
        }
        $cmd("MAIL FROM:<$de>", '250'); $cmd("RCPT TO:<$para>", '25'); $cmd('DATA', '354');
        $msg = self::replyTo() . "Date: " . date('r') . "\r\nMessage-ID: <" . bin2hex(random_bytes(8)) . '@' . $ehlo . ">\r\nFrom: " . self::nome($nomeDe) . " <$de>\r\nTo: <$para>\r\nSubject: " . self::assunto($assunto)
            . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html));
        fwrite($s, $msg . "\r\n.\r\n");
        $r = $ler(); if (strpos($r, '250') !== 0) throw new RuntimeException('SMTP: ' . trim($r));
        fwrite($s, "QUIT\r\n"); fclose($s);
        return true;
    }
}
```

### Passo 47 — Notificacao.php

#### Arquivo: `Models/Notificacao.php` — CRIAR

```php
<?php
class Notificacao
{
    public static function naoLidas(): int { return (int) Database::val('SELECT COUNT(*) FROM notificacoes WHERE lida = 0'); }
    public static function recentes(int $n = 8): array { return Database::all('SELECT * FROM notificacoes ORDER BY id DESC LIMIT ' . (int) $n); }
    public static function marcarLidas(): void { Database::run('UPDATE notificacoes SET lida = 1 WHERE lida = 0'); }
}
```

### Passo 48 — Notificador.php

#### Arquivo: `app/Notificador.php` — CRIAR

```php
<?php
/** Central de avisos: e-mail + notificação dentro do painel do administrador. */
class Notificador
{
    private static function emailsAdmin(): array
    {
        $l = array_filter(array_map('trim', explode(',', (string) env('ADMIN_NOTIFY_EMAIL', ''))));
        if (!$l) $l = array_column(Database::all('SELECT email FROM admins'), 'email');
        return array_values(array_unique($l));
    }
    private static function paraAdmins(string $assunto, string $html): void
    {
        foreach (self::emailsAdmin() as $e) Mailer::enviar($e, $assunto, $html);
    }
    public static function interna(string $tipo, int $ref, string $msg): void
    {
        Database::insert('notificacoes', ['tipo' => $tipo, 'referencia_id' => $ref, 'mensagem' => $msg, 'lida' => 0, 'criado_em' => Database::agora()]);
    }

    // ---- empresa ----
    public static function empresaCadastrada(array $emp): void
    {
        self::interna('empresa', (int) $emp['id'], 'Nova empresa para aprovar: ' . $emp['nome']);
        self::paraAdmins('Nova empresa aguardando aprovação: ' . $emp['nome'], Mailer::layout('Nova empresa cadastrada',
            '<p>A empresa <strong>' . e($emp['nome']) . '</strong> (CNPJ ' . e(Cnpj::formatar($emp['cnpj'])) . ') pediu cadastro e aguarda análise.</p>'
            . '<p>Responsável: ' . e($emp['responsavel']) . '<br>E-mail: ' . e($emp['email']) . '<br>Telefone: ' . e(fone_fmt($emp['telefone'])) . '</p>',
            ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($emp['email'], 'Recebemos o cadastro da sua empresa', Mailer::layout('Cadastro recebido',
            '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>Recebemos o cadastro da <strong>' . e($emp['nome']) . '</strong>. A Coordenação vai analisar os dados e o termo de cooperação. Você receberá um e-mail com o resultado.</p>'));
    }

    public static function empresaAvaliada(array $emp): void
    {
        if ($emp['status'] === Estado::E_BLOQUEADA) {
            Mailer::enviar($emp['email'], 'Conta bloqueada', Mailer::layout('Conta bloqueada',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O acesso da <strong>' . e($emp['nome']) . '</strong> foi bloqueado pela Coordenação e as vagas deixaram de aparecer no site.</p>'
                . '<p><strong>Motivo:</strong><br>' . nl2br(e((string) $emp['motivo'])) . '</p><p>Para esclarecimentos, fale com a Coordenação.</p>'));
            return;
        }
        if ($emp['status'] === Estado::E_PENDENTE) return;
        if ($emp['status'] === 'aprovada') {
            Mailer::enviar($emp['email'], 'Cadastro aprovado: sua empresa já pode publicar vagas', Mailer::layout('Cadastro aprovado',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O cadastro da <strong>' . e($emp['nome']) . '</strong> foi <strong>aprovado</strong>. Entre na área da empresa para publicar suas vagas.</p>',
                ['Acessar área da empresa', url_abs('empresa-login')]));
        } else {
            Mailer::enviar($emp['email'], 'Cadastro não aprovado', Mailer::layout('Cadastro não aprovado',
                '<p>Olá, ' . e($emp['responsavel']) . '!</p><p>O cadastro da <strong>' . e($emp['nome']) . '</strong> não foi aprovado.</p>'
                . '<p><strong>Motivo informado pela Coordenação:</strong><br>' . nl2br(e((string) $emp['motivo'])) . '</p><p>Você pode se cadastrar novamente com os dados corrigidos.</p>'));
        }
    }

    // ---- vaga ----
    public static function vagaEnviada(array $vaga, array $emp): void
    {
        self::interna('vaga', (int) $vaga['id'], 'Nova vaga para aprovar: ' . $vaga['titulo'] . ' (' . $emp['nome'] . ')');
        self::paraAdmins('Nova vaga aguardando aprovação: ' . $vaga['titulo'], Mailer::layout('Nova vaga enviada',
            '<p><strong>' . e($vaga['titulo']) . '</strong> · ' . e($vaga['tipo']) . ' · ' . e($vaga['area']) . '<br>Empresa: ' . e($emp['nome'])
            . '<br>No ar de ' . data_br($vaga['data_publicacao']) . ' até ' . data_br($vaga['data_limite']) . '</p>', ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($emp['email'], 'Vaga recebida: ' . $vaga['titulo'], Mailer::layout('Vaga enviada para análise',
            '<p>Recebemos a vaga <strong>' . e($vaga['titulo']) . '</strong> (código ' . e($vaga['codigo']) . '). Assim que a Coordenação aprovar, avisaremos por e-mail com as datas em que ela ficará no ar.</p>'));
    }

    /** Empresa editou vaga já aprovada/rejeitada: volta para análise. */
    public static function vagaReenviada(array $vaga, array $emp): void
    {
        self::interna('vaga', (int) $vaga['id'], 'Vaga editada aguarda nova aprovação: ' . $vaga['titulo'] . ' (' . $emp['nome'] . ')');
        self::paraAdmins('Vaga editada aguarda nova aprovação: ' . $vaga['titulo'], Mailer::layout('Vaga editada',
            '<p>A empresa <strong>' . e($emp['nome']) . '</strong> alterou a vaga <strong>' . e($vaga['titulo']) . '</strong> (' . e($vaga['codigo']) . '). Ela saiu do ar e precisa de nova aprovação.</p>', ['Analisar no painel', url_abs('admin-aprovacoes')]));
        Mailer::enviar($emp['email'], 'Vaga em nova análise: ' . $vaga['titulo'], Mailer::layout('Vaga em nova análise',
            '<p>As alterações na vaga <strong>' . e($vaga['titulo']) . '</strong> foram recebidas. Ela só volta ao ar depois de aprovada pela Coordenação.</p>'));
    }

    public static function vagaAvaliada(array $vaga, array $emp): void
    {
        if ($vaga['status'] === 'aprovada') {
            $hoje = date('Y-m-d');
            $quando = $vaga['data_publicacao'] <= $hoje ? 'já está no ar' : 'entra no ar em ' . data_br($vaga['data_publicacao']);
            Mailer::enviar($emp['email'], 'Vaga aprovada: ' . $vaga['titulo'], Mailer::layout('Vaga aprovada',
                '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> foi <strong>aprovada</strong> e ' . $quando . '.</p>'
                . '<p>Ela ficará no ar <strong>até ' . data_br($vaga['data_limite']) . '</strong> e sai sozinha depois dessa data. Código da vaga: <strong>' . e($vaga['codigo']) . '</strong>.</p>',
                ['Ver vaga no site', url_abs('vaga', ['id' => $vaga['id']])]));
        } else {
            Mailer::enviar($emp['email'], 'Vaga não aprovada: ' . $vaga['titulo'], Mailer::layout('Vaga não aprovada',
                '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> não foi aprovada.</p><p><strong>Motivo:</strong><br>' . nl2br(e((string) $vaga['motivo'])) . '</p><p>Você pode corrigir e publicar novamente.</p>'));
        }
    }

    public static function vagaVencendo(array $vaga, array $emp): void
    {
        Mailer::enviar($emp['email'], 'Sua vaga encerra em breve: ' . $vaga['titulo'], Mailer::layout('Vaga perto do fim do prazo',
            '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> sai do ar em <strong>' . data_br($vaga['data_limite']) . '</strong>. Se ainda precisar de candidatos, publique uma nova vaga com novo prazo.</p>', ['Publicar vaga', url_abs('vaga-cadastro')]));
    }

    public static function vagaEncerrada(array $vaga, array $emp): void
    {
        Mailer::enviar($emp['email'], 'Vaga encerrada: ' . $vaga['titulo'], Mailer::layout('Vaga encerrada',
            '<p>A vaga <strong>' . e($vaga['titulo']) . '</strong> (código ' . e($vaga['codigo']) . ') saiu do ar.</p>'));
    }
}
```

### Passo 49 — Verificar a branch

```bash
MAIL_DRIVER=log php -r 'require "config.php"; require_once "database/schema.php"; migrar(); var_dump(Mailer::enviar("a@b.com","Teste","<p>oi</p>"));' && ls storage/mail
```

Resultado esperado:
- `MAIL_DRIVER=log php -r 'require "config.php"; require_once "database/s…` → bool(true) e um arquivo .html em storage/mail

### Passo 50 — Fechar a branch

```bash
git add .
git commit -m "feat(email): remetente único, avisos de cadastro e vagas, notificações no painel"
git push -u origin feature/notifications
```
Abra o Pull Request `feature/notifications` → `develop` e peça revisão a **Pedro Henri**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 7: `feature/backend-auth` — dono: José Roberto

Autenticação: contextos empresa × admin exclusivos, sessão, limite de tentativas, cadastro da empresa e administrador inicial.

**Revisor do PR:** Pedro Miguel. **Depende de:** `feature/security`, `feature/database-company`, `feature/backend-core`, `feature/notifications`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `105.01` | Validar com o demandante os dados mínimos do cadastro da empresa | 1 | 1 |
| `106.02` | Implementar Auth: login, logout, sessão e regeneração do ID para empresa e administrador | 3 | 2 |
| `106.03` | Bloquear o perfil cruzado no backend (rotas e POST) e registrar a tentativa | 2 | 2 |
| `106.04` | Expiração de sessão por inatividade e por tempo total | 1 | 3 |
| `106.05` | Limite de tentativas de login e registro de acessos (acessos_log) | 2 | 3 |
| `105.02` | Validação no backend do cadastro da empresa (CNPJ alfanumérico, senha, links, uploads) | 3 | 4 |
| `105.10` | Aviso "empresa aprovada" controlado pelo backend | 1 | 8 |
| `105.11` | Reenvio para análise de cadastro reprovado | 1 | 8 |

```bash
git checkout develop && git pull
git checkout -b feature/backend-auth
```

### Passo 51 — Admin.php

#### Arquivo: `Models/Admin.php` — CRIAR

```php
<?php
class Admin
{
    public static function porId(int $id): ?array { return Database::one('SELECT * FROM admins WHERE id = :i', ['i' => $id]); }
    public static function porEmail(string $e): ?array { return Database::one('SELECT * FROM admins WHERE email = :e', ['e' => mb_strtolower($e)]); }
    public static function todos(): array { return Database::all('SELECT id, nome, email, papel, ativo, criado_em, ultimo_acesso FROM admins ORDER BY id'); }
    public static function criar(string $nome, string $email, string $senha): int
    {
        return Database::insert('admins', ['nome' => $nome, 'email' => mb_strtolower($email), 'senha_hash' => Auth::hash($senha), 'papel' => 'admin', 'ativo' => 1, 'criado_em' => Database::agora()]);
    }
    public static function definirAtivo(int $id, bool $ativo): void { Database::update('admins', $id, ['ativo' => $ativo ? 1 : 0]); }
}
```

### Passo 52 — Empresa.php

#### Arquivo: `Models/Empresa.php` — CRIAR

```php
<?php
class Empresa
{
    public static function porId(int $id): ?array { return Database::one('SELECT * FROM empresas WHERE id = :i', ['i' => $id]); }
    public static function porEmail(string $e): ?array { return Database::one('SELECT * FROM empresas WHERE email = :e', ['e' => mb_strtolower($e)]); }
    public static function porCnpj(string $c): ?array { return Database::one('SELECT * FROM empresas WHERE cnpj = :c', ['c' => Cnpj::limpar($c)]); }

    /** "Rua A, 10, sala 2 - Centro, Guarulhos/SP - CEP 07000-000" (texto usado nas telas e e-mails). */
    public static function comporEndereco(array $d): string
    {
        $cep = preg_replace('/\D/', '', (string) ($d['cep'] ?? ''));
        $rua = trim($d['logradouro'] . ', ' . $d['numero'] . (($d['complemento'] ?? '') !== '' ? ', ' . $d['complemento'] : ''));
        return mb_substr($rua . ' - ' . $d['bairro'] . ', ' . $d['cidade'] . '/' . strtoupper($d['uf']) . ' - CEP ' . substr($cep, 0, 5) . '-' . substr($cep, 5), 0, 200);
    }

    private static function campos(array $d): array
    {
        $n = fn($k) => (($d[$k] ?? '') !== '') ? $d[$k] : null;
        return [
            'razao_social' => $d['razao_social'], 'nome' => $d['nome'], 'descricao' => $d['descricao'], 'segmento' => $d['segmento'], 'porte' => $d['porte'],
            'endereco' => self::comporEndereco($d), 'cep' => preg_replace('/\D/', '', $d['cep']), 'logradouro' => $d['logradouro'], 'numero' => $d['numero'],
            'complemento' => $n('complemento'), 'bairro' => $d['bairro'], 'cidade' => $d['cidade'], 'uf' => strtoupper($d['uf']),
            'responsavel' => $d['responsavel'], 'cargo' => $d['cargo'], 'telefone' => preg_replace('/\D/', '', $d['telefone']),
            'celular' => $n('celular') ? preg_replace('/\D/', '', $d['celular']) : null, 'email_contato' => $n('email_contato') ? mb_strtolower($d['email_contato']) : null,
            'site' => $n('site'), 'linkedin' => $n('linkedin'), 'link_principal' => $d['link_principal'] ?? 'site',
        ];
    }

    public static function criar(array $d): int
    {
        $id = Database::insert('empresas', self::campos($d) + [
            'cnpj' => Cnpj::limpar($d['cnpj']), 'email' => mb_strtolower($d['email']), 'senha_hash' => Auth::hash($d['senha']),
            'logo' => $d['logo'] ?? null, 'termo_arquivo' => $d['termo_arquivo'], 'termos_aceitos_em' => Database::agora(),
            'status' => Estado::E_PENDENTE, 'criado_em' => Database::agora(),
        ]);
        Historico::registrar('empresa', $id, 'cadastro', null, Estado::E_PENDENTE, 'empresa', $id);
        return $id;
    }

    /** A própria empresa edita seu perfil (não altera CNPJ, e-mail de acesso, senha nem status). */
    public static function atualizarPerfil(int $id, array $d): void
    {
        $dados = self::campos($d);
        if (array_key_exists('logo', $d)) $dados['logo'] = $d['logo'];
        Database::update('empresas', $id, $dados);
        Historico::registrar('empresa', $id, 'perfil', null, null, 'empresa', $id);
    }

    /** Muda o estado respeitando a máquina de estados. @return bool false se a transição não é permitida. */
    public static function mudarStatus(int $id, string $para, ?string $motivo, string $atorTipo, ?int $atorId): bool
    {
        $e = self::porId($id);
        if (!$e || !Estado::empresaPode($e['status'], $para)) return false;
        $dados = ['status' => $para, 'motivo' => in_array($para, [Estado::E_REPROVADA, Estado::E_BLOQUEADA], true) ? $motivo : null];
        if (in_array($para, [Estado::E_APROVADA, Estado::E_REPROVADA], true) && $e['status'] === Estado::E_PENDENTE) { $dados['avaliado_em'] = Database::agora(); $dados['aprovacao_vista'] = 0; }
        if ($para === Estado::E_PENDENTE) { $dados['avaliado_em'] = null; }
        Database::update('empresas', $id, $dados);
        $acao = [Estado::E_APROVADA => $e['status'] === Estado::E_BLOQUEADA ? 'desbloqueada' : 'aprovada', Estado::E_REPROVADA => 'reprovada', Estado::E_BLOQUEADA => 'bloqueada', Estado::E_PENDENTE => 'reenviada'][$para];
        Historico::registrar('empresa', $id, $acao, $e['status'], $para, $atorTipo, $atorId, $motivo);
        return true;
    }
    public static function dispensarAviso(int $id): void { Database::update('empresas', $id, ['aprovacao_vista' => 1]); }

    /** O aviso "empresa aprovada" aparece enquanto: aprovada, não dispensado, sem vagas publicadas ainda e há menos de 7 dias. */
    public static function mostrarAvisoAprovacao(array $e): bool
    {
        if ($e['status'] !== Estado::E_APROVADA || (int) $e['aprovacao_vista'] || !$e['avaliado_em']) return false;
        if (strtotime($e['avaliado_em']) < time() - 7 * 86400) return false;
        return !Database::val('SELECT 1 FROM vagas WHERE empresa_id = :e LIMIT 1', ['e' => $e['id']]);
    }

    public static function pendentes(): array { return Database::all("SELECT * FROM empresas WHERE status = 'pendente' ORDER BY criado_em"); }

    // ---------- listagens ----------
    private const SUBS = '(SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id) AS total_vagas,
        (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status = \'aprovada\' AND v.data_publicacao <= :hoje AND v.data_limite >= :hoje) AS vagas_abertas';

    public static function abasAdmin(): array
    {
        $h = date('Y-m-d'); $trinta = date('Y-m-d H:i:s', time() - 30 * 86400);
        $q = fn(string $w, array $p = []) => (int) Database::val("SELECT COUNT(*) FROM empresas e WHERE $w", $p);
        return [
            'pendentes' => ['Pendentes', $q("status = 'pendente'")], 'aprovadas' => ['Aprovadas', $q("status = 'aprovada'")],
            'ativas' => ['Ativas (30 dias)', $q("status = 'aprovada' AND ultimo_acesso >= :t", ['t' => $trinta])],
            'com_vagas' => ['Com vagas', $q('EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)')], 'sem_vagas' => ['Sem vagas', $q('NOT EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)')],
            'bloqueadas' => ['Bloqueadas', $q("status = 'bloqueada'")], 'reprovadas' => ['Reprovadas', $q("status = 'reprovada'")], 'todas' => ['Todas', $q('1 = 1')],
        ];
    }
    /** @return array [linhas, total] */
    public static function listarAdmin(string $aba, string $busca, int $pg, int $por = POR_PAGINA): array
    {
        $w = ['1 = 1']; $p = ['hoje' => date('Y-m-d')];
        switch ($aba) {
            case 'pendentes': $w[] = "e.status = 'pendente'"; break; case 'aprovadas': $w[] = "e.status = 'aprovada'"; break;
            case 'bloqueadas': $w[] = "e.status = 'bloqueada'"; break; case 'reprovadas': $w[] = "e.status = 'reprovada'"; break;
            case 'ativas': $w[] = "e.status = 'aprovada' AND e.ultimo_acesso >= :t30"; $p['t30'] = date('Y-m-d H:i:s', time() - 30 * 86400); break;
            case 'com_vagas': $w[] = 'EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)'; break;
            case 'sem_vagas': $w[] = 'NOT EXISTS (SELECT 1 FROM vagas v WHERE v.empresa_id = e.id)'; break;
        }
        if ($busca !== '') { $w[] = '(LOWER(e.nome) LIKE :q OR LOWER(e.razao_social) LIKE :q OR e.cnpj LIKE :qc OR LOWER(e.email) LIKE :q)'; $p['q'] = '%' . mb_strtolower($busca) . '%'; $p['qc'] = '%' . Cnpj::limpar($busca) . '%'; }
        $where = implode(' AND ', $w);
        $tp = array_diff_key($p, ['hoje' => 1]);
        $total = (int) Database::val("SELECT COUNT(*) FROM empresas e WHERE $where", $tp);
        [$off] = Paginador::calc($total, $pg, $por);
        $l = Database::all('SELECT e.*, ' . self::SUBS . " FROM empresas e WHERE $where ORDER BY e.criado_em DESC, e.id DESC LIMIT " . (int) $por . ' OFFSET ' . (int) $off, $p);
        return [$l, $total];
    }
    public static function comContagem(int $id): ?array
    {
        return Database::one('SELECT e.*, ' . self::SUBS . ' FROM empresas e WHERE e.id = :id', ['hoje' => date('Y-m-d'), 'id' => $id]);
    }

    /** Parceiras para o estudante: só empresas aprovadas. $ordem: recentes | frequentes | abertas */
    public static function parceiras(string $ordem, int $n = 12): array
    {
        $ord = ['recentes' => 'e.avaliado_em DESC, e.id DESC', 'frequentes' => 'total_vagas DESC, e.nome', 'abertas' => 'vagas_abertas DESC, e.nome'][$ordem] ?? 'e.nome';
        $extra = $ordem === 'frequentes' ? ' AND (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status IN (\'aprovada\',\'encerrada\')) > 0' : '';
        $extra .= $ordem === 'abertas' ? ' AND (SELECT COUNT(*) FROM vagas v WHERE v.empresa_id = e.id AND v.status = \'aprovada\' AND v.data_publicacao <= :hoje AND v.data_limite >= :hoje) > 0' : '';
        return Database::all('SELECT e.*, ' . self::SUBS . " FROM empresas e WHERE e.status = 'aprovada'$extra ORDER BY $ord LIMIT " . (int) $n, ['hoje' => date('Y-m-d')]);
    }
    public static function parceiraPorId(int $id): ?array
    {
        $e = self::comContagem($id);
        return ($e && $e['status'] === Estado::E_APROVADA) ? $e : null;
    }
    /** Destino principal escolhido pela empresa (site ou LinkedIn); cai no outro se o escolhido estiver vazio. */
    public static function linkExterno(array $e): ?string
    {
        $ordem = ($e['link_principal'] ?? 'site') === 'linkedin' ? ['linkedin', 'site'] : ['site', 'linkedin'];
        foreach ($ordem as $k) if (!empty($e[$k]) && Link::valido($e[$k])) return $e[$k];
        return null;
    }
    public static function segmentoTxt(array $e): string { return $e['segmento'] ?: 'Segmento não informado'; }
}
```

### Passo 53 — Auth.php

#### Arquivo: `app/Auth.php` — CRIAR
Login único por vez, expiração de sessão, limite de tentativas e log de acessos.

```php
<?php
/**
 * Dois contextos de autenticação independentes e EXCLUSIVOS (nunca os dois ao mesmo tempo):
 *  - empresa  (tabela empresas) : área /painel...
 *  - admin    (tabela admins)   : área /admin...
 * Toda autorização é conferida aqui, no backend. Eventos de acesso vão para acessos_log (controle de acesso).
 */
class Auth
{
    private const MAX_TENTATIVAS = 5;
    private const JANELA_MIN = 15;
    private static function ocioso(string $perfil): int { return (int) env('SESSION_IDLE_' . strtoupper($perfil), $perfil === 'admin' ? 1800 : 7200); }   // segundos sem atividade
    private const ABSOLUTO = 43200;                                 // 12 h desde o login, mesmo com atividade

    // ---------- log de acessos ----------
    public static function log(string $perfil, ?int $uid, ?string $email, string $evento): void
    {
        try {
            Database::insert('acessos_log', ['perfil' => $perfil, 'usuario_id' => $uid, 'email' => $email ? mb_substr($email, 0, 120) : null, 'evento' => $evento,
                'ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'), 0, 45), 'agente' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 160), 'criado_em' => Database::agora()]);
        } catch (Throwable $t) { /* log nunca derruba a requisição */ }
    }

    // ---------- sessão ----------
    public static function empresa(): ?array
    {
        $id = (int) ($_SESSION['empresa_id'] ?? 0);
        if (!$id) return null;
        $e = Empresa::porId($id);
        if (!$e || $e['status'] === Estado::E_BLOQUEADA) { unset($_SESSION['empresa_id']); return null; }   // bloqueada no meio da sessão: cai na hora
        return $e;
    }
    public static function admin(): ?array
    {
        $id = (int) ($_SESSION['admin_id'] ?? 0);
        if (!$id) return null;
        $a = Admin::porId($id);
        if (!$a || !(int) $a['ativo']) { unset($_SESSION['admin_id']); return null; }
        return $a;
    }
    /** 'admin', 'empresa' ou null. */
    public static function perfil(): ?string { return self::admin() ? 'admin' : (self::empresa() ? 'empresa' : null); }

    /** Chamado em toda requisição: nunca os dois perfis; expira por inatividade e por tempo total. */
    public static function normalizar(): void
    {
        if (!empty($_SESSION['admin_id']) && !empty($_SESSION['empresa_id'])) unset($_SESSION['empresa_id']);
        $perfil = !empty($_SESSION['admin_id']) ? 'admin' : (!empty($_SESSION['empresa_id']) ? 'empresa' : null);
        if (!$perfil) return;
        $agora = time(); $ult = (int) ($_SESSION['_ultima'] ?? $agora); $ini = (int) ($_SESSION['_inicio'] ?? $agora);
        if ($agora - $ult > self::ocioso($perfil) || $agora - $ini > self::ABSOLUTO) {
            self::log($perfil, (int) $_SESSION[$perfil . '_id'], null, 'timeout');
            unset($_SESSION['admin_id'], $_SESSION['empresa_id'], $_SESSION['_ultima'], $_SESSION['_inicio']);
            session_regenerate_id(true);
            flash('info', 'Sua sessão expirou. Entre novamente.');
            return;
        }
        $_SESSION['_ultima'] = $agora; $_SESSION['_inicio'] = $ini;
    }

    /** Já há OUTRO perfil conectado: bloqueia, registra a tentativa e leva à área do perfil atual. */
    public static function bloquearSeOutroPerfil(string $quero): void
    {
        $atual = self::perfil();
        if ($atual && $atual !== $quero) {
            self::log($atual, (int) ($_SESSION[$atual . '_id'] ?? 0), null, 'negado');
            flash('info', $atual === 'admin'
                ? 'Você está conectado como administrador. Saia da conta para entrar ou cadastrar como empresa.'
                : 'Você está conectado como empresa. Saia da conta para entrar como administrador.');
            redirect($atual === 'admin' ? 'admin' : 'painel');
        }
    }
    public static function exigirEmpresa(): array
    {
        self::bloquearSeOutroPerfil('empresa');
        $e = self::empresa();
        if (!$e) { flash('info', 'Entre com a conta da empresa para continuar.'); redirect('empresa-login', ['next' => $_GET['p'] ?? 'painel']); }
        return $e;
    }
    public static function exigirAdmin(): array
    {
        self::bloquearSeOutroPerfil('admin');
        $a = self::admin();
        if (!$a) { flash('info', 'Acesso restrito à Coordenação/Secretaria.'); redirect('admin-login'); }
        return $a;
    }
    /** Recurso inexistente OU de outra empresa: mesma resposta (não revela que existe). */
    public static function recusar(string $motivo = 'recurso'): void
    {
        $p = self::perfil() ?? 'anonimo';
        self::log($p, (int) ($_SESSION[$p . '_id'] ?? 0) ?: null, null, 'negado');
        http_response_code(404);
        $perfil = self::perfil();
        render('erro', ['titulo' => 'Não encontrado', 'msg' => 'O item não existe ou você não tem acesso a ele.', 'area' => $perfil ?? 'empresa', 'secao' => '', 'emp' => self::empresa() ?? [], 'adm' => self::admin() ?? []], $perfil ? 'app' : 'auth');
        exit;
    }

    // ---------- login ----------
    /** @return array [usuario|null, erro|null] */
    public static function entrar(string $perfil, string $email, string $senha): array
    {
        $atual = self::perfil();
        if ($atual && $atual !== $perfil) return [null, 'Já existe um login ativo como ' . ($atual === 'admin' ? 'administrador' : 'empresa') . '. Saia primeiro.'];
        $email = mb_strtolower(trim($email));
        $chave = hash('sha256', $perfil . '|' . $email . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'cli'));
        if (self::tentativas($chave) >= self::MAX_TENTATIVAS) {
            self::log($perfil, null, $email, 'bloqueado');
            return [null, 'Muitas tentativas. Aguarde ' . self::JANELA_MIN . ' minutos e tente de novo.'];
        }
        $u = $perfil === 'admin' ? Admin::porEmail($email) : Empresa::porEmail($email);
        $hash = $u['senha_hash'] ?? '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyzABCDE';   // tempo constante
        if (!$u || !password_verify($senha, $hash)) {
            Database::insert('login_tentativas', ['chave' => $chave, 'criado_em' => Database::agora()]);
            self::log($perfil, $u ? (int) $u['id'] : null, $email, 'login_falha');
            return [null, 'E-mail ou senha incorretos.'];
        }
        if ($perfil === 'admin' && !(int) $u['ativo']) { self::log($perfil, (int) $u['id'], $email, 'negado'); return [null, 'Esta conta está desativada. Fale com a Coordenação.']; }
        if ($perfil === 'empresa' && $u['status'] === Estado::E_BLOQUEADA) { self::log($perfil, (int) $u['id'], $email, 'negado'); return [null, 'Esta conta está bloqueada. Fale com a Coordenação.']; }

        Database::run('DELETE FROM login_tentativas WHERE chave = :c', ['c' => $chave]);
        $anterior = $u['ultimo_acesso'];
        session_regenerate_id(true);
        $_SESSION[$perfil === 'admin' ? 'admin_id' : 'empresa_id'] = (int) $u['id'];
        $_SESSION['_ultima'] = $_SESSION['_inicio'] = time();
        Database::update($perfil === 'admin' ? 'admins' : 'empresas', (int) $u['id'], ['ultimo_acesso' => Database::agora()]);
        // aviso "empresa aprovada": some no 2º login depois da aprovação (também some ao publicar a 1ª vaga, ao dispensar ou após 7 dias)
        if ($perfil === 'empresa' && $u['status'] === Estado::E_APROVADA && !(int) $u['aprovacao_vista'] && $anterior && $u['avaliado_em'] && $anterior >= $u['avaliado_em']) {
            Database::update('empresas', (int) $u['id'], ['aprovacao_vista' => 1]);
        }
        self::log($perfil, (int) $u['id'], $email, 'login_ok');
        return [$u, null];
    }

    /** Abre a sessão de um perfil já autenticado (ex.: logo após o cadastro da empresa). */
    public static function iniciarSessao(string $perfil, int $id, ?string $email = null): void
    {
        session_regenerate_id(true);
        $_SESSION[$perfil === 'admin' ? 'admin_id' : 'empresa_id'] = $id;
        $_SESSION['_ultima'] = $_SESSION['_inicio'] = time();
        Database::update($perfil === 'admin' ? 'admins' : 'empresas', $id, ['ultimo_acesso' => Database::agora()]);
        self::log($perfil, $id, $email, 'login_ok');
    }

    public static function sair(string $perfil): void
    {
        self::log($perfil, (int) ($_SESSION[$perfil . '_id'] ?? 0) ?: null, null, 'logout');
        unset($_SESSION[$perfil === 'admin' ? 'admin_id' : 'empresa_id'], $_SESSION['_ultima'], $_SESSION['_inicio']);
        session_regenerate_id(true);
    }
    private static function tentativas(string $chave): int
    {
        $desde = date('Y-m-d H:i:s', time() - self::JANELA_MIN * 60);
        return (int) Database::val('SELECT COUNT(*) FROM login_tentativas WHERE chave = :c AND criado_em >= :d', ['c' => $chave, 'd' => $desde]);
    }
    public static function hash(string $senha): string { return password_hash($senha, PASSWORD_DEFAULT); }
}
```

### Passo 54 — Bootstrap.php

#### Arquivo: `app/Bootstrap.php` — CRIAR
No primeiro acesso cria/atualiza o banco e o administrador (usa `ADMIN_EMAIL`/`ADMIN_PASSWORD`).

```php
<?php
/** Primeiro acesso: cria tabelas/áreas e o administrador (se ADMIN_PASSWORD estiver no .env). Idempotente e barato. */
class Bootstrap
{
    public static function garantir(): void
    {
        $pdo = Database::pdo();
        require_once ROOT_PATH . '/database/schema.php';
        // Atualiza sozinho: banco vazio -> esquema final; banco antigo -> migrations pendentes.
        $precisa = true;
        try {
            $pdo->query('SELECT 1 FROM migracoes WHERE 1 = 0');
            $ap = array_column(Database::all('SELECT nome FROM migracoes'), 'nome');
            $precisa = (bool) array_diff(array_column(migrations(), 'nome'), $ap);
        } catch (PDOException $e) { $precisa = true; }
        if ($precisa) migrar();

        if (!Database::val('SELECT 1 FROM admins LIMIT 1')) {
            $senha = (string) env('ADMIN_PASSWORD', '');
            if (strlen($senha) >= 8) {
                Database::insert('admins', ['nome' => 'Coordenação', 'email' => mb_strtolower((string) env('ADMIN_EMAIL', 'admin@ifsp.local')),
                    'senha_hash' => Auth::hash($senha), 'criado_em' => Database::agora()]);
            }
        }
    }

    /** Mensagem útil (sem expor segredos) para erros de configuração. */
    public static function dica(Throwable $t): string
    {
        $m = $t->getMessage();
        if ($t instanceof PDOException || str_contains($m, 'could not find driver')) {
            if (str_contains($m, 'could not find driver')) return 'Falta a extensão PDO do banco no PHP (pdo_sqlite, pdo_mysql ou pdo_pgsql). No cPanel: "Select PHP Version" > Extensions.';
            if (str_contains($m, 'unable to open database') || str_contains($m, 'readonly')) return 'Sem permissão de escrita na pasta storage/. Dê permissão 755/775 a storage/.';
            return 'Não foi possível usar o banco de dados. Confira DB_* no arquivo .env.';
        }
        return '';
    }
}
```

### Passo 55 — EmpresaForm.php

#### Arquivo: `app/EmpresaForm.php` — CRIAR

```php
<?php
/** Validação do cadastro/perfil da empresa (a mesma regra vale no cadastro em etapas e na edição do perfil). */
class EmpresaForm
{
    /** Regra de senha: 8+ caracteres, ao menos uma letra e um número. */
    public static function senhaForte(string $s): bool { return strlen($s) >= 8 && preg_match('/[A-Za-z]/', $s) && preg_match('/\d/', $s); }

    /** @return array [dados normalizados, erros] ; $modo = cadastro | perfil ; $atual = empresa existente (perfil) */
    public static function validar(array $d, array $files, string $modo, ?array $atual = null): array
    {
        $e = [];
        $d['site'] = Link::normalizar($d['site'] ?? ''); $d['linkedin'] = Link::normalizar($d['linkedin'] ?? '');
        $len = fn($k) => mb_strlen($d[$k] ?? '');
        if ($modo === 'cadastro') {
            if (!Cnpj::valido($d['cnpj'] ?? '')) $e['cnpj'] = 'CNPJ inválido. Confira os 14 caracteres (letras e números).';
            elseif (Empresa::porCnpj($d['cnpj'])) $e['cnpj'] = 'Este CNPJ já está cadastrado. Entre na área da empresa.';
        }
        if ($len('razao_social') < 3 || $len('razao_social') > 150) $e['razao_social'] = 'Informe a razão social.';
        if ($len('nome') < 2 || $len('nome') > 120) $e['nome'] = 'Informe o nome fantasia.';
        if ($len('descricao') < 30 || $len('descricao') > 600) $e['descricao'] = 'Descreva a empresa em 30 a 600 caracteres: é o que o estudante vê.';
        if (!in_array($d['segmento'] ?? '', SEGMENTOS, true)) $e['segmento'] = 'Escolha o segmento.';
        if (!in_array($d['porte'] ?? '', PORTES, true)) $e['porte'] = 'Escolha o porte.';
        if ($d['site'] && !Link::valido($d['site'])) $e['site'] = 'Endereço inválido. Exemplo: https://www.suaempresa.com.br';
        if ($d['linkedin'] && !Link::valido($d['linkedin'], 'linkedin')) $e['linkedin'] = 'Use o endereço do LinkedIn da empresa (linkedin.com/company/...).';
        $d['link_principal'] = ($d['link_principal'] ?? 'site') === 'linkedin' ? 'linkedin' : 'site';
        if ($d['link_principal'] === 'linkedin' && !$d['linkedin']) $d['link_principal'] = 'site';
        if (strlen(preg_replace('/\D/', '', $d['cep'] ?? '')) !== 8) $e['cep'] = 'CEP inválido. Informe os 8 números.';
        foreach (['logradouro' => [3, 120], 'bairro' => [2, 60], 'cidade' => [2, 60]] as $c => [$min, $max]) if ($len($c) < $min || $len($c) > $max) $e[$c] = 'Preencha este campo corretamente.';
        $n = $len('numero'); if ($n < 1 || $n > 10) $e['numero'] = 'Informe o número (ou S/N).';
        if ($len('complemento') > 60) $e['complemento'] = 'Máximo de 60 caracteres.';
        if (!in_array(strtoupper($d['uf'] ?? ''), UFS, true)) $e['uf'] = 'Escolha o estado.';
        if ($len('responsavel') < 3 || $len('responsavel') > 100) $e['responsavel'] = 'Informe o nome do responsável.';
        if ($len('cargo') < 2 || $len('cargo') > 60) $e['cargo'] = 'Informe o cargo ou função.';
        $f = strlen(preg_replace('/\D/', '', $d['telefone'] ?? '')); if ($f < 10 || $f > 11) $e['telefone'] = 'Telefone incompleto.';
        if (($d['celular'] ?? '') !== '') { $c = strlen(preg_replace('/\D/', '', $d['celular'])); if ($c < 10 || $c > 11) $e['celular'] = 'Celular incompleto.'; }
        if (($d['email_contato'] ?? '') !== '' && !filter_var($d['email_contato'], FILTER_VALIDATE_EMAIL)) $e['email_contato'] = 'E-mail inválido.';

        // logo (opcional)
        [$logo, $eLogo] = Upload::salvar($files['logo'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/logos', false, 2);
        if ($eLogo) $e['logo'] = $eLogo; elseif ($logo) $d['logo'] = 'public/uploads/logos/' . $logo;

        if ($modo === 'cadastro') {
            if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 120) $e['email'] = 'E-mail inválido.';
            elseif (Empresa::porEmail($d['email'])) $e['email'] = 'Este e-mail já tem cadastro. Entre na área da empresa.';
            $s = $_POST['senha'] ?? '';
            if (!self::senhaForte($s)) $e['senha'] = 'A senha precisa ter 8 ou mais caracteres, com letras e números.';
            elseif ($s !== ($_POST['senha2'] ?? '')) $e['senha2'] = 'As senhas não conferem.';
            $d['senha'] = $s;
            [$arq, $eArq] = Upload::salvar($files['termo'] ?? null, ['application/pdf', 'image/jpeg', 'image/png'], ROOT_PATH . '/storage/termos', true);
            if ($eArq) $e['termo'] = $eArq; else $d['termo_arquivo'] = $arq;
            if (empty($d['aceite_termos'])) $e['aceite_termos'] = 'É preciso aceitar os termos de uso.';
            if (empty($d['aceite_privacidade'])) $e['aceite_privacidade'] = 'É preciso confirmar a política de privacidade.';
        }
        if ($e) { // não deixa arquivos órfãos quando há erro
            if (!empty($d['logo']) && (!$atual || $d['logo'] !== $atual['logo'])) @unlink(ROOT_PATH . '/' . $d['logo']);
            if (!empty($d['termo_arquivo'])) @unlink(ROOT_PATH . '/storage/termos/' . $d['termo_arquivo']);
        }
        return [$d, $e];
    }
}
```

### Passo 56 — AuthController.php

#### Arquivo: `Controllers/AuthController.php` — CRIAR

```php
<?php
class AuthController
{
    private function destino(): string
    {
        $n = $_GET['next'] ?? $_POST['next'] ?? '';
        return in_array($n, ['painel', 'vaga-cadastro', 'empresa-vagas', 'empresa-perfil'], true) ? $n : 'painel';
    }

    public function empresaLogin(): void
    {
        Auth::bloquearSeOutroPerfil('empresa');
        if (Auth::empresa()) redirect('painel');
        $erro = null; $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $erro] = Auth::entrar('empresa', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect($this->destino());
        }
        render('empresa_login', ['titulo' => 'Entrar - Empresa', 'ambiente' => 'empresa', 'erro' => $erro, 'email' => $email, 'next' => $this->destino()], 'auth');
    }

    public function adminLogin(): void
    {
        Auth::bloquearSeOutroPerfil('admin');
        if (Auth::admin()) redirect('admin');
        $erro = null; $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $email = trim((string) ($_POST['email'] ?? ''));
            [$u, $erro] = Auth::entrar('admin', $email, (string) ($_POST['senha'] ?? ''));
            if ($u) redirect('admin');
        }
        render('admin_login', ['titulo' => 'Entrar - Administração', 'ambiente' => 'admin', 'erro' => $erro, 'email' => $email], 'auth');
    }

    public function sair(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('home');
        Csrf::exigir();
        $perfil = ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'empresa';
        if (Auth::perfil() === $perfil) Auth::sair($perfil);   // só encerra o perfil que de fato está conectado
        flash('ok', 'Você saiu da conta.');
        redirect($perfil === 'admin' ? 'admin-login' : 'home');
    }
}
```

### Passo 57 — EmpresaController.php

#### Arquivo: `Controllers/EmpresaController.php` — CRIAR

```php
<?php
/** Cadastro da empresa em 5 etapas (+ conclusão). A empresa fica pendente até a Coordenação aprovar. */
class EmpresaController
{
    public function cadastro(): void
    {
        Auth::bloquearSeOutroPerfil('empresa');
        if (Auth::empresa()) redirect('painel');
        $erros = []; $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $old = limpar_post($_POST);
            [$dados, $erros] = EmpresaForm::validar($old, $_FILES, 'cadastro');
            if (!$erros) {
                $id = Empresa::criar($dados);
                Notificador::empresaCadastrada(Empresa::porId($id));
                Auth::iniciarSessao('empresa', $id, $dados['email']);
                $_SESSION['concluido'] = $id;
                redirect('empresa-concluido');
            }
        }
        render('empresa_cadastro', ['titulo' => 'Cadastro de empresa', 'ambiente' => 'empresa', 'erros' => $erros, 'old' => $old], 'auth');
    }

    /** Etapa 6: conclusão (só logo após enviar o cadastro). */
    public function concluido(): void
    {
        $emp = Auth::exigirEmpresa();
        if ((int) ($_SESSION['concluido'] ?? 0) !== (int) $emp['id']) redirect('painel');
        unset($_SESSION['concluido']);
        render('empresa_concluido', ['titulo' => 'Cadastro enviado', 'ambiente' => 'empresa', 'emp' => $emp], 'auth');
    }
}
```

### Passo 58 — seed.php

#### Arquivo: `database/seed.php` — CRIAR
Cria o administrador. Com `--demo` carrega empresas e vagas de exemplo (precisa dos arquivos `database/seed/*.json` e dos banners, da Preparação).

```php
<?php
// php database/seed.php  -> cria o administrador e (opcional, com --demo) empresas/vagas de exemplo
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
migrar();

$email = mb_strtolower((string) env('ADMIN_EMAIL', 'admin@ifsp.local'));
$senha = (string) env('ADMIN_PASSWORD', '');
if (!Database::val('SELECT 1 FROM admins WHERE email = :e', ['e' => $email])) {
    if (strlen($senha) < 8) { $senha = bin2hex(random_bytes(6)); echo "ADMIN_PASSWORD não definida: senha temporária gerada -> $senha\n"; }
    Database::insert('admins', ['nome' => 'Coordenação', 'email' => $email, 'senha_hash' => Auth::hash($senha), 'criado_em' => Database::agora()]);
    echo "Administrador criado: $email\n";
} else { echo "Administrador já existe: $email\n"; }

if (!in_array('--demo', $argv ?? [], true)) exit;

// ----- dados de demonstração (empresas fictícias; senha de todas: demo12345) -----
if (Database::val('SELECT COUNT(*) FROM empresas') > 0) { echo "Já existem empresas; demo ignorada.\n"; exit; }
$emps = json_decode(file_get_contents(__DIR__ . '/seed/empresas.json'), true);
$vagas = json_decode(file_get_contents(__DIR__ . '/seed/vagas.json'), true);
$hoje = strtotime('today'); $d = fn(int $n) => date('Y-m-d', $hoje + $n * 86400);
$agora = Database::agora();
$perfil = [
    1 => ['Tecnologia da Informação', 'Média empresa', 'Desenvolvemos sistemas web e mobile para o varejo. Time jovem, mentoria constante e projetos reais desde o primeiro dia.', 'site'],
    2 => ['Indústria', 'Grande empresa', 'Indústria de componentes mecânicos com fábrica em Guarulhos. Programa de estágio com rodízio entre áreas.', 'site'],
    3 => ['Logística e Transporte', 'Média empresa', 'Operadora logística com centros de distribuição na Grande São Paulo e foco em eficiência e segurança.', 'linkedin'],
    4 => ['Tecnologia da Informação', 'Pequena empresa', 'Consultoria em dados e inteligência de negócios para pequenas e médias empresas.', 'site'],
    5 => ['Serviços', 'Pequena empresa', 'Escritório de serviços administrativos e financeiros para empresas locais.', 'site'],
    6 => ['Educação', 'Microempresa', 'Plataforma de cursos técnicos online com parcerias com institutos federais.', 'site'],
];
$idMap = [];
foreach ($emps as $e) {
    [$seg, $porte, $desc, $lp] = $perfil[$e['id']] ?? ['Serviços', 'Pequena empresa', 'Empresa parceira do campus Guarulhos com foco em oportunidades para estudantes.', 'site'];
    $st = ['aprovada' => 'aprovada', 'pendente' => 'pendente', 'reprovada' => 'reprovada'][$e['status']] ?? 'pendente';
    $site = $e['site'] ?: null;
    $idMap[$e['id']] = Database::insert('empresas', [
        'cnpj' => Cnpj::limpar($e['cnpj']), 'razao_social' => $e['nome'] . ' Ltda', 'nome' => $e['nome'], 'descricao' => $desc, 'segmento' => $seg, 'porte' => $porte,
        'endereco' => $e['endereco'], 'cep' => '07011000', 'logradouro' => 'Avenida Tiradentes', 'numero' => (string) (100 + $e['id']), 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP',
        'responsavel' => 'RH ' . $e['nome'], 'cargo' => 'Analista de RH', 'telefone' => preg_replace('/\D/', '', $e['telefone']), 'celular' => '11988776655', 'email' => $e['email'], 'email_contato' => $e['email'],
        'site' => $site, 'linkedin' => $lp === 'linkedin' || $e['id'] % 2 ? 'https://www.linkedin.com/company/' . preg_replace('/\W/', '', strtolower($e['nome'])) : null, 'link_principal' => $lp, 'senha_hash' => Auth::hash('demo12345'),
        'termos_aceitos_em' => $agora, 'status' => $st, 'motivo' => $st === 'reprovada' ? 'Termo de cooperação ilegível. Envie uma cópia digitalizada com boa resolução.' : null,
        'aprovacao_vista' => 1, 'criado_em' => date('Y-m-d H:i:s', $hoje - (30 - $e['id'] * 3) * 86400), 'avaliado_em' => $st === 'pendente' ? null : date('Y-m-d H:i:s', $hoje - (28 - $e['id'] * 3) * 86400),
        'ultimo_acesso' => in_array($e['id'], [1, 3], true) ? $agora : null,
    ]);
    Historico::registrar('empresa', $idMap[$e['id']], 'cadastro', null, 'pendente', 'empresa', $idMap[$e['id']]);
    if ($st !== 'pendente') Historico::registrar('empresa', $idMap[$e['id']], $st === 'aprovada' ? 'aprovada' : 'reprovada', 'pendente', $st, 'admin', 1);
}
$alfa = Database::insert('empresas', ['cnpj' => Cnpj::completar('AB3DE5GH0001'), 'razao_social' => 'Orbita Dados Tecnologia Ltda', 'nome' => 'Orbita Dados', 'descricao' => 'Startup de análise de dados com foco em dashboards e ciência de dados aplicada ao varejo.',
    'segmento' => 'Tecnologia da Informação', 'porte' => 'Pequena empresa', 'endereco' => 'Avenida Tiradentes, 800 - Centro, Guarulhos/SP - CEP 07011-000', 'cep' => '07011000', 'logradouro' => 'Avenida Tiradentes', 'numero' => '800', 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP',
    'responsavel' => 'Equipe de Talentos', 'cargo' => 'Head de Pessoas', 'telefone' => '11940001234', 'email' => 'talentos@orbita.example', 'site' => 'https://orbita.example', 'link_principal' => 'site', 'senha_hash' => Auth::hash('demo12345'),
    'termos_aceitos_em' => $agora, 'status' => 'aprovada', 'aprovacao_vista' => 1, 'criado_em' => date('Y-m-d H:i:s', $hoje - 5 * 86400), 'avaliado_em' => date('Y-m-d H:i:s', $hoje - 4 * 86400), 'ultimo_acesso' => $agora]);
Historico::registrar('empresa', $alfa, 'cadastro', null, 'pendente', 'empresa', $alfa); Historico::registrar('empresa', $alfa, 'aprovada', 'pendente', 'aprovada', 'admin', 1);
// empresa bloqueada (para demonstrar o estado)
$blq = Database::insert('empresas', ['cnpj' => Cnpj::completar('ZZ9XY8WV0001'), 'razao_social' => 'Casa Antiga Comércio Ltda', 'nome' => 'Casa Antiga', 'descricao' => 'Comércio varejista. Conta bloqueada para demonstração.', 'segmento' => 'Comércio', 'porte' => 'Microempresa',
    'endereco' => 'Rua Sete, 7 - Centro, Guarulhos/SP - CEP 07011-000', 'cep' => '07011000', 'logradouro' => 'Rua Sete', 'numero' => '7', 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP', 'responsavel' => 'Dono', 'cargo' => 'Sócio', 'telefone' => '1140001111',
    'email' => 'contato@casaantiga.example', 'senha_hash' => Auth::hash('demo12345'), 'termos_aceitos_em' => $agora, 'status' => 'bloqueada', 'motivo' => 'Vaga divulgada sem relação com estágio.', 'aprovacao_vista' => 1, 'criado_em' => date('Y-m-d H:i:s', $hoje - 20 * 86400), 'avaliado_em' => date('Y-m-d H:i:s', $hoje - 19 * 86400)]);
Historico::registrar('empresa', $blq, 'bloqueada', 'aprovada', 'bloqueada', 'admin', 1, 'Vaga divulgada sem relação com estágio.');

$mapV = ['aprovada' => 'aprovada', 'pendente' => 'aguardando_aprovacao', 'reprovada' => 'rejeitada'];
$n = 0;
$novaVaga = function (array $x) use ($agora) {
    $id = Database::insert('vagas', $x + ['criado_em' => $agora, 'versao' => 1]);
    $st = $x['status']; Historico::registrar('vaga', $id, 'criada', null, 'aguardando_aprovacao', 'empresa', (int) $x['empresa_id']);
    if ($st !== 'aguardando_aprovacao') Historico::registrar('vaga', $id, $st === 'rejeitada' ? 'rejeitada' : 'aprovada', 'aguardando_aprovacao', $st === 'rejeitada' ? 'rejeitada' : 'aprovada', 'admin', 1, $st === 'rejeitada' ? ($x['motivo'] ?? null) : null);
    if ($st === 'encerrada') Historico::registrar('vaga', $id, 'encerrada', 'aprovada', 'encerrada', 'empresa', (int) $x['empresa_id']);
    return $id;
};
foreach ($vagas as $v) {
    $n++; $st = $mapV[$v['status']] ?? 'aguardando_aprovacao';
    $novaVaga([
        'codigo' => $v['codigo'], 'empresa_id' => $idMap[$v['empresa_id']], 'titulo' => $v['titulo'], 'tipo' => 'Estágio', 'area' => $v['area'], 'modalidade' => $v['modalidade'], 'local' => $v['local'],
        'bolsa' => parse_dinheiro(str_replace('R$', '', (string) $v['bolsa'])), 'carga_horaria' => '6h/dia · Seg a Sex · Manhã', 'carga_horas_dia' => 6, 'carga_dias' => 'seg,ter,qua,qui,sex', 'carga_turno' => 'manha',
        'descricao' => $v['resumo'] . "\n" . $v['descricao'], 'atividades' => implode("\n", $v['atividades']), 'requisitos' => implode("\n", $v['requisitos']), 'requisitos_desejaveis' => "Inglês intermediário\nExperiência anterior na área",
        'beneficios' => implode("\n", $v['beneficios']), 'data_publicacao' => $st === 'aprovada' ? $d(-($n * 3 % 14)) : $d(0), 'data_limite' => $st === 'aprovada' ? $d(5 + $n * 13) : $d(30), 'contato' => $v['contato'],
        'imagem' => $v['imagem'] ?: null, 'destaque' => !empty($v['destaque']) ? 1 : 0, 'status' => $st, 'motivo' => $st === 'rejeitada' ? 'Informe a remuneração e a carga horária.' : null,
        'avaliado_em' => $st === 'aguardando_aprovacao' ? null : $agora, 'link_candidatura' => $n % 3 === 0 ? 'https://empresa.gupy.io/jobs/' . (1000 + $n) : null, 'link_plataforma' => $n % 3 === 0 ? 'gupy' : null,
    ]);
}
// vagas extras: tipos variados e todas as situações
$ex = [
  ['TechNova Sistemas', 'Trainee de Engenharia de Software', 'Trainee', 'Tecnologia da Informação', 'Híbrido', 4800, 'aprovada', -3, 40, "Programa de 12 meses com rodízio entre squads de produto, dados e infraestrutura.\nVocê terá mentoria, projetos reais e plano de carreira."],
  ['Alfa Logística', 'Jovem Aprendiz em Operações', 'Jovem Aprendiz', 'Logística', 'Presencial', 1100, 'aprovada', -1, 25, "Primeiro emprego com carteira assinada no centro de distribuição, com formação teórica em parceria com o campus.\nAprenda rotinas de estoque e expedição."],
  ['Orbita Dados', 'Jovem Profissional em Análise de Dados', 'Jovem Profissional', 'Tecnologia da Informação', 'Remoto', 5200, 'aprovada', -2, 30, "Para quem acabou de se formar e quer atuar com SQL, Python e dashboards.\nTime enxuto, ritmo de startup e muito aprendizado."],
  ['Orbita Dados', 'Estágio em Ciência de Dados', 'Estágio', 'Tecnologia da Informação', 'Remoto', 2300, 'aprovada', 0, 6, "Apoio na limpeza, modelagem e visualização de dados de clientes.\nBolsa acima da média do mercado e horário flexível."],
  ['MecPrime Indústria', 'Jovem Aprendiz em Manufatura', 'Jovem Aprendiz', 'Engenharia', 'Presencial', 1150, 'aprovada', -4, 18, "Aprendizado na linha de produção com acompanhamento de supervisores.\nVale-transporte e refeição no local."],
  ['Alfa Logística', 'Estágio em Planejamento de Frota', 'Estágio', 'Logística', 'Híbrido', 1450, 'aprovada', 0, 3, "Auxílio no planejamento de rotas e controle de custos da frota.\nIdeal para quem cursa Logística ou Administração."],
  ['Orbita Dados', 'Estágio em Design Gráfico', 'Estágio', 'Design Gráfico', 'Híbrido', 1600, 'aprovada', -1, 45, "Criação de peças para redes sociais e apresentações.\nUso de Figma e Illustrator."],
  // situações para demonstrar o fluxo
  ['TechNova Sistemas', 'Estágio em QA e Testes (agendada)', 'Estágio', 'Qualidade', 'Remoto', 1800, 'aprovada', 10, 40, "Vaga aprovada com publicação futura: aparece como Agendada para a empresa e para a administração.\nTestes manuais e automatizados."],
  ['TechNova Sistemas', 'Estágio em Suporte (prazo encerrado)', 'Estágio', 'Tecnologia da Informação', 'Presencial', 1500, 'aprovada', -40, -5, "Vaga cujo prazo terminou: continua no sistema como Expirada.\nAtendimento de primeiro nível."],
  ['Alfa Logística', 'Estágio em Compras (encerrada pela empresa)', 'Estágio', 'Administração', 'Presencial', 1400, 'encerrada', -30, 10, "Vaga encerrada pela empresa antes do prazo: fica no histórico.\nApoio ao setor de compras."],
  ['Orbita Dados', 'Estágio em Marketing de Dados (em análise)', 'Estágio', 'Marketing', 'Remoto', 1700, 'aguardando_aprovacao', 0, 30, "Vaga aguardando aprovação da Coordenação.\nAnálise de campanhas e relatórios."],
  ['TechNova Sistemas', 'Trainee em Dados (rejeitada)', 'Trainee', 'Tecnologia da Informação', 'Híbrido', 3000, 'rejeitada', 0, 30, "Vaga rejeitada para demonstrar o motivo exibido à empresa.\nPrograma de formação em dados."],
];
foreach ($ex as $i => [$nomeEmp, $tit, $tipo, $area, $mod, $sal, $st, $pub, $lim, $desc]) {
    $eid = (int) Database::val('SELECT id FROM empresas WHERE nome = :n', ['n' => $nomeEmp]);
    if ($area === 'Design Gráfico' && !Database::val("SELECT 1 FROM areas WHERE nome = 'Design Gráfico'")) Database::insert('areas', ['nome' => 'Design Gráfico', 'padrao' => 0]);
    $novaVaga(['codigo' => 'VG-2026-1' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 'empresa_id' => $eid, 'titulo' => $tit, 'tipo' => $tipo, 'area' => $area, 'modalidade' => $mod, 'local' => 'Guarulhos/SP', 'bolsa' => $sal,
        'carga_horaria' => ($tipo === 'Estágio' ? '6h/dia' : '8h/dia') . ' · Seg a Sex · ' . ($tipo === 'Estágio' ? 'Manhã' : 'Integral'), 'carga_horas_dia' => $tipo === 'Estágio' ? 6 : 8, 'carga_dias' => 'seg,ter,qua,qui,sex', 'carga_turno' => $tipo === 'Estágio' ? 'manha' : 'integral',
        'descricao' => $desc, 'atividades' => "Apoiar a equipe nas rotinas do dia a dia\nParticipar de treinamentos", 'requisitos' => "Boa comunicação\nVontade de aprender", 'requisitos_desejaveis' => 'Inglês básico', 'beneficios' => "Vale-refeição\nVale-transporte\nPlano de saúde",
        'data_publicacao' => $d($pub), 'data_limite' => $d($lim), 'contato' => 'vagas@' . strtolower(preg_replace('/\W/', '', $nomeEmp)) . '.example', 'status' => $st, 'motivo' => $st === 'rejeitada' ? 'A descrição não informa as atividades do trainee. Detalhe o programa e reenvie.' : null,
        'avaliado_em' => $st === 'aguardando_aprovacao' ? null : $agora, 'aviso_encerrada' => 0]);
}
echo "Dados de demonstração criados (empresas: senha demo12345).\n";
```

### Passo 59 — Verificar a branch

```bash
php database/seed.php
php -r 'require "config.php"; Bootstrap::garantir(); var_dump((bool) Admin::porEmail(env("ADMIN_EMAIL")));'
```

Resultado esperado:
- `php database/seed.php…` → Administrador criado: <ADMIN_EMAIL>
- `php -r 'require "config.php"; Bootstrap::garantir(); var_dump((bool) A…` → bool(true)

### Passo 60 — Fechar a branch

```bash
git add .
git commit -m "feat(auth): login exclusivo, sessão com expiração, bloqueio de tentativas e cadastro de empresa"
git push -u origin feature/backend-auth
```
Abra o Pull Request `feature/backend-auth` → `develop` e peça revisão a **Pedro Miguel**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 8: `feature/backend-jobs` — dono: José Roberto

Backend das vagas e dos painéis: modelo de vaga, validação, estatísticas e controllers (público, empresa, administração).

**Revisor do PR:** Pedro Miguel. **Depende de:** `feature/backend-auth`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `111.02` | Investigar e corrigir o bug "vaga aprovada não aparece" | 2 | 3 |
| `111.04` | Consultas por situação feitas no SQL (publicada, agendada, expirada) | 2 | 4 |
| `111.05` | Rotina diária (cron) de avisos de vencimento e expiração, sem alterar o estado | 2 | 5 |
| `109.04` | Termo de cooperação guardado fora da pasta pública, aberto só pelo administrador | 1 | 5 |
| `111.03` | Transições validadas no backend, com histórico de toda mudança | 3 | 6 |
| `108.01` | Backend das vagas da empresa com ownership (só acessa as próprias) | 2 | 6 |
| `105.12` | Validação estruturada da vaga (requisitos, remuneração numérica, carga horária, período e links) | 3 | 7 |
| `108.13` | Consulta das vagas públicas e áreas (filtro e "Outras") | 2 | 7 |
| `108.07` | Consultas de empresas parceiras (recentes, mais frequentes, com vagas abertas) | 2 | 8 |
| `105.15` | Edição de vaga: alteração relevante devolve a vaga para análise (reaprovação) | 3 | 9 |

```bash
git checkout develop && git pull
git checkout -b feature/backend-jobs
```

### Passo 61 — Vaga.php

#### Arquivo: `Models/Vaga.php` — CRIAR
Estados, ownership, reaprovação ao editar e consultas por situação.

```php
<?php
class Vaga
{
    private const SEL = 'v.*, e.nome AS e_nome, e.cnpj AS e_cnpj, e.endereco AS e_endereco, e.email AS e_email, e.telefone AS e_telefone, e.site AS e_site, e.linkedin AS e_linkedin,
                         e.status AS e_status, e.ultimo_acesso AS e_acesso, e.logo AS e_logo, e.segmento AS e_segmento, e.cidade AS e_cidade, e.uf AS e_uf, e.link_principal AS e_lp';
    private const FROM = 'FROM vagas v JOIN empresas e ON e.id = v.empresa_id';
    /** Campos de conteúdo: qualquer mudança nelas em vaga aprovada exige nova aprovação. */
    private const CONTEUDO = ['titulo', 'tipo', 'area', 'modalidade', 'local', 'bolsa', 'carga_horas_dia', 'carga_dias', 'carga_turno', 'descricao', 'atividades', 'requisitos',
        'requisitos_desejaveis', 'beneficios', 'data_publicacao', 'data_limite', 'contato', 'imagem', 'link_site', 'link_linkedin', 'link_candidatura', 'link_plataforma'];

    public static function linhas(?string $t): array { return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $t)), 'strlen')); }
    /** Lista de itens (um por linha): sem repetidos, até 20 itens de 120 caracteres. */
    public static function normalizarLista(?string $t): string
    {
        $o = [];
        foreach (self::linhas($t) as $x) { $x = mb_substr($x, 0, 120); if (!in_array(mb_strtolower($x), array_map('mb_strtolower', $o), true)) $o[] = $x; }
        return implode("\n", array_slice($o, 0, 20));
    }
    public static function cargaTexto(?int $horas, ?string $dias, ?string $turno): string
    {
        $p = [];
        if ($horas) $p[] = $horas . 'h/dia';
        $sel = array_values(array_intersect(array_keys(DIAS_SEMANA), array_filter(explode(',', (string) $dias))));   // na ordem da semana
        if ($sel) {
            $todos = array_keys(DIAS_SEMANA); $ini = array_search($sel[0], $todos, true);
            $continuo = count($sel) > 2 && array_slice($todos, $ini, count($sel)) === $sel;
            $p[] = $continuo ? DIAS_SEMANA[$sel[0]] . ' a ' . DIAS_SEMANA[end($sel)] : implode(', ', array_map(fn($x) => DIAS_SEMANA[$x], $sel));
        }
        if ($turno && isset(TURNOS[$turno])) $p[] = TURNOS[$turno];
        return implode(' · ', $p);
    }

    private static function hidratar(array $r): array
    {
        $r['empresa'] = ['id' => $r['empresa_id'], 'nome' => $r['e_nome'], 'cnpj' => $r['e_cnpj'], 'endereco' => $r['e_endereco'], 'email' => $r['e_email'], 'telefone' => $r['e_telefone'],
            'site' => $r['e_site'], 'linkedin' => $r['e_linkedin'], 'status' => $r['e_status'], 'ultimo_acesso' => $r['e_acesso'], 'logo' => $r['e_logo'], 'segmento' => $r['e_segmento'],
            'cidade' => $r['e_cidade'], 'uf' => $r['e_uf'], 'link_principal' => $r['e_lp']];
        foreach (['e_nome', 'e_cnpj', 'e_endereco', 'e_email', 'e_telefone', 'e_site', 'e_linkedin', 'e_status', 'e_acesso', 'e_logo', 'e_segmento', 'e_cidade', 'e_uf', 'e_lp'] as $k) unset($r[$k]);
        foreach (['atividades', 'requisitos', 'requisitos_desejaveis', 'beneficios'] as $c) $r[$c] = self::linhas($r[$c] ?? '');
        $r['resumo'] = resumo_da_descricao($r['descricao']);          // breve descrição = primeiras linhas da descrição
        $r['bolsa_txt'] = dinheiro($r['bolsa']);
        $r['area_exibicao'] = $r['area'];
        $r['situacao'] = Estado::situacaoVaga($r);                    // calculada pelas datas, no backend
        $r['dias'] = dias_restantes($r['data_limite']);
        $r['carga_txt'] = self::cargaTexto($r['carga_horas_dia'] !== null ? (int) $r['carga_horas_dia'] : null, $r['carga_dias'], $r['carga_turno']) ?: ($r['carga_horaria'] ?: '');
        return $r;
    }

    // ---------- consultas públicas ----------
    /** Vagas no ar: aprovadas, de empresa aprovada e dentro do período. */
    public static function publicas(): array
    {
        [$sit, $p] = Estado::sqlSituacao('publicada');
        $rows = Database::all('SELECT ' . self::SEL . ' ' . self::FROM . " WHERE $sit AND e.status = 'aprovada' ORDER BY v.data_publicacao DESC, v.id DESC", $p);
        return self::marcar(array_map([self::class, 'hidratar'], $rows));
    }
    /** Histórico para o estudante: expiradas e encerradas de empresas ainda aprovadas (últimos 120 dias). */
    public static function encerradasPublico(int $n = 60): array
    {
        $desde = date('Y-m-d', time() - 120 * 86400);
        $rows = Database::all('SELECT ' . self::SEL . ' ' . self::FROM . " WHERE e.status = 'aprovada' AND v.data_limite >= :d AND (v.status = 'encerrada' OR (v.status = 'aprovada' AND v.data_limite < :h)) ORDER BY v.data_limite DESC LIMIT " . (int) $n, ['d' => $desde, 'h' => date('Y-m-d')]);
        return array_map([self::class, 'hidratar'], $rows);
    }
    private static function marcar(array $l): array
    {
        $padroes = array_column(Database::all('SELECT nome FROM areas WHERE padrao = 1'), 'nome');
        $cont = array_count_values(array_column($l, 'area'));
        $sal = array_filter(array_column($l, 'bolsa', 'id'), fn($b) => $b !== null);
        arsort($sal);
        $top = count($sal) >= 4 ? array_map('intval', array_slice(array_keys($sal), 0, 5)) : [];
        $limiteAtivo = time() - DIAS_RECRUTADOR_ATIVO * 86400;
        foreach ($l as &$v) {
            $v['area_exibicao'] = (in_array($v['area'], $padroes, true) || ($cont[$v['area']] ?? 0) >= AREA_MIN_VAGAS) ? $v['area'] : 'Outras';
            $v['recrutador_ativo'] = $v['empresa']['ultimo_acesso'] && strtotime($v['empresa']['ultimo_acesso']) >= $limiteAtivo;
            $v['melhor_salario'] = in_array((int) $v['id'], $top, true);
            $v['selos'] = self::selos($v);
        }
        return $l;
    }
    public static function selos(array $v): array
    {
        $s = ['tipo' => [$v['tipo'], 'vg-tag-tipo']];
        $d = dias_restantes($v['data_limite']);
        if ($d <= DIAS_VAGA_VENCENDO) $s['vencendo'] = [$d <= 0 ? 'Encerra hoje' : 'Encerra em ' . $d . ($d === 1 ? ' dia' : ' dias'), 'vg-tag-warn'];
        if (vaga_e_nova($v)) $s['nova'] = ['Nova', 'vg-tag-new'];
        if (!empty($v['destaque'])) $s['destaque'] = ['Destaque', 'vg-tag-star'];
        if (!empty($v['melhor_salario'])) $s['salario'] = ['Melhor salário', 'vg-tag-money'];
        if (!empty($v['recrutador_ativo'])) $s['ativo'] = ['Recrutador ativo', 'vg-tag-hot'];
        return $s;
    }
    public static function destaques(array $publicas): array { return array_slice(array_values(array_filter($publicas, fn($v) => !empty($v['destaque']))), 0, CARROSSEL_MAX); }

    /** Vaga que o ESTUDANTE pode abrir: no ar, expirada ou encerrada (histórico). Nunca em análise, rejeitada ou agendada. */
    public static function visivelPorId(int $id): ?array
    {
        $v = self::porId($id);
        if (!$v || $v['empresa']['status'] !== Estado::E_APROVADA) return null;
        if (!in_array($v['situacao'], ['publicada', 'expirada', 'encerrada'], true)) return null;
        if ($v['situacao'] === 'publicada') { foreach (self::publicas() as $p) if ((int) $p['id'] === $id) return $p; }
        return $v;
    }
    public static function existe(int $id): bool { return (bool) Database::val('SELECT 1 FROM vagas WHERE id = :i', ['i' => $id]); }
    public static function relacionadas(array $vaga, array $publicas, int $n = 3): array
    {
        $o = array_values(array_filter($publicas, fn($v) => (int) $v['id'] !== (int) $vaga['id']));
        usort($o, fn($a, $b) => ($b['area'] === $vaga['area']) <=> ($a['area'] === $vaga['area']));
        return array_slice($o, 0, $n);
    }
    public static function areasFiltro(array $publicas): array
    {
        $a = array_unique(array_column($publicas, 'area_exibicao')); sort($a, SORT_LOCALE_STRING);
        return array_values(array_merge(array_diff($a, ['Outras']), in_array('Outras', $a, true) ? ['Outras'] : []));
    }

    // ---------- painéis (empresa e admin) ----------
    public static function porId(int $id): ?array
    {
        $r = Database::one('SELECT ' . self::SEL . ' ' . self::FROM . ' WHERE v.id = :i', ['i' => $id]);
        return $r ? self::hidratar($r) : null;
    }
    /** Ownership: só devolve a vaga se pertencer à empresa informada. */
    public static function daEmpresaPorId(int $empId, int $id): ?array
    {
        $v = self::porId($id);
        return ($v && (int) $v['empresa_id'] === $empId) ? $v : null;
    }

    public static function contagens(?int $empId = null): array
    {
        $h = ['hoje' => date('Y-m-d')]; $w = $empId ? 'WHERE empresa_id = :e' : ''; $p = $h + ($empId ? ['e' => $empId] : []);
        $r = Database::one("SELECT COUNT(*) AS todas,
            SUM(CASE WHEN status = 'aguardando_aprovacao' THEN 1 ELSE 0 END) AS aguardando,
            SUM(CASE WHEN status = 'aprovada' AND data_publicacao > :hoje THEN 1 ELSE 0 END) AS agendada,
            SUM(CASE WHEN status = 'aprovada' AND data_publicacao <= :hoje AND data_limite >= :hoje THEN 1 ELSE 0 END) AS publicada,
            SUM(CASE WHEN status = 'aprovada' AND data_publicacao <= :hoje AND data_limite >= :hoje AND destaque = 1 THEN 1 ELSE 0 END) AS destaque,
            SUM(CASE WHEN status = 'aprovada' AND data_limite < :hoje THEN 1 ELSE 0 END) AS expirada,
            SUM(CASE WHEN status = 'encerrada' THEN 1 ELSE 0 END) AS encerrada,
            SUM(CASE WHEN status = 'rejeitada' THEN 1 ELSE 0 END) AS rejeitada
            FROM vagas $w", $p);
        return array_map('intval', $r ?: []);
    }

    /** Listagem com aba (situação), busca, tipo, ordem e paginação feitas no banco. @return array [linhas, total] */
    public static function listar(?int $empId, string $aba, string $busca, string $tipo, string $ordem, int $pg, int $por = POR_PAGINA, ?int $soEmpresa = null): array
    {
        $w = ['1 = 1']; $p = [];
        if ($empId) { $w[] = 'v.empresa_id = :emp'; $p['emp'] = $empId; }
        if ($aba === 'destaque') { [$s, $sp] = Estado::sqlSituacao('publicada'); $w[] = "$s AND v.destaque = 1"; $p += $sp; }
        elseif (in_array($aba, Estado::SITUACOES, true)) { [$s, $sp] = Estado::sqlSituacao($aba); $w[] = $s; $p += $sp; }
        if ($busca !== '') { $w[] = '(LOWER(v.titulo) LIKE :q OR LOWER(e.nome) LIKE :q OR LOWER(v.codigo) LIKE :q OR LOWER(v.area) LIKE :q)'; $p['q'] = '%' . mb_strtolower($busca) . '%'; }
        if ($tipo !== '' && in_array($tipo, TIPOS_VAGA, true)) { $w[] = 'v.tipo = :tipo'; $p['tipo'] = $tipo; }
        $where = implode(' AND ', $w);
        $ord = ['recentes' => 'v.id DESC', 'prazo' => 'v.data_limite ASC, v.id DESC', 'titulo' => 'v.titulo ASC', 'empresa' => 'e.nome ASC, v.id DESC'][$ordem] ?? 'v.id DESC';
        $total = (int) Database::val('SELECT COUNT(*) ' . self::FROM . " WHERE $where", $p);
        [$off] = Paginador::calc($total, $pg, $por);
        $rows = Database::all('SELECT ' . self::SEL . ' ' . self::FROM . " WHERE $where ORDER BY $ord LIMIT " . (int) $por . ' OFFSET ' . (int) $off, $p);
        return [array_map([self::class, 'hidratar'], $rows), $total];
    }
    public static function todasDaEmpresa(int $empId): array
    {
        return array_map([self::class, 'hidratar'], Database::all('SELECT ' . self::SEL . ' ' . self::FROM . ' WHERE v.empresa_id = :e ORDER BY v.id DESC', ['e' => $empId]));
    }
    public static function areas(): array { return array_column(Database::all('SELECT nome FROM areas ORDER BY padrao DESC, nome'), 'nome'); }

    private static function semAcento(string $s): string
    {
        return mb_strtolower(strtr(trim($s), ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','É'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ç'=>'c']));
    }
    /** "Outra área": cria (ou reaproveita) a área. @return array [nome|null, erro|null] */
    public static function resolverArea(string $escolha, string $outra): array
    {
        if ($escolha !== AREA_OUTRA) { foreach (self::areas() as $a) { if ($a === $escolha) return [$a, null]; } return [null, 'Escolha uma área.']; }
        $nome = preg_replace('/\s+/u', ' ', trim($outra));
        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 60) return [null, 'Informe o nome da área (3 a 60 caracteres).'];
        $nome = mb_strtoupper(mb_substr($nome, 0, 1)) . mb_substr($nome, 1);
        foreach (self::areas() as $a) { if (self::semAcento($a) === self::semAcento($nome)) return [$a, null]; }
        Database::insert('areas', ['nome' => $nome, 'padrao' => 0]);
        return [$nome, null];
    }

    // ---------- escrita (sempre passando pela máquina de estados + histórico) ----------
    private static function dadosGravar(array $d): array
    {
        $carga = self::cargaTexto($d['carga_horas_dia'] ?? null, $d['carga_dias'] ?? null, $d['carga_turno'] ?? null);
        $n = fn($k) => (($d[$k] ?? '') !== '') ? $d[$k] : null;
        return [
            'titulo' => $d['titulo'], 'tipo' => $d['tipo'], 'area' => $d['area'], 'modalidade' => $d['modalidade'], 'local' => $d['local'], 'bolsa' => $d['bolsa'],
            'carga_horas_dia' => $n('carga_horas_dia'), 'carga_dias' => $n('carga_dias'), 'carga_turno' => $n('carga_turno'), 'carga_horaria' => $carga ?: null,
            'descricao' => $d['descricao'], 'atividades' => $d['atividades'] ?? '', 'requisitos' => $d['requisitos'] ?? '', 'requisitos_desejaveis' => $d['requisitos_desejaveis'] ?? '',
            'beneficios' => $d['beneficios'] ?? '', 'data_publicacao' => $d['data_publicacao'], 'data_limite' => $d['data_limite'], 'contato' => $d['contato'] ?? '', 'imagem' => $d['imagem'] ?? null,
            'link_site' => $n('link_site'), 'link_linkedin' => $n('link_linkedin'), 'link_candidatura' => $n('link_candidatura'), 'link_plataforma' => $n('link_plataforma'),
        ];
    }
    public static function criar(array $d, int $empId): int
    {
        $id = Database::insert('vagas', self::dadosGravar($d) + ['codigo' => 'TMP' . bin2hex(random_bytes(6)), 'empresa_id' => $empId, 'status' => Estado::V_AGUARDANDO, 'versao' => 1, 'criado_em' => Database::agora()]);
        Database::update('vagas', $id, ['codigo' => sprintf('VG-%s-%04d', date('Y'), $id)]);
        Historico::registrar('vaga', $id, 'criada', null, Estado::V_AGUARDANDO, 'empresa', $empId);
        // primeira publicação: o aviso "empresa aprovada" não precisa mais aparecer
        Database::update('empresas', $empId, ['aprovacao_vista' => 1]);
        return $id;
    }

    /** Campos de conteúdo que mudaram entre a vaga atual e os dados novos. */
    public static function camposAlterados(array $atual, array $novo): array
    {
        // bolsa vem como int/float/string conforme o banco (SQLite devolve int): compara sempre com 2 casas
        $norm = fn($v, $k) => $v === null || $v === '' ? '' : ($k === 'bolsa' ? number_format((float) $v, 2, '.', '') : trim(str_replace("\r", '', (string) $v)));   // navegadores enviam \r\n; o banco guarda \n
        $g = self::dadosGravar($novo + ['imagem' => $atual['imagem']]); $diff = [];
        foreach (self::CONTEUDO as $k) {
            $a = $atual[$k] ?? null;
            if (in_array($k, ['atividades', 'requisitos', 'requisitos_desejaveis', 'beneficios'], true)) $a = implode("\n", (array) $a);
            if ($norm($a, $k) !== $norm($g[$k] ?? null, $k)) $diff[] = $k;
        }
        return $diff;
    }

    /** Empresa edita a vaga. Aprovada/rejeitada com alteração -> volta para análise (exige nova aprovação). @return array [mudou, novoStatus] */
    public static function atualizar(int $id, array $novo, int $empId): array
    {
        $v = self::porId($id);
        if (!$v || (int) $v['empresa_id'] !== $empId) throw new RuntimeException('Vaga não pertence à empresa.');
        if ($v['status'] === Estado::V_ENCERRADA) throw new RuntimeException('Vaga encerrada não pode ser editada.');
        $dif = self::camposAlterados($v, $novo);
        if (!$dif) return [false, $v['status']];
        $dados = self::dadosGravar($novo + ['imagem' => $v['imagem']]) + ['atualizado_em' => Database::agora(), 'versao' => (int) $v['versao'] + 1];
        $para = $v['status'];
        if (in_array($v['status'], [Estado::V_APROVADA, Estado::V_REJEITADA], true)) {
            if (!Estado::vagaPode($v['status'], Estado::V_AGUARDANDO)) throw new RuntimeException('Transição inválida.');
            $para = Estado::V_AGUARDANDO; $dados['status'] = $para; $dados['motivo'] = null; $dados['avaliado_em'] = null;
        }
        Database::update('vagas', $id, $dados);
        Historico::registrar('vaga', $id, $v['status'] === Estado::V_REJEITADA ? 'reenviada' : 'editada', $v['status'], $para, 'empresa', $empId, 'Campos: ' . implode(', ', $dif));
        return [true, $para];
    }

    /** Aprovar/rejeitar/encerrar com validação da transição. @return bool */
    public static function mudarStatus(int $id, string $para, ?string $motivo, string $atorTipo, ?int $atorId): bool
    {
        $v = Database::one('SELECT status FROM vagas WHERE id = :i', ['i' => $id]);
        if (!$v || !Estado::vagaPode($v['status'], $para)) return false;
        $d = ['status' => $para, 'motivo' => $para === Estado::V_REJEITADA ? $motivo : null];
        if (in_array($para, [Estado::V_APROVADA, Estado::V_REJEITADA], true)) $d['avaliado_em'] = Database::agora();
        if ($para === Estado::V_ENCERRADA) $d['aviso_encerrada'] = 1;
        Database::update('vagas', $id, $d);
        Historico::registrar('vaga', $id, $para === Estado::V_APROVADA ? 'aprovada' : ($para === Estado::V_REJEITADA ? 'rejeitada' : 'encerrada'), $v['status'], $para, $atorTipo, $atorId, $motivo);
        return true;
    }
    public static function alternarDestaque(int $id, string $atorTipo, ?int $atorId): void
    {
        Database::run('UPDATE vagas SET destaque = 1 - destaque WHERE id = :i', ['i' => $id]);
        Historico::registrar('vaga', $id, 'destaque', null, null, $atorTipo, $atorId);
    }

    /** Rotina diária (cron). NÃO muda o estado: vaga expirada continua no sistema (a situação vem das datas). Só envia avisos. @return array [expiradas avisadas, vencendo avisadas] */
    public static function rotina(): array
    {
        $h = date('Y-m-d'); $em3 = date('Y-m-d', strtotime('+3 days')); $exp = 0; $av = 0;
        $q = 'SELECT ' . self::SEL . ' ' . self::FROM;
        foreach (Database::all($q . " WHERE v.status = 'aprovada' AND v.aviso_encerrada = 0 AND v.data_limite < :h", ['h' => $h]) as $r) {
            $v = self::hidratar($r);
            Database::update('vagas', (int) $v['id'], ['aviso_encerrada' => 1]);
            Historico::registrar('vaga', (int) $v['id'], 'expirada', 'publicada', 'expirada', 'sistema', null);
            Notificador::vagaEncerrada($v, $v['empresa']); $exp++;
        }
        foreach (Database::all($q . " WHERE v.status = 'aprovada' AND v.aviso_vencendo = 0 AND v.data_limite >= :h AND v.data_limite <= :t", ['h' => $h, 't' => $em3]) as $r) {
            $v = self::hidratar($r);
            Database::update('vagas', (int) $v['id'], ['aviso_vencendo' => 1]);
            Notificador::vagaVencendo($v, $v['empresa']); $av++;
        }
        return [$exp, $av];
    }
}
```

### Passo 62 — VagaForm.php

#### Arquivo: `app/VagaForm.php` — CRIAR

```php
<?php
/** Validação do cadastro/edição de vaga (mesma regra para criar e editar). */
class VagaForm
{
    /** @return array [dados normalizados, erros] ; $atual = vaga existente (edição) */
    public static function validar(array $d, array $files, ?array $atual = null): array
    {
        $e = []; $hoje = date('Y-m-d');
        $d['descricao'] = str_replace(["\r\n", "\r"], "\n", (string) ($d['descricao'] ?? ''));   // grava sempre com \n
        $len = fn($k) => mb_strlen($d[$k] ?? '');
        // 1. básicas
        if ($len('titulo') < 5 || $len('titulo') > 80) $e['titulo'] = 'Informe o nome da vaga (5 a 80 caracteres).';
        if (!in_array($d['tipo'] ?? '', TIPOS_VAGA, true)) $e['tipo'] = 'Escolha o tipo da oportunidade.';
        if (!in_array($d['modalidade'] ?? '', MODALIDADES, true)) $e['modalidade'] = 'Escolha a modalidade.';
        [$area, $eArea] = Vaga::resolverArea($d['area'] ?? '', $d['area_outra'] ?? '');
        if ($eArea) $e['area'] = $eArea;
        if ($len('local') < 2 || $len('local') > 80) $e['local'] = 'Informe o local.';
        if ($len('descricao') < 40) $e['descricao'] = 'Descreva a vaga com mais detalhes (mínimo 40 caracteres). As primeiras linhas viram a breve descrição do card.';
        // 2/3. requisitos e benefícios (um item por linha; a tela monta com chips)
        $d['requisitos'] = Vaga::normalizarLista($d['requisitos'] ?? ''); $d['requisitos_desejaveis'] = Vaga::normalizarLista($d['requisitos_desejaveis'] ?? '');
        $d['beneficios'] = Vaga::normalizarLista($d['beneficios'] ?? ''); $d['atividades'] = Vaga::normalizarLista($d['atividades'] ?? '');
        if ($d['requisitos'] === '') $e['requisitos'] = 'Informe ao menos um requisito obrigatório.';
        // 4. remuneração estruturada
        $bolsa = null;
        if (($d['remuneracao_tipo'] ?? 'valor') === 'combinar') { $bolsa = null; }
        else {
            $bolsa = parse_dinheiro($d['bolsa'] ?? '');
            if ($bolsa === null || $bolsa <= 0 || $bolsa > 99999.99) $e['bolsa'] = 'Informe um valor mensal válido (somente números) ou escolha "A combinar".';
        }
        // 5. carga horária estruturada
        $h = $d['carga_horas_dia'] ?? ''; $horas = ctype_digit((string) $h) ? (int) $h : 0;
        if ($horas < 1 || $horas > 12) $e['carga_horas_dia'] = 'Informe de 1 a 12 horas por dia.';
        $dias = array_values(array_intersect(array_keys(DIAS_SEMANA), (array) ($d['dias'] ?? [])));
        if (!$dias) $e['dias'] = 'Marque ao menos um dia da semana.';
        if (!isset(TURNOS[$d['carga_turno'] ?? ''])) $e['carga_turno'] = 'Escolha o turno.';
        // 6. período
        $pub = $d['data_publicacao'] ?? ''; $lim = $d['data_limite'] ?? '';
        $okData = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) && checkdate((int) substr($s, 5, 2), (int) substr($s, 8, 2), (int) substr($s, 0, 4));
        $pubAntiga = $atual && $pub === $atual['data_publicacao'];
        if (!$okData($pub) || ($pub < $hoje && !$pubAntiga)) $e['data_publicacao'] = 'Escolha uma data de hoje em diante.';
        if (!$okData($lim) || $lim <= $pub) $e['data_limite'] = 'A data final deve ser depois da inicial.';
        elseif ((strtotime($lim) - strtotime($pub)) > 366 * 86400) $e['data_limite'] = 'O período máximo é de 1 ano.';
        elseif ($lim < $hoje) $e['data_limite'] = 'A data final já passou.';
        // 7. links
        foreach (['link_site' => null, 'link_linkedin' => 'linkedin'] as $k => $plat) { $d[$k] = Link::normalizar($d[$k] ?? ''); if ($d[$k] && !Link::valido($d[$k], $plat)) $e[$k] = $plat ? 'Use um endereço do LinkedIn.' : 'Endereço inválido.'; }
        $d['link_candidatura'] = Link::normalizar($d['link_candidatura'] ?? '');
        $plat = $d['link_plataforma'] ?? '';
        if ($plat !== '' && !isset(Link::PLATAFORMAS[$plat])) $e['link_plataforma'] = 'Escolha a plataforma do processo seletivo.';
        if ($d['link_candidatura'] && !Link::valido($d['link_candidatura'], in_array($plat, ['gupy', 'linkedin'], true) ? $plat : null)) {
            $e['link_candidatura'] = $plat === 'gupy' ? 'O link da Gupy deve ser de gupy.io.' : ($plat === 'linkedin' ? 'O link deve ser do LinkedIn.' : 'Endereço inválido.');
        }
        if ($d['link_candidatura'] && $plat === '') $e['link_plataforma'] = 'Informe onde o processo seletivo acontece.';
        if (($d['contato'] ?? '') !== '' && !filter_var($d['contato'], FILTER_VALIDATE_EMAIL)) $e['contato'] = 'E-mail inválido.';
        if (($d['contato'] ?? '') === '' && !$d['link_candidatura']) $e['contato'] = 'Informe um e-mail ou um link para candidatura.';
        // banner
        [$img, $eImg] = Upload::salvar($files['banner'] ?? null, ['image/jpeg', 'image/png'], ROOT_PATH . '/public/uploads/banners', false);
        if ($eImg) $e['banner'] = $eImg; elseif ($img) $d['imagem'] = 'public/uploads/banners/' . $img;

        if ($e) { if ($img) @unlink(ROOT_PATH . '/public/uploads/banners/' . $img); return [$d, $e]; }
        return [[
            'titulo' => $d['titulo'], 'tipo' => $d['tipo'], 'area' => $area, 'modalidade' => $d['modalidade'], 'local' => $d['local'], 'bolsa' => $bolsa,
            'carga_horas_dia' => $horas, 'carga_dias' => implode(',', $dias), 'carga_turno' => $d['carga_turno'], 'descricao' => $d['descricao'], 'atividades' => $d['atividades'],
            'requisitos' => $d['requisitos'], 'requisitos_desejaveis' => $d['requisitos_desejaveis'], 'beneficios' => $d['beneficios'], 'data_publicacao' => $pub, 'data_limite' => $lim,
            'contato' => $d['contato'] ?? '', 'imagem' => $d['imagem'] ?? ($atual['imagem'] ?? null), 'link_site' => $d['link_site'], 'link_linkedin' => $d['link_linkedin'],
            'link_candidatura' => $d['link_candidatura'], 'link_plataforma' => $plat ?: null,
        ], []];
    }

    /** Vaga existente -> formato do formulário (edição). */
    public static function paraFormulario(array $v): array
    {
        return ['titulo' => $v['titulo'], 'tipo' => $v['tipo'], 'area' => $v['area'], 'modalidade' => $v['modalidade'], 'local' => $v['local'], 'descricao' => $v['descricao'],
            'requisitos' => implode("\n", $v['requisitos']), 'requisitos_desejaveis' => implode("\n", $v['requisitos_desejaveis']), 'beneficios' => implode("\n", $v['beneficios']), 'atividades' => implode("\n", $v['atividades']),
            'remuneracao_tipo' => $v['bolsa'] === null ? 'combinar' : 'valor', 'bolsa' => $v['bolsa'] === null ? '' : number_format((float) $v['bolsa'], 2, ',', ''),
            'carga_horas_dia' => (string) ($v['carga_horas_dia'] ?? ''), 'dias' => array_filter(explode(',', (string) $v['carga_dias'])), 'carga_turno' => (string) $v['carga_turno'],
            'data_publicacao' => $v['data_publicacao'], 'data_limite' => $v['data_limite'], 'contato' => $v['contato'], 'link_site' => (string) $v['link_site'],
            'link_linkedin' => (string) $v['link_linkedin'], 'link_candidatura' => (string) $v['link_candidatura'], 'link_plataforma' => (string) $v['link_plataforma']];
    }
}
```

### Passo 63 — Stats.php

#### Arquivo: `Models/Stats.php` — CRIAR

```php
<?php
/** Indicadores do dashboard e do controle de acesso. Tudo vem do banco; sem dados, a tela mostra estado vazio (nada é inventado). */
class Stats
{
    public static function empresas(): array
    {
        $r = Database::one("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) AS pendente, SUM(CASE WHEN status = 'aprovada' THEN 1 ELSE 0 END) AS aprovada,
            SUM(CASE WHEN status = 'reprovada' THEN 1 ELSE 0 END) AS reprovada, SUM(CASE WHEN status = 'bloqueada' THEN 1 ELSE 0 END) AS bloqueada FROM empresas");
        return array_map('intval', $r ?: []);
    }
    /** Cadastros de empresas e envios de vagas por semana (últimas N semanas, mais antiga primeiro). */
    public static function porSemana(int $n = 8): array
    {
        $ini = strtotime('monday this week') - ($n - 1) * 7 * 86400;
        $b = []; for ($i = 0; $i < $n; $i++) $b[$i] = ['rot' => date('d/m', $ini + $i * 7 * 86400), 'empresas' => 0, 'vagas' => 0];
        $desde = date('Y-m-d H:i:s', $ini);
        foreach (['empresas', 'vagas'] as $t) {
            foreach (Database::all("SELECT criado_em FROM $t WHERE criado_em >= :d", ['d' => $desde]) as $r) {
                $i = (int) floor((strtotime($r['criado_em']) - $ini) / (7 * 86400)); if ($i >= 0 && $i < $n) $b[$i][$t]++;
            }
        }
        return $b;
    }
    public static function acessosResumo(int $dias = 7): array
    {
        $d = date('Y-m-d H:i:s', time() - $dias * 86400); $o = ['login_ok' => 0, 'login_falha' => 0, 'negado' => 0, 'bloqueado' => 0, 'timeout' => 0, 'logout' => 0];
        foreach (Database::all('SELECT evento, COUNT(*) AS n FROM acessos_log WHERE criado_em >= :d GROUP BY evento', ['d' => $d]) as $r) $o[$r['evento']] = (int) $r['n'];
        return $o;
    }
    public static function loginsPorDia(int $dias = 7): array
    {
        $b = []; for ($i = $dias - 1; $i >= 0; $i--) $b[date('Y-m-d', time() - $i * 86400)] = ['rot' => date('d/m', time() - $i * 86400), 'ok' => 0, 'falha' => 0];
        foreach (Database::all("SELECT evento, criado_em FROM acessos_log WHERE evento IN ('login_ok','login_falha') AND criado_em >= :d", ['d' => date('Y-m-d 00:00:00', time() - ($dias - 1) * 86400)]) as $r) {
            $k = substr($r['criado_em'], 0, 10); if (isset($b[$k])) $b[$k][$r['evento'] === 'login_ok' ? 'ok' : 'falha']++;
        }
        return array_values($b);
    }
    public static function eventosRecentes(int $n = 15, bool $soSuspeitos = false): array
    {
        $w = $soSuspeitos ? "WHERE evento IN ('login_falha','negado','bloqueado')" : '';
        return Database::all("SELECT * FROM acessos_log $w ORDER BY id DESC LIMIT " . (int) $n);
    }
    public static function ultimosAcessos(string $perfil, int $n = 8): array
    {
        $t = $perfil === 'admin' ? 'admins' : 'empresas';
        return Database::all("SELECT id, nome, email, ultimo_acesso FROM $t WHERE ultimo_acesso IS NOT NULL ORDER BY ultimo_acesso DESC LIMIT " . (int) $n);
    }
    /** Chaves (e-mail+IP) travadas agora pelo limite de tentativas. */
    public static function bloqueiosAtivos(): int
    {
        $d = date('Y-m-d H:i:s', time() - 15 * 60);
        return (int) Database::val('SELECT COUNT(*) FROM (SELECT chave FROM login_tentativas WHERE criado_em >= :d GROUP BY chave HAVING COUNT(*) >= 5) t', ['d' => $d]);
    }
    public static function contasRestritas(): array
    {
        return ['empresas_bloqueadas' => (int) Database::val("SELECT COUNT(*) FROM empresas WHERE status = 'bloqueada'"), 'admins_inativos' => (int) Database::val('SELECT COUNT(*) FROM admins WHERE ativo = 0'),
                'admins_ativos' => (int) Database::val('SELECT COUNT(*) FROM admins WHERE ativo = 1')];
    }
    public static function empresasRecentes(int $n = 5): array { return Database::all('SELECT id, nome, status, criado_em FROM empresas ORDER BY id DESC LIMIT ' . (int) $n); }
}
```

### Passo 64 — VagaController.php

#### Arquivo: `Controllers/VagaController.php` — CRIAR

```php
<?php
class VagaController
{
    /** Home estilo "portal de vagas": destaque + carrosséis temáticos + lista filtrável. */
    public function index(): void
    {
        $vagas = Vaga::publicas();
        $ord = function (array $l, callable $cmp) { usort($l, $cmp); return $l; };
        $trilhos = [
            ['id' => 'novas', 'titulo' => 'Vagas novas', 'icone' => 'flame', 'chave' => 'nova',
             'itens' => array_slice(array_values(array_filter($vagas, fn($v) => isset($v['selos']['nova']))), 0, 12)],
            ['id' => 'ativos', 'titulo' => 'Recrutador ativo', 'icone' => 'user', 'chave' => 'ativo',
             'sub' => 'Empresas que acessaram o painel nos últimos ' . DIAS_RECRUTADOR_ATIVO . ' dias.',
             'itens' => array_slice(array_values(array_filter($vagas, fn($v) => $v['recrutador_ativo'])), 0, 12)],
            ['id' => 'salarios', 'titulo' => 'Melhores salários', 'icone' => 'trending', 'chave' => 'salario',
             'itens' => array_slice($ord(array_filter($vagas, fn($v) => $v['bolsa'] !== null), fn($a, $b) => $b['bolsa'] <=> $a['bolsa']), 0, 12)],
            ['id' => 'jovem', 'titulo' => 'Jovem Aprendiz e Jovem Profissional', 'icone' => 'star', 'chave' => 'tipo',
             'itens' => array_values(array_filter($vagas, fn($v) => in_array($v['tipo'], ['Jovem Aprendiz', 'Jovem Profissional'], true)))],
            ['id' => 'trainee', 'titulo' => 'Trainee', 'icone' => 'briefcase', 'chave' => 'tipo',
             'itens' => array_values(array_filter($vagas, fn($v) => $v['tipo'] === 'Trainee'))],
            ['id' => 'estagio', 'titulo' => 'Estágio', 'icone' => 'building', 'chave' => 'tipo',
             'itens' => array_slice(array_values(array_filter($vagas, fn($v) => $v['tipo'] === 'Estágio')), 0, 12)],
            ['id' => 'vencendo', 'titulo' => 'Encerrando em breve', 'icone' => 'clock', 'chave' => 'vencendo',
             'itens' => $ord(array_filter($vagas, fn($v) => isset($v['selos']['vencendo'])), fn($a, $b) => strcmp($a['data_limite'], $b['data_limite']))],
        ];
        view('home', [
            'titulo' => 'Vagas de Estágio e Primeiro Emprego', 'vagas' => $vagas, 'destaques' => Vaga::destaques($vagas),
            'trilhos' => array_values(array_filter($trilhos, fn($t) => $t['itens'])),
            'areas' => Vaga::areasFiltro($vagas), 'tipos' => array_values(array_intersect(TIPOS_VAGA, array_column($vagas, 'tipo'))),
            'modalidades' => array_values(array_intersect(MODALIDADES, array_column($vagas, 'modalidade'))),
        ]);
    }

    /** Detalhe público: no ar, expirada ou encerrada (histórico). Em análise, rejeitada e agendada nunca aparecem. */
    public function mostrar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $vaga = Vaga::visivelPorId($id);
        if (!$vaga) {
            http_response_code(Vaga::existe($id) ? 410 : 404);
            view('vaga_indisponivel', ['titulo' => 'Vaga indisponível']);
            return;
        }
        $aberta = $vaga['situacao'] === 'publicada';
        view('vaga', ['titulo' => $vaga['titulo'] . ' - ' . $vaga['empresa']['nome'], 'vaga' => $vaga, 'aberta' => $aberta, 'relacionadas' => $aberta ? Vaga::relacionadas($vaga, Vaga::publicas()) : []]);
    }

    /** Vagas que já saíram do ar (expiradas ou encerradas), só para consulta. */
    public function encerradas(): void
    {
        view('vagas_encerradas', ['titulo' => 'Vagas encerradas', 'vagas' => Vaga::encerradasPublico()]);
    }

    public function tv(): void { view('tv', ['vagas' => Vaga::publicas()]); }
}
```

### Passo 65 — ParceirasController.php

#### Arquivo: `Controllers/ParceirasController.php` — CRIAR

```php
<?php
/** Empresas parceiras para o estudante. Só empresas APROVADAS aparecem. */
class ParceirasController
{
    public function index(): void
    {
        $trilhos = [];
        foreach ([['recentes', 'Empresas recentes', 'Cadastradas há pouco tempo.'], ['frequentes', 'Empresas mais frequentes', 'As que mais publicam vagas.'], ['abertas', 'Com vagas abertas agora', 'Oportunidades disponíveis neste momento.']] as [$k, $t, $sub]) {
            $l = Empresa::parceiras($k, 12);
            if ($l) $trilhos[] = ['id' => $k, 'titulo' => $t, 'sub' => $sub, 'itens' => $l];
        }
        view('empresas_parceiras', ['titulo' => 'Empresas parceiras', 'trilhos' => $trilhos, 'todas' => Empresa::parceiras('nome', 200)]);
    }

    /** Perfil público da empresa dentro do sistema (vagas abertas e informações). */
    public function perfil(): void
    {
        $e = Empresa::parceiraPorId((int) ($_GET['id'] ?? 0));
        if (!$e) { http_response_code(404); view('vaga_indisponivel', ['titulo' => 'Empresa não encontrada']); return; }
        $vagas = array_values(array_filter(Vaga::publicas(), fn($v) => (int) $v['empresa_id'] === (int) $e['id']));
        view('empresa_publica', ['titulo' => $e['nome'], 'e' => $e, 'vagas' => $vagas]);
    }

    /** Aviso "você está saindo do sistema". O destino vem SEMPRE do banco (nunca da URL): sem open redirect. */
    public function externo(): void
    {
        $e = Empresa::parceiraPorId((int) ($_GET['id'] ?? 0));
        $destino = $e ? Empresa::linkExterno($e) : null;
        if (!$e || !$destino) redirect($e ? 'empresa' : 'empresas', $e ? ['id' => $e['id']] : []);
        view('externo', ['titulo' => 'Saindo do sistema', 'e' => $e, 'destino' => $destino]);
    }
}
```

### Passo 66 — EmpresaAreaController.php

#### Arquivo: `Controllers/EmpresaAreaController.php` — CRIAR

```php
<?php
/** Área da empresa: só enxerga e altera o que é dela (ownership conferido no backend em toda ação). */
class EmpresaAreaController
{
    private function aprovada(array $emp): void
    {
        if ($emp['status'] !== Estado::E_APROVADA) { flash('info', 'Sua empresa ainda não foi aprovada pela Coordenação. Assim que for, você poderá publicar vagas.'); redirect('painel'); }
    }
    private function pagina(string $view, array $dados, string $secao, string $titulo): void
    {
        render($view, $dados + ['titulo' => $titulo, 'area' => 'empresa', 'secao' => $secao, 'emp' => $dados['emp']], 'app');
    }

    public function painel(): void
    {
        $emp = Auth::exigirEmpresa();
        $cont = Vaga::contagens((int) $emp['id']);
        [$recentes] = Vaga::listar((int) $emp['id'], 'todas', '', '', 'recentes', 1, 3);
        $vencendo = array_values(array_filter(Vaga::todasDaEmpresa((int) $emp['id']), fn($v) => $v['situacao'] === 'publicada' && $v['dias'] <= DIAS_VAGA_VENCENDO));
        $this->pagina('empresa_painel', ['emp' => $emp, 'cont' => $cont, 'recentes' => $recentes, 'vencendo' => $vencendo, 'aviso' => Empresa::mostrarAvisoAprovacao($emp)], 'painel', 'Visão geral');
    }

    public function dispensarAviso(): void
    {
        $emp = Auth::exigirEmpresa();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { Csrf::exigir(); Empresa::dispensarAviso((int) $emp['id']); }
        redirect('painel');
    }

    public function vagas(): void
    {
        $emp = Auth::exigirEmpresa();
        $cont = Vaga::contagens((int) $emp['id']);
        $aba = $_GET['aba'] ?? 'todas';
        if (!in_array($aba, array_merge(['todas', 'destaque'], Estado::SITUACOES), true)) $aba = 'todas';
        $q = trim((string) ($_GET['q'] ?? '')); $pg = (int) ($_GET['pg'] ?? 1);
        [$lista, $total] = Vaga::listar((int) $emp['id'], $aba, $q, '', 'recentes', $pg);
        [, $pg, $paginas] = Paginador::calc($total, $pg, POR_PAGINA);
        $this->pagina('empresa_vagas', compact('emp', 'cont', 'aba', 'q', 'lista', 'total', 'pg', 'paginas'), 'vagas', 'Minhas vagas');
    }

    public function vaga(): void
    {
        $emp = Auth::exigirEmpresa();
        $v = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_GET['id'] ?? 0));
        if (!$v) Auth::recusar('vaga');
        $this->pagina('empresa_vaga', ['emp' => $emp, 'v' => $v, 'hist' => Historico::da('vaga', (int) $v['id'])], 'vagas', $v['titulo']);
    }

    public function perfil(): void
    {
        $emp = Auth::exigirEmpresa();
        $erros = []; $old = $emp;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            if (($_POST['acao'] ?? '') === 'reenviar') {   // empresa reprovada corrige o perfil e pede nova análise
                if (Empresa::mudarStatus((int) $emp['id'], Estado::E_PENDENTE, null, 'empresa', (int) $emp['id'])) {
                    Notificador::empresaCadastrada(Empresa::porId((int) $emp['id']));
                    flash('ok', 'Cadastro reenviado para análise.');
                }
                redirect('painel');
            }
            $old = limpar_post($_POST) + ['logo' => $emp['logo']];
            [$dados, $erros] = EmpresaForm::validar($old, $_FILES, 'perfil', $emp);
            if (!$erros) {
                if (empty($dados['logo'])) $dados['logo'] = $emp['logo'];
                elseif ($emp['logo'] && $dados['logo'] !== $emp['logo']) @unlink(ROOT_PATH . '/' . $emp['logo']);
                Empresa::atualizarPerfil((int) $emp['id'], $dados);
                flash('ok', 'Perfil atualizado.');
                redirect('empresa-perfil');
            }
        }
        $this->pagina('empresa_perfil', ['emp' => $emp, 'old' => $old, 'erros' => $erros, 'cont' => Vaga::contagens((int) $emp['id'])], 'perfil', 'Perfil da empresa');
    }

    /** Criar (sem id) ou editar (com id) a vaga, em etapas. */
    public function formVaga(): void
    {
        $emp = Auth::exigirEmpresa(); $this->aprovada($emp);
        $id = (int) ($_GET['id'] ?? 0); $atual = null;
        if ($id) {
            $atual = Vaga::daEmpresaPorId((int) $emp['id'], $id);
            if (!$atual) Auth::recusar('vaga');
            if ($atual['status'] === Estado::V_ENCERRADA) { flash('info', 'Vagas encerradas não podem ser editadas.'); redirect('empresa-vagas'); }
        }
        $erros = []; $old = $atual ? VagaForm::paraFormulario($atual) : ['remuneracao_tipo' => 'valor', 'data_publicacao' => date('Y-m-d'), 'contato' => $emp['email_contato'] ?: $emp['email'], 'dias' => ['seg', 'ter', 'qua', 'qui', 'sex']];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::exigir();
            $old = limpar_post($_POST);
            [$dados, $erros] = VagaForm::validar($old, $_FILES, $atual);
            if (!$erros) {
                if ($atual) {
                    [$mudou, $para] = Vaga::atualizar($id, $dados, (int) $emp['id']);
                    $v = Vaga::porId($id);
                    if ($mudou && $para === Estado::V_AGUARDANDO) {
                        Notificador::vagaReenviada($v, $emp);
                        flash('ok', 'Alterações salvas. A vaga saiu do ar e volta depois de nova aprovação da Coordenação.');
                    } else flash('ok', $mudou ? 'Alterações salvas.' : 'Nada foi alterado.');
                    redirect('empresa-vaga', ['id' => $id]);
                }
                $novo = Vaga::criar($dados, (int) $emp['id']);
                Notificador::vagaEnviada(Vaga::porId($novo), $emp);
                flash('ok', 'Vaga enviada para análise. Você receberá um e-mail com o resultado e o período em que ficará no ar.');
                redirect('empresa-vaga', ['id' => $novo]);
            }
        }
        $this->pagina('vaga_form', ['emp' => $emp, 'atual' => $atual, 'old' => $old, 'erros' => $erros, 'areas' => Vaga::areas()], 'vagas', $atual ? 'Editar vaga' : 'Publicar vaga');
    }

    public function encerrar(): void
    {
        $emp = Auth::exigirEmpresa();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('empresa-vagas');
        Csrf::exigir();
        $v = Vaga::daEmpresaPorId((int) $emp['id'], (int) ($_POST['id'] ?? 0));
        if (!$v) Auth::recusar('vaga');
        if (Vaga::mudarStatus((int) $v['id'], Estado::V_ENCERRADA, null, 'empresa', (int) $emp['id'])) flash('ok', 'Vaga encerrada. Ela deixou de aparecer para os estudantes e continua no seu histórico.');
        else flash('erro', 'Só vagas aprovadas podem ser encerradas.');
        redirect('empresa-vagas');
    }
}
```

### Passo 67 — AdminController.php

#### Arquivo: `Controllers/AdminController.php` — CRIAR

```php
<?php
/** Administração: cada ação é autorizada aqui no backend (Auth::exigirAdmin), nunca só escondendo botões. */
class AdminController
{
    private function pagina(string $view, array $dados, string $secao, string $titulo): void
    {
        $dados += ['adm' => Auth::admin()];
        render($view, $dados + ['titulo' => $titulo, 'area' => 'admin', 'secao' => $secao, 'pendentes' => (int) Database::val("SELECT COUNT(*) FROM empresas WHERE status = 'pendente'") + (int) Database::val("SELECT COUNT(*) FROM vagas WHERE status = 'aguardando_aprovacao'")], 'app');
    }
    private function paramLista(): array
    {
        return [trim((string) ($_GET['q'] ?? '')), max(1, (int) ($_GET['pg'] ?? 1))];
    }

    public function painel(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_dashboard', [
            'emp' => Stats::empresas(), 'vag' => Vaga::contagens(), 'semanas' => Stats::porSemana(8), 'acessos' => Stats::acessosResumo(7), 'logins' => Stats::loginsPorDia(7),
            'atividade' => Historico::recentes(8), 'empresasRecentes' => Stats::empresasRecentes(5), 'naoLidas' => Notificacao::naoLidas(), 'notifs' => Notificacao::recentes(6),
        ], 'dashboard', 'Dashboard');
    }

    public function vagas(): void
    {
        Auth::exigirAdmin();
        $cont = Vaga::contagens();
        $aba = $_GET['aba'] ?? 'todas';
        if (!in_array($aba, array_merge(['todas', 'destaque'], Estado::SITUACOES), true)) $aba = 'todas';
        [$q, $pg] = $this->paramLista();
        $tipo = (string) ($_GET['tipo'] ?? ''); $ord = (string) ($_GET['ord'] ?? 'recentes'); $vista = ($_GET['vista'] ?? 'cards') === 'tabela' ? 'tabela' : 'cards';
        [$lista, $total] = Vaga::listar(null, $aba, $q, $tipo, $ord, $pg);
        [, $pg, $paginas] = Paginador::calc($total, $pg, POR_PAGINA);
        $this->pagina('admin_vagas', compact('cont', 'aba', 'q', 'tipo', 'ord', 'vista', 'lista', 'total', 'pg', 'paginas'), 'vagas', 'Vagas');
    }

    public function vaga(): void
    {
        Auth::exigirAdmin();
        $v = Vaga::porId((int) ($_GET['id'] ?? 0));
        if (!$v) { http_response_code(404); render('erro', ['titulo' => 'Não encontrada', 'msg' => 'Vaga não encontrada.', 'area' => 'admin', 'secao' => 'vagas'], 'app'); return; }
        $this->pagina('admin_vaga', ['v' => $v, 'hist' => Historico::da('vaga', (int) $v['id'])], 'vagas', $v['titulo']);
    }

    public function empresas(): void
    {
        Auth::exigirAdmin();
        $abas = Empresa::abasAdmin();
        $aba = array_key_exists($_GET['aba'] ?? '', $abas) ? $_GET['aba'] : ($abas['pendentes'][1] ? 'pendentes' : 'todas');
        [$q, $pg] = $this->paramLista();
        [$lista, $total] = Empresa::listarAdmin($aba, $q, $pg);
        [, $pg, $paginas] = Paginador::calc($total, $pg, POR_PAGINA);
        $this->pagina('admin_empresas', compact('abas', 'aba', 'q', 'lista', 'total', 'pg', 'paginas'), 'empresas', 'Empresas');
    }

    public function empresa(): void
    {
        Auth::exigirAdmin();
        $e = Empresa::comContagem((int) ($_GET['id'] ?? 0));
        if (!$e) { http_response_code(404); render('erro', ['titulo' => 'Não encontrada', 'msg' => 'Empresa não encontrada.', 'area' => 'admin', 'secao' => 'empresas'], 'app'); return; }
        $this->pagina('admin_empresa', ['e' => $e, 'vagas' => Vaga::todasDaEmpresa((int) $e['id']), 'hist' => Historico::da('empresa', (int) $e['id'])], 'empresas', $e['nome']);
    }

    public function aprovacoes(): void
    {
        Auth::exigirAdmin();
        [$vp] = Vaga::listar(null, 'aguardando', '', '', 'recentes', 1, 50);
        $this->pagina('admin_aprovacoes', ['empPend' => Empresa::pendentes(), 'vagasPend' => $vp], 'aprovacoes', 'Aprovações');
    }

    public function usuarios(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_usuarios', ['admins' => Admin::todos(), 'erros' => $_SESSION['erros_admin'] ?? [], 'old' => $_SESSION['old_admin'] ?? []], 'usuarios', 'Usuários');
        unset($_SESSION['erros_admin'], $_SESSION['old_admin']);
    }

    public function acesso(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_acesso', [
            'resumo' => Stats::acessosResumo(7), 'resumo30' => Stats::acessosResumo(30), 'bloqueios' => Stats::bloqueiosAtivos(), 'contas' => Stats::contasRestritas(),
            'ultAdmins' => Stats::ultimosAcessos('admin'), 'ultEmpresas' => Stats::ultimosAcessos('empresa'), 'suspeitos' => Stats::eventosRecentes(12, true), 'eventos' => Stats::eventosRecentes(15), 'logins' => Stats::loginsPorDia(7),
        ], 'acesso', 'Controle de acesso');
    }

    public function relatorios(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_relatorios', ['vag' => Vaga::contagens(), 'emp' => Stats::empresas()], 'relatorios', 'Relatórios');
    }

    public function config(): void
    {
        Auth::exigirAdmin();
        $this->pagina('admin_config', ['emails' => Mailer::recentes(8), 'mailModo' => Mailer::modo()], 'config', 'Configurações');
    }

    // ---------- ações (POST) ----------
    private const RETORNOS = ['admin', 'admin-vagas', 'admin-vaga', 'admin-empresas', 'admin-empresa', 'admin-aprovacoes', 'admin-usuarios', 'admin-config'];

    public function acao(): void
    {
        $adm = Auth::exigirAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin');
        Csrf::exigir();
        $tipo = (string) ($_POST['tipo'] ?? ''); $acao = (string) ($_POST['acao'] ?? ''); $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim((string) ($_POST['motivo'] ?? ''));
        $ator = ['admin', (int) $adm['id']];
        $ret = in_array($_POST['ret'] ?? '', self::RETORNOS, true) ? $_POST['ret'] : 'admin';
        $retParams = ((int) ($_POST['ret_id'] ?? 0)) ? ['id' => (int) $_POST['ret_id']] : [];
        $voltar = function () use (&$ret, &$retParams) { redirect($ret, $retParams); };

        switch ($tipo) {
            case 'notificacoes': Notificacao::marcarLidas(); $voltar();

            case 'email-teste':
                $ok = Mailer::enviar($adm['email'], 'Teste de e-mail do sistema de vagas', Mailer::layout('Teste de e-mail', '<p>Se você está lendo isto, o envio de e-mails está funcionando.</p>'));
                if (!$ok) flash('erro', 'Falha ao enviar: ' . (Mailer::$ultimoErro ?: 'erro desconhecido'));
                elseif (Mailer::modo() === 'log') flash('info', 'Modo de teste (MAIL_DRIVER=log): nada foi enviado de verdade. A mensagem foi gravada em storage/mail. Configure SMTP no .env para receber na caixa de entrada.');
                else flash('ok', 'E-mail de teste enviado para ' . $adm['email'] . '. Confira a caixa de entrada e o spam.');
                redirect('admin-config');

            case 'empresa':
                $emp = Empresa::porId($id);
                if (!$emp) { flash('erro', 'Empresa não encontrada.'); $voltar(); }
                $para = ['aprovar' => Estado::E_APROVADA, 'reprovar' => Estado::E_REPROVADA, 'bloquear' => Estado::E_BLOQUEADA, 'desbloquear' => Estado::E_APROVADA][$acao] ?? null;
                if (!$para) $voltar();
                if (in_array($acao, ['reprovar', 'bloquear'], true) && mb_strlen($motivo) < 5) { flash('erro', 'Informe o motivo (ele é enviado à empresa).'); $voltar(); }
                if (!Empresa::mudarStatus($id, $para, $motivo ?: null, $ator[0], $ator[1])) { flash('erro', 'Essa ação não é permitida para a situação atual da empresa.'); $voltar(); }
                $nova = Empresa::porId($id);
                if ($acao !== 'desbloquear') Notificador::empresaAvaliada($nova);
                flash('ok', $emp['nome'] . ': ' . ['aprovar' => 'aprovada', 'reprovar' => 'reprovada', 'bloquear' => 'bloqueada', 'desbloquear' => 'desbloqueada'][$acao] . '. ' . ($acao === 'desbloquear' ? '' : 'A empresa foi avisada por e-mail.'));
                break;

            case 'vaga':
                $v = Vaga::porId($id);
                if (!$v) { flash('erro', 'Vaga não encontrada.'); $voltar(); }
                if ($acao === 'destaque') { Vaga::alternarDestaque($id, $ator[0], $ator[1]); flash('ok', 'Destaque atualizado.'); break; }
                $para = ['aprovar' => Estado::V_APROVADA, 'rejeitar' => Estado::V_REJEITADA, 'encerrar' => Estado::V_ENCERRADA][$acao] ?? null;
                if (!$para) $voltar();
                if ($acao === 'aprovar' && $v['empresa']['status'] !== Estado::E_APROVADA) { flash('erro', 'Aprove a empresa antes de aprovar a vaga.'); $voltar(); }
                if ($acao === 'rejeitar' && mb_strlen($motivo) < 5) { flash('erro', 'Informe o motivo (ele é enviado à empresa).'); $voltar(); }
                if (!Vaga::mudarStatus($id, $para, $motivo ?: null, $ator[0], $ator[1])) { flash('erro', 'Essa ação não é permitida para a situação atual da vaga (' . Estado::rotuloSituacao($v['situacao']) . ').'); $voltar(); }
                Notificador::vagaAvaliada(Vaga::porId($id), $v['empresa']);
                flash('ok', 'Vaga ' . ['aprovar' => 'aprovada', 'rejeitar' => 'rejeitada', 'encerrar' => 'encerrada'][$acao] . '. A empresa foi avisada por e-mail.');
                break;

            case 'admin':   // gestão de usuários administradores
                if ($acao === 'criar') {
                    $d = limpar_post($_POST); $er = [];
                    if (mb_strlen($d['nome'] ?? '') < 3) $er['nome'] = 'Informe o nome.';
                    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)) $er['email'] = 'E-mail inválido.'; elseif (Admin::porEmail($d['email'])) $er['email'] = 'Já existe um administrador com este e-mail.';
                    if (!EmpresaForm::senhaForte($_POST['senha'] ?? '')) $er['senha'] = 'A senha precisa ter 8 ou mais caracteres, com letras e números.';
                    if ($er) { $_SESSION['erros_admin'] = $er; $_SESSION['old_admin'] = $d; $voltar(); }
                    $novo = Admin::criar($d['nome'], $d['email'], $_POST['senha']);
                    Historico::registrar('admin', $novo, 'cadastro', null, null, $ator[0], $ator[1], $d['email']);
                    flash('ok', 'Administrador criado.');
                } elseif (in_array($acao, ['ativar', 'desativar'], true)) {
                    if ($id === (int) $adm['id']) { flash('erro', 'Você não pode desativar a própria conta.'); $voltar(); }
                    if ($acao === 'desativar' && Stats::contasRestritas()['admins_ativos'] <= 1) { flash('erro', 'É preciso manter ao menos um administrador ativo.'); $voltar(); }
                    Admin::definirAtivo($id, $acao === 'ativar');
                    Historico::registrar('admin', $id, $acao === 'ativar' ? 'desbloqueada' : 'bloqueada', null, null, $ator[0], $ator[1]);
                    flash('ok', 'Conta ' . ($acao === 'ativar' ? 'ativada' : 'desativada') . '.');
                }
                break;
        }
        if (Mailer::$falhas > 0) flash('erro', 'A ação foi feita, mas o e-mail NÃO foi enviado: ' . (Mailer::$ultimoErro ?: 'erro desconhecido') . ' (veja Configurações > Diagnóstico de e-mail).');
        $voltar();
    }

    /** Termo de cooperação: arquivo fora da pasta pública; só admin lê. */
    public function termo(): void
    {
        Auth::exigirAdmin();
        $emp = Empresa::porId((int) ($_GET['id'] ?? 0));
        $arq = $emp ? ROOT_PATH . '/storage/termos/' . basename((string) $emp['termo_arquivo']) : '';
        if (!$emp || !$emp['termo_arquivo'] || !is_file($arq)) { http_response_code(404); exit('Arquivo não encontrado.'); }
        header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($arq));
        header('Content-Disposition: inline; filename="termo-' . preg_replace('/\W/', '', $emp['cnpj']) . '.' . pathinfo($arq, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, no-store');
        readfile($arq); exit;
    }

    /** CSV (vagas ou empresas). Células que começam com = + - @ recebem apóstrofo (evita injeção de fórmula no Excel). */
    public function exportar(): void
    {
        Auth::exigirAdmin();
        $tipo = ($_GET['tipo'] ?? '') === 'empresas' ? 'empresas' : 'vagas';
        $esc = function ($v) { $v = (string) $v; return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v; };
        header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="' . $tipo . '-' . date('Ymd') . '.csv"'); header('X-Content-Type-Options: nosniff');
        $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
        if ($tipo === 'vagas') {
            fputcsv($o, ['Código', 'Título', 'Tipo', 'Empresa', 'Área', 'Modalidade', 'Local', 'Remuneração', 'Situação', 'Publicação', 'Data limite', 'Criada em'], ';');
            [$l] = Vaga::listar(null, 'todas', '', '', 'recentes', 1, 100000);
            foreach ($l as $v) fputcsv($o, array_map($esc, [$v['codigo'], $v['titulo'], $v['tipo'], $v['empresa']['nome'], $v['area'], $v['modalidade'], $v['local'], $v['bolsa_txt'], Estado::rotuloSituacao($v['situacao']), data_br($v['data_publicacao']), data_br($v['data_limite']), data_br(substr($v['criado_em'], 0, 10))]), ';');
        } else {
            fputcsv($o, ['CNPJ', 'Razão social', 'Nome fantasia', 'Segmento', 'Cidade/UF', 'Responsável', 'E-mail', 'Situação', 'Vagas', 'Cadastro'], ';');
            [$l] = Empresa::listarAdmin('todas', '', 1, 100000);
            foreach ($l as $e) fputcsv($o, array_map($esc, [Cnpj::formatar($e['cnpj']), $e['razao_social'], $e['nome'], $e['segmento'], trim(($e['cidade'] ?? '') . '/' . ($e['uf'] ?? ''), '/'), $e['responsavel'], $e['email'], Estado::STATUS_EMPRESA[$e['status']][0] ?? $e['status'], $e['total_vagas'], data_br(substr($e['criado_em'], 0, 10))]), ';');
        }
        fclose($o); exit;
    }
}
```

### Passo 68 — rotina.php

#### Arquivo: `cron/rotina.php` — CRIAR

```php
<?php
// Rode 1x por dia (cPanel > Cron Jobs):  php /home/USUARIO/projeto-estagio/cron/rotina.php
// Envia os avisos por e-mail ("encerra em breve" e "prazo encerrado"). NÃO altera o estado das vagas: a situação (publicada/expirada) vem das datas, calculada pelo backend.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config.php';
[$exp, $av] = Vaga::rotina();
echo date('c') . " avisos enviados: expiradas=$exp vencendo=$av\n";
```

### Passo 69 — Verificar a branch

```bash
php -r 'require "config.php"; Bootstrap::garantir(); echo json_encode(Vaga::contagens()), "\n";'
php cron/rotina.php
```

Resultado esperado:
- `php -r 'require "config.php"; Bootstrap::garantir(); echo json_encode(…` → {"todas":0,...}
- `php cron/rotina.php…` → avisos enviados: expiradas=0 vencendo=0

### Passo 70 — Fechar a branch

```bash
git add .
git commit -m "feat(vagas): modelo, validação, estados, reaprovação e controllers de empresa e administração"
git push -u origin feature/backend-jobs
```
Abra o Pull Request `feature/backend-jobs` → `develop` e peça revisão a **Pedro Miguel**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 9: `feature/frontend-company` — dono: Rodrigo

Telas da empresa: login, cadastro em 5 etapas com ViaCEP, perfil e lista de vagas em cards.

**Revisor do PR:** José Roberto. **Depende de:** `feature/design-system`, `feature/backend-jobs`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `106.06` | Tela de login da empresa independente, sem cabeçalho/rodapé do IFSP | 1 | 2 |
| `105.03` | Componente de formulário em etapas reutilizável (progresso, validação por etapa e revisão) | 5 | 3 |
| `105.04` | Etapa 1 – Dados da empresa (razão social, fantasia, CNPJ, segmento, porte, descrição, site, LinkedIn, logo) | 2 | 4 |
| `105.05` | Etapa 2 – Endereço com ViaCEP | 3 | 4 |
| `105.06` | Etapa 3 – Contato (responsável, cargo, telefone, celular, e-mail de contato) | 1 | 5 |
| `105.07` | Etapas 4 e 5 – Acesso (e-mail, senha e regras) e Termos (aceites e termo assinado) | 2 | 5 |
| `105.08` | Etapa 6 – Tela de conclusão do cadastro | 1 | 5 |
| `105.09` | Perfil da empresa: visualizar e editar (logo, descrição, segmento, links e link principal) | 3 | 6 |
| `108.03` | Lista de vagas da empresa em cards com abas por situação, busca e paginação | 3 | 7 |
| `105.16` | Encerramento de vaga com modal de confirmação; a vaga permanece no banco | 2 | 7 |
| `109.02` | Logo da empresa com pré-visualização e iniciais como alternativa | 2 | 8 |

```bash
git checkout develop && git pull
git checkout -b feature/frontend-company
```

### Passo 71 — empresa_login.php

#### Arquivo: `Views/empresa_login.php` — CRIAR

```php
<?php $larga = false; ?>
<div class="auth-card">
  <span class="auth-env"><?= icon('building', 14) ?> Ambiente da empresa</span>
  <h1>Entrar</h1><p class="lead">Acesse a área da sua empresa para gerenciar o perfil e as vagas.</p>
  <form method="post" action="<?= url('empresa-login') ?>" data-form novalidate>
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next) ?>">
    <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($erro) ?></div></div><?php endif; ?>
    <?= Ui::campo(['name' => 'email', 'label' => 'E-mail de acesso', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username']], ['email' => $email]) ?>
    <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
    <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Entrar</button>
  </form>
  <div class="auth-links">
    <span>Esqueceu a senha? Fale com a Coordenação do campus.</span>
    <span>Ainda não é parceira? <a href="<?= url('empresa-cadastro') ?>">Cadastrar empresa</a></span>
  </div>
</div>
```

### Passo 72 — empresa_campos.php

#### Arquivo: `Views/partials/empresa_campos.php` — CRIAR
Etapas 1 a 3 (dados, endereço com CEP, contato), usadas no cadastro e no perfil.

```php
<?php
/** Etapas 1 a 3 (dados, endereço, contato). Usadas no cadastro e na edição do perfil. Espera $old, $erros, $modo ('cadastro'|'perfil'). */
$c = fn(array $o) => Ui::campo($o, $old, $erros);
$logoAtual = $old['logo'] ?? null;
?>
<fieldset class="ui-fieldset ui-step" data-step="0" data-title="Dados da empresa">
  <legend>Dados da empresa</legend><p class="ui-step-sub">Essas informações aparecem para os estudantes na página da empresa.</p>
  <?php if ($modo === 'cadastro'): ?>
    <?= $c(['name' => 'cnpj', 'label' => 'CNPJ', 'req' => true, 'hint' => 'Aceita o CNPJ novo, com letras e números, e também o só com números.', 'attrs' => ['maxlength' => 18, 'autocapitalize' => 'characters', 'spellcheck' => 'false', 'placeholder' => '12.ABC.345/01DE-35', 'data-mask' => 'cnpj', 'data-rev' => 'CNPJ']]) ?>
  <?php else: ?>
    <div class="ui-field"><span class="ui-label">CNPJ</span><div><span class="ui-code"><?= e(Cnpj::formatar($old['cnpj'])) ?></span> <small class="ui-hint" style="display:inline">Não pode ser alterado. Fale com a Coordenação se precisar corrigir.</small></div></div>
  <?php endif; ?>
  <div class="ui-cols">
    <?= $c(['name' => 'razao_social', 'label' => 'Razão social', 'req' => true, 'attrs' => ['maxlength' => 150, 'data-rev' => 'Razão social']]) ?>
    <?= $c(['name' => 'nome', 'label' => 'Nome fantasia', 'req' => true, 'hint' => 'É o nome exibido nas vagas.', 'attrs' => ['maxlength' => 120, 'autocomplete' => 'organization', 'data-rev' => 'Nome fantasia']]) ?>
  </div>
  <div class="ui-cols">
    <?= $c(['name' => 'segmento', 'label' => 'Segmento', 'type' => 'select', 'req' => true, 'opcoes' => SEGMENTOS, 'attrs' => ['data-rev' => 'Segmento']]) ?>
    <?= $c(['name' => 'porte', 'label' => 'Porte', 'type' => 'select', 'req' => true, 'opcoes' => PORTES, 'attrs' => ['data-rev' => 'Porte']]) ?>
  </div>
  <?= $c(['name' => 'descricao', 'label' => 'Sobre a empresa', 'type' => 'textarea', 'rows' => 5, 'req' => true, 'hint' => 'De 30 a 600 caracteres. Conte o que a empresa faz e como é o ambiente para estagiários.', 'attrs' => ['maxlength' => 600, 'minlength' => 30]]) ?>
  <div class="ui-cols">
    <?= $c(['name' => 'site', 'label' => 'Site oficial', 'attrs' => ['data-url' => '', 'placeholder' => 'https://www.suaempresa.com.br', 'data-rev' => 'Site', 'maxlength' => 200]]) ?>
    <?= $c(['name' => 'linkedin', 'label' => 'LinkedIn da empresa', 'attrs' => ['data-url' => '', 'data-host' => 'linkedin.com', 'placeholder' => 'https://www.linkedin.com/company/...', 'data-rev' => 'LinkedIn', 'maxlength' => 200]]) ?>
  </div>
  <div class="ui-field"><span class="ui-label">Para onde o estudante vai ao clicar na empresa?</span>
    <div class="ui-checks" role="radiogroup" aria-label="Link principal">
      <label class="ui-check"><input type="radio" name="link_principal" value="site" <?= ($old['link_principal'] ?? 'site') !== 'linkedin' ? 'checked' : '' ?>> Site oficial</label>
      <label class="ui-check"><input type="radio" name="link_principal" value="linkedin" <?= ($old['link_principal'] ?? '') === 'linkedin' ? 'checked' : '' ?>> LinkedIn</label>
    </div><small class="ui-hint">Se o escolhido estiver vazio, usamos o outro. O estudante é avisado de que está saindo do sistema.</small></div>
  <div class="ui-field"><label for="logo">Logo da empresa</label>
    <div class="ui-logo-pick">
      <?php if ($logoAtual): ?><img class="ui-logo" data-logo-prev width="64" height="64" src="<?= e(BASE_URL . $logoAtual) ?>" alt="Logo atual"><?php else: ?><span class="ui-logo ui-logo-ini" data-logo-prev style="width:64px;height:64px"><?= e(iniciais($old['nome'] ?: 'Empresa')) ?></span><?php endif; ?>
      <div><input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png" data-file data-max="2" data-img-only data-logo-pick><small class="ui-hint">JPG ou PNG, até 2 MB. Quadrado fica melhor.</small></div></div>
    <small class="ui-err" data-err><?= e($erros['logo'] ?? '') ?></small></div>
</fieldset>

<fieldset class="ui-fieldset ui-step" data-step="1" data-title="Endereço">
  <legend>Endereço</legend><p class="ui-step-sub">Digite o CEP e preenchemos o resto. Você confirma ou corrige.</p>
  <div class="ui-field" style="max-width:240px"><label for="cep">CEP <span class="ui-req" aria-hidden="true">*</span></label>
    <input id="cep" name="cep" class="ui-input <?= isset($erros['cep']) ? 'is-invalid' : '' ?>" inputmode="numeric" maxlength="9" autocomplete="postal-code" placeholder="00000-000" data-mask="cep" data-viacep data-rev="CEP" value="<?= e($old['cep'] ?? '') ?>" required>
    <small class="ui-hint" data-cep-status aria-live="polite">Ao digitar o CEP, preenchemos rua, bairro, cidade e estado.</small><small class="ui-err" data-err><?= e($erros['cep'] ?? '') ?></small></div>
  <?= $c(['name' => 'logradouro', 'label' => 'Rua, avenida ou travessa', 'req' => true, 'attrs' => ['maxlength' => 120, 'data-rev' => 'Rua', 'data-cep-fill' => 'logradouro', 'autocomplete' => 'address-line1']]) ?>
  <div class="ui-cols ui-cols-num">
    <?= $c(['name' => 'numero', 'label' => 'Número', 'req' => true, 'attrs' => ['maxlength' => 10, 'data-rev' => 'Número', 'placeholder' => 'S/N se não houver']]) ?>
    <?= $c(['name' => 'complemento', 'label' => 'Complemento', 'attrs' => ['maxlength' => 60, 'data-rev' => 'Complemento', 'placeholder' => 'Sala, andar, bloco']]) ?>
  </div>
  <?= $c(['name' => 'bairro', 'label' => 'Bairro', 'req' => true, 'attrs' => ['maxlength' => 60, 'data-rev' => 'Bairro', 'data-cep-fill' => 'bairro']]) ?>
  <div class="ui-cols ui-cols-uf">
    <?= $c(['name' => 'cidade', 'label' => 'Cidade', 'req' => true, 'attrs' => ['maxlength' => 60, 'data-rev' => 'Cidade', 'data-cep-fill' => 'cidade', 'autocomplete' => 'address-level2']]) ?>
    <?= $c(['name' => 'uf', 'label' => 'Estado', 'type' => 'select', 'req' => true, 'vazio' => 'UF', 'opcoes' => UFS, 'attrs' => ['data-cep-fill' => 'uf', 'data-rev' => 'Estado']]) ?>
  </div>
</fieldset>

<fieldset class="ui-fieldset ui-step" data-step="2" data-title="Contato">
  <legend>Contato</legend><p class="ui-step-sub">Quem a Coordenação procura em caso de dúvida.</p>
  <div class="ui-cols">
    <?= $c(['name' => 'responsavel', 'label' => 'Nome do responsável', 'req' => true, 'attrs' => ['maxlength' => 100, 'autocomplete' => 'name', 'data-rev' => 'Responsável']]) ?>
    <?= $c(['name' => 'cargo', 'label' => 'Cargo ou função', 'req' => true, 'attrs' => ['maxlength' => 60, 'data-rev' => 'Cargo', 'placeholder' => 'Ex.: Analista de RH']]) ?>
  </div>
  <div class="ui-cols">
    <?= $c(['name' => 'telefone', 'label' => 'Telefone', 'req' => true, 'attrs' => ['inputmode' => 'tel', 'placeholder' => '11 4000-0000', 'data-mask' => 'tel', 'data-rev' => 'Telefone']]) ?>
    <?= $c(['name' => 'celular', 'label' => 'Celular / WhatsApp', 'attrs' => ['inputmode' => 'tel', 'placeholder' => '11 90000-0000', 'data-mask' => 'tel']]) ?>
  </div>
  <?= $c(['name' => 'email_contato', 'label' => 'E-mail de contato da empresa', 'type' => 'email', 'hint' => 'Opcional. Aparece no perfil da empresa. Se vazio, usamos o e-mail de acesso.', 'attrs' => ['maxlength' => 120]]) ?>
</fieldset>
```

### Passo 73 — empresa_cadastro.php

#### Arquivo: `Views/empresa_cadastro.php` — CRIAR

```php
<?php
$larga = true; $modo = 'cadastro'; $old = $old + ['logo' => null, 'cnpj' => '', 'nome' => ''];
$etapaDe = ['cnpj' => 0, 'razao_social' => 0, 'nome' => 0, 'descricao' => 0, 'segmento' => 0, 'porte' => 0, 'site' => 0, 'linkedin' => 0, 'logo' => 0,
    'cep' => 1, 'logradouro' => 1, 'numero' => 1, 'complemento' => 1, 'bairro' => 1, 'cidade' => 1, 'uf' => 1, 'responsavel' => 2, 'cargo' => 2, 'telefone' => 2, 'celular' => 2, 'email_contato' => 2,
    'email' => 3, 'senha' => 3, 'senha2' => 3, 'termo' => 4, 'aceite_termos' => 4, 'aceite_privacidade' => 4];
$inicio = $erros ? min(array_map(fn($k) => $etapaDe[$k] ?? 0, array_keys($erros))) : 0;
?>
<div class="auth-card">
  <span class="auth-env"><?= icon('building', 14) ?> Cadastro de empresa parceira</span>
  <h1>Cadastre sua empresa</h1>
  <p class="lead">Informe apenas os dados essenciais. Depois que a Coordenação aprovar o cadastro, sua empresa poderá publicar vagas de estágio.</p>
  <form class="ui-form" method="post" action="<?= url('empresa-cadastro') ?>" enctype="multipart/form-data" data-form data-wiz data-start="<?= (int) $inicio ?>" novalidate>
    <?= csrf_field() ?><span data-stepper></span>
    <?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert"><div>Corrija os campos destacados e envie de novo.</div></div><?php endif; ?>
    <?php require __DIR__ . '/partials/empresa_campos.php'; ?>

    <fieldset class="ui-fieldset ui-step" data-step="3" data-title="Acesso">
      <legend>Acesso à plataforma</legend><p class="ui-step-sub">Com esses dados você entra na área da empresa.</p>
      <?= Ui::campo(['name' => 'email', 'label' => 'E-mail de acesso', 'type' => 'email', 'req' => true, 'hint' => 'Recebe os avisos de aprovação e é usado para entrar.', 'attrs' => ['maxlength' => 120, 'autocomplete' => 'username', 'data-rev' => 'E-mail de acesso']], $old, $erros) ?>
      <div class="ui-cols">
        <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'new-password', 'minlength' => 8, 'data-pwd' => '']], [], $erros) ?>
        <?= Ui::campo(['name' => 'senha2', 'label' => 'Repita a senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'new-password']], [], $erros) ?>
      </div>
      <ul class="ui-rules" data-pwd-rules><li>Pelo menos 8 caracteres</li><li>Pelo menos uma letra</li><li>Pelo menos um número</li></ul>
    </fieldset>

    <fieldset class="ui-fieldset ui-step" data-step="4" data-title="Termos">
      <legend>Termos e confirmação</legend><p class="ui-step-sub">Leia, anexe o termo assinado e confirme.</p>
      <div class="ui-terms" tabindex="0" aria-label="Termos de uso e política de privacidade">
        <h3>Termos de uso</h3>
        <p>A empresa declara que os dados informados são verdadeiros e atualizados, e que as vagas divulgadas respeitam a legislação de estágio e as regras do campus. Cada vaga passa por análise da Coordenação antes de ser publicada e pode ser reprovada, bloqueada ou encerrada quando houver informação incorreta ou descumprimento do termo de cooperação.</p>
        <h3>Privacidade</h3>
        <p>O sistema armazena somente dados da empresa e de seus responsáveis (nome, cargo, telefone e e-mail), usados para análise do cadastro, comunicação e exibição do perfil. Nenhum dado pessoal de estudante é coletado. Os dados são tratados conforme a LGPD.</p>
        <p><em>Texto-base: revisar e aprovar com a Coordenação do IFSP antes da publicação.</em></p>
      </div>
      <div class="ui-field"><label for="termo">Termo de cooperação assinado <span class="ui-req" aria-hidden="true">*</span></label>
        <input id="termo" name="termo" type="file" accept=".pdf,.jpg,.jpeg,.png" data-file data-max="<?= UPLOAD_MAX_MB ?>" required data-rev="Termo anexado">
        <small class="ui-hint">PDF, JPG ou PNG, até <?= UPLOAD_MAX_MB ?> MB.<?= $erros ? ' Por segurança o navegador não guarda o arquivo: anexe de novo.' : '' ?></small><small class="ui-err" data-err><?= e($erros['termo'] ?? '') ?></small></div>
      <div class="ui-field"><label class="ui-consent"><input type="checkbox" name="aceite_termos" value="1" required <?= !empty($old['aceite_termos']) ? 'checked' : '' ?>> <span>Li e aceito os termos de uso.</span></label><small class="ui-err" data-err><?= e($erros['aceite_termos'] ?? '') ?></small></div>
      <div class="ui-field"><label class="ui-consent"><input type="checkbox" name="aceite_privacidade" value="1" required <?= !empty($old['aceite_privacidade']) ? 'checked' : '' ?>> <span>Confirmo que li a política de privacidade e autorizo o tratamento dos dados da empresa.</span></label><small class="ui-err" data-err><?= e($erros['aceite_privacidade'] ?? '') ?></small></div>
      <div class="ui-review" data-review hidden><h3>Confira antes de enviar</h3><dl></dl></div>
    </fieldset>

    <div class="ui-wiz-nav">
      <button type="button" class="ui-btn ui-btn-lg" data-prev hidden><?= icon('arrow-left', 16) ?> Voltar</button>
      <button type="button" class="ui-btn ui-btn-primary ui-btn-lg" data-next hidden>Continuar <?= icon('arrow-right', 16) ?></button>
      <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" data-submit>Enviar cadastro</button>
      <span class="ui-spacer"></span><a href="<?= url('empresa-login') ?>">Já tenho cadastro</a>
    </div>
  </form>
</div>
```

### Passo 74 — empresa_concluido.php

#### Arquivo: `Views/empresa_concluido.php` — CRIAR

```php
<?php $larga = true; ?>
<div class="auth-card" style="text-align:center">
  <span class="auth-env"><?= icon('check', 14) ?> Etapa final</span>
  <ol class="ui-stepper" style="display:flex;text-align:left;margin:10px 0 24px">
    <?php foreach (['Dados', 'Endereço', 'Contato', 'Acesso', 'Termos', 'Conclusão'] as $i => $t): ?><li class="is-done"><button type="button" disabled><span class="n"><?= $i + 1 ?></span><span class="t"><?= e($t) ?></span></button></li><?php endforeach; ?>
  </ol>
  <h1>Cadastro enviado</h1>
  <p class="lead">Recebemos os dados da <strong><?= e($emp['nome']) ?></strong>. A Coordenação vai conferir as informações e o termo de cooperação.</p>
  <div class="ui-card" style="text-align:left;margin:0 auto 20px;max-width:520px"><ul class="ui-list">
    <li>Você receberá um e-mail em <strong><?= e($emp['email']) ?></strong> com o resultado da análise.</li>
    <li>Enquanto isso, já pode completar o perfil da empresa.</li>
    <li>Depois da aprovação, você poderá publicar vagas.</li></ul></div>
  <a class="ui-btn ui-btn-primary ui-btn-lg" href="<?= url('painel') ?>">Ir para a área da empresa</a>
</div>
```

### Passo 75 — empresa_perfil.php

#### Arquivo: `Views/empresa_perfil.php` — CRIAR

```php
<?php $modo = 'perfil'; $logoPrev = $old; ?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 300px;align-items:start">
  <div>
    <?php if ($emp['status'] === 'reprovada'): ?><div class="ui-alert ui-alert-danger"><div><strong>Cadastro não aprovado</strong><p>Motivo: <?= nl2br(e((string) $emp['motivo'])) ?> Corrija os dados abaixo, salve e reenvie para análise.</p></div></div><?php endif; ?>
    <?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert"><div>Corrija os campos destacados e salve de novo.</div></div><?php endif; ?>
    <div class="ui-card"><form class="ui-form" method="post" action="<?= url('empresa-perfil') ?>" enctype="multipart/form-data" data-form data-wiz data-free data-start="0" novalidate>
      <?= csrf_field() ?><span data-stepper></span>
      <?php require __DIR__ . '/partials/empresa_campos.php'; ?>
      <div class="ui-wiz-nav"><button type="button" class="ui-btn" data-prev hidden><?= icon('arrow-left', 16) ?> Voltar</button><button type="button" class="ui-btn" data-next hidden>Continuar <?= icon('arrow-right', 16) ?></button>
        <button type="submit" class="ui-btn ui-btn-primary" data-submit>Salvar perfil</button></div>
    </form></div>
    <?php if ($emp['status'] === 'reprovada'): ?><form method="post" action="<?= url('empresa-perfil') ?>" style="margin-top:14px"><?= csrf_field() ?><input type="hidden" name="acao" value="reenviar"><button class="ui-btn ui-btn-lg" type="submit">Reenviar para análise</button></form><?php endif; ?>
  </div>
  <aside>
    <div class="ui-card"><h2>Como o estudante vê</h2>
      <div class="ui-comp-head" style="margin-bottom:10px"><?= Ui::logo($emp, 56) ?><div><strong><?= e($emp['nome']) ?></strong><p class="ui-sub" style="margin:0"><?= e(Empresa::segmentoTxt($emp)) ?></p></div></div>
      <p class="ui-sub" style="margin:0 0 10px"><?= e(mb_substr((string) $emp['descricao'], 0, 140)) ?><?= mb_strlen((string) $emp['descricao']) > 140 ? '…' : '' ?></p>
      <p class="ui-sub" style="margin:0"><?= (int) ($cont['publicada'] ?? 0) ?> vaga(s) no ar</p>
      <?php if ($emp['status'] === 'aprovada'): ?><a class="ui-btn ui-btn-sm ui-btn-block" style="margin-top:12px" target="_blank" rel="noopener" href="<?= url('empresa', ['id' => $emp['id']]) ?>">Ver página pública</a><?php endif; ?></div>
    <div class="ui-card" style="margin-top:14px"><h2>Situação <?= Ui::statusEmpresa($emp['status']) ?></h2><dl class="ui-kv" style="grid-template-columns:100px 1fr"><dt>CNPJ</dt><dd><?= e(Cnpj::formatar($emp['cnpj'])) ?></dd><dt>E-mail de acesso</dt><dd><?= e($emp['email']) ?></dd><dt>Cadastro</dt><dd><?= data_br(substr($emp['criado_em'], 0, 10)) ?></dd></dl></div>
  </aside>
</div>
```

### Passo 76 — empresa_vagas.php

#### Arquivo: `Views/empresa_vagas.php` — CRIAR

```php
<?php
$abas = ['todas' => ['Todas', $cont['todas'] ?? 0], 'aguardando' => ['Em análise', $cont['aguardando'] ?? 0], 'publicada' => ['Publicadas', $cont['publicada'] ?? 0]];
if (!empty($cont['agendada'])) $abas['agendada'] = ['Agendadas', $cont['agendada']];
$abas += ['destaque' => ['Em destaque', $cont['destaque'] ?? 0], 'expirada' => ['Expiradas', $cont['expirada'] ?? 0], 'encerrada' => ['Encerradas', $cont['encerrada'] ?? 0], 'rejeitada' => ['Rejeitadas', $cont['rejeitada'] ?? 0]];
?>
<div class="ui-row-between"><p class="ui-sub" style="margin:0">Vagas expiradas e encerradas ficam guardadas no histórico; nada é excluído.</p>
  <?php if ($emp['status'] === 'aprovada'): ?><a class="ui-btn ui-btn-primary" href="<?= url('vaga-cadastro') ?>"><?= icon('plus', 16) ?> Publicar vaga</a><?php endif; ?></div>
<?= Ui::abas($abas, $aba, 'empresa-vagas', ['q' => $q]) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('empresa-vagas') ?>" role="search"><input type="hidden" name="p" value="empresa-vagas"><input type="hidden" name="aba" value="<?= e($aba) ?>">
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por título, área ou código"><button class="ui-btn" type="submit"><?= icon('search', 16) ?> Buscar</button></form></div>
<?php if (!$lista): echo Ui::vazio($q ? 'Nada encontrado' : 'Nenhuma vaga nesta categoria', $q ? 'Tente outro termo ou limpe a busca.' : '', $aba === 'todas' && $emp['status'] === 'aprovada' ? ['Publicar vaga', url('vaga-cadastro')] : null);
else: ?><div class="ui-grid"><?php foreach ($lista as $v) { $ctx = 'empresa'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div>
<?= Ui::paginacao($pg, $paginas, 'empresa-vagas', ['aba' => $aba, 'q' => $q]) ?><?php endif; ?>
```

### Passo 77 — app.js

#### Arquivo: `public/js/app.js` — parte 2 — ANEXAR-ANTES-DO-FECHAMENTO
**Onde:** colar ANTES da última linha `})();`. Parte 2: consulta ao ViaCEP e o assistente em etapas (formulário passo a passo com revisão).

```javascript
  /* ---------- ViaCEP ---------- */
  var cep = $('[data-viacep]');
  if (cep) {
    var st = $('[data-cep-status]'), box = cep.closest('.ui-field'), ult = '', rid = 0;
    var msg = function (t, c) { st.textContent = t; st.className = 'ui-hint' + (c ? ' ' + c : ''); };
    var busca = function () {
      var d = cep.value.replace(/\D/g, ''); if (d.length !== 8) { ult = ''; return; } if (d === ult) return; ult = d;
      var meu = ++rid, ctl = window.AbortController ? new AbortController() : null, t = setTimeout(function () { if (ctl) ctl.abort(); }, 7000);
      msg('Buscando endereço...');
      fetch('https://viacep.com.br/ws/' + d + '/json/', ctl ? { signal: ctl.signal } : undefined)
        .then(function (r) { if (!r.ok) throw new Error('http'); return r.json(); })
        .then(function (j) {
          if (meu !== rid) return; clearTimeout(t);
          if (j.erro) { msg('CEP não encontrado. Confira o número ou preencha o endereço manualmente.', 'is-warn'); return; }
          var m = { logradouro: j.logradouro, bairro: j.bairro, cidade: j.localidade, uf: j.uf };
          Object.keys(m).forEach(function (k) { var el = $('[data-cep-fill=' + k + ']'); if (el && m[k]) { el.value = m[k]; el.dispatchEvent(new Event('change', { bubbles: true })); } });
          msg('Endereço encontrado. Confira e informe o número.', 'is-ok');
          var a = document.activeElement, n = $('#numero'); if (n && (a === cep || a === document.body)) n.focus();
        })
        .catch(function () { if (meu !== rid) return; clearTimeout(t); msg('Não foi possível buscar o CEP agora. Preencha o endereço manualmente.', 'is-warn'); });
    };
    cep.addEventListener('input', busca); cep.addEventListener('blur', busca);
  }

  /* ---------- assistente em etapas (cadastro de empresa e de vaga) ---------- */
  var wiz = $('[data-wiz]');
  if (wiz) {
    wiz.classList.add('ui-wiz-on');
    var panels = $$('.ui-step', wiz), ult2 = panels.length - 1, atual = -1, alcance = 0;
    var ol = document.createElement('ol'); ol.className = 'ui-stepper' + (panels.length > 5 ? ' is-many' : ''); ol.setAttribute('aria-label', 'Etapas');
    panels.forEach(function (p, i) {
      var li = document.createElement('li'), b = document.createElement('button'); b.type = 'button';
      b.innerHTML = '<span class="n">' + (i + 1) + '</span><span class="t"></span>'; $('.t', b).textContent = p.dataset.title; b.setAttribute('aria-label', 'Etapa ' + (i + 1) + ' de ' + panels.length + ': ' + p.dataset.title);
      b.addEventListener('click', function () { if (i <= alcance) ir(i, true); }); li.appendChild(b); ol.appendChild(li);
    });
    var barra = document.createElement('div'); barra.className = 'ui-progress'; barra.innerHTML = '<i></i>';
    var alvo = $('[data-stepper]', wiz) || wiz.firstElementChild; alvo.parentNode.insertBefore(ol, alvo); alvo.parentNode.insertBefore(barra, alvo);
    var lis = $$('li', ol), bVolta = $('[data-prev]', wiz), bProx = $('[data-next]', wiz), bEnvia = $('[data-submit]', wiz);
    var live = document.createElement('p'); live.className = 'ui-sr'; live.setAttribute('aria-live', 'polite'); wiz.appendChild(live);
    function rotulo(c) {
      if (c.tagName === 'SELECT') return c.selectedIndex > 0 || c.value ? c.options[c.selectedIndex].text : '';
      if (c.type === 'date') return c.value ? c.value.split('-').reverse().join('/') : '';
      if (c.type === 'file') return c.files[0] ? c.files[0].name : '';
      if (c.tagName === 'TEXTAREA') return c.value.trim().split(/\n/).filter(Boolean).join(' • ');
      if (c.type === 'password') return c.value ? '••••••••' : ''; return c.value.trim();
    }
    function resumo() {
      var box = $('[data-review]', wiz); if (!box) return; var dl = $('dl', box); dl.innerHTML = '';
      var linha = function (k, v) { if (!v) return; var d = document.createElement('div'), dt = document.createElement('dt'), dd = document.createElement('dd'); dt.textContent = k; dd.textContent = v; d.appendChild(dt); d.appendChild(dd); dl.appendChild(d); };
      $$('[data-rev]', wiz).forEach(function (c) {
        if (c.closest('[hidden]')) return;
        if (c.type === 'radio') { if (c.checked) linha(c.dataset.rev, (c.closest('label') || c).textContent.trim()); return; }
        if (c.hasAttribute('data-rev-group')) { linha(c.dataset.rev, $$('input:checked', c).map(function (i) { return i.closest('label').textContent.trim(); }).join(', ')); return; }
        var v = rotulo(c); if (c.dataset.prefix && v) v = c.dataset.prefix + v; if (c.dataset.suffix && v) v += c.dataset.suffix; linha(c.dataset.rev, v);
      });
      box.hidden = false;
    }
    function erros() { lis.forEach(function (li, i) { li.classList.toggle('has-error', !!$('.is-invalid', panels[i]) || !!$$('[data-err]', panels[i]).some(function (e) { return e.textContent; })); }); }
    function ir(i, foco) {
      i = Math.max(0, Math.min(ult2, i)); var mudou = i !== atual; atual = i; alcance = Math.max(alcance, i);
      panels.forEach(function (p, k) { p.classList.toggle('is-active', k === i); });
      lis.forEach(function (li, k) { li.classList.toggle('is-current', k === i); li.classList.toggle('is-done', k !== i && k < alcance); var b = $('button', li); b.disabled = k > alcance; if (k === i) b.setAttribute('aria-current', 'step'); else b.removeAttribute('aria-current'); });
      $('i', barra).style.width = ((i + 1) / panels.length * 100) + '%';
      bVolta.hidden = i === 0; bProx.hidden = i === ult2; bEnvia.hidden = i !== ult2; if (i === ult2) resumo();
      live.textContent = 'Etapa ' + (i + 1) + ' de ' + panels.length + ': ' + panels[i].dataset.title; erros();
      if (foco && mudou) { ol.scrollIntoView({ behavior: reduz ? 'auto' : 'smooth', block: 'start' }); var p = camposDe(panels[i])[0]; if (p) p.focus({ preventScroll: true }); }
    }
    function painelOk(i) {
      var primeiro = null; camposDe(panels[i]).forEach(function (c) { if (!valida(c) && !primeiro) primeiro = c; });
      if (!validaGrupos(panels[i]) && !primeiro) primeiro = $('[data-need-one] input', panels[i]);
      erros(); if (primeiro) foca(primeiro); return !primeiro;
    }
    function proximo() { if (painelOk(atual)) ir(atual + 1, true); }
    bProx.addEventListener('click', proximo); bVolta.addEventListener('click', function () { ir(atual - 1, true); });
    wiz.addEventListener('keydown', function (e) { if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'file' && e.target.type !== 'checkbox' && !e.target.closest('.ui-chips') && atual < ult2) { e.preventDefault(); proximo(); } });
    wiz._wizGo = function (campo) { var p = campo.closest('.ui-step'); alcance = ult2; ir(+p.dataset.step, false); erros(); foca(campo); };
    if (wiz.hasAttribute('data-free') || $('.ui-alert-danger', wiz)) alcance = ult2;
    ir(+wiz.dataset.start || 0, false);
  }
```

### Passo 78 — Verificar a branch

Com o servidor rodando (`php -S localhost:8000 router.php`):

| Endereço | Deve responder |
|---|---|
| `/?p=empresa-login` | 200 |
| `/?p=empresa-cadastro` | 200 |
| `/?p=painel` | 302 |

### Passo 79 — Fechar a branch

```bash
git add .
git commit -m "feat(empresa): login, cadastro em etapas com ViaCEP, perfil e lista de vagas"
git push -u origin feature/frontend-company
```
Abra o Pull Request `feature/frontend-company` → `develop` e peça revisão a **José Roberto**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 10: `feature/frontend-admin` — dono: Pedro Henri

Telas da administração: login, dashboard, aprovações, vagas, empresas, relatórios e configurações.

**Revisor do PR:** Rodrigo. **Depende de:** `feature/design-system`, `feature/backend-jobs`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `106.07` | Tela de login administrativo independente | 1 | 2 |
| `111.07` | Navegação do painel em seções | 2 | 3 |
| `111.11` | Empresas no admin: abas, cards, perfil completo, bloqueio e desbloqueio | 3 | 3 |
| `111.15` | Relatórios (CSV) e configurações (regras de negócio) | 2 | 4 |
| `111.16` | Cadastro manual de empresa pelo administrador (decidir com o Product Owner) | 2 | 4 |
| `111.09` | Vagas no admin: abas por situação, busca, filtros, ordenação, paginação e vista cards/tabela | 3 | 5 |
| `111.10` | Detalhe da vaga no admin com ações e histórico | 2 | 5 |
| `107.05` | Diagnóstico de e-mail no painel (e-mail de teste e registro de envios) | 2 | 6 |
| `111.08` | Aprovações: fila de empresas e vagas com ações em modal | 3 | 7 |
| `111.12` | Dashboard com indicadores e gráficos reais e estados vazios (extra de sprint) | 3 | 8 |

```bash
git checkout develop && git pull
git checkout -b feature/frontend-admin
```

### Passo 80 — admin_login.php

#### Arquivo: `Views/admin_login.php` — CRIAR

```php
<div class="auth-card">
  <span class="auth-env"><?= icon('lock', 14) ?> Ambiente administrativo</span>
  <h1>Entrar na administração</h1><p class="lead">Acesso restrito à Coordenação e à Secretaria.</p>
  <form method="post" action="<?= url('admin-login') ?>" data-form novalidate>
    <?= csrf_field() ?>
    <?php if ($erro): ?><div class="ui-alert ui-alert-danger" role="alert"><div><?= e($erro) ?></div></div><?php endif; ?>
    <?= Ui::campo(['name' => 'email', 'label' => 'E-mail institucional', 'type' => 'email', 'req' => true, 'attrs' => ['autocomplete' => 'username']], ['email' => $email]) ?>
    <?= Ui::campo(['name' => 'senha', 'label' => 'Senha', 'type' => 'password', 'req' => true, 'attrs' => ['autocomplete' => 'current-password']]) ?>
    <button class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block" type="submit">Entrar</button>
  </form>
</div>
```

### Passo 81 — admin_dashboard.php

#### Arquivo: `Views/admin_dashboard.php` — CRIAR

```php
<?php
$pend = (int) ($emp['pendente'] ?? 0) + (int) ($vag['aguardando'] ?? 0);
$evento = ['login_ok' => 'Logins', 'login_falha' => 'Falhas de login', 'negado' => 'Acessos negados', 'bloqueado' => 'Bloqueios'];
?>
<?php if ($pend): ?><div class="ui-alert ui-alert-warn"><?= icon('bell', 22) ?><div><strong>Aguardando sua análise</strong><p><?= (int) $emp['pendente'] ?> empresa(s) e <?= (int) $vag['aguardando'] ?> vaga(s). <a href="<?= url('admin-aprovacoes') ?>">Abrir aprovações</a></p></div></div><?php endif; ?>
<div class="ui-section"><h2>Empresas</h2><div class="ui-metrics">
  <?= Ui::metrica('Total de empresas', $emp['total'] ?? 0, 'building', url('admin-empresas', ['aba' => 'todas'])) ?>
  <?= Ui::metrica('Aprovadas', $emp['aprovada'] ?? 0, 'check', url('admin-empresas', ['aba' => 'aprovadas'])) ?>
  <?= Ui::metrica('Pendentes', $emp['pendente'] ?? 0, 'clock', url('admin-empresas', ['aba' => 'pendentes']), 'warn') ?>
  <?= Ui::metrica('Bloqueadas', $emp['bloqueada'] ?? 0, 'lock', url('admin-empresas', ['aba' => 'bloqueadas']), 'danger') ?></div></div>
<div class="ui-section"><h2>Vagas</h2><div class="ui-metrics">
  <?= Ui::metrica('Total de vagas', $vag['todas'] ?? 0, 'briefcase', url('admin-vagas')) ?>
  <?= Ui::metrica('Publicadas', $vag['publicada'] ?? 0, 'check', url('admin-vagas', ['aba' => 'publicada'])) ?>
  <?= Ui::metrica('Aguardando aprovação', $vag['aguardando'] ?? 0, 'clock', url('admin-vagas', ['aba' => 'aguardando']), 'warn') ?>
  <?= Ui::metrica('Agendadas', $vag['agendada'] ?? 0, 'clock', url('admin-vagas', ['aba' => 'agendada']), 'info') ?>
  <?= Ui::metrica('Expiradas', $vag['expirada'] ?? 0, 'briefcase', url('admin-vagas', ['aba' => 'expirada']), 'mute') ?>
  <?= Ui::metrica('Encerradas', $vag['encerrada'] ?? 0, 'x', url('admin-vagas', ['aba' => 'encerrada']), 'mute') ?></div></div>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Vagas por situação</h2><?= Ui::donut([['Publicadas', $vag['publicada'] ?? 0, 'var(--chart-1)'], ['Em análise', $vag['aguardando'] ?? 0, 'var(--chart-3)'], ['Agendadas', $vag['agendada'] ?? 0, 'var(--chart-2)'],
    ['Expiradas', $vag['expirada'] ?? 0, 'var(--chart-5)'], ['Encerradas', $vag['encerrada'] ?? 0, 'var(--border-strong)'], ['Rejeitadas', $vag['rejeitada'] ?? 0, 'var(--chart-4)']], 'vagas') ?></div>
  <div class="ui-card"><h2>Cadastros e envios por semana</h2><?= Ui::barras($semanas, [['empresas', 'Empresas', 'var(--chart-1)'], ['vagas', 'Vagas', 'var(--chart-2)']], 'Empresas cadastradas e vagas enviadas nas últimas 8 semanas') ?></div>
</div>
<div class="ui-grid-2" style="margin-top:16px">
  <div class="ui-card"><h2>Atividade recente</h2>
    <?php if (!$atividade): echo Ui::vazio('Nenhuma atividade ainda'); else: ?><ol class="ui-timeline"><?php foreach ($atividade as $h): $nome = $h['entidade'] === 'vaga' ? $h['v_titulo'] : ($h['entidade'] === 'empresa' ? $h['e_nome'] : '');
      $link = $h['entidade'] === 'vaga' ? url('admin-vaga', ['id' => $h['entidade_id']]) : ($h['entidade'] === 'empresa' ? url('admin-empresa', ['id' => $h['entidade_id']]) : null); ?>
      <li><strong><?= e(Historico::rotulo($h)) ?></strong> <?= $link && $nome ? '<a href="' . e($link) . '">' . e($nome) . '</a>' : e((string) $nome) ?><small><?= e(date('d/m H:i', strtotime($h['criado_em']))) ?></small></li><?php endforeach; ?></ol><?php endif; ?></div>
  <div class="ui-card"><h2>Empresas recentes</h2>
    <?php if (!$empresasRecentes): echo Ui::vazio('Nenhuma empresa cadastrada'); else: ?><ul style="list-style:none;margin:0;padding:0;display:grid;gap:10px"><?php foreach ($empresasRecentes as $r): ?>
      <li style="display:flex;justify-content:space-between;gap:8px;align-items:center"><span><a href="<?= url('admin-empresa', ['id' => $r['id']]) ?>"><?= e($r['nome']) ?></a><br><small class="ui-hint" style="margin:0"><?= data_br(substr($r['criado_em'], 0, 10)) ?></small></span><?= Ui::statusEmpresa($r['status']) ?></li><?php endforeach; ?></ul><?php endif; ?></div>
</div>
<div class="ui-card" style="margin-top:16px"><div class="ui-row-between" style="margin-bottom:8px"><h2 style="margin:0">Acessos nos últimos 7 dias</h2><a class="ui-btn ui-btn-sm" href="<?= url('admin-acesso') ?>">Controle de acesso</a></div>
  <div class="ui-metrics" style="margin-bottom:12px"><?php foreach ($evento as $k => $rot): echo Ui::metrica($rot, $acessos[$k] ?? 0, $k === 'login_ok' ? 'user' : 'shield', null, $k === 'login_ok' ? '' : ($k === 'login_falha' ? 'warn' : 'danger')); endforeach; ?></div>
  <?= Ui::barras($logins, [['ok', 'Logins', 'var(--chart-1)'], ['falha', 'Falhas', 'var(--chart-4)']], 'Logins e falhas por dia nos últimos 7 dias') ?></div>
```

### Passo 82 — admin_aprovacoes.php

#### Arquivo: `Views/admin_aprovacoes.php` — CRIAR

```php
<?php $ret = 'admin-aprovacoes'; $total = count($empPend) + count($vagasPend); ?>
<?php if (!$total): echo Ui::vazio('Tudo em dia', 'Não há empresas nem vagas aguardando análise.'); endif; ?>
<?php if ($empPend): ?><section class="ui-section"><h2>Empresas aguardando aprovação <?= Ui::badge((string) count($empPend), 'warn') ?></h2><div class="ui-grid">
  <?php foreach ($empPend as $e): ?>
    <article class="ui-comp ui-job"><div class="ui-comp-head"><?= Ui::logo($e, 52) ?><div><h3><a href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a></h3><p><?= e(Empresa::segmentoTxt($e)) ?></p></div></div>
      <ul class="ui-job-meta"><li><?= icon('building', 15) ?> CNPJ <?= e(Cnpj::formatar($e['cnpj'])) ?></li><li><?= icon('pin', 15) ?> <?= e($e['cidade'] ? $e['cidade'] . '/' . $e['uf'] : $e['endereco']) ?></li><li><?= icon('user', 15) ?> <?= e($e['responsavel']) ?> · <?= e($e['email']) ?></li><li><?= icon('clock', 15) ?> Cadastrada em <?= e(date('d/m/Y H:i', strtotime($e['criado_em']))) ?></li></ul>
      <div class="ui-job-foot"><a class="ui-btn ui-btn-sm" href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= icon('eye', 15) ?> Abrir</a><?php if ($e['termo_arquivo']): ?><a class="ui-btn ui-btn-sm" href="<?= url('admin-termo', ['id' => $e['id']]) ?>" target="_blank" rel="noopener"><?= icon('file', 15) ?> Termo</a><?php endif; ?>
        <?= Ui::acoesEmpresaAdmin($e, 'admin-aprovacoes') ?></div></article>
  <?php endforeach; ?></div></section><?php endif; ?>
<?php if ($vagasPend): ?><section class="ui-section"><h2>Vagas aguardando aprovação <?= Ui::badge((string) count($vagasPend), 'warn') ?></h2><div class="ui-grid">
  <?php foreach ($vagasPend as $v) { $ctx = 'admin'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div></section><?php endif; ?>
```

### Passo 83 — admin_vagas.php

#### Arquivo: `Views/admin_vagas.php` — CRIAR

```php
<?php
$ret = 'admin-vagas';
$abas = ['todas' => ['Todas', $cont['todas'] ?? 0], 'aguardando' => ['Aguardando aprovação', $cont['aguardando'] ?? 0], 'publicada' => ['Publicadas', $cont['publicada'] ?? 0], 'agendada' => ['Agendadas', $cont['agendada'] ?? 0],
    'destaque' => ['Em destaque', $cont['destaque'] ?? 0], 'expirada' => ['Expiradas', $cont['expirada'] ?? 0], 'encerrada' => ['Encerradas', $cont['encerrada'] ?? 0], 'rejeitada' => ['Rejeitadas', $cont['rejeitada'] ?? 0]];
$pq = ['aba' => $aba, 'q' => $q, 'tipo' => $tipo, 'ord' => $ord, 'vista' => $vista];
?>
<?= Ui::abas($abas, $aba, 'admin-vagas', ['q' => $q, 'tipo' => $tipo, 'ord' => $ord, 'vista' => $vista]) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('admin-vagas') ?>" role="search"><input type="hidden" name="p" value="admin-vagas"><input type="hidden" name="aba" value="<?= e($aba) ?>"><input type="hidden" name="vista" value="<?= e($vista) ?>">
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Título, empresa, área ou código">
  <label class="ui-sr" for="tipo">Tipo</label><select id="tipo" class="ui-select" name="tipo"><option value="">Todos os tipos</option><?php foreach (TIPOS_VAGA as $t): ?><option <?= $tipo === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
  <label class="ui-sr" for="ord">Ordenar</label><select id="ord" class="ui-select" name="ord"><?php foreach (['recentes' => 'Mais recentes', 'prazo' => 'Prazo final', 'titulo' => 'Título (A-Z)', 'empresa' => 'Empresa (A-Z)'] as $k => $r): ?><option value="<?= $k ?>" <?= $ord === $k ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select>
  <button class="ui-btn" type="submit"><?= icon('search', 16) ?> Filtrar</button></form>
  <div class="ui-seg" role="group" aria-label="Modo de exibição"><a href="<?= e(url('admin-vagas', array_merge($pq, ['vista' => 'cards']))) ?>" class="<?= $vista === 'cards' ? 'is-active' : '' ?>">Cards</a><a href="<?= e(url('admin-vagas', array_merge($pq, ['vista' => 'tabela']))) ?>" class="<?= $vista === 'tabela' ? 'is-active' : '' ?>">Tabela</a></div></div>
<p class="ui-sub"><?= (int) $total ?> vaga(s) encontrada(s).</p>
<?php if (!$lista): echo Ui::vazio('Nenhuma vaga encontrada', 'Ajuste a busca, o tipo ou a aba.');
elseif ($vista === 'tabela'): ?><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Código</th><th>Vaga</th><th>Empresa</th><th>Situação</th><th>Período</th><th>Ações</th></tr></thead><tbody>
  <?php foreach ($lista as $v): ?><tr><td><span class="ui-code"><?= e($v['codigo']) ?></span></td><td><a href="<?= url('admin-vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a><br><small><?= e($v['tipo']) ?> · <?= e($v['area']) ?></small></td>
    <td><?= e($v['empresa']['nome']) ?></td><td><?= Ui::situacao($v['situacao']) ?></td><td><?= data_br($v['data_publicacao']) ?> a <?= data_br($v['data_limite']) ?></td><td><?= Ui::acoesVagaAdmin($v, 'admin-vagas') ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php else: ?><div class="ui-grid"><?php foreach ($lista as $v) { $ctx = 'admin'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div><?php endif; ?>
<?= Ui::paginacao($pg, $paginas, 'admin-vagas', $pq) ?>
```

### Passo 84 — admin_vaga.php

#### Arquivo: `Views/admin_vaga.php` — CRIAR

```php
<?php $sit = $v['situacao']; $e = $v['empresa']; ?>
<div class="ui-row-between"><div class="ui-actions"><a class="ui-btn ui-btn-sm" href="<?= url('admin-vagas') ?>"><?= icon('arrow-left', 15) ?> Vagas</a> <?= Ui::situacao($sit) ?> <span class="ui-badge ui-badge-info"><?= e($v['tipo']) ?></span></div>
  <?= Ui::acoesVagaAdmin($v, 'admin-vaga', (int) $v['id']) ?></div>
<h2 style="font-size:22px;margin-bottom:4px"><?= e($v['titulo']) ?></h2>
<p class="ui-sub"><a href="<?= url('admin-empresa', ['id' => $e['id']]) ?>"><?= e($e['nome']) ?></a> <?= Ui::statusEmpresa($e['status']) ?><?= $sit === 'publicada' ? ' · <a href="' . e(url_vaga((int) $v['id'])) . '" target="_blank" rel="noopener">ver no site</a>' : '' ?></p>
<?php if ($v['status'] === 'aguardando_aprovacao' && $e['status'] !== 'aprovada'): ?><div class="ui-alert ui-alert-warn"><div><strong>Empresa ainda não aprovada</strong><p>Aprove a empresa antes de aprovar esta vaga.</p></div></div><?php endif; ?>
<?php if ($sit === 'rejeitada'): ?><div class="ui-alert ui-alert-danger"><div><strong>Rejeitada</strong><p>Motivo: <?= nl2br(e((string) $v['motivo'])) ?></p></div></div><?php endif; ?>
<?php require __DIR__ . '/partials/vaga_detalhe.php'; ?>
<div class="ui-card" style="margin-top:16px"><h2>Histórico</h2><?php require __DIR__ . '/partials/timeline.php'; ?></div>
```

### Passo 85 — admin_empresas.php

#### Arquivo: `Views/admin_empresas.php` — CRIAR

```php
<?= Ui::abas($abas, $aba, 'admin-empresas', ['q' => $q]) ?>
<div class="ui-toolbar"><form method="get" action="<?= url('admin-empresas') ?>" role="search"><input type="hidden" name="p" value="admin-empresas"><input type="hidden" name="aba" value="<?= e($aba) ?>">
  <label class="ui-sr" for="q">Buscar</label><input id="q" class="ui-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Nome, razão social, CNPJ ou e-mail"><button class="ui-btn" type="submit"><?= icon('search', 16) ?> Buscar</button></form>
  <a class="ui-btn" href="<?= url('admin-exportar', ['tipo' => 'empresas']) ?>"><?= icon('file', 16) ?> Exportar CSV</a></div>
<p class="ui-sub"><?= (int) $total ?> empresa(s) encontrada(s).</p>
<?php if (!$lista): echo Ui::vazio('Nenhuma empresa nesta categoria', $q ? 'Tente outro termo.' : '');
else: ?><div class="ui-grid"><?php foreach ($lista as $e) require __DIR__ . '/partials/empresa_card_app.php'; ?></div><?= Ui::paginacao($pg, $paginas, 'admin-empresas', ['aba' => $aba, 'q' => $q]) ?><?php endif; ?>
```

### Passo 86 — admin_empresa.php

#### Arquivo: `Views/admin_empresa.php` — CRIAR

```php
<?php $ret = 'admin-empresa'; ?>
<div class="ui-row-between"><div class="ui-actions"><a class="ui-btn ui-btn-sm" href="<?= url('admin-empresas') ?>"><?= icon('arrow-left', 15) ?> Empresas</a> <?= Ui::statusEmpresa($e['status']) ?></div><?= Ui::acoesEmpresaAdmin($e, 'admin-empresa', (int) $e['id']) ?></div>
<div class="ui-card" style="margin-bottom:16px"><div class="ui-comp-head" style="gap:16px"><?= Ui::logo($e, 72) ?><div><h2 style="font-size:22px;margin:0 0 2px"><?= e($e['nome']) ?></h2>
  <p class="ui-sub" style="margin:0"><?= e(Empresa::segmentoTxt($e)) ?><?= $e['porte'] ? ' · ' . e($e['porte']) : '' ?><?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?></p></div></div>
  <?php if ($e['descricao']): ?><p style="margin:14px 0 0"><?= nl2br(e($e['descricao'])) ?></p><?php endif; ?></div>
<?php if ($e['status'] === 'reprovada' || $e['status'] === 'bloqueada'): ?><div class="ui-alert ui-alert-danger"><div><strong><?= $e['status'] === 'bloqueada' ? 'Empresa bloqueada' : 'Cadastro reprovado' ?></strong><p>Motivo: <?= nl2br(e((string) $e['motivo'])) ?></p></div></div><?php endif; ?>
<div class="ui-metrics"><?= Ui::metrica('Vagas cadastradas', $e['total_vagas'], 'briefcase') ?><?= Ui::metrica('Vagas no ar', $e['vagas_abertas'], 'check') ?><?= Ui::metrica('Último acesso', $e['ultimo_acesso'] ? date('d/m/Y', strtotime($e['ultimo_acesso'])) : 'Nunca', 'clock', null, 'mute') ?></div>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Dados cadastrais</h2><dl class="ui-kv">
    <dt>Razão social</dt><dd><?= e($e['razao_social'] ?: '-') ?></dd><dt>CNPJ</dt><dd><span class="ui-code"><?= e(Cnpj::formatar($e['cnpj'])) ?></span></dd>
    <dt>Endereço</dt><dd><?= e($e['endereco']) ?></dd>
    <dt>Site</dt><dd><?= $e['site'] ? '<a href="' . e($e['site']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($e['site'])) . '</a>' : '-' ?></dd>
    <dt>LinkedIn</dt><dd><?= $e['linkedin'] ? '<a href="' . e($e['linkedin']) . '" target="_blank" rel="noopener noreferrer nofollow">' . e(Link::dominio($e['linkedin'])) . '</a>' : '-' ?></dd>
    <dt>Link principal</dt><dd><?= $e['link_principal'] === 'linkedin' ? 'LinkedIn' : 'Site oficial' ?></dd>
    <dt>Cadastro</dt><dd><?= e(date('d/m/Y H:i', strtotime($e['criado_em']))) ?></dd><dt>Termos aceitos</dt><dd><?= $e['termos_aceitos_em'] ? e(date('d/m/Y H:i', strtotime($e['termos_aceitos_em']))) : '-' ?></dd>
    <dt>Termo anexado</dt><dd><?= $e['termo_arquivo'] ? '<a href="' . e(url('admin-termo', ['id' => $e['id']])) . '" target="_blank" rel="noopener">Abrir termo de cooperação</a>' : '-' ?></dd></dl></div>
  <div class="ui-card"><h2>Contato</h2><dl class="ui-kv">
    <dt>Responsável</dt><dd><?= e($e['responsavel']) ?><?= $e['cargo'] ? ' (' . e($e['cargo']) . ')' : '' ?></dd><dt>Telefone</dt><dd><?= e(fone_fmt($e['telefone'])) ?></dd><dt>Celular</dt><dd><?= $e['celular'] ? e(fone_fmt($e['celular'])) : '-' ?></dd>
    <dt>E-mail de acesso</dt><dd><?= e($e['email']) ?></dd><dt>E-mail de contato</dt><dd><?= e($e['email_contato'] ?: '-') ?></dd></dl></div>
</div>
<section class="ui-section" style="margin-top:22px"><h2>Vagas da empresa</h2>
  <?php if (!$vagas): echo Ui::vazio('Esta empresa ainda não enviou vagas'); else: ?><div class="ui-grid"><?php foreach ($vagas as $v) { $ctx = 'admin'; $ret = 'admin-empresa'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div><?php endif; ?></section>
<div class="ui-card"><h2>Histórico da empresa</h2><?php require __DIR__ . '/partials/timeline.php'; ?></div>
```

### Passo 87 — admin_relatorios.php

#### Arquivo: `Views/admin_relatorios.php` — CRIAR

```php
<div class="ui-metrics"><?= Ui::metrica('Empresas', $emp['total'] ?? 0, 'building') ?><?= Ui::metrica('Vagas', $vag['todas'] ?? 0, 'briefcase') ?><?= Ui::metrica('Vagas publicadas', $vag['publicada'] ?? 0, 'check') ?><?= Ui::metrica('Vagas expiradas e encerradas', ($vag['expirada'] ?? 0) + ($vag['encerrada'] ?? 0), 'clock', null, 'mute') ?></div>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Exportar vagas</h2><p class="ui-sub">Todas as vagas, com situação, período e empresa. Arquivo CSV (abre no Excel).</p><a class="ui-btn ui-btn-primary" href="<?= url('admin-exportar', ['tipo' => 'vagas']) ?>"><?= icon('file', 16) ?> Baixar vagas.csv</a></div>
  <div class="ui-card"><h2>Exportar empresas</h2><p class="ui-sub">Cadastro das empresas com situação e quantidade de vagas.</p><a class="ui-btn ui-btn-primary" href="<?= url('admin-exportar', ['tipo' => 'empresas']) ?>"><?= icon('file', 16) ?> Baixar empresas.csv</a></div>
</div>
<p class="ui-sub" style="margin-top:16px">Relatórios gráficos e por período ficam para uma próxima sprint; os dados e os gráficos do dashboard já usam os mesmos números do banco.</p>
```

### Passo 88 — admin_config.php

#### Arquivo: `Views/admin_config.php` — CRIAR

```php
<section class="ui-section" id="emails"><h2>Diagnóstico de e-mail</h2>
  <?php if ($mailModo === 'log'): ?><div class="ui-alert ui-alert-warn"><?= icon('mail', 22) ?><div><strong>Modo de teste: nenhum e-mail é enviado de verdade</strong><p>O <code class="ui-code">.env</code> está com <code class="ui-code">MAIL_DRIVER=log</code>: as mensagens só são gravadas em <code class="ui-code">storage/mail/</code>. Para enviar de verdade, use <code class="ui-code">MAIL_DRIVER=smtp</code> e preencha <code class="ui-code">SMTP_*</code> (veja o README).</p></div></div>
  <?php else: ?><p class="ui-sub">Envio por <strong><?= e($mailModo) ?></strong>. Remetente: <?= e((string) env('MAIL_FROM', 'vagas@localhost')) ?>. Avisos de novas empresas e vagas vão para: <?= e((string) (env('ADMIN_NOTIFY_EMAIL') ?: $adm['email'])) ?>.</p><?php endif; ?>
  <?= Ui::formConfirmar(url('admin-acao'), ['tipo' => 'email-teste', 'ret' => 'admin-config'], 'Enviar e-mail de teste para ' . $adm['email'], 'Enviar e-mail de teste?', 'Uma mensagem de teste será enviada para ' . $adm['email'] . '.', 'primary', 'ui-btn') ?>
  <div class="ui-table-wrap" style="margin-top:14px"><table class="ui-table"><thead><tr><th>Quando</th><th>Para</th><th>Assunto</th><th>Resultado</th></tr></thead><tbody>
    <?php foreach ($emails as $m): ?><tr><td><?= e(date('d/m H:i', strtotime($m['criado_em']))) ?></td><td><?= e($m['destinatario']) ?></td><td><?= e($m['assunto']) ?></td>
      <td><?= Ui::badge($m['status'] === 'enviado' ? 'Enviado' : ($m['status'] === 'falha' ? 'Falhou' : 'Só gravado'), $m['status'] === 'enviado' ? 'ok' : ($m['status'] === 'falha' ? 'danger' : 'warn')) ?><?= $m['erro'] ? '<br><small>' . e($m['erro']) . '</small>' : '' ?></td></tr><?php endforeach; ?></tbody></table></div>
  <?php if (!$emails): echo Ui::vazio('Nenhum e-mail registrado ainda'); endif; ?></section>
<section class="ui-section"><h2>Regras de negócio (somente leitura)</h2><div class="ui-card"><dl class="ui-kv" style="grid-template-columns:260px 1fr">
  <dt>Vagas no banner de destaque</dt><dd><?= (int) CARROSSEL_MAX ?></dd><dt>Selo "Nova"</dt><dd>até <?= (int) DIAS_VAGA_NOVA ?> dias após a publicação</dd><dt>Selo "Encerra em breve"</dt><dd>últimos <?= (int) DIAS_VAGA_VENCENDO ?> dias</dd>
  <dt>Área nova vira filtro com</dt><dd><?= (int) AREA_MIN_VAGAS ?> vagas no ar (senão entra em "Outras")</dd><dt>"Recrutador ativo"</dt><dd>empresa que acessou nos últimos <?= (int) DIAS_RECRUTADOR_ATIVO ?> dias</dd><dt>Upload máximo</dt><dd><?= (int) UPLOAD_MAX_MB ?> MB</dd></dl>
  <p class="ui-sub" style="margin:10px 0 0">Para alterar, edite <code class="ui-code">config.php</code>.</p></div></section>
<section class="ui-section"><h2>Sistema</h2><div class="ui-card"><dl class="ui-kv" style="grid-template-columns:260px 1fr"><dt>PHP</dt><dd><?= e(PHP_VERSION) ?></dd><dt>Banco de dados</dt><dd><?= e(Database::driver()) ?></dd>
  <dt>Migrations aplicadas</dt><dd><?= e(implode(', ', array_column(Database::all('SELECT nome FROM migracoes ORDER BY id'), 'nome')) ?: '-') ?></dd><dt>Links curtos de vaga (PRETTY_URLS)</dt><dd><?= env('PRETTY_URLS') && env('PRETTY_URLS') !== '0' ? 'ligado' : 'desligado' ?></dd></dl></div></section>
```

### Passo 89 — Verificar a branch

Com o servidor rodando (`php -S localhost:8000 router.php`):

| Endereço | Deve responder |
|---|---|
| `/?p=admin-login` | 200 |
| `/?p=admin` | 302 |

### Passo 90 — Fechar a branch

```bash
git add .
git commit -m "feat(admin): login, dashboard, aprovações, vagas, empresas, relatórios e diagnóstico de e-mail"
git push -u origin feature/frontend-admin
```
Abra o Pull Request `feature/frontend-admin` → `develop` e peça revisão a **Rodrigo**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 11: `feature/access-control` — dono: Pedro Miguel

Usuários administradores e controle de acesso (logins, falhas, acessos negados, bloqueios).

**Revisor do PR:** Pedro Henri. **Depende de:** `feature/frontend-admin`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `111.13` | Usuários: criar, ativar e desativar administradores | 2 | 4 |
| `111.14` | Controle de acesso: logins, falhas, acessos negados, bloqueios e últimos acessos | 3 | 4 |

```bash
git checkout develop && git pull
git checkout -b feature/access-control
```

### Passo 91 — admin_usuarios.php

#### Arquivo: `Views/admin_usuarios.php` — CRIAR

```php
<?php $c = fn(array $o, $v = null) => Ui::campo($o, $old, $erros); ?>
<div class="ui-grid-2" style="grid-template-columns:minmax(0,1fr) 340px;align-items:start">
  <section><h2 style="font-size:17px;margin-bottom:12px">Administradores</h2>
    <div class="ui-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))"><?php foreach ($admins as $a): ?>
      <article class="ui-job"><div class="ui-job-top"><strong><?= e($a['nome']) ?></strong><?= $a['ativo'] ? Ui::badge('Ativo', 'ok') : Ui::badge('Desativado', 'mute') ?></div>
        <ul class="ui-job-meta"><li><?= icon('mail', 15) ?> <?= e($a['email']) ?></li><li><?= icon('clock', 15) ?> Último acesso: <?= $a['ultimo_acesso'] ? e(date('d/m/Y H:i', strtotime($a['ultimo_acesso']))) : 'nunca' ?></li><li><?= icon('user', 15) ?> Criado em <?= data_br(substr($a['criado_em'], 0, 10)) ?></li></ul>
        <?php if ((int) $a['id'] !== (int) $adm['id']): ?><div class="ui-job-foot"><?php
          echo $a['ativo'] ? Ui::formConfirmar(url('admin-acao'), ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $a['id'], 'ret' => 'admin-usuarios'], 'Desativar', 'Desativar este administrador?', $a['nome'] . ' não conseguirá mais entrar. Você pode reativar depois.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o')
                        : Ui::formConfirmar(url('admin-acao'), ['tipo' => 'admin', 'acao' => 'ativar', 'id' => $a['id'], 'ret' => 'admin-usuarios'], 'Reativar', 'Reativar este administrador?', $a['nome'] . ' volta a acessar a administração.', 'primary', 'ui-btn ui-btn-sm'); ?></div>
        <?php else: ?><div class="ui-job-foot"><span class="ui-hint" style="margin:0">Esta é a sua conta.</span></div><?php endif; ?></article>
    <?php endforeach; ?></div></section>
  <aside class="ui-card"><h2>Novo administrador</h2>
    <form method="post" action="<?= url('admin-acao') ?>" data-form novalidate><?= csrf_field() ?><input type="hidden" name="tipo" value="admin"><input type="hidden" name="acao" value="criar"><input type="hidden" name="ret" value="admin-usuarios">
      <?= $c(['name' => 'nome', 'label' => 'Nome', 'req' => true, 'attrs' => ['maxlength' => 100]]) ?>
      <?= $c(['name' => 'email', 'label' => 'E-mail institucional', 'type' => 'email', 'req' => true]) ?>
      <?= Ui::campo(['name' => 'senha', 'label' => 'Senha inicial', 'type' => 'password', 'req' => true, 'hint' => '8+ caracteres, com letras e números.', 'attrs' => ['autocomplete' => 'new-password', 'data-pwd' => '']], [], $erros) ?>
      <button class="ui-btn ui-btn-primary ui-btn-block" type="submit">Criar administrador</button></form></aside>
</div>
```

### Passo 92 — admin_acesso.php

#### Arquivo: `Views/admin_acesso.php` — CRIAR

```php
<?php
$rot = ['login_ok' => ['Login', 'ok'], 'login_falha' => ['Falha de login', 'warn'], 'negado' => ['Acesso negado', 'danger'], 'bloqueado' => ['Bloqueio por tentativas', 'danger'], 'timeout' => ['Sessão expirada', 'mute'], 'logout' => ['Saída', 'mute']];
$linha = function ($ev) use ($rot) { [$t, $c] = $rot[$ev['evento']] ?? [$ev['evento'], 'mute'];
  return '<tr><td>' . e(date('d/m H:i', strtotime($ev['criado_em']))) . '</td><td>' . Ui::badge($t, $c) . '</td><td>' . e($ev['perfil']) . '</td><td>' . e($ev['email'] ?: '-') . '</td><td><span class="ui-code">' . e($ev['ip'] ?: '-') . '</span></td></tr>'; };
?>
<div class="ui-metrics">
  <?= Ui::metrica('Logins (7 dias)', $resumo['login_ok'], 'user') ?><?= Ui::metrica('Falhas de login (7 dias)', $resumo['login_falha'], 'info', null, 'warn') ?>
  <?= Ui::metrica('Acessos negados (7 dias)', $resumo['negado'], 'shield', null, 'danger') ?><?= Ui::metrica('Bloqueios ativos agora', $bloqueios, 'lock', null, $bloqueios ? 'danger' : 'mute') ?></div>
<div class="ui-grid-2">
  <div class="ui-card"><h2>Logins e falhas por dia</h2><?= Ui::barras($logins, [['ok', 'Logins', 'var(--chart-1)'], ['falha', 'Falhas', 'var(--chart-4)']], 'Logins e falhas por dia nos últimos 7 dias') ?></div>
  <div class="ui-card"><h2>Contas e proteção</h2><dl class="ui-kv" style="grid-template-columns:200px 1fr">
    <dt>Administradores ativos</dt><dd><?= (int) $contas['admins_ativos'] ?></dd><dt>Administradores desativados</dt><dd><?= (int) $contas['admins_inativos'] ?></dd><dt>Empresas bloqueadas</dt><dd><?= (int) $contas['empresas_bloqueadas'] ?></dd>
    <dt>Eventos em 30 dias</dt><dd><?= (int) array_sum($resumo30) ?></dd><dt>Limite de tentativas</dt><dd>5 por e-mail/IP a cada 15 minutos</dd><dt>Expiração de sessão</dt><dd>Admin: 30 min sem atividade · Empresa: 2 h · máximo 12 h</dd></dl>
    <p class="ui-sub" style="margin:10px 0 0">Sessões ativas ainda não são armazenadas no banco (preparado para evolução).</p></div>
</div>
<div class="ui-grid-2" style="margin-top:16px">
  <div class="ui-card"><h2>Últimos acessos: administradores</h2><?php if (!$ultAdmins): echo Ui::vazio('Sem acessos registrados'); else: ?><ul style="list-style:none;margin:0;padding:0;display:grid;gap:8px"><?php foreach ($ultAdmins as $u): ?><li style="display:flex;justify-content:space-between;gap:8px"><span><?= e($u['nome']) ?><br><small class="ui-hint" style="margin:0"><?= e($u['email']) ?></small></span><small><?= e(date('d/m H:i', strtotime($u['ultimo_acesso']))) ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
  <div class="ui-card"><h2>Últimos acessos: empresas</h2><?php if (!$ultEmpresas): echo Ui::vazio('Sem acessos registrados'); else: ?><ul style="list-style:none;margin:0;padding:0;display:grid;gap:8px"><?php foreach ($ultEmpresas as $u): ?><li style="display:flex;justify-content:space-between;gap:8px"><span><a href="<?= url('admin-empresa', ['id' => $u['id']]) ?>"><?= e($u['nome']) ?></a><br><small class="ui-hint" style="margin:0"><?= e($u['email']) ?></small></span><small><?= e(date('d/m H:i', strtotime($u['ultimo_acesso']))) ?></small></li><?php endforeach; ?></ul><?php endif; ?></div>
</div>
<section class="ui-section" style="margin-top:22px"><h2>Tentativas suspeitas recentes</h2><?php if (!$suspeitos): echo Ui::vazio('Nenhuma tentativa suspeita registrada'); else: ?><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Quando</th><th>Evento</th><th>Perfil</th><th>E-mail</th><th>IP</th></tr></thead><tbody><?php foreach ($suspeitos as $ev) echo $linha($ev); ?></tbody></table></div><?php endif; ?></section>
<section class="ui-section"><h2>Registro de eventos</h2><?php if (!$eventos): echo Ui::vazio('Nenhum evento ainda'); else: ?><div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Quando</th><th>Evento</th><th>Perfil</th><th>E-mail</th><th>IP</th></tr></thead><tbody><?php foreach ($eventos as $ev) echo $linha($ev); ?></tbody></table></div><?php endif; ?></section>
```

### Passo 93 — Verificar a branch

```bash
php -l Views/admin_usuarios.php && php -l Views/admin_acesso.php
```

**Verificação manual:** Entre como administrador e abra `/?p=admin-usuarios` e `/?p=admin-acesso`.

### Passo 94 — Fechar a branch

```bash
git add .
git commit -m "feat(acesso): gestão de administradores e tela de controle de acesso"
git push -u origin feature/access-control
```
Abra o Pull Request `feature/access-control` → `develop` e peça revisão a **Pedro Henri**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 12: `feature/frontend-jobs` — dono: Arthur

Site público (vagas, carrosséis, empresas parceiras), formulário de vaga em 8 etapas e telas da empresa para vagas.

**Revisor do PR:** Pedro Henri. **Depende de:** `feature/frontend-company`, `feature/frontend-admin`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `108.05` | Validar com o demandante as regras de destaque do carrossel (tempo e limite de vagas) | 1 | 1 |
| `108.10` | Home: banner de destaque, carrosséis por tema e lista filtrável | 5 | 5 |
| `108.11` | Selos do card (tipo, nova, encerra em breve, destaque, melhor salário, recrutador ativo) | 2 | 6 |
| `108.12` | Detalhe público da vaga (dados, benefícios, candidatura) | 3 | 6 |
| `108.02` | Painel da empresa: visão geral, alertas (rejeitadas, vencendo) e completude do perfil | 2 | 7 |
| `108.04` | Detalhe da vaga da empresa com histórico (linha do tempo) | 2 | 7 |
| `105.13` | Formulário de vaga em 8 etapas | 5 | 8 |
| `105.14` | Editor de requisitos e benefícios por etiquetas, com sugestões | 2 | 9 |
| `108.08` | Página Empresas parceiras: carrosséis e lista | 3 | 9 |
| `108.09` | Página pública da empresa e aviso de saída do sistema | 3 | 10 |
| `110.03` | Detalhe público de vaga expirada ou encerrada como histórico (sem candidatura) | 2 | 10 |
| `109.03` | Banner da vaga opcional e card sem imagem (iniciais da empresa) | 2 | 11 |
| `104.03` | Modo noturno na área pública de vagas (fase posterior) | 3 | 11 |
| `104.04` | Revisão de acessibilidade e responsividade (foco, contraste, celular) | 2 | 12 |

```bash
git checkout develop && git pull
git checkout -b feature/frontend-jobs
```

### Passo 95 — Arquivos originais do portal

Estas telas incluem `Views/header.php` e `Views/footer.php` (cabeçalho e rodapé do IFSP). Eles já foram copiados na Preparação e **não devem ser alterados**.

### Passo 96 — vagas.css

#### Arquivo: `public/css/vagas.css` — parte 1 — CRIAR
**Onde:** criar o arquivo. Parte 1 (site público). A parte do Modo TV será acrescentada na branch do Pedro Miguel.

```css
/* Vagas - IFSP Guarulhos. Tudo com prefixo .vg para não conflitar com o template do portal (Bootstrap 2 + padrão gov). */
.vg{--verde:#2f9e41;--verde-esc:#1f6f2d;--verde-ink:#14532d;--verde-cl:#eaf5ec;--texto:#222;--muted:#5d6b62;--borda:#dfe7e1;--warn:#b45309;--warn-bg:#fff3e0;--r:12px;color:var(--texto);font-size:15px;line-height:1.5}
.vg *,.vg *::before,.vg *::after{box-sizing:border-box}
.vg h1,.vg h2,.vg h3{font-family:inherit;line-height:1.2;margin:0}
.vg a,.vg a:hover,.vg a:focus{text-decoration:none}
.vg ul,.vg ol{margin:0}.vg li{line-height:1.4}
.vg button{font-family:inherit}
.vg :focus-visible,.vg-tv :focus-visible{outline:3px solid #f5a623;outline-offset:2px}
.vg-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}
.vg-ico{display:inline-block;vertical-align:-3px;flex:none}

/* navegação */
.vg-tabs{display:flex;flex-wrap:wrap;align-items:flex-end;gap:6px;border-bottom:2px solid var(--borda);margin:0 0 20px}
.vg-tabs a,.vg-tab-out{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:8px 8px 0 0;color:var(--verde-esc);font-weight:600;font-size:14px}
.vg-tabs a:hover{background:var(--verde-cl)}
.vg-tabs a.is-active{background:var(--verde);color:#fff}
.vg-tab-right{margin-left:auto}
.vg-tab-form{margin:0}.vg-tab-out{border:0;background:none;cursor:pointer}.vg-tab-out:hover{background:#fdecec;color:#a12626}
.vg-flash{display:flex;gap:10px;align-items:flex-start;padding:12px 16px;border-radius:10px;margin:0 0 16px;font-weight:600}
.vg-flash-ok{background:var(--verde-cl);color:var(--verde-ink);border:1px solid #bfe0c6}.vg-flash-erro{background:#fdecec;color:#8a1f1f;border:1px solid #f0b8b8}.vg-flash-info{background:#eef5fb;color:#0c4a6e;border:1px solid #c5ddf0}

/* banner de título: fundo verde do IF com texto branco (a cor é forçada para o template não sobrescrever) */
.vg .vg-hero{background:linear-gradient(120deg,#14532d 0%,#1f6f2d 55%,#2f9e41 100%);border-radius:var(--r);padding:24px 28px;margin:0 0 20px;box-shadow:0 4px 14px rgba(20,83,45,.25)}
.vg .vg-hero h1{color:#fff!important;font-size:28px;font-weight:700;margin:0 0 6px;text-shadow:0 1px 2px rgba(0,0,0,.25)}
.vg .vg-hero p{color:#eaf7ec!important;margin:0;max-width:64ch;font-size:15.5px}

/* carrossel grande (banner 900x400) */
.vg-carousel{position:relative;border-radius:var(--r);overflow:hidden;background:#123;margin-bottom:30px;box-shadow:0 4px 14px rgba(0,0,0,.15)}
.vg-slides{position:relative;aspect-ratio:900/400}
.vg-slide{position:absolute;inset:0;opacity:0;visibility:hidden;transition:opacity .5s}
.vg-slide.is-active{opacity:1;visibility:visible}
.vg-slide img,.vg-slide .vg-fallback{width:100%;height:100%;object-fit:cover;display:block}
.vg-slide-caption{position:absolute;left:0;right:0;bottom:0;padding:44px 64px 18px 22px;color:#fff;display:flex;flex-direction:column;gap:2px;background:linear-gradient(transparent,rgba(0,0,0,.8))}
.vg-slide-caption strong{font-size:21px;color:#fff}.vg-slide-caption span{color:#fff}
.vg-slide-tag{align-self:flex-start;background:#f5a623;color:#222!important;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;padding:2px 9px;border-radius:99px;margin-bottom:4px}
.vg-car-btn{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;border-radius:50%;border:0;background:#fff;color:var(--verde-esc);cursor:pointer;z-index:2;display:flex;align-items:center;justify-content:center;padding:0;box-shadow:0 2px 8px rgba(0,0,0,.35);transition:background .15s,transform .15s}
.vg-car-btn .vg-ico{display:block;stroke-width:2.6}
.vg-car-btn:hover{background:var(--verde);color:#fff;transform:translateY(-50%) scale(1.06)}
.vg-prev{left:12px}.vg-next{right:12px}
.vg-dots{position:absolute;right:18px;bottom:16px;display:flex;gap:6px;z-index:2}
.vg-dots button{width:10px;height:10px;border-radius:50%;border:0;padding:0;background:rgba(255,255,255,.55);cursor:pointer}
.vg-dots button.is-active{background:#fff;width:24px;border-radius:99px}

/* trilhos (carrosséis por tema) */
.vg-rail{margin:0 0 28px}
.vg-rail-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:10px}
.vg-rail-head h2{font-size:21px;color:var(--verde-esc);display:flex;align-items:center;gap:8px}
.vg-rail-head h2 .vg-ico{color:var(--verde)}
.vg-rail-head p{margin:2px 0 0;color:var(--muted);font-size:13.5px}
.vg-rail-ctrl{display:flex;gap:8px;flex:none}
.vg-rail-btn{width:38px;height:38px;border-radius:50%;border:2px solid var(--verde);background:#fff;color:var(--verde-esc);display:flex;align-items:center;justify-content:center;padding:0;cursor:pointer;transition:background .15s,color .15s}
.vg-rail-btn .vg-ico{display:block;stroke-width:2.6}
.vg-rail-btn:hover:not(:disabled){background:var(--verde);color:#fff}
.vg-rail-btn:disabled{opacity:.35;cursor:default}
.vg-rail-track{display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;padding:4px 2px 14px;scrollbar-width:thin;scrollbar-color:#b7cfbb transparent;-webkit-overflow-scrolling:touch}
.vg-rail-track::-webkit-scrollbar{height:8px}.vg-rail-track::-webkit-scrollbar-thumb{background:#b7cfbb;border-radius:99px}
.vg-card-trilho{flex:0 0 270px;scroll-snap-align:start}

/* lista, chips e filtros */
.vg-list-head{display:flex;justify-content:space-between;align-items:center;margin:8px 0 12px}
.vg-list-head h2{font-size:22px;color:var(--verde-esc)}
.vg-count{background:var(--verde);color:#fff;border-radius:99px;font-size:13px;padding:2px 10px;vertical-align:middle;margin-left:6px}
.vg-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.vg-chip{border:2px solid var(--borda);background:#fff;color:var(--verde-esc);border-radius:99px;padding:6px 16px;font-weight:700;font-size:14px;cursor:pointer}
.vg-chip:hover{border-color:var(--verde)}.vg-chip.is-active{background:var(--verde);border-color:var(--verde);color:#fff}
.vg-filters{display:grid;grid-template-columns:2fr 1fr 1fr;gap:10px;margin-bottom:18px}
.vg-filters input,.vg-filters select{width:100%;height:42px;margin:0;padding:0 12px;border:1px solid #c3cdc6;border-radius:8px;background:#fff;font-size:14px}
.vg-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.vg-empty{padding:26px;text-align:center;color:var(--muted);background:#f6f8f6;border-radius:var(--r)}

/* card de vaga */
.vg-card{position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid var(--borda);border-radius:var(--r);overflow:hidden;transition:box-shadow .2s,transform .2s}
.vg-card:hover,.vg-card:focus-within{box-shadow:0 8px 22px rgba(0,0,0,.13);transform:translateY(-2px)}
.vg-card[hidden]{display:none}
.vg-card-media{position:relative;aspect-ratio:900/400;background:#e9efe9}
.vg-card-media img,.vg-card-media .vg-fallback{width:100%;height:100%;object-fit:cover;display:block}
.vg-card-body{padding:12px 14px 14px;display:flex;flex-direction:column;flex:1;gap:4px}
.vg-card-emp{display:flex;align-items:center;gap:8px}
.vg-avatar{flex:none;width:28px;height:28px;border-radius:50%;background:var(--verde-cl);color:var(--verde-esc);font-weight:800;font-size:11px;display:grid;place-items:center;border:1px solid #cfe6d3}
.vg-card-company{color:var(--muted);font-weight:600;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vg-card-title{font-size:17px;font-weight:700}
.vg-card-title a{color:var(--texto)}
.vg-stretched::after{content:"";position:absolute;inset:0;z-index:1}
.vg-meta{list-style:none;padding:0;margin:2px 0 0;display:flex;flex-direction:column;gap:3px;font-size:13.5px;color:#3a463e}
.vg-meta li{display:flex;align-items:center;gap:6px;margin:0}.vg-meta .vg-ico{color:var(--verde)}
.vg-meta-money{font-weight:800;color:var(--verde-ink)}
.vg-card-text{margin:4px 0 0;color:#444;font-size:14px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.vg-card-area{margin:auto 0 0;padding-top:6px;font-size:11.5px;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);font-weight:700}

/* selos no "print" (banner) da vaga */
.vg-tags-print{position:absolute;left:10px;top:10px;right:10px;display:flex;flex-wrap:wrap;gap:6px;list-style:none;margin:0;padding:0;z-index:2}
.vg-tag{display:inline-block;font-size:11.5px;font-weight:800;padding:3px 10px;border-radius:99px;line-height:1.4;box-shadow:0 1px 5px rgba(0,0,0,.3);margin:0}
.vg-tag-tipo{background:var(--verde-ink);color:#fff}
.vg-tag-warn{background:#d9480f;color:#fff}
.vg-tag-new{background:#f5a623;color:#222}
.vg-tag-star{background:#fff;color:var(--verde-esc)}
.vg-tag-money{background:#0c4a6e;color:#fff}
.vg-tag-hot{background:#831843;color:#fff}
.vg-tag-inline{box-shadow:none;margin-left:6px;vertical-align:middle;font-size:11px}
.vg-detail-banner{position:relative}

/* selos simples (detalhe) */
.vg-badges{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px}
.vg-badge{display:inline-block;font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;line-height:1.4}
.vg-badge-soft{background:var(--verde-cl);color:var(--verde-esc)}

/* banner sem imagem */
.vg-fallback{display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;text-align:center;padding:8px}
.vg-fallback span{font-size:44px;font-weight:800;line-height:1;color:#fff}
.vg-fallback small{opacity:.92;font-size:14px;margin-top:4px;color:#fff}
.vg-fb-0{background:linear-gradient(135deg,#14532d,#2f9e41)}.vg-fb-1{background:linear-gradient(135deg,#0c4a6e,#3b9bd6)}
.vg-fb-2{background:linear-gradient(135deg,#7c2d12,#e07b39)}.vg-fb-3{background:linear-gradient(135deg,#581c87,#a463d6)}
.vg-fb-4{background:linear-gradient(135deg,#831843,#d8578a)}.vg-fb-5{background:linear-gradient(135deg,#134e4a,#3bb3a7)}
.vg .vg-fallback,.vg-tv-media .vg-fallback{display:flex}

/* chamada para empresas */
.vg-cta-box{display:flex;align-items:center;justify-content:space-between;gap:20px;margin:34px 0 10px;padding:26px 28px;background:linear-gradient(120deg,#14532d,#2f9e41);border-radius:var(--r);box-shadow:0 6px 18px rgba(20,83,45,.28)}
.vg .vg-cta-box h2{font-size:21px;color:#fff!important;margin-bottom:4px}.vg .vg-cta-box p{margin:0;color:#e4f4e7!important;max-width:46ch}
.vg-cta-actions{display:flex;flex-direction:column;align-items:center;gap:10px;flex:none}
.vg-cta-box .vg-btn{background:#fff;color:var(--verde-ink)!important;border-color:#fff;box-shadow:0 3px 10px rgba(0,0,0,.2)}
.vg-cta-box .vg-btn:hover{background:#f5a623;border-color:#f5a623;color:#222!important;transform:translateY(-2px)}
.vg-cta-box .vg-btn-link{color:#fff!important}

/* botões */
.vg-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--verde);color:#fff!important;border:2px solid var(--verde);border-radius:8px;padding:10px 18px;font-weight:700;font-size:14px;cursor:pointer;text-align:center;line-height:1.2;transition:background .15s,transform .15s,box-shadow .15s}
.vg-btn:hover{background:var(--verde-esc);border-color:var(--verde-esc)}
.vg-btn-lg{padding:13px 24px;font-size:15.5px;border-radius:10px}
.vg-btn-sm{padding:6px 12px;font-size:13px;border-radius:7px}
.vg-btn-block{width:100%}
.vg-btn-ghost{background:#fff;color:var(--verde-esc)!important}
.vg-btn-ghost:hover{background:var(--verde-cl);border-color:var(--verde)}
.vg-btn-no{background:#fff;color:#a12626!important;border-color:#c94a4a}.vg-btn-no:hover{background:#fdecec;border-color:#a12626}
.vg-btn-link{display:inline-flex;align-items:center;gap:6px;color:var(--verde-esc);font-weight:700;font-size:14px;margin-left:14px}
.vg-back{color:var(--verde-esc);font-weight:600}

/* detalhe */
.vg-crumbs{font-size:13px;color:var(--muted);margin-bottom:12px}.vg-crumbs a{color:var(--verde-esc)}
.vg-detail{background:#fff;border:1px solid var(--borda);border-radius:var(--r);overflow:hidden;margin-bottom:28px}
.vg-detail-banner{aspect-ratio:900/400;background:#e9efe9}
.vg-detail-banner img,.vg-detail-banner .vg-fallback{width:100%;height:100%;object-fit:cover;display:block}
.vg-detail-head{padding:20px 24px 4px;background:none}
.vg-detail-head h1{font-size:27px;margin-bottom:4px;color:var(--texto)}
.vg-detail-company{color:var(--verde-esc);font-weight:700;font-size:17px;margin:0}
.vg-facts{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--borda);border-block:1px solid var(--borda);margin:18px 0 0}
.vg-facts>div{background:#fafcfa;padding:12px 24px}
.vg-facts dt{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);font-weight:600}
.vg-facts dd{margin:2px 0 0;font-weight:700}
.vg-code{font-family:monospace;background:#eef2ee;padding:2px 6px;border-radius:5px}
.vg-copy,.vg-link-btn{border:0;background:none;color:var(--verde-esc);font-weight:700;cursor:pointer;padding:2px 6px;font-size:13px}
.vg-copy:hover,.vg-link-btn:hover{text-decoration:underline}.vg-link-danger{color:#a12626}
.vg-block{padding:18px 24px 0}.vg-block h2{font-size:18px;color:var(--verde-esc);margin-bottom:8px}
.vg-block p{margin:0}.vg-block ul{margin:0;padding-left:20px}.vg-block li{margin-bottom:4px}
.vg-two{display:grid;grid-template-columns:1fr 1fr}
.vg-tags{list-style:none;padding:0!important;display:flex;flex-wrap:wrap;gap:8px}
.vg-tags li{background:var(--verde-cl);color:var(--verde-esc);padding:4px 12px;border-radius:99px;font-size:14px;font-weight:600;margin:0}
.vg-company{padding-bottom:4px}
.vg-apply{display:flex;flex-wrap:wrap;gap:16px;justify-content:space-between;align-items:center;margin:22px 0 0;padding:20px 24px;background:var(--verde-cl);border-top:1px solid #cfe6d3}
.vg-apply h2{font-size:18px;color:var(--verde-esc);margin-bottom:4px}.vg-apply p{margin:0;max-width:44ch}
.vg-apply-actions{display:flex;flex-wrap:wrap;gap:8px}
.vg-notice{text-align:center;padding:50px 20px;background:#f6f8f6;border-radius:var(--r)}
.vg-notice h1{font-size:24px;margin-bottom:8px}.vg-notice p{color:var(--muted);max-width:52ch;margin:0 auto 16px}
.vg-notice-box{display:flex;gap:14px;align-items:center;padding:16px 20px;border-radius:var(--r);margin-bottom:22px;border:1px solid}
.vg-notice-box>div{flex:1}.vg-notice-box strong{display:block;font-size:16px}.vg-notice-box p{margin:2px 0 0;font-size:14px}
.vg-notice-pend{background:var(--warn-bg);border-color:#f5d3a0;color:#7c4a03}.vg-notice-ok{background:var(--verde-cl);border-color:#bfe0c6;color:var(--verde-ink)}.vg-notice-no{background:#fdecec;border-color:#f0b8b8;color:#8a1f1f}
.vg-notice-box a{font-weight:700;text-decoration:underline}

/* etapas */
.vg-steps{list-style:none;display:flex;flex-wrap:wrap;gap:8px 22px;padding:0;margin:0 0 20px}
.vg-steps li{display:flex;align-items:center;gap:8px;color:var(--muted);font-weight:600;font-size:14px}
.vg-steps span{display:inline-grid;place-items:center;width:26px;height:26px;border-radius:50%;background:#e4e9e5;font-size:13px}
.vg-steps .is-current{color:var(--verde-esc)}.vg-steps .is-current span,.vg-steps .is-done span{background:var(--verde);color:#fff}

/* formulários e login */
.vg-split{display:grid;grid-template-columns:minmax(0,1fr) 290px;gap:22px;align-items:start}
.vg-form fieldset{border:1px solid var(--borda);border-radius:var(--r);padding:14px 18px 6px;margin:0 0 16px;background:#fff}
.vg-form legend{font-size:15px;font-weight:700;color:var(--verde-esc);padding:0 8px;width:auto;margin:0;border:0;line-height:1.3}
.vg-field{margin-bottom:12px}
.vg-field label{display:block;font-weight:600;font-size:14px;margin-bottom:4px}
.vg-form input:not([type=file]):not([type=hidden]),.vg-form select,.vg-form textarea{width:100%;max-width:100%;margin:0;padding:9px 11px;height:auto;border:1px solid #c3cdc6;border-radius:8px;font-size:14px;background:#fff;box-shadow:none}
.vg-form input[type=file]{width:100%;font-size:14px}
.vg-form select{height:40px}
.vg-form textarea{resize:vertical}
.vg-form .is-invalid{border-color:#c94a4a!important;background:#fff8f8}
.vg-cols{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}
.vg-hint{display:block;color:var(--muted);font-size:12.5px;margin-top:3px}
.vg-err{display:block;color:#a12626;font-size:12.5px;margin-top:3px;font-weight:600}.vg-err:empty{display:none}
.vg-alert{display:flex;gap:8px;align-items:flex-start;background:#fdecec;color:#8a1f1f;border:1px solid #f0b8b8;border-radius:8px;padding:10px 14px;margin:0 0 14px;font-weight:600}
.vg-as{margin:0 0 14px;color:var(--verde-esc);display:flex;align-items:center;gap:8px}
.vg-area-outra{margin-top:10px;padding:10px 12px;background:var(--verde-cl);border-radius:8px}.vg-area-outra[hidden]{display:none}
.vg-privacy{font-size:13px;color:var(--muted);margin:0 0 14px;display:flex;gap:8px;align-items:center}
.vg-preview{position:sticky;top:12px}.vg-preview h2{font-size:14px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px}
.vg-preview .vg-card:hover{transform:none;box-shadow:none}
.vg-auth{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:22px;align-items:stretch;max-width:880px}
.vg-auth-single{grid-template-columns:minmax(0,440px)}
.vg-auth-card{background:#fff;border:1px solid var(--borda);border-radius:var(--r);padding:24px;margin:0}
.vg-auth-card h2{font-size:21px;color:var(--verde-esc);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.vg-auth-side{background:var(--verde-cl);border:1px solid #cfe6d3;border-radius:var(--r);padding:24px;display:flex;flex-direction:column;gap:12px;justify-content:center;align-items:flex-start}
.vg-auth-side h2{font-size:19px;color:var(--verde-esc)}.vg-auth-side p{margin:0;color:var(--muted)}

/* painel / admin */
.vg-adm-head{display:flex;gap:14px;align-items:flex-start}.vg-adm-head .vg-hero{flex:1}
.vg-bell{position:relative}.vg-bell summary{list-style:none;cursor:pointer;width:48px;height:48px;border-radius:50%;background:#fff;border:2px solid var(--verde);color:var(--verde-esc);display:grid;place-items:center;position:relative}
.vg-bell summary::-webkit-details-marker{display:none}
.vg-bell-n{position:absolute;top:-6px;right:-6px;background:#d9480f;color:#fff;border-radius:99px;font-size:12px;font-weight:800;min-width:22px;height:22px;display:grid;place-items:center;padding:0 5px}
.vg-bell-box{position:absolute;right:0;top:56px;width:min(340px,86vw);background:#fff;border:1px solid var(--borda);border-radius:var(--r);box-shadow:0 10px 28px rgba(0,0,0,.2);padding:14px;z-index:20}
.vg-bell-box p{margin:8px 0 0;padding:8px 10px;border-radius:8px;background:#f6f8f6;display:flex;flex-direction:column;font-size:13.5px}.vg-bell-box p.is-new{background:var(--warn-bg);font-weight:700}.vg-bell-box small{color:var(--muted);font-weight:400}
.vg-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px}
.vg-stats>a{background:#fff;border:1px solid var(--borda);border-radius:var(--r);padding:14px 16px;display:block;color:inherit}.vg-stats>a:hover{border-color:var(--verde)}
.vg-stats strong{display:block;font-size:30px;color:var(--verde-esc);line-height:1}.vg-stats span{color:var(--muted);font-size:13px}
.vg-admin{margin-bottom:26px}.vg-admin h2{font-size:19px;color:var(--verde-esc);margin-bottom:10px}
.vg-row{display:flex;flex-wrap:wrap;gap:10px 16px;justify-content:space-between;align-items:center;background:#fff;border:1px solid var(--borda);border-radius:var(--r);padding:12px 16px;margin-bottom:10px}
.vg-row-info{display:flex;flex-direction:column;gap:2px;min-width:0;flex:1 1 280px}.vg-row-info span{font-size:13px;color:var(--muted)}
.vg-row-actions{display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap}.vg-row-actions form{margin:0}
.vg-status{font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;align-self:center}
.vg-status-pend{background:var(--warn-bg);color:var(--warn)}.vg-status-ok{background:var(--verde-cl);color:var(--verde-esc)}.vg-status-no{background:#fdecec;color:#a12626}.vg-status-off{background:#eceeed;color:#555}
.vg-reject{position:relative}.vg-reject summary{list-style:none;cursor:pointer}.vg-reject summary::-webkit-details-marker{display:none}
.vg-reject form{position:absolute;right:0;top:40px;width:280px;background:#fff;border:1px solid var(--borda);border-radius:10px;box-shadow:0 10px 28px rgba(0,0,0,.2);padding:12px;z-index:10;display:flex;flex-direction:column;gap:8px}
.vg-reject textarea{width:100%;margin:0;border:1px solid #c3cdc6;border-radius:8px;padding:8px;font-size:13.5px}
.vg-table-wrap{overflow-x:auto}.vg-table{width:100%;border-collapse:collapse;background:#fff;font-size:14px}
.vg-table th,.vg-table td{padding:9px 12px;border-bottom:1px solid var(--borda);text-align:left;vertical-align:top}.vg-table th{background:#f3f7f3;font-size:12px;text-transform:uppercase;color:var(--muted)}
.vg-table form{margin:0}.vg-table small{color:var(--muted)}

@media (max-width:900px){.vg-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.vg-split{grid-template-columns:1fr}.vg-preview{position:static;order:-1}.vg-auth{grid-template-columns:1fr}}
@media (max-width:640px){
  .vg-grid,.vg-two,.vg-cols,.vg-stats,.vg-filters{grid-template-columns:1fr}.vg-facts{grid-template-columns:1fr 1fr}
  .vg .vg-hero{padding:18px}.vg .vg-hero h1{font-size:23px}.vg-detail-head h1{font-size:22px}
  .vg-cta-box{flex-direction:column;align-items:flex-start}.vg-cta-actions{align-items:flex-start}
  .vg-tab-right{margin-left:0}.vg-slide-caption strong{font-size:16px}.vg-slide-caption{padding:30px 14px 12px}
  .vg-card-trilho{flex-basis:78%}.vg-rail-ctrl{display:none}.vg-btn-link{margin-left:0}
  .vg-reject form{position:static;width:100%;margin-top:8px}
}
@media (prefers-reduced-motion:reduce){.vg *{transition:none!important}.vg-rail-track{scroll-behavior:auto}}

/* ===== Empresas parceiras (estudante) ===== */
.vg-comp{padding:16px;gap:8px}.vg-comp-top{display:flex;gap:12px;align-items:center}.vg-comp-top .ui-logo{width:56px;height:56px}
.vg-comp-seg{margin:2px 0 0;color:var(--muted,#5d6b62);font-size:13px}.vg-comp-desc{margin:6px 0 0;font-size:14px;color:#444;min-height:42px}
.vg-comp-foot{margin:auto 0 0;padding-top:8px;font-size:13.5px;color:#3a463e}.vg-comp-link{position:relative;z-index:2;font-weight:700;font-size:13.5px;color:var(--verde-esc,#1f6f2d)}
.vg-comp-page{display:flex;gap:18px;align-items:center}.vg-comp-page .ui-logo{width:84px;height:84px}
.vg .ui-logo{width:44px;height:44px;border-radius:10px;object-fit:cover;background:#eef3ef;border:1px solid #dfe7e1;flex:none}
.vg .ui-logo-ini{display:grid;place-items:center;font-weight:800;color:#1f6f2d;background:#e4f3e8}
```

### Passo 97 — vagas.js

#### Arquivo: `public/js/vagas.js` — parte 1 — CRIAR
**Onde:** criar o arquivo. Parte 1 (carrossel, trilhos, filtros e copiar código). Termina com `})();`: a parte do Modo TV entra antes dessa linha.

```javascript
/* Site público de vagas (IF): carrosséis, filtros, copiar código, QR Code e Modo TV. Formulários e áreas logadas usam app.js. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduzMovimento = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Carrossel grande de destaques ---------- */
  $$('[data-carousel]').forEach(function (car) {
    var slides = $$('.vg-slide', car), dots = $$('[data-dot]', car), i = 0, timer;
    if (slides.length < 2) return;
    function go(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('is-active', k === i); });
      dots.forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
    }
    function stop() { clearInterval(timer); }
    function play() { if (!reduzMovimento) { stop(); timer = setInterval(function () { go(i + 1); }, 6000); } }
    $('[data-prev]', car).addEventListener('click', function () { go(i - 1); play(); });
    $('[data-next]', car).addEventListener('click', function () { go(i + 1); play(); });
    dots.forEach(function (d) { d.addEventListener('click', function () { go(+d.dataset.dot); play(); }); });
    car.addEventListener('mouseenter', stop); car.addEventListener('mouseleave', play);
    car.addEventListener('focusin', stop); car.addEventListener('focusout', play);
    var x0 = null;
    car.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    car.addEventListener('touchend', function (e) {
      if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) { go(dx < 0 ? i + 1 : i - 1); play(); } x0 = null;
    });
    play();
  });

  /* ---------- Trilhos (carrosséis por tema) ---------- */
  $$('[data-rail]').forEach(function (rail) {
    var trilho = $('[data-rail-track]', rail), ant = $('[data-rail-prev]', rail), prox = $('[data-rail-next]', rail);
    function passo() { return Math.max(240, trilho.clientWidth * 0.85); }
    function estado() {
      ant.disabled = trilho.scrollLeft <= 4;
      prox.disabled = trilho.scrollLeft + trilho.clientWidth >= trilho.scrollWidth - 4;
    }
    ant.addEventListener('click', function () { trilho.scrollBy({ left: -passo(), behavior: reduzMovimento ? 'auto' : 'smooth' }); });
    prox.addEventListener('click', function () { trilho.scrollBy({ left: passo(), behavior: reduzMovimento ? 'auto' : 'smooth' }); });
    trilho.addEventListener('scroll', function () { window.requestAnimationFrame(estado); }, { passive: true });
    trilho.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); prox.click(); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); ant.click(); }
    });
    window.addEventListener('resize', estado); estado();
  });

  /* ---------- Filtros da lista (chips de tipo + busca + área + modalidade) ---------- */
  var filtros = $('[data-filters]');
  if (filtros) {
    var cards = $$('[data-grid] .vg-card'), busca = $('[data-f-busca]'), area = $('[data-f-area]'), mod = $('[data-f-mod]');
    var chips = $$('[data-chip]'), tipo = '', contador = $('[data-count]'), vazio = $('[data-empty]');
    var normaliza = function (t) { return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); };
    var aplicar = function () {
      var q = normaliza(busca.value.trim()), n = 0;
      cards.forEach(function (c) {
        var ok = (!q || normaliza(c.dataset.busca).indexOf(q) > -1) && (!tipo || c.dataset.tipo === tipo) &&
                 (!area.value || c.dataset.area === area.value) && (!mod.value || c.dataset.modalidade === mod.value);
        c.hidden = !ok; if (ok) n++;
      });
      contador.textContent = n; vazio.hidden = n > 0;
      vazio.textContent = 'Nenhuma vaga encontrada. Tente limpar os filtros.';
    };
    [busca, area, mod].forEach(function (el) { el.addEventListener('input', aplicar); });
    chips.forEach(function (c) {
      c.addEventListener('click', function () {
        tipo = c.dataset.chip; chips.forEach(function (x) { x.classList.toggle('is-active', x === c); }); aplicar();
      });
    });
  }

  /* ---------- Copiar código da vaga ---------- */
  $$('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var txt = b.dataset.copy, orig = b.innerHTML;
      var ok = function () { b.textContent = 'Copiado!'; setTimeout(function () { b.innerHTML = orig; }, 1600); };
      if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(txt).then(ok); }
      else { var t = document.createElement('textarea'); t.value = txt; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); ok(); }
    });
  });

})();
```

### Passo 98 — nav.php

#### Arquivo: `Views/partials/nav.php` — CRIAR

```php
<?php $ativa = $ativa ?? 'home'; $perfilAtual = Auth::perfil(); $empLog = $perfilAtual === 'empresa' ? Auth::empresa() : null; ?>
<div id="topo" class="vg">
  <nav class="vg-tabs" aria-label="Navegação">
    <a href="<?= url() ?>" class="<?= $ativa === 'home' ? 'is-active' : '' ?>"><?= icon('briefcase', 16) ?> Vagas</a>
    <a href="<?= url('empresas') ?>" class="<?= $ativa === 'empresas' ? 'is-active' : '' ?>"><?= icon('building', 16) ?> Empresas parceiras</a>
    <?php if ($perfilAtual === 'admin'): ?><a href="<?= url('admin') ?>"><?= icon('lock', 16) ?> Administração</a>
    <?php else: ?><a href="<?= url($empLog ? 'painel' : 'empresa-login') ?>"><?= icon('user', 16) ?> Sou empresa<?= $empLog ? ' (minha área)' : '' ?></a><?php endif; ?>
    <a href="<?= url('tv') ?>" target="_blank" rel="noopener" class="vg-tab-right"><?= icon('tv', 16) ?> Modo TV</a>
    <?php if ($perfilAtual): ?><form method="post" action="<?= url('sair') ?>" class="vg-tab-form"><?= csrf_field() ?><input type="hidden" name="perfil" value="<?= e($perfilAtual) ?>"><button type="submit" class="vg-tab-out"><?= icon('logout', 15) ?> Sair</button></form><?php endif; ?>
  </nav>
  <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?><div class="vg-flash vg-flash-<?= e($t) ?>" role="status"><?= icon($t === 'erro' ? 'info' : 'check', 18) ?> <span><?= e($m) ?></span></div><?php endforeach; unset($_SESSION['flash']); ?>
```

### Passo 99 — card_vaga.php

#### Arquivo: `Views/partials/card_vaga.php` — CRIAR

```php
<?php
// Espera $v. Opcional: $chave (selo prioritário do carrossel) e $trilho (true = card de carrossel)
$chave = $chave ?? null; $trilho = $trilho ?? false;
$selos = $v['selos'] ?? [];
if ($chave && isset($selos[$chave])) { $selos = [$chave => $selos[$chave]] + $selos; }
$selos = array_slice($selos, 0, 3, true);
$prazo = selo_prazo($v);
?>
<article class="vg-card <?= $trilho ? 'vg-card-trilho' : '' ?>" data-tipo="<?= e($v['tipo']) ?>" data-area="<?= e($v['area_exibicao'] ?? $v['area']) ?>" data-modalidade="<?= e($v['modalidade']) ?>"
         data-busca="<?= e($v['titulo'] . ' ' . $v['empresa']['nome'] . ' ' . $v['resumo'] . ' ' . $v['area'] . ' ' . $v['tipo'] . ' ' . $v['local']) ?>">
  <div class="vg-card-media">
    <?= vaga_imagem($v) ?>
    <ul class="vg-tags-print" aria-label="Destaques da vaga">
      <?php foreach ($selos as $k => [$txt, $cls]): ?><li class="vg-tag <?= e($cls) ?>"><?= e($txt) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <div class="vg-card-body">
    <div class="vg-card-emp"><span class="vg-avatar" aria-hidden="true"><?= e(iniciais($v['empresa']['nome'])) ?></span><span class="vg-card-company"><?= e($v['empresa']['nome']) ?></span></div>
    <h3 class="vg-card-title"><a class="vg-stretched" href="<?= url('vaga', ['id' => $v['id']]) ?>"><?= e($v['titulo']) ?></a></h3>
    <ul class="vg-meta">
      <li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li>
      <li class="vg-meta-money"><?= icon('money', 15) ?> <?= e($v['bolsa_txt']) ?></li>
      <li><?= icon('clock', 15) ?> <?= e($prazo[0]) ?></li>
    </ul>
    <?php if (!$trilho): ?><p class="vg-card-text"><?= e($v['resumo']) ?></p><?php endif; ?>
    <p class="vg-card-area"><?= e($v['area_exibicao'] ?? $v['area']) ?></p>
  </div>
</article>
```

### Passo 100 — card_empresa_publico.php

#### Arquivo: `Views/partials/card_empresa_publico.php` — CRIAR

```php
<?php /** Card de empresa parceira (estudante). Espera $e. O clique principal leva ao aviso de saída para o site/LinkedIn; "Ver vagas" abre a página interna. */
$ext = Empresa::linkExterno($e); ?>
<article class="vg-card vg-comp vg-card-trilho">
  <div class="vg-comp-top"><?= Ui::logo($e, 56) ?><div><h3 class="vg-card-title" style="margin:0"><a class="vg-stretched" href="<?= e($ext ? url('externo', ['id' => $e['id']]) : url('empresa', ['id' => $e['id']])) ?>"><?= e($e['nome']) ?></a></h3><p class="vg-comp-seg"><?= e(Empresa::segmentoTxt($e)) ?></p></div></div>
  <p class="vg-comp-desc"><?= e(mb_strimwidth((string) $e['descricao'], 0, 110, '…')) ?></p>
  <p class="vg-comp-foot"><strong><?= (int) $e['vagas_abertas'] ?></strong> vaga(s) aberta(s)<?= $e['cidade'] ? ' · ' . e($e['cidade'] . '/' . $e['uf']) : '' ?></p>
  <a class="vg-comp-link" href="<?= url('empresa', ['id' => $e['id']]) ?>">Ver vagas e informações</a>
</article>
```

### Passo 101 — home.php

#### Arquivo: `Views/home.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'home'; require __DIR__ . '/partials/nav.php'; ?>

  <div class="vg-hero">
    <h1>Vagas de Estágio e Primeiro Emprego</h1>
    <p>Oportunidades divulgadas por empresas parceiras do IFSP Campus Guarulhos. Escolha uma vaga, veja os detalhes e fale direto com a empresa.</p>
  </div>

  <?php if ($destaques): ?>
  <section class="vg-carousel" data-carousel aria-roledescription="carrossel" aria-label="Vagas em destaque">
    <div class="vg-slides">
      <?php foreach ($destaques as $i => $v): ?>
        <a class="vg-slide <?= $i === 0 ? 'is-active' : '' ?>" href="<?= url('vaga', ['id' => $v['id']]) ?>" aria-label="<?= ($i + 1) ?> de <?= count($destaques) ?>: <?= e($v['titulo']) ?> - <?= e($v['empresa']['nome']) ?>">
          <?= vaga_imagem($v) ?>
          <span class="vg-slide-caption">
            <span class="vg-slide-tag"><?= e($v['tipo']) ?> · Destaque</span>
            <strong><?= e($v['titulo']) ?></strong>
            <span><?= e($v['empresa']['nome']) ?> · <?= e($v['modalidade']) ?> · <?= e($v['bolsa_txt']) ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (count($destaques) > 1): ?>
    <button type="button" class="vg-car-btn vg-prev" data-prev aria-label="Vaga anterior"><?= icon('chevron-left', 22) ?></button>
    <button type="button" class="vg-car-btn vg-next" data-next aria-label="Próxima vaga"><?= icon('chevron-right', 22) ?></button>
    <div class="vg-dots" role="tablist" aria-label="Escolher slide">
      <?php foreach ($destaques as $i => $v): ?><button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="Ir para a vaga <?= $i + 1 ?>"></button><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <?php foreach ($trilhos as $t): ?>
  <section class="vg-rail" data-rail aria-labelledby="rail-<?= e($t['id']) ?>">
    <div class="vg-rail-head">
      <div>
        <h2 id="rail-<?= e($t['id']) ?>"><?= icon($t['icone'], 20) ?> <?= e($t['titulo']) ?></h2>
        <?php if (!empty($t['sub'])): ?><p><?= e($t['sub']) ?></p><?php endif; ?>
      </div>
      <div class="vg-rail-ctrl">
        <button type="button" class="vg-rail-btn" data-rail-prev aria-label="Voltar em <?= e($t['titulo']) ?>"><?= icon('chevron-left', 20) ?></button>
        <button type="button" class="vg-rail-btn" data-rail-next aria-label="Avançar em <?= e($t['titulo']) ?>"><?= icon('chevron-right', 20) ?></button>
      </div>
    </div>
    <div class="vg-rail-track" data-rail-track tabindex="0">
      <?php foreach ($t['itens'] as $v) { $chave = $t['chave']; $trilho = true; require __DIR__ . '/partials/card_vaga.php'; } ?>
    </div>
  </section>
  <?php endforeach; ?>

  <section id="todas" aria-labelledby="vg-lista-titulo">
    <div class="vg-list-head"><h2 id="vg-lista-titulo">Todas as vagas abertas <span class="vg-count" data-count><?= count($vagas) ?></span></h2></div>

    <?php if ($tipos): ?>
    <div class="vg-chips" role="group" aria-label="Filtrar por tipo de vaga">
      <button type="button" class="vg-chip is-active" data-chip="">Todas</button>
      <?php foreach ($tipos as $t): ?><button type="button" class="vg-chip" data-chip="<?= e($t) ?>"><?= e($t) ?></button><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form class="vg-filters" data-filters role="search" onsubmit="return false">
      <label class="vg-sr" for="f-busca">Buscar vaga</label>
      <input id="f-busca" type="search" placeholder="Buscar por cargo, empresa ou palavra-chave" data-f-busca>
      <label class="vg-sr" for="f-area">Área</label>
      <select id="f-area" data-f-area><option value="">Todas as áreas</option><?php foreach ($areas as $a): ?><option><?= e($a) ?></option><?php endforeach; ?></select>
      <label class="vg-sr" for="f-mod">Modalidade</label>
      <select id="f-mod" data-f-mod><option value="">Toda modalidade</option><?php foreach ($modalidades as $m): ?><option><?= e($m) ?></option><?php endforeach; ?></select>
    </form>

    <div class="vg-grid" data-grid>
      <?php foreach ($vagas as $v) { $trilho = false; $chave = null; require __DIR__ . '/partials/card_vaga.php'; } ?>
    </div>
    <p style="text-align:right;margin:10px 0 0"><a class="vg-btn-link" href="<?= url('encerradas') ?>">Ver vagas encerradas e expiradas <?= icon('arrow-right', 16) ?></a></p>
    <p class="vg-empty" data-empty <?= $vagas ? 'hidden' : '' ?>><?= $vagas ? 'Nenhuma vaga encontrada. Tente limpar os filtros.' : 'Nenhuma vaga aberta no momento. Volte em breve!' ?></p>
  </section>

  <?php if (!Auth::perfil()): ?>
  <aside class="vg-cta-box">
    <div>
      <h2>É uma empresa parceira?</h2>
      <p>Cadastre sua empresa e, depois de aprovada pela Coordenação, publique suas vagas.</p>
    </div>
    <div class="vg-cta-actions">
      <a class="vg-btn vg-btn-lg" href="<?= url('empresa-cadastro') ?>"><?= icon('building', 18) ?> Cadastrar minha empresa</a>
      <a class="vg-btn-link" href="<?= url('empresa-login') ?>">Já sou parceira: entrar <?= icon('arrow-right', 16) ?></a>
    </div>
  </aside>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 102 — vaga.php

#### Arquivo: `Views/vaga.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'home'; require __DIR__ . '/partials/nav.php'; $emp = $vaga['empresa']; $selos = $aberta ? array_slice($vaga['selos'], 0, 4, true) : []; ?>

  <nav class="vg-crumbs" aria-label="Você está aqui"><a href="<?= url() ?>">Vagas</a> <span aria-hidden="true">›</span> <span><?= e($vaga['titulo']) ?></span></nav>
  <?php if (!$aberta): ?><div class="vg-flash vg-flash-info" role="status"><?= icon('info', 18) ?> <span><strong>Esta vaga não recebe mais inscrições.</strong> <?= $vaga['situacao'] === 'encerrada' ? 'Foi encerrada pela empresa.' : 'O prazo terminou em ' . data_br($vaga['data_limite']) . '.' ?> As informações abaixo ficam disponíveis como histórico.</span></div><?php endif; ?>

  <article class="vg-detail">
    <div class="vg-detail-banner"><?= vaga_imagem($vaga) ?>
      <ul class="vg-tags-print"><?php foreach ($selos as [$txt, $cls]): ?><li class="vg-tag <?= e($cls) ?>"><?= e($txt) ?></li><?php endforeach; ?><?php if (!$aberta): ?><li class="vg-tag vg-tag-tipo">Encerrada</li><?php endif; ?></ul>
    </div>
    <div class="vg-detail-head">
      <div class="vg-badges"><span class="vg-badge vg-badge-soft"><?= e($vaga['tipo']) ?></span><span class="vg-badge vg-badge-soft"><?= e($vaga['area']) ?></span><span class="vg-badge vg-badge-soft"><?= e($vaga['modalidade']) ?></span></div>
      <h1><?= e($vaga['titulo']) ?></h1>
      <p class="vg-detail-company"><?= icon('building', 18) ?> <a href="<?= url('empresa', ['id' => $emp['id']]) ?>" style="color:inherit"><?= e($emp['nome']) ?></a></p>
    </div>
    <dl class="vg-facts">
      <div><dt><?= $vaga['tipo'] === 'Estágio' ? 'Bolsa-auxílio' : 'Salário' ?></dt><dd><?= e($vaga['bolsa_txt']) ?></dd></div>
      <div><dt>Carga horária</dt><dd><?= e($vaga['carga_txt'] ?: 'A combinar') ?></dd></div>
      <div><dt>Local</dt><dd><?= e($vaga['local']) ?></dd></div>
      <div><dt>Publicada em</dt><dd><?= data_br($vaga['data_publicacao']) ?></dd></div>
      <div><dt><?= $aberta ? 'Inscrições até' : 'Inscrições encerradas em' ?></dt><dd><?= data_br($vaga['data_limite']) ?></dd></div>
      <div><dt>Código da vaga</dt><dd><span class="vg-code"><?= e($vaga['codigo']) ?></span> <button type="button" class="vg-copy" data-copy="<?= e($vaga['codigo']) ?>" aria-label="Copiar código da vaga"><?= icon('copy', 14) ?> Copiar</button></dd></div>
    </dl>
    <section class="vg-block"><h2>Sobre a vaga</h2><p><?= nl2br(e($vaga['descricao'])) ?></p></section>
    <div class="vg-two">
      <?php if ($vaga['atividades']): ?><section class="vg-block"><h2>Atividades</h2><ul><?php foreach ($vaga['atividades'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></section><?php endif; ?>
      <?php if ($vaga['requisitos']): ?><section class="vg-block"><h2>Requisitos</h2><ul><?php foreach ($vaga['requisitos'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul>
        <?php if ($vaga['requisitos_desejaveis']): ?><h2 style="margin-top:12px">Desejáveis</h2><ul><?php foreach ($vaga['requisitos_desejaveis'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?></section><?php endif; ?>
    </div>
    <?php if ($vaga['beneficios']): ?><section class="vg-block"><h2>Benefícios</h2><ul class="vg-tags"><?php foreach ($vaga['beneficios'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></section><?php endif; ?>
    <section class="vg-block vg-company"><h2>Sobre a empresa</h2>
      <p><strong><?= e($emp['nome']) ?></strong><?= $emp['segmento'] ? ' · ' . e($emp['segmento']) : '' ?><br>CNPJ: <?= e(Cnpj::formatar($emp['cnpj'])) ?><br><?= e($emp['endereco']) ?><br><a href="<?= url('empresa', ['id' => $emp['id']]) ?>">Ver página da empresa</a></p></section>

    <?php if ($aberta): ?>
    <section class="vg-apply">
      <div><h2>Como se candidatar</h2><p>A candidatura é feita diretamente com a empresa<?= $vaga['link_candidatura'] ? ' (' . e(Link::PLATAFORMAS[$vaga['link_plataforma']] ?? 'processo externo') . ')' : '' ?>. Informe o código <strong><?= e($vaga['codigo']) ?></strong> no contato.</p></div>
      <div class="vg-apply-actions">
        <?php if ($vaga['link_candidatura']): ?><a class="vg-btn" href="<?= e($vaga['link_candidatura']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 16) ?> Candidatar-se<?= !empty($vaga['link_plataforma']) && isset(Link::PLATAFORMAS[$vaga['link_plataforma']]) ? ' pela ' . e(Link::PLATAFORMAS[$vaga['link_plataforma']]) : '' ?></a><?php endif; ?>
        <?php if ($vaga['contato']): ?><a class="vg-btn <?= $vaga['link_candidatura'] ? 'vg-btn-ghost' : '' ?>" href="mailto:<?= e($vaga['contato']) ?>?subject=<?= rawurlencode('Candidatura ' . $vaga['codigo'] . ' - ' . $vaga['titulo']) ?>"><?= icon('mail', 16) ?> <?= e($vaga['contato']) ?></a><?php endif; ?>
        <?php if ($emp['telefone']): ?><a class="vg-btn vg-btn-ghost" href="tel:<?= e(preg_replace('/\D/', '', $emp['telefone'])) ?>"><?= icon('phone', 16) ?> <?= e(fone_fmt($emp['telefone'])) ?></a><?php endif; ?>
        <?php foreach (['link_site' => 'Site da empresa', 'link_linkedin' => 'LinkedIn'] as $k => $rot): if ($vaga[$k]): ?><a class="vg-btn vg-btn-ghost" href="<?= e($vaga[$k]) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 16) ?> <?= $rot ?></a><?php endif; endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </article>

  <?php if ($relacionadas): ?><section aria-labelledby="vg-rel"><div class="vg-list-head"><h2 id="vg-rel">Outras vagas</h2></div><div class="vg-grid"><?php foreach ($relacionadas as $v) { $trilho = false; $chave = null; require __DIR__ . '/partials/card_vaga.php'; } ?></div></section><?php endif; ?>
  <p><a class="vg-back" href="<?= url() ?>"><?= icon('arrow-left', 16) ?> Voltar para todas as vagas</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 103 — vaga_indisponivel.php

#### Arquivo: `Views/vaga_indisponivel.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'home'; require __DIR__ . '/partials/nav.php'; ?>
  <div class="vg-notice">
    <h1>Esta vaga não está mais disponível</h1>
    <p>O prazo de inscrição terminou, a vaga foi encerrada ou o endereço está incorreto. As vagas saem do ar automaticamente após a data limite.</p>
    <a class="vg-btn vg-btn-lg" href="<?= url() ?>">Ver vagas abertas</a>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 104 — vagas_encerradas.php

#### Arquivo: `Views/vagas_encerradas.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'home'; require __DIR__ . '/partials/nav.php'; ?>
  <div class="vg-hero"><h1>Vagas encerradas</h1><p>Vagas que já saíram do ar nos últimos 120 dias. Elas não recebem mais inscrições e ficam aqui só para consulta.</p></div>
  <?php if (!$vagas): ?><p class="vg-empty">Nenhuma vaga encerrada recentemente.</p><?php else: ?>
  <div class="vg-grid"><?php foreach ($vagas as $v): ?>
    <article class="vg-card"><div class="vg-card-media"><?= vaga_imagem($v) ?><ul class="vg-tags-print"><li class="vg-tag vg-tag-tipo"><?= e($v['tipo']) ?></li><li class="vg-tag vg-tag-warn"><?= $v['situacao'] === 'encerrada' ? 'Encerrada' : 'Prazo encerrado' ?></li></ul></div>
      <div class="vg-card-body"><div class="vg-card-emp"><span class="vg-avatar" aria-hidden="true"><?= e(iniciais($v['empresa']['nome'])) ?></span><span class="vg-card-company"><?= e($v['empresa']['nome']) ?></span></div>
        <h3 class="vg-card-title"><a class="vg-stretched" href="<?= e(url_vaga((int) $v['id'])) ?>"><?= e($v['titulo']) ?></a></h3>
        <ul class="vg-meta"><li><?= icon('pin', 15) ?> <?= e($v['local']) ?> · <?= e($v['modalidade']) ?></li><li><?= icon('clock', 15) ?> Inscrições até <?= data_br($v['data_limite']) ?></li></ul><p class="vg-card-area"><?= e($v['area']) ?></p></div></article>
  <?php endforeach; ?></div><?php endif; ?>
  <p><a class="vg-back" href="<?= url() ?>"><?= icon('arrow-left', 16) ?> Voltar para as vagas abertas</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 105 — empresas_parceiras.php

#### Arquivo: `Views/empresas_parceiras.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'empresas'; require __DIR__ . '/partials/nav.php'; ?>
  <div class="vg-hero"><h1>Empresas parceiras</h1><p>Conheça as empresas que divulgam oportunidades para os estudantes do campus. Clique em uma empresa para conhecer o site ou o LinkedIn dela.</p></div>
  <?php foreach ($trilhos as $t): ?>
  <section class="vg-rail" data-rail aria-labelledby="rail-<?= e($t['id']) ?>">
    <div class="vg-rail-head"><div><h2 id="rail-<?= e($t['id']) ?>"><?= icon('building', 20) ?> <?= e($t['titulo']) ?></h2><p><?= e($t['sub']) ?></p></div>
      <div class="vg-rail-ctrl"><button type="button" class="vg-rail-btn" data-rail-prev aria-label="Voltar"><?= icon('chevron-left', 20) ?></button><button type="button" class="vg-rail-btn" data-rail-next aria-label="Avançar"><?= icon('chevron-right', 20) ?></button></div></div>
    <div class="vg-rail-track" data-rail-track tabindex="0"><?php foreach ($t['itens'] as $e) require __DIR__ . '/partials/card_empresa_publico.php'; ?></div>
  </section>
  <?php endforeach; ?>
  <section aria-labelledby="todas-emp"><div class="vg-list-head"><h2 id="todas-emp">Todas as empresas <span class="vg-count"><?= count($todas) ?></span></h2></div>
    <?php if (!$todas): ?><p class="vg-empty">Ainda não há empresas parceiras aprovadas.</p><?php else: ?>
    <div class="vg-grid"><?php foreach ($todas as $e) require __DIR__ . '/partials/card_empresa_publico.php'; ?></div><?php endif; ?></section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 106 — empresa_publica.php

#### Arquivo: `Views/empresa_publica.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'empresas'; require __DIR__ . '/partials/nav.php'; $ext = Empresa::linkExterno($e); ?>
  <nav class="vg-crumbs" aria-label="Você está aqui"><a href="<?= url('empresas') ?>">Empresas parceiras</a> <span aria-hidden="true">›</span> <span><?= e($e['nome']) ?></span></nav>
  <article class="vg-detail"><div class="vg-detail-head vg-comp-page"><?= Ui::logo($e, 84) ?><div>
    <div class="vg-badges"><?php if ($e['segmento']): ?><span class="vg-badge vg-badge-soft"><?= e($e['segmento']) ?></span><?php endif; ?><?php if ($e['porte']): ?><span class="vg-badge vg-badge-soft"><?= e($e['porte']) ?></span><?php endif; ?></div>
    <h1><?= e($e['nome']) ?></h1><p class="vg-detail-company"><?= icon('pin', 18) ?> <?= e($e['cidade'] ? $e['cidade'] . '/' . $e['uf'] : 'Localização não informada') ?></p></div></div>
    <?php if ($e['descricao']): ?><section class="vg-block"><h2>Sobre a empresa</h2><p><?= nl2br(e($e['descricao'])) ?></p></section><?php endif; ?>
    <dl class="vg-facts" style="margin-top:18px"><div><dt>Vagas abertas</dt><dd><?= (int) $e['vagas_abertas'] ?></dd></div><div><dt>Vagas já publicadas</dt><dd><?= (int) $e['total_vagas'] ?></dd></div><div><dt>Contato</dt><dd><?= e($e['email_contato'] ?: '-') ?></dd></div></dl>
    <section class="vg-apply"><div><h2>Conheça a empresa</h2><p>Os links abaixo levam para fora do sistema.</p></div><div class="vg-apply-actions">
      <?php if ($ext): ?><a class="vg-btn" href="<?= url('externo', ['id' => $e['id']]) ?>"><?= icon('external', 16) ?> <?= $e['link_principal'] === 'linkedin' && $e['linkedin'] ? 'LinkedIn da empresa' : 'Site da empresa' ?></a><?php else: ?><span class="vg-hint">A empresa ainda não informou site ou LinkedIn.</span><?php endif; ?></div></section>
  </article>
  <section aria-labelledby="vg-ev"><div class="vg-list-head"><h2 id="vg-ev">Vagas abertas desta empresa</h2></div>
    <?php if (!$vagas): ?><p class="vg-empty">Nenhuma vaga aberta no momento.</p><?php else: ?><div class="vg-grid"><?php foreach ($vagas as $v) { $trilho = false; $chave = null; require __DIR__ . '/partials/card_vaga.php'; } ?></div><?php endif; ?></section>
  <p><a class="vg-back" href="<?= url('empresas') ?>"><?= icon('arrow-left', 16) ?> Voltar para empresas parceiras</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 107 — externo.php

#### Arquivo: `Views/externo.php` — CRIAR

```php
<?php require __DIR__ . '/header.php'; $ativa = 'empresas'; require __DIR__ . '/partials/nav.php'; ?>
  <div class="vg-notice"><h1>Você está saindo do sistema</h1>
    <p>Você será levado para o endereço informado pela empresa <strong><?= e($e['nome']) ?></strong>:</p>
    <p><span class="vg-code" style="word-break:break-all"><?= e($destino) ?></span></p>
    <p>O IFSP não controla o conteúdo de sites externos. Confira o endereço antes de informar qualquer dado pessoal.</p>
    <div class="vg-apply-actions" style="justify-content:center"><a class="vg-btn vg-btn-lg" href="<?= e($destino) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external', 18) ?> Continuar para <?= e(Link::dominio($destino)) ?></a>
      <a class="vg-btn vg-btn-ghost vg-btn-lg" href="<?= url('empresas') ?>">Voltar</a></div></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
```

### Passo 108 — empresa_painel.php

#### Arquivo: `Views/empresa_painel.php` — CRIAR

```php
<?php
$checks = [['Logo da empresa', !empty($emp['logo'])], ['Descrição completa', mb_strlen((string) $emp['descricao']) >= 30], ['Site ou LinkedIn', !empty($emp['site']) || !empty($emp['linkedin'])],
           ['Segmento e porte', !empty($emp['segmento']) && !empty($emp['porte'])], ['Celular de contato', !empty($emp['celular'])]];
$feitos = count(array_filter($checks, fn($c) => $c[1])); $pct = (int) round($feitos / count($checks) * 100);
?>
<?php if ($emp['status'] === 'pendente'): ?>
  <div class="ui-alert ui-alert-warn"><?= icon('clock', 22) ?><div><strong>Cadastro em análise</strong><p>A Coordenação está conferindo os dados e o termo de cooperação. Você receberá um e-mail com o resultado. Enquanto isso, complete o perfil da empresa.</p></div></div>
<?php elseif ($emp['status'] === 'reprovada'): ?>
  <div class="ui-alert ui-alert-danger"><?= icon('info', 22) ?><div><strong>Cadastro não aprovado</strong><p>Motivo: <?= nl2br(e((string) $emp['motivo'])) ?></p>
    <form method="post" action="<?= url('empresa-perfil') ?>" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="acao" value="reenviar">
      <a class="ui-btn ui-btn-sm" href="<?= url('empresa-perfil') ?>">Corrigir perfil</a> <button class="ui-btn ui-btn-sm ui-btn-primary" type="submit">Reenviar para análise</button></form></div></div>
<?php elseif ($aviso): ?>
  <div class="ui-alert ui-alert-ok"><?= icon('check', 22) ?><div><strong>Empresa aprovada</strong><p>Você já pode publicar vagas. Cada vaga passa por análise antes de ir ao ar.</p></div>
    <div class="ui-actions"><a class="ui-btn ui-btn-primary ui-btn-sm" href="<?= url('vaga-cadastro') ?>">Publicar vaga</a>
      <form method="post" action="<?= url('aviso-dispensar') ?>"><?= csrf_field() ?><button class="ui-link-btn" type="submit">Dispensar</button></form></div></div>
<?php endif; ?>
<?php if (!empty($cont['rejeitada'])): ?><div class="ui-alert ui-alert-danger"><?= icon('info', 22) ?><div><strong><?= (int) $cont['rejeitada'] ?> vaga(s) rejeitada(s)</strong><p>Veja o motivo, corrija e reenvie para análise. <a href="<?= url('empresa-vagas', ['aba' => 'rejeitada']) ?>">Ver vagas rejeitadas</a></p></div></div><?php endif; ?>
<?php foreach ($vencendo as $v): ?><div class="ui-alert ui-alert-warn"><?= icon('clock', 22) ?><div><strong><?= e($v['titulo']) ?>: <?= e(dias_txt($v['dias'])) ?></strong><p>Depois de <?= data_br($v['data_limite']) ?> a vaga sai do ar. Para estender o prazo, edite a data final (a vaga passa por nova aprovação).</p></div></div><?php endforeach; ?>

<div class="ui-metrics">
  <?= Ui::metrica('Publicadas', $cont['publicada'] ?? 0, 'check', url('empresa-vagas', ['aba' => 'publicada'])) ?>
  <?= Ui::metrica('Em análise', $cont['aguardando'] ?? 0, 'clock', url('empresa-vagas', ['aba' => 'aguardando']), 'warn') ?>
  <?= Ui::metrica('Agendadas', $cont['agendada'] ?? 0, 'clock', url('empresa-vagas', ['aba' => 'agendada']), 'info') ?>
  <?= Ui::metrica('Expiradas', $cont['expirada'] ?? 0, 'briefcase', url('empresa-vagas', ['aba' => 'expirada']), 'mute') ?>
  <?= Ui::metrica('Encerradas', $cont['encerrada'] ?? 0, 'x', url('empresa-vagas', ['aba' => 'encerrada']), 'mute') ?>
</div>

<div class="ui-grid-2" style="grid-template-columns:2fr 1fr;align-items:start">
  <section class="ui-section"><div class="ui-row-between"><h2 style="margin:0">Vagas recentes</h2><a class="ui-btn ui-btn-sm" href="<?= url('empresa-vagas') ?>">Ver todas</a></div>
    <?php if (!$recentes): echo Ui::vazio('Nenhuma vaga ainda', $emp['status'] === 'aprovada' ? 'Publique a primeira vaga da sua empresa.' : 'Você poderá publicar vagas depois que a Coordenação aprovar o cadastro.', $emp['status'] === 'aprovada' ? ['Publicar vaga', url('vaga-cadastro')] : null);
    else: ?><div class="ui-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr))"><?php foreach ($recentes as $v) { $ctx = 'empresa'; require __DIR__ . '/partials/vaga_card_app.php'; } ?></div><?php endif; ?></section>
  <aside class="ui-card"><h2>Perfil da empresa <?= Ui::badge($pct . '%', $pct === 100 ? 'ok' : 'warn') ?></h2>
    <div class="ui-progress" style="margin:0 0 12px"><i style="width:<?= $pct ?>%"></i></div>
    <ul class="ui-rules" style="font-size:14px"><?php foreach ($checks as [$t, $ok]): ?><li class="<?= $ok ? 'is-ok' : '' ?>"><?= e($t) ?></li><?php endforeach; ?></ul>
    <p class="ui-sub" style="margin:10px 0">Quanto mais completo o perfil, melhor a apresentação para os estudantes.</p>
    <a class="ui-btn ui-btn-block" href="<?= url('empresa-perfil') ?>">Editar perfil</a></aside>
</div>
```

### Passo 109 — empresa_vaga.php

#### Arquivo: `Views/empresa_vaga.php` — CRIAR

```php
<?php $sit = $v['situacao']; ?>
<div class="ui-row-between"><div class="ui-actions"><a class="ui-btn ui-btn-sm" href="<?= url('empresa-vagas') ?>"><?= icon('arrow-left', 15) ?> Minhas vagas</a> <?= Ui::situacao($sit) ?> <span class="ui-badge ui-badge-info"><?= e($v['tipo']) ?></span></div>
  <div class="ui-actions">
    <?php if ($sit === 'publicada'): ?><a class="ui-btn ui-btn-sm" href="<?= e(url_vaga((int) $v['id'])) ?>" target="_blank" rel="noopener"><?= icon('external', 15) ?> Ver no site</a><?php endif; ?>
    <?php if ($v['status'] !== 'encerrada'): ?><a class="ui-btn ui-btn-sm" href="<?= url('vaga-editar', ['id' => $v['id']]) ?>"><?= icon('edit', 15) ?> Editar</a><?php endif; ?>
    <?php if ($v['status'] === 'aprovada') echo Ui::formConfirmar(url('vaga-encerrar'), ['id' => $v['id']], 'Encerrar vaga', 'Encerrar esta vaga?', 'A vaga "' . $v['titulo'] . '" deixa de aparecer para os estudantes imediatamente. Ela continua guardada no histórico e não será excluída.', 'danger', 'ui-btn ui-btn-sm ui-btn-danger-o'); ?></div></div>
<h2 style="font-size:22px;margin-bottom:14px"><?= e($v['titulo']) ?></h2>
<?php if ($sit === 'rejeitada'): ?><div class="ui-alert ui-alert-danger"><div><strong>Vaga rejeitada</strong><p>Motivo: <?= nl2br(e((string) $v['motivo'])) ?></p><p>Edite e salve para reenviar à análise.</p></div></div>
<?php elseif ($sit === 'aguardando'): ?><div class="ui-alert ui-alert-warn"><div><strong>Em análise</strong><p>A vaga ainda não aparece para os estudantes. Você receberá um e-mail quando a Coordenação decidir.</p></div></div>
<?php elseif ($sit === 'agendada'): ?><div class="ui-alert ui-alert-info"><div><strong>Aprovada e agendada</strong><p>Entra no ar em <?= data_br($v['data_publicacao']) ?>.</p></div></div>
<?php elseif ($sit === 'expirada'): ?><div class="ui-alert ui-alert-info"><div><strong>Prazo encerrado em <?= data_br($v['data_limite']) ?></strong><p>A vaga continua no histórico. Para reabrir, edite a data final: ela passa por nova aprovação.</p></div></div>
<?php elseif ($sit === 'publicada'): ?><div class="ui-alert ui-alert-ok"><div><strong>No ar</strong><p>Visível para os estudantes até <?= data_br($v['data_limite']) ?>. Alterações exigem nova aprovação e tiram a vaga do ar até lá.</p></div></div><?php endif; ?>
<?php require __DIR__ . '/partials/vaga_detalhe.php'; ?>
<div class="ui-card" style="margin-top:16px"><h2>Histórico</h2><?php require __DIR__ . '/partials/timeline.php'; ?></div>
```

### Passo 110 — vaga_form.php

#### Arquivo: `Views/vaga_form.php` — CRIAR
Formulário de vaga em 8 etapas.

```php
<?php
$edit = (bool) $atual; $hoje = date('Y-m-d');
$c = fn(array $o) => Ui::campo($o, $old, $erros);
$etapaDe = ['tipo' => 0, 'titulo' => 0, 'area' => 0, 'modalidade' => 0, 'local' => 0, 'descricao' => 0, 'requisitos' => 1, 'bolsa' => 3, 'carga_horas_dia' => 4, 'dias' => 4, 'carga_turno' => 4,
            'data_publicacao' => 5, 'data_limite' => 5, 'link_site' => 6, 'link_linkedin' => 6, 'link_plataforma' => 6, 'link_candidatura' => 6, 'contato' => 6, 'banner' => 6];
$inicio = $erros ? min(array_map(fn($k) => $etapaDe[$k] ?? 0, array_keys($erros))) : 0;
$areaSel = $old['area'] ?? ''; $diasSel = (array) ($old['dias'] ?? []); $remTipo = ($old['remuneracao_tipo'] ?? 'valor') === 'combinar' ? 'combinar' : 'valor';
$sug = fn(array $l) => '<div class="ui-quick" data-quick aria-label="Sugestões"><span class="ui-hint" style="margin:0">Sugestões:</span>' . implode('', array_map(fn($x) => '<button type="button">' . e($x) . '</button>', $l)) . '</div>';
?>
<?php if ($edit && in_array($atual['status'], ['aprovada', 'rejeitada'], true)): ?>
  <div class="ui-alert ui-alert-warn"><?= icon('info', 22) ?><div><strong>Alterações exigem nova aprovação</strong><p><?= $atual['status'] === 'aprovada' ? 'Se você alterar algo, esta vaga sai do ar e só volta depois que a Coordenação aprovar a nova versão.' : 'Ao salvar, a vaga é reenviada para análise.' ?></p></div></div>
<?php endif; ?>
<?php if ($erros): ?><div class="ui-alert ui-alert-danger" role="alert"><div>Corrija os campos destacados e envie de novo.</div></div><?php endif; ?>
<div class="ui-card"><form class="ui-form" method="post" action="<?= $edit ? url('vaga-editar', ['id' => $atual['id']]) : url('vaga-cadastro') ?>" enctype="multipart/form-data" data-form data-wiz <?= $edit ? 'data-free' : '' ?> data-start="<?= (int) $inicio ?>" novalidate>
  <?= csrf_field() ?><span data-stepper></span>

  <fieldset class="ui-fieldset ui-step" data-step="0" data-title="Informações básicas"><legend>Informações básicas</legend><p class="ui-step-sub">Publicando como <strong><?= e($emp['nome']) ?></strong>.</p>
    <div class="ui-cols">
      <?= $c(['name' => 'tipo', 'label' => 'Tipo de oportunidade', 'type' => 'select', 'req' => true, 'opcoes' => TIPOS_VAGA, 'attrs' => ['data-rev' => 'Tipo']]) ?>
      <?= $c(['name' => 'modalidade', 'label' => 'Modalidade', 'type' => 'select', 'req' => true, 'opcoes' => MODALIDADES, 'attrs' => ['data-rev' => 'Modalidade']]) ?>
    </div>
    <?= $c(['name' => 'titulo', 'label' => 'Nome da vaga', 'req' => true, 'attrs' => ['maxlength' => 80, 'minlength' => 5, 'placeholder' => 'Ex.: Estágio em Desenvolvimento Web', 'data-rev' => 'Vaga']]) ?>
    <div class="ui-field"><label for="area">Área <span class="ui-req" aria-hidden="true">*</span></label>
      <select id="area" name="area" class="ui-select <?= isset($erros['area']) ? 'is-invalid' : '' ?>" data-area-select required data-rev="Área"><option value="">Selecione</option>
        <?php foreach ($areas as $a): ?><option <?= $areaSel === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?><option value="<?= AREA_OUTRA ?>" <?= $areaSel === AREA_OUTRA ? 'selected' : '' ?>>Outra área...</option></select>
      <div data-area-outra style="margin-top:10px" hidden><label for="area_outra">Qual é a área? <span class="ui-req">*</span></label><input id="area_outra" class="ui-input" name="area_outra" maxlength="60" placeholder="Ex.: Design Gráfico" value="<?= e($old['area_outra'] ?? '') ?>" data-rev="Nova área">
        <small class="ui-hint">Se várias vagas usarem essa área, ela vira filtro. Senão, entra em "Outras".</small></div>
      <small class="ui-err" data-err><?= e($erros['area'] ?? '') ?></small></div>
    <?= $c(['name' => 'local', 'label' => 'Local', 'req' => true, 'attrs' => ['maxlength' => 80, 'placeholder' => 'Guarulhos/SP', 'data-rev' => 'Local']]) ?>
    <?= $c(['name' => 'descricao', 'label' => 'Descrição da vaga', 'type' => 'textarea', 'rows' => 7, 'req' => true, 'hint' => 'Escreva o mais importante primeiro: as 2 ou 3 primeiras linhas viram a breve descrição do card.', 'attrs' => ['minlength' => 40, 'data-rev' => 'Descrição']]) ?>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="1" data-title="Requisitos"><legend>Requisitos</legend><p class="ui-step-sub">Digite um item e pressione Enter. Cada item vira uma etiqueta.</p>
    <div class="ui-field" data-chips="20"><label for="req-in">Requisitos obrigatórios <span class="ui-req" aria-hidden="true">*</span></label>
      <textarea id="requisitos" name="requisitos" rows="4" class="ui-textarea" data-chips-required data-rev="Requisitos obrigatórios"><?= e($old['requisitos'] ?? '') ?></textarea>
      <div class="ui-chips"><input id="req-in" type="text" maxlength="120" placeholder="Ex.: Cursando Análise e Desenvolvimento de Sistemas"></div>
      <?= $sug(['Cursando graduação ou curso técnico', 'Boa comunicação', 'Pacote Office', 'Disponibilidade de horário']) ?>
      <small class="ui-hint">Formação, conhecimentos e habilidades indispensáveis.</small><small class="ui-err" data-err><?= e($erros['requisitos'] ?? '') ?></small></div>
    <div class="ui-field" data-chips="20"><label for="des-in">Requisitos desejáveis</label>
      <textarea name="requisitos_desejaveis" rows="3" class="ui-textarea" data-rev="Requisitos desejáveis"><?= e($old['requisitos_desejaveis'] ?? '') ?></textarea>
      <div class="ui-chips"><input id="des-in" type="text" maxlength="120" placeholder="Ex.: Inglês intermediário, Git, SQL"></div>
      <?= $sug(['Inglês intermediário', 'Experiência anterior na área', 'Conhecimento em Git']) ?>
      <small class="ui-hint">Diferenciais: não eliminam o candidato.</small></div>
    <div class="ui-field" data-chips="20"><label for="atv-in">Atividades do estagiário</label>
      <textarea name="atividades" rows="3" class="ui-textarea" data-rev="Atividades"><?= e($old['atividades'] ?? '') ?></textarea>
      <div class="ui-chips"><input id="atv-in" type="text" maxlength="120" placeholder="Ex.: Apoiar o desenvolvimento de telas"></div></div>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="2" data-title="Benefícios"><legend>Benefícios</legend><p class="ui-step-sub">O que a empresa oferece além da remuneração.</p>
    <div class="ui-field" data-chips="20"><label for="ben-in">Benefícios</label>
      <textarea name="beneficios" rows="4" class="ui-textarea" data-rev="Benefícios"><?= e($old['beneficios'] ?? '') ?></textarea>
      <div class="ui-chips"><input id="ben-in" type="text" maxlength="120" placeholder="Digite e pressione Enter"></div>
      <?= $sug(['Vale-refeição', 'Vale-transporte', 'Plano de saúde', 'Recesso remunerado', 'Auxílio home office', 'Seguro de vida']) ?></div>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="3" data-title="Remuneração"><legend>Remuneração</legend><p class="ui-step-sub">Valor mensal em reais (bolsa-auxílio ou salário).</p>
    <div class="ui-field"><span class="ui-label">Como será informada?</span><div class="ui-checks" role="radiogroup">
      <label class="ui-check"><input type="radio" name="remuneracao_tipo" value="valor" data-rev="Remuneração" <?= $remTipo === 'valor' ? 'checked' : '' ?>> Informar valor mensal</label>
      <label class="ui-check"><input type="radio" name="remuneracao_tipo" value="combinar" data-rev="Remuneração" <?= $remTipo === 'combinar' ? 'checked' : '' ?>> A combinar</label></div></div>
    <div class="ui-field" data-when="remuneracao_tipo=valor"><label for="bolsa">Valor mensal <span class="ui-req" aria-hidden="true">*</span></label>
      <div class="ui-money" style="max-width:260px"><span>R$</span><input id="bolsa" name="bolsa" class="ui-input <?= isset($erros['bolsa']) ? 'is-invalid' : '' ?>" inputmode="decimal" placeholder="1500,00" data-mask="money" data-money-req value="<?= e($old['bolsa'] ?? '') ?>" data-rev="Valor mensal" data-prefix="R$ "></div>
      <small class="ui-hint">Somente números, com vírgula para os centavos.</small><small class="ui-err" data-err><?= e($erros['bolsa'] ?? '') ?></small></div>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="4" data-title="Carga horária"><legend>Carga horária</legend><p class="ui-step-sub">Quantas horas, em quais dias e em que turno.</p>
    <div class="ui-cols">
      <?= $c(['name' => 'carga_horas_dia', 'label' => 'Horas por dia', 'req' => true, 'hint' => 'De 1 a 12 horas.', 'attrs' => ['inputmode' => 'numeric', 'data-mask' => 'int', 'data-min' => 1, 'data-max' => 12, 'placeholder' => '6', 'data-rev' => 'Horas por dia', 'data-suffix' => 'h']]) ?>
      <?= $c(['name' => 'carga_turno', 'label' => 'Turno', 'type' => 'select', 'req' => true, 'opcoes' => TURNOS, 'attrs' => ['data-rev' => 'Turno']]) ?>
    </div>
    <div class="ui-field"><span class="ui-label">Dias da semana <span class="ui-req" aria-hidden="true">*</span></span>
      <div class="ui-checks" data-need-one="Marque ao menos um dia da semana." data-rev="Dias" data-rev-group><?php foreach (DIAS_SEMANA as $k => $r): ?><label class="ui-check"><input type="checkbox" name="dias[]" value="<?= $k ?>" <?= in_array($k, $diasSel, true) ? 'checked' : '' ?>> <?= e($r) ?></label><?php endforeach; ?></div>
      <small class="ui-err" data-err><?= e($erros['dias'] ?? '') ?></small></div>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="5" data-title="Período"><legend>Período da vaga</legend><p class="ui-step-sub">Enquanto estiver dentro do período, a vaga fica ativa. Depois da data final ela vai para o histórico (não é excluída).</p>
    <div class="ui-cols">
      <?= $c(['name' => 'data_publicacao', 'label' => 'Publicar a partir de', 'type' => 'date', 'req' => true, 'attrs' => ['min' => $edit && $atual['data_publicacao'] < $hoje ? $atual['data_publicacao'] : $hoje, 'data-min-date' => $edit && $atual['data_publicacao'] < $hoje ? $atual['data_publicacao'] : $hoje, 'data-rev' => 'Publicação']]) ?>
      <?= $c(['name' => 'data_limite', 'label' => 'Inscrições até', 'type' => 'date', 'req' => true, 'hint' => 'Depois dessa data a vaga sai do ar sozinha.', 'attrs' => ['min' => date('Y-m-d', strtotime('+1 day')), 'data-after' => 'data_publicacao', 'data-rev' => 'Inscrições até']]) ?>
    </div>
    <div class="ui-period" data-period hidden><span data-period-txt></span><div class="ui-period-bar"><i data-period-bar></i></div></div>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="6" data-title="Links"><legend>Links e candidatura</legend><p class="ui-step-sub">Informe onde o processo seletivo acontece. O estudante é avisado quando sai do sistema.</p>
    <div class="ui-cols">
      <?= $c(['name' => 'link_plataforma', 'label' => 'Onde é o processo seletivo?', 'type' => 'select', 'vazio' => 'Por e-mail (sem link)', 'opcoes' => Link::PLATAFORMAS, 'attrs' => ['data-rev' => 'Processo seletivo']]) ?>
      <?= $c(['name' => 'link_candidatura', 'label' => 'Link para candidatura', 'attrs' => ['data-url' => '', 'data-host-from' => 'link_plataforma', 'maxlength' => 250, 'placeholder' => 'https://empresa.gupy.io/jobs/123', 'data-rev' => 'Link de candidatura']]) ?>
    </div>
    <div class="ui-cols">
      <?= $c(['name' => 'link_site', 'label' => 'Site oficial', 'attrs' => ['data-url' => '', 'maxlength' => 200, 'placeholder' => 'https://www.empresa.com.br']]) ?>
      <?= $c(['name' => 'link_linkedin', 'label' => 'LinkedIn (se a vaga também está lá)', 'attrs' => ['data-url' => '', 'data-host' => 'linkedin.com', 'maxlength' => 200, 'placeholder' => 'https://www.linkedin.com/jobs/...']]) ?>
    </div>
    <?= $c(['name' => 'contato', 'label' => 'E-mail para candidatura', 'type' => 'email', 'hint' => 'Obrigatório se não houver link de candidatura.', 'attrs' => ['maxlength' => 120, 'data-rev' => 'E-mail']]) ?>
    <div class="ui-field"><label for="banner">Imagem de divulgação (opcional)</label><input id="banner" name="banner" type="file" accept=".jpg,.jpeg,.png" data-file data-max="<?= UPLOAD_MAX_MB ?>" data-img-only>
      <small class="ui-hint">JPG ou PNG de 900 × 400 px, até <?= UPLOAD_MAX_MB ?> MB. Sem imagem, o card mostra as iniciais da empresa.<?= $edit && $atual['imagem'] ? ' Já existe uma imagem: envie outra só se quiser trocar.' : '' ?></small><small class="ui-err" data-err><?= e($erros['banner'] ?? '') ?></small></div>
  </fieldset>

  <fieldset class="ui-fieldset ui-step" data-step="7" data-title="Revisão"><legend>Revisão</legend><p class="ui-step-sub">Confira tudo antes de enviar. <?= $edit ? 'Ao salvar, alterações em vaga aprovada voltam para análise.' : 'Depois de enviada, a vaga passa pela aprovação da Coordenação.' ?></p>
    <div class="ui-review" data-review hidden><h3>Resumo da vaga</h3><dl></dl></div>
  </fieldset>

  <div class="ui-wiz-nav">
    <button type="button" class="ui-btn ui-btn-lg" data-prev hidden><?= icon('arrow-left', 16) ?> Voltar</button>
    <button type="button" class="ui-btn ui-btn-primary ui-btn-lg" data-next hidden>Continuar <?= icon('arrow-right', 16) ?></button>
    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" data-submit><?= $edit ? 'Salvar alterações' : 'Enviar para análise' ?></button>
    <span class="ui-spacer"></span><a href="<?= url('empresa-vagas') ?>">Cancelar</a>
  </div>
</form></div>
```

### Passo 111 — app.js

#### Arquivo: `public/js/app.js` — parte 3 — ANEXAR-ANTES-DO-FECHAMENTO
**Onde:** colar ANTES da última linha `})();`. Parte 3: listas por etiquetas (chips), campos condicionais, resumo do período.

```javascript
  /* ---------- listas em "chips" (requisitos, benefícios) ---------- */
  $$('[data-chips]').forEach(function (w) {
    var ta = $('textarea', w), lista = $('.ui-chips', w), inp = $('input', lista), max = +w.dataset.chips || 20, itens = [];
    var le = function () { itens = ta.value.split(/\r?\n/).map(function (s) { return s.trim(); }).filter(Boolean); };
    var desenha = function () {
      $$('.ui-chip', lista).forEach(function (c) { c.remove(); });
      itens.forEach(function (t, i) {
        var s = document.createElement('span'); s.className = 'ui-chip'; s.appendChild(document.createTextNode(t));
        var b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Remover ' + t); b.innerHTML = '&times;';
        b.addEventListener('click', function () { itens.splice(i, 1); sync(); inp.focus(); }); s.appendChild(b); lista.insertBefore(s, inp);
      });
    };
    var sync = function () { ta.value = itens.join('\n'); desenha(); ta.dispatchEvent(new Event('change', { bubbles: true })); };
    var add = function (t) {
      t = t.replace(/[,;]+$/, '').trim().slice(0, 120); if (!t || itens.length >= max) return;
      if (itens.some(function (x) { return x.toLowerCase() === t.toLowerCase(); })) return; itens.push(t); sync();
    };
    w.classList.add('is-enhanced'); le(); desenha();
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(inp.value); inp.value = ''; }
      else if (e.key === 'Backspace' && !inp.value && itens.length) { itens.pop(); sync(); }
    });
    inp.addEventListener('blur', function () { if (inp.value.trim()) { add(inp.value); inp.value = ''; } });
    inp.addEventListener('paste', function (e) { var t = (e.clipboardData || window.clipboardData).getData('text'); if (/[\n,;]/.test(t)) { e.preventDefault(); t.split(/[\n,;]+/).forEach(add); } });
    $$('[data-quick] button', w).forEach(function (b) { b.addEventListener('click', function () { add(b.textContent); }); });
    lista.addEventListener('click', function (e) { if (e.target === lista) inp.focus(); });
  });

  /* ---------- campos condicionais e utilitários de formulário ---------- */
  $$('[data-when]').forEach(function (el) {   // data-when="remuneracao_tipo=valor"
    var p = el.dataset.when.split('='), form = el.closest('form');
    var f = function () { var r = $('[name=' + p[0] + ']:checked', form) || $('[name=' + p[0] + ']', form); el.hidden = !r || r.value !== p[1]; };
    $$('[name=' + p[0] + ']', form).forEach(function (i) { i.addEventListener('change', f); }); f();
  });
  var sel = $('[data-area-select]');
  if (sel) { var outra = $('[data-area-outra]'); var al = function () { outra.hidden = sel.value !== '__outra__'; }; sel.addEventListener('change', al); al(); }
  $$('[data-pwd-rules]').forEach(function (ul) {
    var i = $('[name=senha]', ul.closest('form')), r = $$('li', ul);
    var f = function () { var v = i.value, ok = [v.length >= 8, /[A-Za-z]/.test(v), /\d/.test(v)]; r.forEach(function (li, k) { li.classList.toggle('is-ok', ok[k]); }); };
    i.addEventListener('input', f); f();
  });
  $$('[data-logo-pick]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var f = inp.files[0], prev = $('[data-logo-prev]'); if (!f || !prev || f.type.indexOf('image/') !== 0 || f.size > 2 * 1048576) return;
      var img = document.createElement('img'); img.className = 'ui-logo'; img.alt = 'Pré-visualização do logo'; img.src = URL.createObjectURL(f); img.setAttribute('data-logo-prev', ''); img.width = img.height = 64; prev.replaceWith(img);
    });
  });
  var ini = $('[name=data_publicacao]'), fim = $('[name=data_limite]'), resumoP = $('[data-period]');
  function periodo() {
    if (!ini || !fim || !resumoP) return; var a = ini.value, b = fim.value;
    if (!a || !b || b <= a) { resumoP.hidden = true; return; }
    var d = Math.round((new Date(b) - new Date(a)) / 86400000), br = function (s) { return s.split('-').reverse().join('/'); };
    resumoP.hidden = false; $('[data-period-txt]', resumoP).textContent = 'Fica no ar por ' + d + (d === 1 ? ' dia' : ' dias') + ', de ' + br(a) + ' até ' + br(b) + '.';
    $('[data-period-bar]', resumoP).style.width = Math.min(100, d / 90 * 100) + '%';
  }
  if (ini && fim) { ini.addEventListener('input', periodo); fim.addEventListener('input', periodo); periodo(); }
```

### Passo 112 — Verificar a branch

Com o servidor rodando (`php -S localhost:8000 router.php`):

| Endereço | Deve responder |
|---|---|
| `/` | 200 |
| `/?p=empresas` | 200 |
| `/?p=encerradas` | 200 |
| `/?p=vaga&id=999` | 404 |

### Passo 113 — Fechar a branch

```bash
git add .
git commit -m "feat(vagas): home com carrosséis, detalhe, empresas parceiras, formulário em 8 etapas e telas de vagas da empresa"
git push -u origin feature/frontend-jobs
```
Abra o Pull Request `feature/frontend-jobs` → `develop` e peça revisão a **Pedro Henri**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 13: `feature/tv-mode` — dono: Pedro Miguel

Modo TV com QR Code individual por vaga (biblioteca local, sem internet).

**Revisor do PR:** Arthur. **Depende de:** `feature/frontend-jobs`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `110.02` | QR Code por vaga no modo TV (biblioteca local, contraste, tamanho pequeno) | 3 | 6 |
| `110.04` | Testes do modo TV e dos QR Codes (leitura real) | 2 | 7 |

```bash
git checkout develop && git pull
git checkout -b feature/tv-mode
```

### Passo 114 — Biblioteca de QR Code (local)

Baixe a biblioteca MIT e copie para o projeto (o modo TV funciona sem internet):

```bash
mkdir -p /tmp/qr && cd /tmp/qr && npm pack qrcode-generator@1.4.4 && tar -xzf qrcode-generator-1.4.4.tgz && cd -
cp /tmp/qr/package/qrcode.js public/js/vendor/qrcode.js
echo 'qrcode-generator 1.4.4 - Kazuhiko Arase - MIT License' > public/js/vendor/LICENSE-qrcode.txt
```

### Passo 115 — tv.php

#### Arquivo: `Views/tv.php` — CRIAR

```php
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vagas de Estágio - IFSP Guarulhos (Modo TV)</title>
<link rel="stylesheet" href="<?= BASE_URL ?>public/css/vagas.css">
</head>
<body class="vg-tv" data-tv>
<?php if (!$vagas): ?>
  <div class="vg-tv-empty"><h1>Vagas de Estágio</h1><p>Nenhuma vaga aberta no momento.</p></div>
<?php else: foreach ($vagas as $i => $v): [$pt] = selo_prazo($v); ?>
  <section class="vg-tv-slide <?= $i === 0 ? 'is-active' : '' ?>">
    <div class="vg-tv-media"><?= vaga_imagem($v) ?></div>
    <div class="vg-tv-info">
      <p class="vg-tv-tag"><?= e($v['tipo']) ?> · IFSP Guarulhos</p>
      <h1><?= e($v['titulo']) ?></h1>
      <h2><?= e($v['empresa']['nome']) ?></h2>
      <p class="vg-tv-text"><?= e($v["resumo"]) ?></p>
      <ul>
        <li><?= e($v['modalidade']) ?> · <?= e($v['local']) ?></li>
        <li><?= e($v['tipo']) ?> · <?= e($v['bolsa_txt']) ?><?= $v['carga_txt'] ? ' · ' . e($v['carga_txt']) : '' ?></li>
        <li>Inscrições até <?= data_br($v['data_limite']) ?> (<?= e($pt) ?>)</li>
      </ul>
      <div class="vg-tv-foot"><p class="vg-tv-code">Código <strong><?= e($v['codigo']) ?></strong><br>Aponte a câmera para abrir a vaga</p>
        <div class="vg-tv-qr"><div data-qr="<?= e(url_vaga_abs((int) $v['id'])) ?>" class="vg-tv-qrimg"></div><small><?= e(preg_replace('#^https?://#', '', url_vaga_abs((int) $v['id']))) ?></small></div></div>
    </div>
  </section>
<?php endforeach; endif; ?>
<div class="vg-tv-bar"><span data-tv-count></span><span>Tecla F: tela cheia</span></div>
<script src="<?= BASE_URL ?>public/js/vendor/qrcode.js"></script>
<script src="<?= BASE_URL ?>public/js/vagas.js"></script>
</body>
</html>
```

### Passo 116 — vagas.css

#### Arquivo: `public/css/vagas.css` — parte 2 — ANEXAR-FIM
**Onde:** colar no FINAL do arquivo. Parte 2: estilos do Modo TV e do QR Code.

```css
/* ===== Modo TV (tela cheia, sem header/footer do portal) ===== */
.vg-tv{margin:0;background:#0d1f14;color:#fff;font-family:Arial,Helvetica,sans-serif;overflow:hidden;height:100vh}
.vg-tv *{box-sizing:border-box}
.vg-tv-slide{position:absolute;inset:0;display:grid;grid-template-columns:1.25fr 1fr;opacity:0;visibility:hidden;transition:opacity .8s}
.vg-tv-slide.is-active{opacity:1;visibility:visible}
.vg-tv-media{display:flex;align-items:center;padding:4vh 2vw 4vh 3vw}
.vg-tv-media img,.vg-tv-media .vg-fallback{width:100%;aspect-ratio:900/400;object-fit:cover;border-radius:18px;box-shadow:0 10px 40px rgba(0,0,0,.5)}
.vg-tv-info{padding:6vh 4vw 6vh 2vw;display:flex;flex-direction:column;justify-content:center;gap:1.6vh}
.vg-tv-tag{margin:0;color:#7fd28c;text-transform:uppercase;letter-spacing:.12em;font-size:1.5vw;font-weight:700}
.vg-tv-info h1{font-size:3.6vw;line-height:1.1;margin:0}.vg-tv-info h2{font-size:2.2vw;color:#7fd28c;margin:0;font-weight:700}
.vg-tv-text{font-size:1.7vw;line-height:1.35;margin:0;color:#dfe9e1}
.vg-tv-info ul{list-style:none;padding:0;margin:0;font-size:1.6vw;line-height:1.7}
.vg-tv-code{font-size:1.5vw;color:#f5c26b;margin:0}
.vg-tv-bar{position:fixed;left:0;right:0;bottom:0;display:flex;justify-content:space-between;padding:1vh 2vw;font-size:1.1vw;color:#9ab0a0;background:rgba(0,0,0,.35)}
.vg-tv-empty{display:grid;place-items:center;height:100vh;text-align:center;font-size:2.5vw}
@media (max-width:800px){.vg-tv{overflow:auto;height:auto}.vg-tv-slide{position:static;display:none;grid-template-columns:1fr}.vg-tv-slide.is-active{display:grid}.vg-tv-info h1{font-size:26px}.vg-tv-info h2,.vg-tv-info ul,.vg-tv-text,.vg-tv-tag,.vg-tv-code{font-size:16px}}

/* banner sem imagem dentro de cards: desce o conteúdo para não ficar sob os selos */
.vg-card-media .vg-fallback{padding-top:34px;justify-content:center}
.vg-card-media .vg-fallback span{font-size:30px}.vg-card-media .vg-fallback small{font-size:12px}
.vg-detail-banner .vg-fallback{padding-top:30px}

/* ===== Modo TV: QR Code ===== */
.vg-tv-foot{display:flex;align-items:flex-end;justify-content:space-between;gap:2vw;margin-top:1vh}
.vg-tv-qr{flex:none;width:11vw;min-width:96px;max-width:190px;text-align:center}
.vg-tv-qrimg{background:#fff;border-radius:10px;padding:.5vw;line-height:0}.vg-tv-qrimg svg{width:100%;height:auto;display:block}
.vg-tv-qr small{display:block;margin-top:.5vh;color:#cfe0d3;font-size:.8vw;word-break:break-all;line-height:1.2}
@media (max-width:800px){.vg-tv-foot{flex-direction:column;align-items:flex-start}.vg-tv-qr{width:140px}.vg-tv-qr small{font-size:11px}}
```

### Passo 117 — vagas.js

#### Arquivo: `public/js/vagas.js` — parte 2 — ANEXAR-ANTES-DO-FECHAMENTO
**Onde:** colar ANTES da última linha `})();`. Parte 2: renderização do QR Code e rotação dos slides.

```javascript
  /* ---------- QR Code por vaga (Modo TV): fundo branco, quiet zone e contraste máximo ---------- */
  $$('[data-qr]').forEach(function (el) {
    try {
      var q = window.qrcode(0, 'M'); q.addData(el.dataset.qr); q.make();
      el.innerHTML = q.createSvgTag({ cellSize: 4, margin: 3, scalable: true });
      var s = el.firstElementChild; s.setAttribute('role', 'img'); s.setAttribute('aria-label', 'QR Code que abre esta vaga no site'); s.removeAttribute('width'); s.removeAttribute('height');
    } catch (e) { el.hidden = true; }
  });

  /* ---------- Modo TV ---------- */
  var tv = $('[data-tv]');
  if (tv) {
    var telas = $$('.vg-tv-slide'), n = 0, cont = $('[data-tv-count]');
    var mostra = function (k) {
      n = (k + telas.length) % telas.length;
      telas.forEach(function (s, j) { s.classList.toggle('is-active', j === n); });
      cont.textContent = 'Vaga ' + (n + 1) + ' de ' + telas.length;
    };
    if (telas.length) { mostra(0); if (telas.length > 1) setInterval(function () { mostra(n + 1); }, 9000); }
    document.addEventListener('keydown', function (e) {
      if (e.key === 'f' || e.key === 'F') { if (document.fullscreenElement) document.exitFullscreen(); else document.documentElement.requestFullscreen(); }
      if (e.key === 'ArrowRight') mostra(n + 1); if (e.key === 'ArrowLeft') mostra(n - 1);
    });
    setTimeout(function () { location.reload(); }, 30 * 60 * 1000); // recarrega a cada 30 min p/ refletir vagas novas/vencidas
  }
```

### Passo 118 — Verificar a branch

Com o servidor rodando (`php -S localhost:8000 router.php`):

| Endereço | Deve responder |
|---|---|
| `/?p=tv` | 200 |

**Verificação manual:** Abra `/?p=tv`: cada slide mostra um QR pequeno com a URL da própria vaga. Leia com a câmera do celular.

### Passo 119 — Fechar a branch

```bash
git add .
git commit -m "feat(tv): modo TV com QR Code por vaga"
git push -u origin feature/tv-mode
```
Abra o Pull Request `feature/tv-mode` → `develop` e peça revisão a **Arthur**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 🌿 Branch 14: `feature/qa` — dono: Pedro Miguel

Testes automatizados de fluxo, segurança, QR e migrações.

**Revisor do PR:** José Roberto. **Depende de:** `feature/tv-mode`, `feature/access-control`.

**Tarefas da planilha cobertas por esta branch:**

| Tarefa | Descrição | Duração (dias de 2 h) | Sprint proposta |
|---|---|---|---|
| `105.24` | Teste de migrations (banco antigo com dados e rollback) | 1 | 2 |
| `105.23` | Testes de segurança (autorização, IDOR, CSRF, SQLi, XSS, sessão e força bruta) | 3 | 3 |
| `105.25` | Revisão de segurança (upload, URLs externas, senhas, CSV e cabeçalhos) | 2 | 3 |
| `111.06` | Testes da máquina de estados (transições inválidas, agendada, expirada, reaprovação) | 2 | 7 |
| `105.22` | Testes automatizados de fluxo (empresa, administrador e estudante) | 5 | 8 |
| `105.27` | Homologação com o demandante (cadastro, vagas e moderação) | 2 | 9 |

```bash
git checkout develop && git pull
git checkout -b feature/qa
```

### Passo 120 — fluxo.php

#### Arquivo: `tests/fluxo.php` — CRIAR

```php
<?php
/**
 * Suíte de ponta a ponta (HTTP real) + unidade. Use tests/run.sh (sobe banco de teste e servidor).
 *   php tests/fluxo.php http://127.0.0.1:8123 [http://127.0.0.1:8124 servidor_com_sessao_curta]
 */
$base = rtrim($argv[1] ?? 'http://127.0.0.1:8123', '/'); $baseCurta = isset($argv[2]) ? rtrim($argv[2], '/') : null;
define('TESTING', true);
require_once __DIR__ . '/../config.php';
$falhas = 0; $ok = 0;
function t(string $nome, bool $cond, string $extra = ''): void { global $falhas, $ok; if ($cond) { $ok++; echo "  ok    $nome\n"; } else { $falhas++; echo "  FALHA $nome $extra\n"; } }
function secao(string $s): void { echo "== $s ==\n"; }
class Cli {
    public $jar; public $base;
    function __construct($base) { $this->base = $base; $this->jar = tempnam(sys_get_temp_dir(), 'cj'); }
    function req(string $url, $post = null, bool $follow = true): array {
        $ch = curl_init($this->base . $url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar, CURLOPT_FOLLOWLOCATION => $follow, CURLOPT_HEADER => false, CURLOPT_USERAGENT => 'teste-fluxo']);
        if ($post !== null) {
            if (is_array($post) && !array_filter($post, fn($v) => $v instanceof CURLFile)) $post = http_build_query($post);
            curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $body = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $loc = curl_getinfo($ch, CURLINFO_REDIRECT_URL); $url2 = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); curl_close($ch);
        return [$code, (string) $body, $loc, $url2];
    }
    function csrf(string $url = '/?p=empresa-login'): string { [, $b] = $this->req($url); preg_match('/name="_csrf" value="([a-f0-9]+)"/', $b, $m); return $m[1] ?? ''; }
    function post(string $url, array $d, string $paginaToken = '/?p=empresa-login', bool $follow = true): array { return $this->req($url, ['_csrf' => $this->csrf($paginaToken)] + $d, $follow); }
}
function db() { return Database::pdo(); }
function q(string $sql, array $p = []) { return Database::val($sql, $p); }
function loginAdmin(Cli $c, string $e = 'admin@ifsp.local', string $s = 'Admin@2026!') { return $c->post('/?p=admin-login', ['email' => $e, 'senha' => $s], '/?p=admin-login'); }
function loginEmpresa(Cli $c, string $e, string $s) { return $c->post('/?p=empresa-login', ['email' => $e, 'senha' => $s]); }
function acaoAdmin(Cli $a, string $tipo, string $acao, int $id, string $motivo = '') { return $a->post('/?p=admin-acao', ['tipo' => $tipo, 'acao' => $acao, 'id' => $id, 'motivo' => $motivo, 'ret' => 'admin'], '/?p=admin'); }

secao('Unidade: CNPJ, links, estados, helpers');
t('CNPJ numérico válido', Cnpj::valido('11.222.333/0001-81'));
t('CNPJ alfanumérico oficial 12.ABC.345/01DE-35', Cnpj::valido('12.ABC.345/01DE-35'));
t('CNPJ dígito errado / letra no DV / repetido', !Cnpj::valido('12.ABC.345/01DE-36') && !Cnpj::valido('12.ABC.345/01DE-3A') && !Cnpj::valido('00.000.000/0000-00'));
t('Link válido https', Link::valido('https://empresa.com.br/vagas?id=1'));
t('Link bloqueia javascript:, ftp, credenciais e espaços', !Link::valido('javascript:alert(1)') && !Link::valido('ftp://x.com') && !Link::valido('https://user:pw@x.com') && !Link::valido('https://a b.com'));
t('Link por plataforma: gupy e linkedin', Link::valido('https://x.gupy.io/jobs/1', 'gupy') && !Link::valido('https://evil.com/gupy.io', 'gupy') && Link::valido('https://www.linkedin.com/company/x', 'linkedin') && !Link::valido('https://notlinkedin.com/x', 'linkedin'));
t('Link normaliza domínio sem esquema', Link::normalizar('empresa.com.br') === 'https://empresa.com.br' && Link::normalizar('') === null);
t('Máquina de estados: vaga', Estado::vagaPode('aguardando_aprovacao', 'aprovada') && Estado::vagaPode('aprovada', 'aguardando_aprovacao') && Estado::vagaPode('aprovada', 'encerrada') && !Estado::vagaPode('encerrada', 'aprovada') && !Estado::vagaPode('rejeitada', 'aprovada') && !Estado::vagaPode('aguardando_aprovacao', 'encerrada'));
t('Máquina de estados: empresa', Estado::empresaPode('pendente', 'aprovada') && Estado::empresaPode('aprovada', 'bloqueada') && Estado::empresaPode('bloqueada', 'aprovada') && !Estado::empresaPode('pendente', 'bloqueada') && Estado::empresaPode('reprovada', 'pendente'));
$hj = date('Y-m-d'); $v0 = ['status' => 'aprovada', 'data_publicacao' => $hj, 'data_limite' => $hj];
t('Situação por datas: agendada / publicada / expirada', Estado::situacaoVaga(['data_publicacao' => '2999-01-01'] + $v0) === 'agendada' && Estado::situacaoVaga($v0) === 'publicada' && Estado::situacaoVaga(['data_limite' => '2000-01-01', 'data_publicacao' => '1999-01-01'] + $v0) === 'expirada');
t('Situação: demais estados persistidos', Estado::situacaoVaga(['status' => 'aguardando_aprovacao'] + $v0) === 'aguardando' && Estado::situacaoVaga(['status' => 'rejeitada'] + $v0) === 'rejeitada' && Estado::situacaoVaga(['status' => 'encerrada'] + $v0) === 'encerrada');
t('Carga horária estruturada em texto', Vaga::cargaTexto(6, 'seg,ter,qua,qui,sex', 'manha') === '6h/dia · Seg a Sex · Manhã' && Vaga::cargaTexto(4, 'seg,qua', 'tarde') === '4h/dia · Seg, Qua · Tarde');
t('Dinheiro, telefone e breve descrição', parse_dinheiro('1.500,50') === 1500.5 && parse_dinheiro('abc') === null && fone_fmt('11900001234') === '11 90000-1234' && resumo_da_descricao("Um.\n\nDois.\nTrês.\nQuatro.") === 'Um. Dois. Três.');
t('Senha forte', EmpresaForm::senhaForte('abc12345') && !EmpresaForm::senhaForte('12345678') && !EmpresaForm::senhaForte('abcdefgh') && !EmpresaForm::senhaForte('a1'));

$visitante = new Cli($base); $A = new Cli($base); $B = new Cli($base); $adm = new Cli($base);

secao('Estudante: páginas públicas');
foreach (['/', '/?p=empresas', '/?p=encerradas', '/?p=tv'] as $u) t("público abre $u", $visitante->req($u)[0] === 200);
[, $h] = $visitante->req('/');
t('menu público sem links de admin e com "Empresas parceiras"', !str_contains($h, 'p=admin') && str_contains($h, 'Empresas parceiras') && str_contains($h, 'Sou empresa'));
[$c] = $visitante->req('/storage/database.sqlite'); t('banco não é baixável', in_array($c, [403, 404], true));
[$c] = $visitante->req('/.env'); t('.env não é baixável', in_array($c, [403, 404], true));
[$c] = $visitante->req('/docs/GUIA.md'); t('docs/ não é servida', in_array($c, [403, 404], true));

secao('Segurança: rotas protegidas sem login');
foreach (['admin', 'admin-vagas', 'admin-empresas', 'admin-aprovacoes', 'admin-usuarios', 'admin-acesso', 'admin-config', 'admin-relatorios', 'admin-exportar&tipo=vagas', 'admin-termo&id=1'] as $r) { [$c] = $visitante->req("/?p=$r", null, false); t("anônimo bloqueado em ?p=$r", $c === 302); }
foreach (['painel', 'empresa-vagas', 'empresa-perfil', 'vaga-cadastro', 'vaga-editar&id=1', 'empresa-vaga&id=1'] as $r) { [$c] = $visitante->req("/?p=$r", null, false); t("anônimo bloqueado em ?p=$r", $c === 302); }
[$c] = $visitante->req('/?p=admin-acao', ['tipo' => 'vaga', 'acao' => 'aprovar', 'id' => 1], false); t('POST admin-acao sem login bloqueado', $c === 302);
[$c] = $visitante->req('/?p=empresa-login', ['email' => 'x@x.com', 'senha' => 'y']); t('POST sem CSRF rejeitado (419)', $c === 419);
[, $lg] = $visitante->req('/?p=empresa-login'); t('login da empresa sem header/footer do IFSP', !str_contains($lg, 'gru.ifsp.edu.br') && !str_contains($lg, 'Início da navegação de rodapé') && str_contains($lg, 'Ambiente da empresa'));
[, $lg] = $visitante->req('/?p=admin-login'); t('login do admin sem header/footer do IFSP', !str_contains($lg, 'gru.ifsp.edu.br') && str_contains($lg, 'Ambiente administrativo'));
[, $lg] = $visitante->req('/?p=empresa-cadastro'); t('cadastro em 5 etapas, sem header/footer do IFSP', substr_count($lg, 'class="ui-fieldset ui-step"') === 5 && !str_contains($lg, 'gru.ifsp.edu.br') && str_contains($lg, 'data-viacep'));

secao('Empresa: cadastro em etapas');
$pdf = tempnam(sys_get_temp_dir(), 'pdf'); file_put_contents($pdf, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
$exe = tempnam(sys_get_temp_dir(), 'x'); file_put_contents($exe, "<?php echo 1;");
$cnpjA = Cnpj::completar('K7P2M9Q1R0AB'); $cnpjB = '11222333000181';
$dadosA = ['cnpj' => Cnpj::formatar($cnpjA), 'razao_social' => 'Verde Ágil Tecnologia Ltda', 'nome' => 'Verde Ágil', 'segmento' => 'Tecnologia da Informação', 'porte' => 'Pequena empresa',
    'descricao' => 'Empresa de tecnologia que desenvolve sistemas para o varejo e recebe estagiários.', 'site' => 'verdeagil.example', 'linkedin' => 'https://www.linkedin.com/company/verdeagil', 'link_principal' => 'linkedin',
    'cep' => '07011-000', 'logradouro' => 'Rua das Flores', 'numero' => '100', 'complemento' => 'Sala 2', 'bairro' => 'Centro', 'cidade' => 'Guarulhos', 'uf' => 'SP',
    'responsavel' => 'Marina Souza', 'cargo' => 'Analista de RH', 'telefone' => '11 98877-6655', 'celular' => '11 98877-6655', 'email_contato' => 'contato@verdeagil.example',
    'email' => 'rh@verdeagil.example', 'senha' => 'senha-forte-1', 'senha2' => 'senha-forte-1', 'aceite_termos' => '1', 'aceite_privacidade' => '1'];
$envia = function (Cli $c, array $d, $arq = null) use ($pdf) { $tok = $c->csrf('/?p=empresa-cadastro'); return $c->req('/?p=empresa-cadastro', ['_csrf' => $tok] + $d + ['termo' => new CURLFile($arq ?? $pdf, 'application/pdf', 't.pdf')]); };
[, $r] = $envia($A, array_merge($dadosA, ['cnpj' => '12.ABC.345/01DE-36'])); t('CNPJ inválido recusado', str_contains($r, 'CNPJ inválido'));
[, $r] = $envia($A, array_merge($dadosA, ['senha' => '12345678', 'senha2' => '12345678'])); t('senha só com números recusada', str_contains($r, 'letras e números'));
[, $r] = $envia($A, array_merge($dadosA, ['aceite_termos' => ''])); t('sem aceite dos termos recusado', str_contains($r, 'aceitar os termos'));
[, $r] = $envia($A, array_merge($dadosA, ['cep' => '0701'])); t('CEP incompleto recusado', str_contains($r, 'CEP inválido'));
[, $r] = $envia($A, array_merge($dadosA, ['uf' => 'XX'])); t('UF inválida recusada', str_contains($r, 'Escolha o estado'));
[, $r] = $envia($A, array_merge($dadosA, ['linkedin' => 'https://evil.example/x'])); t('LinkedIn de outro domínio recusado', str_contains($r, 'LinkedIn da empresa'));
[, $r] = $envia($A, array_merge($dadosA, ['descricao' => 'curta'])); t('descrição curta recusada', str_contains($r, '30 a 600'));
[, $r] = $envia($A, $dadosA, $exe); t('arquivo PHP disfarçado de PDF recusado pelo conteúdo', str_contains($r, 'Formato não permitido'));
t('nada foi gravado com dados inválidos', (int) q('SELECT COUNT(*) FROM empresas') === 0);
[, $r, , $u] = $envia($A, $dadosA);
t('cadastro concluído mostra etapa de conclusão', str_contains($r, 'Cadastro enviado') && str_contains($u, 'empresa-concluido'));
$idA = (int) q("SELECT id FROM empresas WHERE cnpj = :c", ['c' => $cnpjA]);
$rowA = Database::one('SELECT * FROM empresas WHERE id = :i', ['i' => $idA]);
t('empresa gravada como pendente com campos novos', $rowA['status'] === 'pendente' && $rowA['razao_social'] === 'Verde Ágil Tecnologia Ltda' && $rowA['segmento'] === 'Tecnologia da Informação' && $rowA['porte'] === 'Pequena empresa' && $rowA['cargo'] === 'Analista de RH');
t('endereço estruturado e composto', $rowA['cep'] === '07011000' && $rowA['uf'] === 'SP' && $rowA['endereco'] === 'Rua das Flores, 100, Sala 2 - Centro, Guarulhos/SP - CEP 07011-000');
t('site normalizado e link principal salvo', $rowA['site'] === 'https://verdeagil.example' && $rowA['link_principal'] === 'linkedin');
t('termos aceitos registrados, senha com hash, telefone só dígitos', $rowA['termos_aceitos_em'] !== null && str_starts_with($rowA['senha_hash'], '$2y$') && $rowA['telefone'] === '11988776655');
t('histórico registra o cadastro', (int) q("SELECT COUNT(*) FROM historico WHERE entidade='empresa' AND entidade_id = :i AND acao='cadastro'", ['i' => $idA]) === 1);
$logs = db()->query('SELECT destinatario, assunto FROM emails_log')->fetchAll(PDO::FETCH_ASSOC);
t('e-mail ao admin e confirmação à empresa', (bool) array_filter($logs, fn($l) => $l['destinatario'] === 'admin@ifsp.local' && str_contains($l['assunto'], 'Verde Ágil')) && (bool) array_filter($logs, fn($l) => $l['destinatario'] === 'rh@verdeagil.example'));
[, $pp] = $A->req('/?p=painel'); t('painel mostra "Cadastro em análise" (layout independente)', str_contains($pp, 'Cadastro em análise') && str_contains($pp, 'Sair') && !str_contains($pp, 'gru.ifsp.edu.br'));
[$c, , $l] = $A->req('/?p=vaga-cadastro', null, false); t('empresa pendente não publica vaga', $c === 302 && str_contains($l, 'p=painel'));
[, $r] = $envia(new Cli($base), $dadosA); t('CNPJ duplicado recusado', str_contains($r, 'já está cadastrado'));

secao('Administração: login, aprovação de empresa');
[, $r] = loginAdmin($adm, 'admin@ifsp.local', 'errada'); t('senha errada não entra', str_contains($r, 'incorretos'));
[, $r] = loginAdmin($adm); t('admin entra no dashboard', str_contains($r, 'Dashboard') && str_contains($r, 'Aguardando sua análise'));
t('empresa pendente aparece em Aprovações com termo', str_contains($adm->req('/?p=admin-aprovacoes')[1], 'Verde Ágil') && $adm->req('/?p=admin-termo&id=' . $idA)[0] === 200);
[, $r] = acaoAdmin($adm, 'empresa', 'reprovar', $idA, ''); t('reprovar exige motivo (backend)', str_contains($r, 'Informe o motivo') && q('SELECT status FROM empresas WHERE id = :i', ['i' => $idA]) === 'pendente');
acaoAdmin($adm, 'empresa', 'aprovar', $idA);
t('empresa aprovada + histórico', q('SELECT status FROM empresas WHERE id = :i', ['i' => $idA]) === 'aprovada' && (int) q("SELECT COUNT(*) FROM historico WHERE entidade='empresa' AND entidade_id = :i AND acao='aprovada'", ['i' => $idA]) === 1);
t('e-mail de aprovação à empresa', (bool) q("SELECT 1 FROM emails_log WHERE destinatario='rh@verdeagil.example' AND assunto LIKE 'Cadastro aprovado%'"));
[, $r] = acaoAdmin($adm, 'empresa', 'aprovar', $idA); t('estado inválido: aprovar de novo é recusado', str_contains($r, 'não é permitida'));
[, $pp] = $A->req('/?p=painel'); t('aviso "Empresa aprovada" aparece', str_contains($pp, 'Empresa aprovada') && str_contains($pp, 'Publicar vaga'));
$A->post('/?p=aviso-dispensar', [], '/?p=painel'); [, $pp] = $A->req('/?p=painel'); t('aviso some ao dispensar (controlado pelo backend)', !str_contains($pp, 'Empresa aprovada</strong>'));

secao('Empresa: publicar vaga em etapas e fluxo de aprovação');
$hoje = date('Y-m-d'); $lim = date('Y-m-d', strtotime('+20 days'));
$vagaPost = ['tipo' => 'Trainee', 'titulo' => 'Trainee de Moda Sustentável', 'area' => AREA_OUTRA, 'area_outra' => 'moda sustentável', 'modalidade' => 'Híbrido', 'local' => 'Guarulhos/SP',
    'descricao' => "Programa de trainee em moda com foco em economia circular.\nVocê vai atuar com curadoria e produção.\nTerceira linha importante.\nQuarta linha que não deve aparecer no card.",
    'requisitos' => "Cursando graduação\nBoa comunicação", 'requisitos_desejaveis' => 'Inglês', 'beneficios' => "Vale-refeição\nPlano de saúde", 'atividades' => 'Pesquisar tendências',
    'remuneracao_tipo' => 'valor', 'bolsa' => '9000,00', 'carga_horas_dia' => '8', 'dias' => ['seg', 'ter', 'qua', 'qui', 'sex'], 'carga_turno' => 'integral',
    'data_publicacao' => $hoje, 'data_limite' => $lim, 'link_plataforma' => 'gupy', 'link_candidatura' => 'https://verdeagil.gupy.io/jobs/1', 'link_site' => '', 'link_linkedin' => '', 'contato' => ''];
$enviaVaga = fn(Cli $c, array $d, string $url = '/?p=vaga-cadastro') => $c->req($url, ['_csrf' => $c->csrf('/?p=painel')] + $d);
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['requisitos' => ''])); t('requisito obrigatório', str_contains($r, 'ao menos um requisito'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['remuneracao_tipo' => 'valor', 'bolsa' => 'abc'])); t('remuneração só numérica', str_contains($r, 'valor mensal válido'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['dias' => []])); t('ao menos um dia da semana', str_contains($r, 'ao menos um dia'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['carga_horas_dia' => '20'])); t('horas por dia de 1 a 12', str_contains($r, '1 a 12'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['data_limite' => $hoje])); t('data final depois da inicial', str_contains($r, 'depois da inicial'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['link_candidatura' => 'https://evil.example/x'])); t('link Gupy precisa ser gupy.io', str_contains($r, 'gupy.io'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['link_candidatura' => 'javascript:alert(1)', 'link_plataforma' => 'outro'])); t('link javascript: recusado', str_contains($r, 'inválido'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['link_candidatura' => '', 'link_plataforma' => '', 'contato' => ''])); t('exige e-mail ou link de candidatura', str_contains($r, 'e-mail ou um link'));
[, $r] = $enviaVaga($A, array_merge($vagaPost, ['area_outra' => ''])); t('"Outra" sem nome da área recusada', str_contains($r, 'nome da área'));
[, $r] = $enviaVaga($A, $vagaPost);
$idV = (int) q("SELECT id FROM vagas WHERE titulo='Trainee de Moda Sustentável'"); $V = Database::one('SELECT * FROM vagas WHERE id = :i', ['i' => $idV]);
t('vaga enviada: estado aguardando_aprovacao, versão 1, código gerado', $V['status'] === 'aguardando_aprovacao' && (int) $V['versao'] === 1 && preg_match('/^VG-\d{4}-\d{4}$/', $V['codigo']) === 1);
t('campos estruturados gravados', $V['carga_horas_dia'] == 8 && $V['carga_dias'] === 'seg,ter,qua,qui,sex' && $V['carga_turno'] === 'integral' && $V['carga_horaria'] === '8h/dia · Seg a Sex · Integral' && (float) $V['bolsa'] === 9000.0 && $V['link_plataforma'] === 'gupy');
t('área nova criada e normalizada', $V['area'] === 'Moda sustentável' && (int) q("SELECT COUNT(*) FROM areas WHERE nome LIKE 'Moda%'") === 1);
t('vaga em análise: não aparece no site (410) nem na home', $visitante->req("/?p=vaga&id=$idV")[0] === 410 && !str_contains($visitante->req('/')[1], 'Trainee de Moda'));
t('empresa vê a vaga em "Em análise"', str_contains($A->req('/?p=empresa-vagas&aba=aguardando')[1], 'Trainee de Moda Sustentável'));
t('admin recebeu aviso (notificação + e-mail)', (bool) q("SELECT 1 FROM notificacoes WHERE tipo='vaga' AND referencia_id = :i", ['i' => $idV]) && (bool) q("SELECT 1 FROM emails_log WHERE destinatario='admin@ifsp.local' AND assunto LIKE 'Nova vaga aguardando%'"));
t('admin vê a vaga em Aprovações', str_contains($adm->req('/?p=admin-aprovacoes')[1], 'Trainee de Moda Sustentável'));
[, $r] = acaoAdmin($adm, 'vaga', 'rejeitar', $idV, 'x'); t('rejeitar exige motivo com 5+ caracteres', str_contains($r, 'Informe o motivo') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aguardando_aprovacao');
acaoAdmin($adm, 'vaga', 'aprovar', $idV);
t('vaga aprovada vira "publicada" (situação pelas datas)', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && Estado::situacaoVaga(Database::one('SELECT * FROM vagas WHERE id = :i', ['i' => $idV])) === 'publicada');
$mailAprov = false; foreach (glob(ROOT_PATH . '/storage/mail/*.html') as $f) { $h = file_get_contents($f); if (str_contains($h, 'Vaga aprovada') && str_contains($h, data_br($lim))) $mailAprov = true; }
t('e-mail de vaga aprovada informa até quando fica no ar', $mailAprov);
[, $home] = $visitante->req('/');
t('vaga aprovada aparece na home (selo Trainee, 3 linhas)', str_contains($home, 'Trainee de Moda Sustentável') && str_contains($home, 'vg-tag-tipo">Trainee') && str_contains($home, 'Terceira linha importante.') && !str_contains($home, 'Quarta linha que não deve'));
t('área nova com 1 vaga vai para "Outras" no filtro', str_contains($home, '<option>Outras</option>') && !str_contains($home, '<option>Moda sustentável</option>'));
[$c, $d] = $visitante->req("/?p=vaga&id=$idV"); t('detalhe público com CNPJ alfanumérico, carga estruturada e botão Gupy', $c === 200 && str_contains($d, Cnpj::formatar($cnpjA)) && str_contains($d, '8h/dia · Seg a Sex · Integral') && str_contains($d, 'pela Gupy') && str_contains($d, 'rel="noopener noreferrer nofollow"'));
t('admin vê a vaga em "Publicadas"', str_contains($adm->req('/?p=admin-vagas&aba=publicada')[1], 'Trainee de Moda Sustentável') && !str_contains($adm->req('/?p=admin-aprovacoes')[1], 'Trainee de Moda Sustentável'));
t('histórico da vaga: criada e aprovada', (int) q("SELECT COUNT(*) FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao IN ('criada','aprovada')", ['i' => $idV]) === 2);

secao('BUG CRÍTICO: aprovada com data futura (agendada) não pode "sumir"');
$futura = array_merge($vagaPost, ['titulo' => 'Estágio Agendado de Teste', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '', 'data_publicacao' => date('Y-m-d', strtotime('+5 days')), 'data_limite' => date('Y-m-d', strtotime('+30 days'))]);
$enviaVaga($A, $futura); $idF = (int) q("SELECT id FROM vagas WHERE titulo='Estágio Agendado de Teste'");
acaoAdmin($adm, 'vaga', 'aprovar', $idF);
t('aprovada com publicação futura fica "Agendada" para o admin', str_contains($adm->req('/?p=admin-vagas&aba=agendada')[1], 'Estágio Agendado de Teste'));
t('...e para a empresa', str_contains($A->req('/?p=empresa-vagas&aba=agendada')[1], 'Estágio Agendado de Teste'));
t('...mas não aparece no site e dá 410', !str_contains($visitante->req('/')[1], 'Estágio Agendado') && $visitante->req("/?p=vaga&id=$idF")[0] === 410);
t('...e não some das contagens do admin (todas = soma das situações)', array_sum(array_diff_key(Vaga::contagens(), ['todas' => 1, 'destaque' => 1])) === Vaga::contagens()['todas']);
db()->exec("UPDATE vagas SET data_publicacao = '$hoje' WHERE id = $idF");
t('ao chegar a data inicial, entra no ar sozinha', str_contains($visitante->req('/')[1], 'Estágio Agendado de Teste'));

secao('Edição de vaga aprovada exige nova aprovação');
$edit = array_merge($vagaPost, ['titulo' => 'Trainee de Moda Sustentável']);   // sem mudança
[, $r] = $enviaVaga($A, $edit, "/?p=vaga-editar&id=$idV");
t('salvar sem alterações não muda o estado', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && (int) q('SELECT versao FROM vagas WHERE id = :i', ['i' => $idV]) === 1);
$crlf = array_merge($vagaPost, ['descricao' => str_replace("\n", "\r\n", $vagaPost['descricao']), 'requisitos' => str_replace("\n", "\r\n", $vagaPost['requisitos'])]);   // o navegador envia CRLF
$enviaVaga($A, $crlf, "/?p=vaga-editar&id=$idV");
t('salvar sem alterar com quebras de linha do navegador (CRLF) não pede reaprovação', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && (int) q('SELECT versao FROM vagas WHERE id = :i', ['i' => $idV]) === 1);
$edit2 = array_merge($vagaPost, ['titulo' => 'Trainee de Moda Circular', 'bolsa' => '9500,00']);
$enviaVaga($A, $edit2, "/?p=vaga-editar&id=$idV"); $V = Database::one('SELECT * FROM vagas WHERE id = :i', ['i' => $idV]);
t('editar vaga aprovada volta para análise (versão 2)', $V['status'] === 'aguardando_aprovacao' && (int) $V['versao'] === 2 && $V['titulo'] === 'Trainee de Moda Circular' && $V['avaliado_em'] === null);
t('...sai do ar imediatamente', !str_contains($visitante->req('/')[1], 'Trainee de Moda Circular') && $visitante->req("/?p=vaga&id=$idV")[0] === 410);
t('...histórico registra a edição com os campos alterados', (bool) q("SELECT 1 FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao='editada' AND detalhe LIKE '%titulo%'", ['i' => $idV]));
t('...admin é avisado da nova análise', (bool) q("SELECT 1 FROM notificacoes WHERE mensagem LIKE 'Vaga editada aguarda nova aprovação%'") && (bool) q("SELECT 1 FROM emails_log WHERE assunto LIKE 'Vaga editada aguarda%'"));
acaoAdmin($adm, 'vaga', 'aprovar', $idV);
t('...só volta ao ar depois da nova aprovação', str_contains($visitante->req('/')[1], 'Trainee de Moda Circular'));

secao('Rejeição, correção e reenvio');
$enviaVaga($A, array_merge($vagaPost, ['titulo' => 'Vaga para Rejeitar', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']));
$idR = (int) q("SELECT id FROM vagas WHERE titulo='Vaga para Rejeitar'");
acaoAdmin($adm, 'vaga', 'rejeitar', $idR, 'Faltam informações sobre as atividades.');
t('vaga rejeitada com motivo + e-mail', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'rejeitada' && (bool) q("SELECT 1 FROM emails_log WHERE destinatario='rh@verdeagil.example' AND assunto LIKE 'Vaga não aprovada%'"));
t('empresa vê o motivo na vaga e na aba Rejeitadas', str_contains($A->req("/?p=empresa-vaga&id=$idR")[1], 'Faltam informações sobre as atividades') && str_contains($A->req('/?p=empresa-vagas&aba=rejeitada')[1], 'Vaga para Rejeitar'));
[, $r] = acaoAdmin($adm, 'vaga', 'aprovar', $idR); t('estado inválido: não aprova vaga rejeitada', str_contains($r, 'não é permitida') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'rejeitada');
$enviaVaga($A, array_merge($vagaPost, ['titulo' => 'Vaga Corrigida e Reenviada', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']), "/?p=vaga-editar&id=$idR");
t('corrigir e salvar reenvia para análise', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'aguardando_aprovacao' && (bool) q("SELECT 1 FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao='reenviada'", ['i' => $idR]));

secao('Encerramento, expiração e histórico (nada é excluído)');
$tok = $A->csrf('/?p=painel');
[, $r] = $A->req('/?p=vaga-encerrar', ['_csrf' => $tok, 'id' => $idR]); t('encerrar vaga que não está aprovada é recusado', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idR]) === 'aguardando_aprovacao');
$antes = (int) q('SELECT COUNT(*) FROM vagas');
$A->req('/?p=vaga-encerrar', ['_csrf' => $tok, 'id' => $idF]);
t('empresa encerra a própria vaga: status encerrada, registro mantido', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idF]) === 'encerrada' && (int) q('SELECT COUNT(*) FROM vagas') === $antes);
t('encerrada some da home mas o detalhe vira histórico (200 + aviso)', !str_contains($visitante->req('/')[1], 'Estágio Agendado') && str_contains($visitante->req("/?p=vaga&id=$idF")[1], 'não recebe mais inscrições') && str_contains($visitante->req('/?p=encerradas')[1], 'Estágio Agendado de Teste'));
t('detalhe encerrado não mostra botão de candidatura', !str_contains($visitante->req("/?p=vaga&id=$idF")[1], 'Como se candidatar'));
[$c, , $l] = $A->req("/?p=vaga-editar&id=$idF", null, false); t('vaga encerrada não pode ser editada', $c === 302);
[, $r] = acaoAdmin($adm, 'vaga', 'encerrar', $idF); t('estado inválido: admin não encerra de novo', str_contains($r, 'não é permitida'));
db()->exec("UPDATE vagas SET data_limite = '" . date('Y-m-d', strtotime('-2 days')) . "', data_publicacao = '" . date('Y-m-d', strtotime('-20 days')) . "' WHERE id = $idV");
t('prazo terminou: some da home, vira Expirada (não "encerrada")', !str_contains($visitante->req('/')[1], 'Trainee de Moda Circular') && str_contains($A->req('/?p=empresa-vagas&aba=expirada')[1], 'Trainee de Moda Circular') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada');
t('expirada continua acessível como histórico', str_contains($visitante->req("/?p=vaga&id=$idV")[1], 'não recebe mais inscrições') && str_contains($adm->req('/?p=admin-vagas&aba=expirada')[1], 'Trainee de Moda Circular'));
$out = shell_exec('DB_SQLITE=' . escapeshellarg(env('DB_SQLITE', ROOT_PATH . '/storage/database.sqlite')) . ' MAIL_DRIVER=log php ' . escapeshellarg(ROOT_PATH . '/cron/rotina.php'));
t('rotina diária só avisa: não altera o estado e não exclui', str_contains((string) $out, 'avisos') && q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada' && (int) q('SELECT aviso_encerrada FROM vagas WHERE id = :i', ['i' => $idV]) === 1 && (bool) q("SELECT 1 FROM historico WHERE entidade='vaga' AND entidade_id = :i AND acao='expirada'", ['i' => $idV]), (string) $out);

secao('Empresa B + isolamento entre empresas (IDOR)');
$dadosB = array_merge($dadosA, ['cnpj' => '11.222.333/0001-81', 'razao_social' => 'Beta Indústria Ltda', 'nome' => 'Beta Indústria', 'email' => 'rh@beta.example', 'site' => '', 'linkedin' => '', 'link_principal' => 'site']);
$envia($B, $dadosB); $idB = (int) q("SELECT id FROM empresas WHERE cnpj = :c", ['c' => $cnpjB]); acaoAdmin($adm, 'empresa', 'aprovar', $idB);
$B->req('/?p=empresa-login'); $enviaVaga($B, array_merge($vagaPost, ['titulo' => 'Vaga da Beta Industrial', 'tipo' => 'Estágio', 'area' => 'Engenharia', 'area_outra' => '', 'link_plataforma' => '', 'link_candidatura' => '', 'contato' => 'rh@beta.example']));
$idVB = (int) q("SELECT id FROM vagas WHERE titulo='Vaga da Beta Industrial'"); acaoAdmin($adm, 'vaga', 'aprovar', $idVB);
t('B existe e tem a própria vaga publicada', $idVB > 0 && q('SELECT empresa_id FROM vagas WHERE id = :i', ['i' => $idVB]) == $idB);
foreach (["empresa-vaga&id=$idV", "vaga-editar&id=$idV"] as $r) { [$c] = $B->req("/?p=$r"); t("B não acessa vaga da A (?p=$r)", $c === 404); }
$tokB = $B->csrf('/?p=painel'); $B->req('/?p=vaga-encerrar', ['_csrf' => $tokB, 'id' => $idV]);
t('B não consegue encerrar vaga da A (POST direto)', q('SELECT status FROM vagas WHERE id = :i', ['i' => $idV]) === 'aprovada');
$B->req("/?p=vaga-editar&id=$idV", ['_csrf' => $tokB] + array_merge($vagaPost, ['titulo' => 'SEQUESTRADA POR B']));
t('B não consegue editar vaga da A (POST direto)', q('SELECT titulo FROM vagas WHERE id = :i', ['i' => $idV]) === 'Trainee de Moda Circular');
t('B não vê a vaga da A na própria lista', !str_contains($B->req('/?p=empresa-vagas')[1], 'Trainee de Moda') && str_contains($B->req('/?p=empresa-vagas')[1], 'Vaga da Beta Industrial'));
foreach (['abc', '-1', '9999999', '1 OR 1=1', "1'"] as $x) { [$c] = $B->req('/?p=empresa-vaga&id=' . rawurlencode($x)); t("id manipulado '$x' não vaza dados nem gera erro 500", in_array($c, [404], true)); }
[$c] = $B->req('/?p=empresa-vaga&id[]=1'); t('id como array não gera erro 500', $c === 404);
t('tentativas negadas ficam no log de acessos', (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='negado'") >= 4);

secao('Sessões isoladas: empresa x admin');
foreach (['admin', 'admin-vagas', 'admin-empresas', 'admin-usuarios', 'admin-acesso', 'admin-exportar&tipo=vagas', "admin-termo&id=$idA", 'admin-login'] as $r) { [$c, , $l] = $B->req("/?p=$r", null, false); t("empresa logada bloqueada em ?p=$r", $c === 302 && str_contains($l, 'p=painel')); }
[$c] = $B->req('/?p=admin-acao', ['_csrf' => $tokB, 'tipo' => 'empresa', 'acao' => 'aprovar', 'id' => $idA], false); t('empresa não executa ação administrativa', $c === 302);
t('empresa não consegue se aprovar/desbloquear', q('SELECT status FROM empresas WHERE id = :i', ['i' => $idB]) === 'aprovada');
foreach (['painel', 'empresa-vagas', 'empresa-perfil', 'vaga-cadastro', 'empresa-login', 'empresa-cadastro', "empresa-vaga&id=$idV"] as $r) { [$c, , $l] = $adm->req("/?p=$r", null, false); t("admin logado fora da área da empresa (?p=$r)", $c === 302 && str_contains($l, 'p=admin')); }
[, $hAdm] = $adm->req('/?p=admin'); [, $hEmp] = $B->req('/?p=painel');
t('menus exclusivos por perfil', str_contains($hAdm, 'Controle de acesso') && !str_contains($hAdm, 'Publicar vaga') && str_contains($hEmp, 'Publicar vaga') && !str_contains($hEmp, 'Controle de acesso'));
[, $r] = $B->post('/?p=admin-login', ['email' => 'admin@ifsp.local', 'senha' => 'Admin@2026!'], '/?p=painel'); t('empresa logada não vira admin postando credenciais', !str_contains($r, 'Dashboard'));
[, $pub] = $adm->req('/'); t('site público mostra "Administração" só para admin e "Sair"', str_contains($pub, 'Administração') && str_contains($pub, 'Sair'));

secao('Bloqueio de empresa e sessão invalidada');
acaoAdmin($adm, 'empresa', 'bloquear', $idB, 'Teste de bloqueio');
t('empresa bloqueada: sessão cai na próxima requisição', $B->req('/?p=painel', null, false)[0] === 302);
t('...vagas dela saem do site', !str_contains($visitante->req('/')[1], 'Vaga da Beta Industrial') && $visitante->req("/?p=vaga&id=$idVB")[0] === 410);
[, $r] = loginEmpresa(new Cli($base), 'rh@beta.example', 'senha-forte-1'); t('...e não consegue entrar', str_contains($r, 'bloqueada'));
[, $r] = acaoAdmin($adm, 'empresa', 'desbloquear', $idB); t('desbloquear restaura: vaga volta ao site', str_contains($visitante->req('/')[1], 'Vaga da Beta Industrial'));

secao('Força bruta e controle de acesso');
$bf = new Cli($base); for ($i = 0; $i < 5; $i++) loginEmpresa($bf, 'rh@beta.example', 'errada-' . $i);
[, $r] = loginEmpresa($bf, 'rh@beta.example', 'senha-forte-1'); t('após 5 falhas o login é travado, mesmo com a senha certa', str_contains($r, 'Muitas tentativas'));
t('eventos de login (ok/falha/bloqueio) registrados', (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='login_falha'") >= 5 && (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='login_ok'") >= 3 && (int) q("SELECT COUNT(*) FROM acessos_log WHERE evento='bloqueado'") >= 1);
[, $ac] = $adm->req('/?p=admin-acesso'); t('tela de Controle de acesso mostra números reais', str_contains($ac, 'Bloqueios ativos agora') && str_contains($ac, 'Tentativas suspeitas recentes') && str_contains($ac, 'Falha de login'));

secao('Perfil da empresa e página pública');
[, $pf] = $A->req('/?p=empresa-perfil'); t('perfil mostra os dados e a prévia', str_contains($pf, 'Verde Ágil Tecnologia Ltda') && str_contains($pf, 'Como o estudante vê'));
$novo = array_diff_key($dadosA, array_flip(['cnpj', 'email', 'senha', 'senha2', 'aceite_termos', 'aceite_privacidade'])); $novo['descricao'] = 'Descrição atualizada da empresa, agora com mais detalhes sobre o programa de estágio.'; $novo['link_principal'] = 'site';
$A->req('/?p=empresa-perfil', ['_csrf' => $A->csrf('/?p=painel')] + $novo);
t('empresa edita o próprio perfil (sem alterar CNPJ/e-mail/status)', str_contains((string) q('SELECT descricao FROM empresas WHERE id = :i', ['i' => $idA]), 'atualizada') && q('SELECT status FROM empresas WHERE id = :i', ['i' => $idA]) === 'aprovada' && q('SELECT cnpj FROM empresas WHERE id = :i', ['i' => $idA]) === $cnpjA);
[, $ep] = $visitante->req('/?p=empresas'); t('Empresas Parceiras lista as aprovadas (e só elas)', str_contains($ep, 'Verde Ágil') && str_contains($ep, 'Empresas recentes') && str_contains($ep, 'Empresas mais frequentes') && !str_contains($ep, 'Casa Antiga'));
[, $pe] = $visitante->req("/?p=empresa&id=$idA"); t('página pública da empresa com vagas abertas', str_contains($pe, 'Verde Ágil') && str_contains($pe, 'Vagas abertas desta empresa'));
[$c] = $visitante->req('/?p=empresa&id=' . q("SELECT id FROM empresas WHERE status='pendente' LIMIT 1")); t('empresa pendente não tem página pública', $c === 404 || (int) q("SELECT COUNT(*) FROM empresas WHERE status='pendente'") === 0);
[, $ex] = $visitante->req("/?p=externo&id=$idA&url=https://evil.example"); t('saída para site externo avisa e usa só o link salvo (sem open redirect)', str_contains($ex, 'Você está saindo do sistema') && str_contains($ex, 'verdeagil.example') && !str_contains($ex, 'evil.example'));
[$c, , $l] = $visitante->req("/?p=externo&id=$idB", null, false); t('empresa sem links vai para a página interna', $c === 302 && str_contains($l, 'p=empresa'));

secao('Modo TV e QR Code por vaga');
[, $tv] = $visitante->req('/?p=tv');
$publicas = Vaga::publicas();
t('TV: um QR por vaga publicada', substr_count($tv, 'data-qr=') === count($publicas) && count($publicas) >= 1);
$todosOk = true; foreach ($publicas as $v) { $esperado = rtrim((string) env('APP_URL'), '/') . (env('PRETTY_URLS') ? '/vaga/' . $v['id'] : '/?p=vaga&amp;id=' . $v['id']); if (!str_contains($tv, 'data-qr="' . $esperado . '"')) $todosOk = false; }
t('TV: cada QR aponta para o link da PRÓPRIA vaga', $todosOk);
t('TV: biblioteca de QR local (sem CDN) e fallback com URL em texto', str_contains($tv, 'vendor/qrcode.js') && str_contains($tv, 'vg-tv-qr') && !str_contains($tv, 'cdn'));
$pretty = env('PRETTY_URLS') && env('PRETTY_URLS') !== '0';
if ($pretty) { t('link curto /vaga/ID abre a vaga', $visitante->req("/vaga/$idVB")[0] === 200 && str_contains($tv, '/vaga/' . $idVB)); }

secao('Dashboard e relatórios com números reais');
[, $dash] = $adm->req('/?p=admin'); $c = Vaga::contagens(); $e = Stats::empresas();
t('dashboard bate com o banco', str_contains($dash, '<span class="ui-metric-n">' . $c['todas'] . '</span><span class="ui-metric-l">Total de vagas') && str_contains($dash, '<span class="ui-metric-n">' . $e['total'] . '</span><span class="ui-metric-l">Total de empresas'));
t('gráficos são SVG com descrição acessível', str_contains($dash, 'role="img"') && str_contains($dash, 'aria-label="Gráfico de rosca'));
[, $csv] = $adm->req('/?p=admin-exportar&tipo=vagas'); t('CSV de vagas com BOM e cabeçalho', str_starts_with($csv, "\xEF\xBB\xBFCódigo;Título"));
$enviaVaga($A, array_merge($vagaPost, ['titulo' => '=HYPERLINK("http://x")', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']));
[, $csv] = $adm->req('/?p=admin-exportar&tipo=vagas'); t('CSV neutraliza injeção de fórmula', str_contains($csv, "'=HYPERLINK") && !preg_match('/;=HYPERLINK/', $csv));
$enviaVaga($A, array_merge($vagaPost, ['titulo' => '<script>alert(1)</script> XSS', 'tipo' => 'Estágio', 'area' => 'Administração', 'area_outra' => '']));
$idX = (int) q("SELECT id FROM vagas WHERE titulo LIKE '%XSS'"); [, $x1] = $adm->req('/?p=admin-aprovacoes'); [, $x2] = $A->req("/?p=empresa-vaga&id=$idX");
t('XSS: título com <script> sai escapado no admin e na empresa', !str_contains($x1, '<script>alert(1)') && str_contains($x1, '&lt;script&gt;') && !str_contains($x2, '<script>alert(1)'));
[$c] = $visitante->req('/?q=' . rawurlencode("' OR 1=1 --")); t('SQLi na busca: sem erro', $c === 200);
[$c] = $adm->req('/?p=admin-vagas&q=' . rawurlencode("'; DROP TABLE vagas; --") . '&ord=' . rawurlencode('x;DROP')); t('SQLi na busca/ordenação do admin: sem erro e tabela intacta', $c === 200 && (int) q('SELECT COUNT(*) FROM vagas') > 0);

secao('Usuários administradores');
[, $r] = $adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'criar', 'nome' => 'Secretaria', 'email' => 'secretaria@ifsp.local', 'senha' => 'fraca', 'ret' => 'admin-usuarios'], '/?p=admin'); t('admin com senha fraca recusado', !q("SELECT 1 FROM admins WHERE email='secretaria@ifsp.local'") && str_contains($r, 'letras e números'));
$adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'criar', 'nome' => 'Secretaria', 'email' => 'secretaria@ifsp.local', 'senha' => 'Secretaria2026', 'ret' => 'admin-usuarios'], '/?p=admin'); t('novo administrador criado', (bool) q("SELECT 1 FROM admins WHERE email='secretaria@ifsp.local'"));
$meu = (int) q("SELECT id FROM admins WHERE email='admin@ifsp.local'"); [, $r] = $adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $meu, 'ret' => 'admin-usuarios'], '/?p=admin'); t('não pode desativar a própria conta', str_contains($r, 'própria conta') && (int) q('SELECT ativo FROM admins WHERE id = :i', ['i' => $meu]) === 1);
$sec = (int) q("SELECT id FROM admins WHERE email='secretaria@ifsp.local'"); $adm->post('/?p=admin-acao', ['tipo' => 'admin', 'acao' => 'desativar', 'id' => $sec, 'ret' => 'admin-usuarios'], '/?p=admin');
[, $r] = loginAdmin(new Cli($base), 'secretaria@ifsp.local', 'Secretaria2026'); t('administrador desativado não entra', str_contains($r, 'desativada'));

secao('Logout e sessão expirada');
[$c] = $B->req('/?p=sair', ['_csrf' => $B->csrf('/?p=painel'), 'perfil' => 'empresa']); [$c2] = $B->req('/?p=painel', null, false); t('logout encerra a sessão (rota protegida volta ao login)', $c2 === 302);
if ($baseCurta) {
    $S = new Cli($baseCurta); loginAdmin($S); t('(servidor com sessão curta) admin logado', $S->req('/?p=admin', null, false)[0] === 200);
    sleep(3); [$c, , $l] = $S->req('/?p=admin', null, false); t('sessão expira por inatividade e exige novo login', $c === 302 && str_contains($l, 'admin-login'));
}
echo "\n$ok ok, $falhas falhas\n"; exit($falhas ? 1 : 0);
```

### Passo 121 — migracao.php

#### Arquivo: `tests/migracao.php` — CRIAR

```php
<?php
// Migrations: banco antigo recebe as colunas/estados novos sem perder dados; rollback restaura. Usa o banco do .env/DB_SQLITE.
require_once __DIR__ . '/../config.php'; require_once __DIR__ . '/../database/schema.php';
$f = 0; function ok(string $n, bool $c) { global $f; echo ($c ? '  ok    ' : '  FALHA ') . "$n\n"; if (!$c) $f++; }
echo "== Migrations: atualização de banco antigo com dados ==\n";
$emp = Database::val('SELECT id FROM empresas LIMIT 1'); $antes = (int) Database::val('SELECT COUNT(*) FROM vagas');
Database::insert('vagas', ['codigo' => 'MIG-1', 'empresa_id' => $emp, 'titulo' => 'Vaga de migração', 'tipo' => 'Estágio', 'area' => 'Administração', 'modalidade' => 'Remoto', 'local' => 'SP', 'descricao' => 'x', 'data_publicacao' => date('Y-m-d'), 'data_limite' => date('Y-m-d', strtotime('+9 days')), 'contato' => 'a@b.com', 'status' => 'aguardando_aprovacao', 'criado_em' => Database::agora()]);
Database::insert('vagas', ['codigo' => 'MIG-2', 'empresa_id' => $emp, 'titulo' => 'Vaga rejeitada migração', 'tipo' => 'Estágio', 'area' => 'Administração', 'modalidade' => 'Remoto', 'local' => 'SP', 'descricao' => 'x', 'data_publicacao' => date('Y-m-d'), 'data_limite' => date('Y-m-d', strtotime('+9 days')), 'contato' => 'a@b.com', 'status' => 'rejeitada', 'criado_em' => Database::agora()]);
$total = (int) Database::val('SELECT COUNT(*) FROM vagas'); $empTot = (int) Database::val('SELECT COUNT(*) FROM empresas');
$n = desfazer_ultima(); ok("rollback da última migration ($n)", $n === '002_refinamento_empresas_vagas' && !coluna_existe('vagas', 'versao') && !tabela_existe('historico'));
ok('rollback devolve os nomes de estado antigos', (int) Database::val("SELECT COUNT(*) FROM vagas WHERE status IN ('pendente','reprovada')") >= 2);
$feitas = migrar();
ok('migrate reaplica a migration pendente', $feitas === ['002_refinamento_empresas_vagas'] && coluna_existe('vagas', 'versao') && tabela_existe('historico') && coluna_existe('empresas', 'segmento'));
ok('estados renomeados e dados preservados', (int) Database::val("SELECT COUNT(*) FROM vagas WHERE status IN ('pendente','reprovada')") === 0 && (int) Database::val('SELECT COUNT(*) FROM vagas') === $total && (int) Database::val('SELECT COUNT(*) FROM empresas') === $empTot);
ok('migrate é idempotente', migrar() === []);
echo $f ? "\n$f falha(s) na migração\n" : "\nmigrações ok\n"; exit($f ? 1 : 0);
```

### Passo 122 — run.sh

#### Arquivo: `tests/run.sh` — CRIAR
Depois: `chmod +x tests/run.sh`.

```bash
#!/usr/bin/env bash
# Roda a suíte completa com banco descartável. Uso: tests/run.sh [sqlite|mysql|pgsql]
set -e
cd "$(dirname "$0")/.."
DRV=${1:-sqlite}
export ADMIN_EMAIL=admin@ifsp.local ADMIN_PASSWORD='Admin@2026!' ADMIN_NOTIFY_EMAIL=admin@ifsp.local MAIL_DRIVER=log PRETTY_URLS=1 APP_URL=http://127.0.0.1:8123
if [ "$DRV" = sqlite ]; then export DB_SQLITE=/tmp/estagia-teste.sqlite; rm -f $DB_SQLITE; fi
rm -f storage/mail/*.html
php database/seed.php >/dev/null
php -S 127.0.0.1:8123 router.php >/tmp/estagia-srv1.log 2>&1 & P1=$!
SESSION_IDLE_ADMIN=2 php -S 127.0.0.1:8124 router.php >/tmp/estagia-srv2.log 2>&1 & P2=$!
trap "kill $P1 $P2 2>/dev/null" EXIT
sleep 1
php tests/fluxo.php http://127.0.0.1:8123 http://127.0.0.1:8124
php tests/migracao.php
```

### Passo 123 — smtp_falso.py

#### Arquivo: `tests/smtp_falso.py` — CRIAR
Servidor SMTP de mentira para testar o envio: `python3 tests/smtp_falso.py 2526 /tmp/smtp.out`.

```python
# Servidor SMTP de mentira para testar o envio: python3 tests/smtp_falso.py 2526 /tmp/smtp.out
import socket, sys, threading, base64
porta, saida = int(sys.argv[1]), sys.argv[2]
srv = socket.socket(); srv.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1); srv.bind(('127.0.0.1', porta)); srv.listen(5)
open(saida, 'w').close()
def atende(c):
    f = c.makefile('rwb', buffering=0)
    w = lambda t: f.write((t + '\r\n').encode())
    w('220 fake ESMTP'); msg = {'auth': []}
    while True:
        l = f.readline().decode().strip()
        if not l: break
        u = l.upper()
        if u.startswith('EHLO'): w('250-fake'); w('250 AUTH LOGIN')
        elif u == 'AUTH LOGIN': w('334 VXNlcm5hbWU6'); msg['auth'].append(base64.b64decode(f.readline().strip()).decode()); w('334 UGFzc3dvcmQ6'); msg['auth'].append(base64.b64decode(f.readline().strip()).decode()); w('235 ok')
        elif u.startswith('MAIL FROM'): msg['from'] = l[10:]; w('250 ok')
        elif u.startswith('RCPT TO'): msg['to'] = l[8:]; w('250 ok')
        elif u == 'DATA':
            w('354 go'); dados = []
            while True:
                x = f.readline().decode()
                if x in ('.\r\n', '.\n'): break
                dados.append(x)
            raw = ''.join(dados); cab, _, corpo = raw.partition('\r\n\r\n')
            assunto = ''; rt = ''
            for h in cab.split('\r\n'):
                if h.lower().startswith('reply-to:'): rt = h[9:].strip()
                if h.lower().startswith('subject:'):
                    v = h[8:].strip(); assunto = base64.b64decode(v[10:-2]).decode() if v.startswith('=?UTF-8?B?') else v
            texto = base64.b64decode(''.join(corpo.split())).decode()
            open(saida, 'a').write('AUTH=%s FROM=%s TO=%s ASSUNTO=%s CORPO_OK=%s REPLYTO=%s\n' % ('/'.join(msg['auth']), msg['from'], msg['to'], assunto, 'sim' if '<html' in texto.lower() else 'nao', rt))
            w('250 queued')
        elif u == 'QUIT': w('221 bye'); break
        else: w('250 ok')
    c.close()
while True:
    c, _ = srv.accept(); threading.Thread(target=atende, args=(c,), daemon=True).start()
```

### Passo 124 — Verificar a branch

```bash
chmod +x tests/run.sh && tests/run.sh
```

Resultado esperado:
- `chmod +x tests/run.sh && tests/run.sh…` → 185 ok, 0 falhas / migrações ok

### Passo 125 — Fechar a branch

```bash
git add .
git commit -m "test: suíte de fluxo, segurança, modo TV e migrações"
git push -u origin feature/qa
```
Abra o Pull Request `feature/qa` → `develop` e peça revisão a **José Roberto**. Depois do merge:
```bash
git checkout develop && git pull
```

---

## 6. Fechamento: develop para main
Com as 14 branches mescladas em `develop`:
```bash
tests/run.sh                          # deve terminar com: 185 ok, 0 falhas / migrações ok
tests/run.sh mysql                    # opcional: com DB_DRIVER=mysql no ambiente e um banco vazio criado
git checkout main && git merge --no-ff develop && git push
```
Deploy no cPanel (resumo): suba os arquivos; crie o banco MySQL e importe `database/mysql.sql` (ou deixe o sistema criar); preencha `.env` (`APP_URL`, `DB_*`, `MAIL_*`, `ADMIN_*`, `PRETTY_URLS=1`); permissão de escrita em `storage/` e `public/uploads/`; cron diário `php /home/USUARIO/pasta/cron/rotina.php`; confirme que `/.env`, `/storage/` e `/docs/` dão 403.

## 7. Se travar
Rode a verificação da branch e leia o erro exato. Os problemas mais comuns: falta de `.env`; extensão `pdo_sqlite`/`pdo_mysql` desativada; pasta `storage/` sem permissão de escrita; esquecer de colar as partes 2 e 3 do `app.js` **antes** da última linha `})();`.
