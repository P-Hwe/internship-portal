<?php

class Notificacao
{
    public static function naoLidas(): int
    {
        return (int) Database::val('SELECT COUNT(*) FROM notificacoes WHERE lida = 0');
    }
    public static function recentes(int $n = 8): array
    {
        return Database::all('SELECT * FROM notificacoes ORDER BY id DESC LIMIT ' . (int) $n);
    }
    public static function marcarLidas(): void
    {
        Database::run('UPDATE notificacoes SET lida = 1 WHERE lida = 0');
    }
}
