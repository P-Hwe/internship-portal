<?php
/**
 * Volta o portal ao estado de recém-instalado, guardando um backup antes.
 *
 *   php database/reset.php                         mostra o que seria apagado (não muda nada)
 *   php database/reset.php --confirmar=APAGAR-TUDO faz o backup, apaga os dados e os arquivos enviados
 *                                                  e recria o administrador principal (ADMIN_EMAIL),
 *                                                  que recebe o e-mail de primeiro acesso.
 *
 * Mantém a estrutura das tabelas, o controle de migrações e a lista de áreas.
 * O backup fica em storage/backups (pasta bloqueada para a internet).
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/schema.php';
migrar();

const KEEP_TABLES = ['migracoes', 'areas'];
const WIPE_TABLES = ['login_tentativas', 'senha_resets', 'visitas', 'emails_log', 'notificacoes', 'acessos_log', 'historico', 'vagas', 'empresa_usuarios', 'empresas', 'admins'];
const FILE_DIRS   = ['/public/uploads/logos', '/public/uploads/capas', '/public/uploads/banners', '/storage/termos', '/storage/convenios', '/storage/mail'];

$confirmed = in_array('--confirmar=APAGAR-TUDO', $argv ?? [], true);
$adminEmail = mb_strtolower(trim((string) env('ADMIN_EMAIL', '')));
$driver = Database::pdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

$files = function (): array {
    $list = [];
    foreach (FILE_DIRS as $dir) {
        foreach (glob(ROOT_PATH . $dir . '/*') ?: [] as $f) if (is_file($f) && basename($f) !== '.gitkeep') $list[] = $f;
    }
    return $list;
};

echo 'Banco: ' . Database::val($driver === 'mysql' ? 'SELECT DATABASE()' : "SELECT 'sqlite'") . " ($driver)\n";
foreach (WIPE_TABLES as $t) if (tabela_existe($t)) printf("  %-18s %d registro(s)\n", $t, (int) Database::val("SELECT COUNT(*) FROM $t"));
printf("  %-18s %d arquivo(s)\n", 'arquivos enviados', count($files()));
echo 'Administrador principal depois do reset: ' . ($adminEmail ?: '(ADMIN_EMAIL vazio no .env)') . "\n";
if (!$confirmed) exit("\nNada foi alterado. Para apagar de verdade: php database/reset.php --confirmar=APAGAR-TUDO\n");
if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) exit("Defina ADMIN_EMAIL no .env antes do reset.\n");

// 1. backup do banco (SQL) e dos arquivos (zip)
$stamp = date('Ymd-His');
$dir = ROOT_PATH . '/storage/backups';
if (!is_dir($dir)) mkdir($dir, 0750, true);
$sqlFile = "$dir/banco-$stamp.sql";
$out = fopen($sqlFile, 'w');
fwrite($out, "-- Backup do ESTAGIA+ em " . date('c') . "\nSET FOREIGN_KEY_CHECKS=0;\n");
$pdo = Database::pdo();
foreach (array_merge(KEEP_TABLES, WIPE_TABLES) as $t) {
    if (!tabela_existe($t)) continue;
    if ($driver === 'mysql') fwrite($out, "\nDROP TABLE IF EXISTS `$t`;\n" . Database::one("SHOW CREATE TABLE `$t`")['Create Table'] . ";\n");
    foreach ($pdo->query("SELECT * FROM $t", PDO::FETCH_ASSOC) as $row) {
        $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row));
        fwrite($out, "INSERT INTO `$t` (`" . implode('`,`', array_keys($row)) . '`) VALUES (' . implode(',', $vals) . ");\n");
    }
}
fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($out);
echo "Backup do banco: storage/backups/" . basename($sqlFile) . ' (' . round(filesize($sqlFile) / 1024) . " KB)\n";

$uploaded = $files();
if ($uploaded && class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    $zipFile = "$dir/arquivos-$stamp.zip";
    if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
        foreach ($uploaded as $f) $zip->addFile($f, ltrim(str_replace(chr(92), '/', substr($f, strlen(ROOT_PATH))), '/'));
        $zip->close();
        echo "Backup dos arquivos: storage/backups/" . basename($zipFile) . ' (' . count($uploaded) . " arquivo(s))\n";
    }
} elseif ($uploaded) {
    // sem a extensão zip: copia os arquivos para uma pasta com a mesma estrutura
    $copyDir = "$dir/arquivos-$stamp";
    foreach ($uploaded as $f) {
        $dest = $copyDir . substr($f, strlen(ROOT_PATH));
        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0750, true);
        if (!copy($f, $dest)) exit("Não foi possível copiar $f para o backup. Nada foi apagado.\n");
    }
    echo 'Backup dos arquivos: storage/backups/' . basename($copyDir) . '/ (' . count($uploaded) . " arquivo(s))\n";
}

// 2. apaga os dados (a estrutura e as migrações ficam)
if ($driver === 'mysql') Database::run('SET FOREIGN_KEY_CHECKS=0');
foreach (WIPE_TABLES as $t) {
    if (!tabela_existe($t)) continue;
    $driver === 'mysql' ? Database::run("TRUNCATE TABLE `$t`") : Database::run("DELETE FROM $t");
}
if ($driver === 'mysql') Database::run('SET FOREIGN_KEY_CHECKS=1');
foreach ($uploaded as $f) @unlink($f);
if (is_file(ROOT_PATH . '/storage/erro.log')) file_put_contents(ROOT_PATH . '/storage/erro.log', '');
echo "Dados e arquivos de teste apagados.\n";

// 3. administrador principal como recém-instalado: senha provisória por e-mail e link de primeiro acesso
[, , $mailed] = Admin::provisionRoot($adminEmail);
echo $mailed
    ? "Administrador principal recriado: $adminEmail. O e-mail de primeiro acesso foi enviado (link válido por 72 horas).\n"
    : "Administrador principal recriado: $adminEmail, mas o e-mail NÃO foi enviado (" . (Mailer::$lastError ?: 'erro desconhecido') . "). Corrija o SMTP e use \"Esqueci a senha\" na tela de login da Coordenação.\n";
