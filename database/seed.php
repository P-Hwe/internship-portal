<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
migrar();

$email = mb_strtolower(trim((string) env('ADMIN_EMAIL', '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit("Defina ADMIN_EMAIL no .env com o e-mail do administrador principal.\n");
$admin = Admin::findByEmail($email);
if ($admin && !((int) $admin['trocar_senha'] && !$admin['ultimo_acesso'])) {
    echo "Administrador já existe e já criou a própria senha: $email\n";
} else {
    [, $password, $mailed] = Admin::provisionRoot($email);
    echo ($admin ? 'Nova senha provisória gerada' : 'Administrador principal criado') . ": $email\n";
    echo "Senha provisória: $password (no primeiro login o sistema pede uma senha nova)\n";
    echo $mailed ? "E-mail enviado com a senha e o link \"Criar minha senha\" (vale 72 horas).\n"
                 : 'O e-mail NÃO foi enviado: ' . (Mailer::$lastError ?: 'erro desconhecido') . ". Entregue a senha acima por um canal seguro.\n";
}

echo "Dados de teste (opcional, só dev/hml): php database/demo.php\n";
