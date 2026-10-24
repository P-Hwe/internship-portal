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
