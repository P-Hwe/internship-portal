<?php

class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); // @phpstan-ignore return.type
    }
    public static function verify(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!is_string($token) || !hash_equals(self::token(), $token)) {
            http_response_code(419);
            exit('Sessão expirada. Volte, recarregue a página e tente novamente.');
        }
    }
    public static function exigir(): void
    {
        self::verify();
    }
}
