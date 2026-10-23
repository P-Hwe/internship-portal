```bash
#!/usr/bin/env bash
# Cria a estrutura de branches do projeto. Uso: scripts/git-setup.sh   (dentro do repositório já iniciado)
set -e
git rev-parse --git-dir >/dev/null 2>&1 || { git init -b main && git add -A && git commit -m "chore: versão inicial do ESTAGIA+"; }
git branch -M main
git branch develop 2>/dev/null || true
for b in architecture security database-company backend-core design-system notifications backend-auth backend-jobs frontend-company frontend-admin access-control frontend-jobs tv-mode qa; do
  git branch "feature/$b" develop 2>/dev/null || true
done
echo "Branches criadas:"; git branch --list
echo "Proteja main e develop no GitHub/GitLab (PR + 1 aprovação + testes)."
```