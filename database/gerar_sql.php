<?php
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
