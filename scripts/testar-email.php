<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../config.php';
$para = $argv[1] ?? '';
if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php scripts/testar-email.php destino@exemplo.com [--fluxos]\n");
    exit(1);
}
$driver = Mailer::modo();
echo "Configuração lida do .env\n";
foreach (['APP_URL', 'MAIL_DRIVER', 'MAIL_FROM', 'MAIL_FROM_NAME', 'MAIL_REPLY_TO', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_SECURE', 'SMTP_USER', 'ADMIN_NOTIFY_EMAIL'] as $k) echo str_pad($k, 20) . (env($k) ?: '(vazio)') . "\n";
echo str_pad('SMTP_PASS', 20) . (env('SMTP_PASS') ? '(preenchida)' : '(vazia)') . "\n\n";
if ($driver === 'log') echo "ATENÇÃO: MAIL_DRIVER=log não envia nada. As mensagens só são gravadas em storage/mail/.\n\n";
if ($driver === 'smtp' && strcasecmp((string) env('MAIL_FROM'), (string) env('SMTP_USER')) !== 0) echo "Aviso: MAIL_FROM é diferente de SMTP_USER; muitos servidores recusam ou mandam para o spam.\n\n";
$ok = Mailer::enviar($para, 'Teste de e-mail - ESTAGIA+', Mailer::layout('Teste de envio', '<p>Se você leu esta mensagem, o envio de e-mail do ESTAGIA+ está funcionando neste ambiente.</p><p>Ambiente: ' . e((string) env('APP_URL')) . '</p>', ['Abrir o site', url_abs('home')]));
echo $ok ? "Mensagem de teste " . ($driver === 'log' ? 'gravada em storage/mail/' : 'enviada para ' . $para) . ".\n" : "FALHOU: " . Mailer::$ultimoErro . "\n";
if ($ok && in_array('--fluxos', $argv, true)) {
    $emp = ['id' => 0, 'nome' => 'Empresa de Teste', 'cnpj' => '11222333000181', 'responsavel' => 'Fulana de Teste', 'email' => $para, 'telefone' => '11900001234', 'status' => 'aprovada', 'motivo' => 'Texto de exemplo do motivo.'];
    $vaga = ['id' => 0, 'titulo' => 'Estágio de Teste', 'tipo' => 'Estágio', 'area' => 'Tecnologia da Informação', 'codigo' => 'VG-TESTE', 'status' => 'aprovada', 'motivo' => 'Texto de exemplo do motivo.', 'data_publicacao' => date('Y-m-d'), 'data_limite' => date('Y-m-d', strtotime('+30 days'))];
    Notificador::empresaCadastrada($emp);
    Notificador::empresaAvaliada($emp);
    Notificador::empresaAvaliada(['status' => 'reprovada'] + $emp);
    Notificador::empresaAvaliada(['status' => 'bloqueada'] + $emp);
    Notificador::vagaEnviada($vaga, $emp);
    Notificador::vagaReenviada($vaga, $emp);
    Notificador::vagaAvaliada($vaga, $emp);
    Notificador::vagaAvaliada(['status' => 'rejeitada'] + $vaga, $emp);
    Notificador::vagaAvaliada(['status' => 'encerrada'] + $vaga, $emp);
    Notificador::vagaVencendo($vaga, $emp);
    Notificador::vagaEncerrada($vaga, $emp);
    Database::run('DELETE FROM notificacoes WHERE mensagem LIKE :m OR mensagem LIKE :v', ['m' => '%Empresa de Teste%', 'v' => '%Estágio de Teste%']);   // não deixa avisos de teste no sino do painel
    echo "Exemplos dos avisos enviados (os de nova empresa/vaga vão para ADMIN_NOTIFY_EMAIL; os demais para $para). Falhas: " . Mailer::$falhas . (Mailer::$ultimoErro ? ' (' . Mailer::$ultimoErro . ')' : '') . "\n";
    echo "\n";
}
echo "\nÚltimos registros (tabela emails_log):\n";
foreach (Mailer::recentes(5) as $l) echo "  {$l['criado_em']}  {$l['status']}  {$l['destinatario']}  {$l['assunto']}" . ($l['erro'] ? "  [{$l['erro']}]" : '') . "\n";
exit($ok ? 0 : 1);
