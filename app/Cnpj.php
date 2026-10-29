<?php
/**
 * Cnpj — validação de CNPJ numérico e alfanumérico (IN RFB 2.229/2024).
 * Classe mantém o nome Cnpj (sigla técnica brasileira, como CPF/CEP).
 * Method names in English; PT aliases kept for backwards compatibility.
 */
class Cnpj
{
    public static function clean(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }

    public static function format(string $value): string
    {
        $value = self::clean($value);
        if (strlen($value) !== 14) return $value;
        return substr($value, 0, 2) . '.' . substr($value, 2, 3) . '.' . substr($value, 5, 3) . '/' . substr($value, 8, 4) . '-' . substr($value, 12, 2);
    }

    private static function checkDigit(string $base): int
    {
        $length  = strlen($base);
        $weights = [];
        for ($i = 0, $p = 2; $i < $length; $i++, $p = $p === 9 ? 2 : $p + 1) {
            $weights[$length - 1 - $i] = $p;
        }
        $sum = 0;
        for ($i = 0; $i < $length; $i++) {
            $sum += (ord($base[$i]) - 48) * $weights[$i];
        }
        $r = $sum % 11;
        return $r < 2 ? 0 : 11 - $r;
    }

    public static function isValid(string $value): bool
    {
        $c = self::clean($value);
        if (!preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $c)) return false;
        if (preg_match('/^(.)\1{13}$/', $c)) return false;
        $d1 = self::checkDigit(substr($c, 0, 12));
        $d2 = self::checkDigit(substr($c, 0, 12) . $d1);
        return $c[12] === (string) $d1 && $c[13] === (string) $d2;
    }

    public static function complete(string $base): string
    {
        $c  = self::clean($base);
        $d1 = self::checkDigit($c);
        return $c . $d1 . self::checkDigit($c . $d1);
    }

    /** @deprecated Use clean() */
    public static function limpar(string $value): string     { return self::clean($value); }
    /** @deprecated Use format() */
    public static function formatar(string $value): string   { return self::format($value); }
    /** @deprecated Use isValid() */
    public static function valido(string $value): bool       { return self::isValid($value); }
    /** @deprecated Use complete() */
    public static function completar(string $base): string   { return self::complete($base); }
}
