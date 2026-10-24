# Equipe, responsabilidades e fluxo Git

O passo a passo por pessoa e por branch (ordem, código a copiar, verificação e PR) está em **`docs/GUIA_PASSO_A_PASSO.md`**. Este arquivo resume as regras.

| Pessoa | Papel | Branches (na ordem do guia) |
|---|---|---|
| José Roberto | Backend; Scrum Master | `backend-core`, `backend-auth`, `backend-jobs` |
| Rodrigo | Banco de dados e front de empresas | `database-company`, `frontend-company` |
| Pedro Miguel | Cybersegurança, QA e modo TV | `security`, `notifications`, `access-control`, `tv-mode`, `qa` |
| Pedro Henri | Arquitetura e front administrativo | `architecture`, `design-system`, `frontend-admin` |
| Arthur | Front de vagas | `frontend-jobs` |

Arquivos **compartilhados** (avisar antes de alterar): `config.php`, `index.php`, `public/js/app.js`, `app/Ui.php`, `docs/ROTAS_E_CONTRATOS.md`.

## Git
```
main        produção (só recebe develop com testes verdes)
develop     integração
feature/*   uma por dono (nomes no guia)
```
Fluxo: `feature/*` → Pull Request → revisão por outra pessoa → `develop` → testes → `main`. Criar a estrutura: `scripts/git-setup.sh`.
Commits pequenos e descritivos (`feat(vagas): ...`, `fix(estado): ...`, `test(seguranca): ...`); proibido `update`, `fix`, `changes`, `teste`, `alterações`.
Antes do PR: `git rebase origin/develop`, a verificação da branch (final do passo a passo) e, no fim, `tests/run.sh` verde; contrato atualizado; sem `.env`, `storage/*` ou uploads no commit.
Conflito: resolva na sua branch e rode os testes de novo; nunca "aceitar tudo de um lado".
