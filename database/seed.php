<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
migrar();

$email = mb_strtolower((string) env('ADMIN_EMAIL', 'admin@ifsp.local'));
$senha = (string) env('ADMIN_PASSWORD', '');
if (!Database::val('SELECT 1 FROM admins WHERE email = :e', ['e' => $email])) {
    if (strlen($senha) < 8) { $senha = bin2hex(random_bytes(6)); echo "ADMIN_PASSWORD não definida: senha temporária gerada -> $senha\n"; }
    Database::insert('admins', ['nome' => 'Coordenação', 'email' => $email, 'senha_hash' => Auth::hash($senha), 'papel' => 'raiz', 'criado_em' => Database::now()]);
    echo "Administrador criado: $email\n";
} else { echo "Administrador já existe: $email\n"; }

echo "Dados de teste (opcional, só dev/hml): php database/demo.php\n";
