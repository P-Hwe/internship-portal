<?php

class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); // @phpstan-ignore return.type
    }
    /** Exige token válido em todo POST. */
    public static function exigir(): void
    {
        $t = $_POST['_csrf'] ?? '';
        if (!is_string($t) || !hash_equals(self::token(), $t)) {
            http_response_code(419);
            exit('Sessão expirada. Volte, recarregue a página e tente novamente.');
        }
    }
}
