# Estrutura de Branches do Projeto

## 📋 Visão Geral

Este repositório segue a estratégia de branching para separar código de produção de código de desenvolvimento e testes.

## 🌿 Branches Principais

### `main` (Production)
- **Propósito**: Código de produção estável e testado
- **Contém**: Apenas o código "cru" pronto para uso em produção
- **Não contém**: Testes, documentação interna, dados de seed, arquivos de IDE
- **Push**: Apenas commits aprovados e testados

### `develop` (Desenvolvimento)
- **Propósito**: Ramo principal de desenvolvimento
- **Contém**: Código em desenvolvimento + testes locais
- **Arquivos extras**: 
  - `tests/` (testes unitários e de integração)
  - `docs/` (documentação interna)
  - `seed/` (dados para testes)
- **Push**: Desenvolvimento ativo

### `hml` (Homologação)
- **Propósito**: Ambiente de homologação
- **Contém**: Código próximo à produção + testes
- **Uso**: Testes em ambiente similar ao produção

## 🔄 Fluxo de Trabalho

1. **Desenvolvimento**: Trabalhe em branches `feature/*` a partir de `develop`
2. **Testes Locais**: Execute testes apenas em `develop` ou `hml`
3. **Merge para main**: Apenas código validado e sem arquivos de teste
4. **Deploy**: `main` → Produção

## 📁 Arquivos que NÃO devem estar em `main`

- ❌ `tests/` - Todos os arquivos de teste
- ❌ `docs/` - Documentação interna
- ❌ `seed/` - Dados de seed para banco de teste
- ❌ `.env.test`, `.env.local` - Arquivos de ambiente não padrão
- ❌ `.idea/`, `.vscode/` - Configurações de IDE
- ❌ `.phpcs.xml.dist` - Configurações de linting (opcional)

## ✅ Checklist para Merge em `main`

- [ ] Código testado e validado
- [ ] Sem arquivos de teste
- [ ] Sem dados de seed
- [ ] Sem documentação interna
- [ ] Sem configurações de IDE
- [ ] Arquivo `.env` substituído por variáveis de ambiente
- [ ] Commit message claro e descritivo

## 🚀 Comandos Úteis

```bash
# Verificar arquivos de teste em main
git diff main..develop --name-only | grep test

# Garantir que main está limpo
git checkout main -- .
git reset --hard HEAD

# Sincronizar com remoto
git push origin main
```
