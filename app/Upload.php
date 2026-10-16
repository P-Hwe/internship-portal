<?php

class Upload
{
    private const EXT = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

    /** @return array [nomeSalvo|null, erro|null] */
    public static function salvar(?array $f, array $mimes, string $destino, bool $obrigatorio, ?int $maxMb = null): array
    {
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, $obrigatorio ? 'Anexe um arquivo.' : null];
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Falha no envio do arquivo. Tente novamente.'];
        }
        $lim = $maxMb ?? UPLOAD_MAX_MB;
        if ($f['size'] > $lim * 1048576) {
            return [null, 'Arquivo maior que ' . $lim . ' MB.'];
        }
        if (!is_uploaded_file($f['tmp_name']) && !(defined('TESTING') && TESTING)) {
            return [null, 'Arquivo inválido.'];
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!in_array($mime, $mimes, true) || !isset(self::EXT[$mime])) {
            return [null, 'Formato não permitido. Envie ' . implode(', ', array_map(fn($m) => strtoupper(self::EXT[$m]), $mimes)) . '.'];
        }
        if (!is_dir($destino)) {
            mkdir($destino, 0755, true);
        }
        $nome = bin2hex(random_bytes(12)) . '.' . self::EXT[$mime];
        $ok = defined('TESTING') && TESTING ? copy($f['tmp_name'], "$destino/$nome") : move_uploaded_file($f['tmp_name'], "$destino/$nome");
        return $ok ? [$nome, null] : [null, 'Não foi possível salvar o arquivo.'];
    }
}
