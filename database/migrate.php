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
    $f = migrar(); echo $f ? "Migrations aplicadas: " . implode(', ', $f) . "\n" : "Banco atualizado.\n";
}