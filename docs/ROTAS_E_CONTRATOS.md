# Rotas, dados e contratos (o que o front espera, o que o back entrega)

Todas as rotas são `index.php?p=<rota>` (ou `/vaga/ID` com `PRETTY_URLS=1`). POST exige o campo `_csrf` (gerado por `csrf_field()`).

## Público
| Rota | Método | Entrega | Dados da view |
|---|---|---|---|
| `home` | GET | vagas no ar + carrosséis | `$vagas`, `$destaques`, `$trilhos`, `$areas`, `$tipos`, `$modalidades` |
| `vaga&id=N` | GET | detalhe: **publicada, expirada ou encerrada** (histórico). Em análise/rejeitada/agendada → 410 | `$vaga` (+`situacao`), `$aberta`, `$relacionadas` |
| `encerradas` | GET | vagas expiradas/encerradas (120 dias) | `$vagas` |
| `empresas` | GET | parceiras aprovadas (recentes, frequentes, com vagas) | `$trilhos`, `$todas` |
| `empresa&id=N` | GET | perfil público (só aprovadas) | `$e`, `$vagas` |
| `externo&id=N` | GET | aviso "saindo do sistema"; destino vem do banco | `$e`, `$destino` |
| `tv` | GET | slides com QR por vaga | `$vagas` (QR = `url_vaga_abs(id)`) |

## Autenticação
| Rota | Método | Campos | Resultado |
|---|---|---|---|
| `empresa-login` / `admin-login` | GET/POST | `email`, `senha`, `next` | redireciona p/ `painel` / `admin`; erro genérico (não revela se o e-mail existe) |
| `sair` | POST | `perfil` | encerra só o perfil conectado |
| `empresa-cadastro` | GET/POST (multipart) | ver abaixo | cria empresa `pendente`, loga, vai para `empresa-concluido` |

**Cadastro da empresa** (`EmpresaForm::validar`): `cnpj` (alfanumérico), `razao_social`, `nome`, `segmento`, `porte`, `descricao` (30–600), `site`, `linkedin`, `link_principal` (site|linkedin), `logo` (arquivo ≤2 MB), `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `responsavel`, `cargo`, `telefone`, `celular`, `email_contato`, `email`, `senha`, `senha2`, `termo` (arquivo), `aceite_termos`, `aceite_privacidade`.

## Empresa (exige login de empresa; sempre filtrado pela empresa logada)
| Rota | Método | Observação |
|---|---|---|
| `painel` | GET | `$cont` (contagens por situação), `$recentes`, `$vencendo`, `$aviso` |
| `empresa-vagas&aba=&q=&pg=` | GET | abas: `todas aguardando publicada agendada destaque expirada encerrada rejeitada` |
| `empresa-vaga&id=` | GET | 404 se não for da empresa |
| `empresa-perfil` | GET/POST | editar perfil; `acao=reenviar` reenvia cadastro reprovado |
| `vaga-cadastro` / `vaga-editar&id=` | GET/POST | 8 etapas; edição de vaga aprovada → volta para `aguardando_aprovacao` (versão+1) |
| `vaga-encerrar` | POST `id` | só vaga `aprovada`; nada é excluído |
| `aviso-dispensar` | POST | dispensa o aviso "empresa aprovada" |

**Vaga** (`VagaForm::validar`): `tipo`, `titulo`, `area` (+`area_outra`), `modalidade`, `local`, `descricao`, `requisitos` (obrigatório, 1 por linha), `requisitos_desejaveis`, `atividades`, `beneficios`, `remuneracao_tipo` (valor|combinar), `bolsa` (só número), `carga_horas_dia` (1–12), `dias[]`, `carga_turno`, `data_publicacao`, `data_limite`, `link_plataforma`, `link_candidatura`, `link_site`, `link_linkedin`, `contato` (e-mail; obrigatório se não houver link), `banner` (arquivo).

## Administração (exige login de admin)
| Rota | Método | Observação |
|---|---|---|
| `admin` | GET | dashboard (dados reais) |
| `admin-aprovacoes` | GET | fila de empresas e vagas |
| `admin-vagas&aba=&q=&tipo=&ord=&vista=&pg=` | GET | abas por situação; `vista=cards|tabela` |
| `admin-vaga&id=` / `admin-empresa&id=` | GET | detalhe + histórico |
| `admin-empresas&aba=` | GET | abas: `pendentes aprovadas ativas com_vagas sem_vagas bloqueadas reprovadas todas` |
| `admin-usuarios`, `admin-relatorios` | GET | gestão de admins, CSV |
| `admin-acao` | POST | `tipo` (empresa/vaga/admin/notificacoes), `acao`, `id`, `motivo`, `ret`, `ret_id` |
| `admin-termo&id=` | GET | arquivo do termo (fora da web) |
| `admin-exportar&tipo=vagas|empresas` | GET | CSV `;` com BOM |

`admin-acao` — ações: empresa `aprovar|reprovar|bloquear|desbloquear`; vaga `aprovar|rejeitar|encerrar|destaque`; admin `criar|ativar|desativar`. Reprovar/rejeitar/bloquear exigem `motivo` ≥ 5 caracteres (enviado por e-mail).

## Estruturas devolvidas pelos Models (arrays)
- `Vaga::porId/listar/publicas` → vaga com: campos da tabela + `empresa[]` (id, nome, logo, segmento, status…), `resumo` (3 primeiras linhas), `bolsa_txt`, `carga_txt`, `situacao`, `dias` (até a data limite), listas (`requisitos`, `requisitos_desejaveis`, `beneficios`, `atividades`), `selos`.
- `Vaga::contagens([empresaId])` → `['todas','aguardando','agendada','publicada','destaque','expirada','encerrada','rejeitada']` (inteiros). **Invariante testada:** `todas = soma das situações`.
- `Empresa::listarAdmin/parceiras/comContagem` → empresa + `total_vagas`, `vagas_abertas`.
- `Stats::*` → números do dashboard e do controle de acesso (sempre do banco).
