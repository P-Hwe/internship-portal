<?php

class Upload
{
    private const EXT = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

    public static function save(?array $file, array $mimes, string $destination, bool $required, ?int $maxMb = null): array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, $required ? 'Anexe um arquivo.' : null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Falha no envio do arquivo. Tente novamente.'];
        }
        $limit = $maxMb ?? UPLOAD_MAX_MB;
        if ($file['size'] > $limit * 1048576) {
            return [null, 'Arquivo maior que ' . $limit . ' MB.'];
        }
        if (!is_uploaded_file($file['tmp_name']) && !(defined('TESTING') && TESTING)) {
            return [null, 'Arquivo inválido.'];
        }
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mimeType, $mimes, true) || !isset(self::EXT[$mimeType])) {
            return [null, 'Formato não permitido. Envie ' . implode(', ', array_map(fn($m) => strtoupper(self::EXT[$m]), $mimes)) . '.'];
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $filename = bin2hex(random_bytes(12)) . '.' . self::EXT[$mimeType];
        $success = defined('TESTING') && TESTING ? copy($file['tmp_name'], "$destination/$filename") : move_uploaded_file($file['tmp_name'], "$destination/$filename");
        return $success ? [$filename, null] : [null, 'Não foi possível salvar o arquivo.'];
    }

    public static function salvar(?array $f, array $mimes, string $dest, bool $required, ?int $maxMb = null): array
    {
        return self::save($f, $mimes, $dest, $required, $maxMb);
    }
}
