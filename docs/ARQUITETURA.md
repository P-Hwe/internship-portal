# ESTAGIA+ — Arquitetura

Plataforma PHP (8.0+) de divulgação de vagas de estágio do IFSP Guarulhos, em **monorepo**: front, back e banco no mesmo projeto e na mesma hospedagem (cPanel).

## 1. Três contextos, independentes

| Contexto | Quem | Layout | Rotas (`?p=`) |
|---|---|---|---|
| Estudante (público) | qualquer pessoa | header/footer do IFSP (`Views/header.php`, `footer.php`) | `home`, `vaga`, `encerradas`, `empresas`, `empresa`, `externo`, `tv` |
| Empresa | empresa autenticada | `layouts/app.php` (menu lateral, sem header/footer do IFSP) | `painel`, `empresa-vagas`, `empresa-vaga`, `empresa-perfil`, `vaga-cadastro`, `vaga-editar`, `vaga-encerrar` |
| Administração | administrador autenticado | `layouts/app.php` | `admin`, `admin-aprovacoes`, `admin-vagas`, `admin-vaga`, `admin-empresas`, `admin-empresa`, `admin-usuarios`, `admin-relatorios` |
| Login/cadastro | visitante | `layouts/auth.php` (minimalista) | `empresa-login`, `admin-login`, `empresa-cadastro`, `empresa-concluido` |

**Login único por vez:** empresa e admin são exclusivos. Com um perfil conectado, as rotas do outro redirecionam (e a tentativa vai para `acessos_log`). Quem faz isso é `Auth::bloquearSeOutroPerfil()` e `Auth::normalizar()`, no backend.

## 2. Camadas e pastas

```
index.php            roteador (tabela de rotas) — único ponto de entrada
config.php           constantes de negócio, helpers (url, e, view/render, ícones), autoload
app/                 núcleo reutilizável (sem HTML de página)
  Auth.php           login, sessão, exclusividade, expiração, log de acessos
  Estado.php         máquina de estados (vaga/empresa) + situação por datas  <- regra mais importante
  Database.php       PDO (sqlite | mysql | pgsql)
  Historico.php      auditoria de mudanças de estado
  EmpresaForm.php / VagaForm.php   validação (cadastro e edição usam a mesma regra)
  Cnpj.php Link.php Upload.php Csrf.php Mailer.php Notificador.php Paginador.php Bootstrap.php
  Ui.php             componentes HTML reutilizáveis (badge, abas, paginação, modais, gráficos)
Models/              acesso a dados + regras do domínio (Vaga, Empresa, Admin, Stats, Notificacao)
Controllers/         recebem a requisição, autorizam, chamam Models, escolhem a View
Views/               telas; layouts/ (app, auth); partials/ (cards, timeline, campos)
public/css/app.css   design system (tokens + modo noturno) das áreas logadas
public/css/vagas.css site público (visual do IF)
public/js/app.js     JS das áreas logadas (wizard, modais, ViaCEP, chips, tema)
public/js/vagas.js   JS do site público e do Modo TV (+ vendor/qrcode.js)
database/            schema.php (instalação) + migrations/ + seed.php + *.sql
cron/rotina.php      avisos diários
storage/             fora da web: termos de cooperação, e-mails de teste, sqlite
tests/               fluxo.php (e2e + unidade), migracao.php, run.sh
```

Regra de dependência: `Controllers → Models/app → Database`. Views só leem dados prontos (nunca consultam o banco, com exceção dos helpers `Ui`/`Auth` de leitura simples).

## 3. Estados (única fonte: `app/Estado.php`)

**Vaga — estado persistido** (`vagas.status`)
```
aguardando_aprovacao ──aprovar──▶ aprovada ──encerrar──▶ encerrada (final)
        │                           │  ▲
        └──rejeitar──▶ rejeitada    │  └─ empresa edita (reaprovação) ─┐
                         └──corrigir/reenviar──▶ aguardando_aprovacao ◀─┘
```
**Situação (calculada pelas datas, no backend/SQL — nunca no JavaScript):**
`aguardando · rejeitada · encerrada · agendada · publicada · expirada`.
`aprovada` vira *agendada* (antes de `data_publicacao`), *publicada* (dentro do período) ou *expirada* (depois de `data_limite`).

