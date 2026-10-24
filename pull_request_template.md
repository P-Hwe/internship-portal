```markdown
## O que muda
<!-- 1–3 linhas. Cite o código da tarefa da planilha (ex.: 108.06). -->

## Tarefa / História
- Tarefa: `NNN.NN`   - Branch: `feature/...`   - Depende de: #

## Checklist
- [ ] `tests/run.sh` verde (e `tests/run.sh mysql` se mexeu em SQL)
- [ ] Autorização conferida **no backend** (não só ocultei o botão)
- [ ] Recursos por ID conferem a empresa dona (ownership)
- [ ] Mudou banco? Criei migration + atualizei `schema_sql()` + regenerei `database/mysql.sql`
- [ ] Mudou rota/campo? Atualizei `docs/ROTAS_E_CONTRATOS.md`
- [ ] Sem cor fixa nos componentes (modo noturno) e sem emojis
- [ ] Sem `.env`, `storage/*` ou uploads no commit
```