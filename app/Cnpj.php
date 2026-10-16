<?php

/**
 * CNPJ numérico e ALFANUMÉRICO (IN RFB 2.229/2024, em vigor a partir de julho/2026).
 * 12 primeiros caracteres: letras A-Z e dígitos 0-9; os 2 últimos são sempre dígitos verificadores.
 * Cálculo: módulo 11, pesos 2..9, valor de cada caractere = código ASCII - 48 (dígitos seguem valendo 0-9).
 * CNPJs numéricos antigos continuam válidos com o mesmo algoritmo.
 */

class Cnpj
{
    /** Remove máscara e põe em maiúsculas: "12.abc.345/01de-35" -> "12ABC34501DE35". */
    public static function limpar(string $v): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $v));
    }

    /** Formata o CNPJ no padrão XX.XXX.XXX/XXXX-XX. */
    public static function formatar(string $v): string
    {
        $v = self::limpar($v);
        if (strlen($v) !== 14) {
            return $v;
        }

        return substr($v, 0, 2) . '.' . substr($v, 2, 3) . '.' . substr($v, 5, 3) . '/' . substr($v, 8, 4) . '-' . substr($v, 12, 2);
    }

    /** Calcula o dígito verificador usando módulo 11 e pesos 2..9. */
    private static function dv(string $base): int
    {
        $n = strlen($base);
        $pesos = [];

        for ($i = 0, $p = 2; $i < $n; $i++, $p = $p === 9 ? 2 : $p + 1) {
            $pesos[$n - 1 - $i] = $p;
        }

        $soma = 0;

        for ($i = 0; $i < $n; $i++) {
            $soma += (ord($base[$i]) - 48) * $pesos[$i];
        }

        $r = $soma % 11;

        return $r < 2 ? 0 : 11 - $r;
    }

    /** Valida CNPJ numérico ou alfanumérico. */
    public static function valido(string $v): bool
    {
        $c = self::limpar($v);

        if (!preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $c)) {
            return false;
        }

        if (preg_match('/^(.)\1{13}$/', $c)) {
            return false;
        }

        $d1 = self::dv(substr($c, 0, 12));
        $d2 = self::dv(substr($c, 0, 12) . $d1);

        return $c[12] === (string) $d1 && $c[13] === (string) $d2;
    }

    /** Completa um CNPJ de 12 caracteres com os 2 dígitos verificadores. */
    public static function completar(string $base12): string
    {
        $b = self::limpar($base12);
        $d1 = self::dv($b);

        return $b . $d1 . self::dv($b . $d1);
    }
}