> **Causa do bug "vaga aprovada não aparece em lugar nenhum":** uma vaga aprovada com data de publicação futura não era "pendente" nem "no ar" e sumia das duas listas. Agora existe a situação **agendada**, listada para admin e empresa e contada no total.

**Empresa:** `pendente → aprovada | reprovada`; `aprovada ↔ bloqueada`; `reprovada → pendente` (reenvio). Empresa bloqueada perde a sessão na próxima requisição e suas vagas saem do ar.

Transições inválidas são recusadas em `Vaga::mudarStatus()` / `Empresa::mudarStatus()` (retornam `false`) e cada mudança grava `historico`.

## 4. Autorização (tudo no backend)

- `Auth::exigirAdmin()` / `Auth::exigirEmpresa()` no início de cada ação.
- **Ownership:** `Vaga::daEmpresaPorId($empresaId, $id)` só devolve a vaga se for da empresa logada; senão `Auth::recusar()` (404 igual para "não existe" e "não é sua").
- **CSRF** em todo POST (`Csrf::exigir()`); **consultas preparadas** (PDO); saída escapada com `e()`.
- **Sessão:** ID regenerado no login/logout; expira por inatividade (admin 30 min, empresa 2 h; `SESSION_IDLE_*`) e em 12 h absolutas.
- **Força bruta:** 5 falhas/15 min por e-mail+IP; tudo vai para `acessos_log` (login_ok, login_falha, negado, bloqueado, timeout, logout).
- **Uploads:** tipo real do arquivo (finfo), tamanho, nome aleatório; termos ficam em `storage/` (fora da web, só admin lê).
- **Links externos:** só http/https, sem credenciais, domínio conferido por plataforma (Gupy/LinkedIn); a saída para site externo usa o destino **salvo no banco**, nunca da URL (sem open redirect).
- **CSV:** células iniciadas por `= + - @` recebem apóstrofo (injeção de fórmula).

## 5. Banco de dados

Tabelas: `empresas`, `vagas`, `areas`, `admins`, `historico`, `acessos_log`, `notificacoes`, `emails_log`, `login_tentativas`, `migracoes`.
Banco novo: `schema.php` cria o esquema final e marca as migrations como aplicadas. Banco antigo: `php database/migrate.php` aplica só as pendentes. Rollback: `php database/migrate.php down`. Detalhes em `GUIA_DESENVOLVIMENTO.md` §8.

Drivers: SQLite (desenvolvimento), **MySQL/MariaDB (recomendado no cPanel)** e PostgreSQL — a suíte de testes passa nos três (`tests/run.sh sqlite|mysql|pgsql`).

## 6. Hospedagem: monorepo ou front/back separados?

**Monorepo no cPanel.** É PHP renderizado no servidor (não há build de front), mesma origem (sessão e CSRF sem CORS), um deploy, um `.env`. Separar só valeria com SPA + API; aqui aumentaria risco e trabalho sem ganho.

## 7. Componentes (design system)

PHP: `Ui::badge/situacao/statusEmpresa`, `Ui::abas`, `Ui::paginacao`, `Ui::metrica`, `Ui::vazio` (EmptyState), `Ui::logo`, `Ui::campo` (Input/Select/Textarea), `Ui::formConfirmar` e `Ui::formMotivo` (Modal), `Ui::acoesVagaAdmin/acoesEmpresaAdmin`, `Ui::donut/barras` (gráficos SVG). Partials: `vaga_card_app`, `empresa_card_app`, `vaga_detalhe`, `timeline`, `empresa_campos`.
JS (`app.js`): `data-wiz` (Stepper com revisão), `data-chips`, `data-viacep`, `data-mask`, `data-confirm`/`data-motivo` (modais), `data-theme-toggle`.
CSS: tokens em `:root` e `[data-theme=dark]`; **nenhum componente tem cor própria para o escuro**.
