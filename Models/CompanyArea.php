<?php
/** EmpresaArea — gerencia áreas de atuação customizáveis por empresa */
class CompanyArea
{
    private static function table(): string
    {
        return 'empresa_areas';
    }

    /** Listar todas as áreas cadastradas */
    public static function lista(int $limite = 100): array
    {
        try {
            $sql = 'SELECT DISTINCT area FROM ' . self::table() . ' ORDER BY area ASC';
            return Database::all($sql, [], $limite);
        } catch (Throwable $t) {
            error_log($t);
            return [];
        }
    }

    /** Salvar nova área */
    public static function salvar(int $empresa_id, string $area): bool
    {
        try {
            if (empty(trim($area))) return false;

            // Verificar se já existe
            $existente = self::um($empresa_id, $area);
            if ($existente) return true;

            Database::insert(self::table(), [
                'empresa_id' => $empresa_id,
                'area' => trim($area),
            ]);

            return true;
        } catch (Throwable $t) {
            error_log($t);
            return false;
        }
    }

    /** Remover área */
    public static function remover(int $empresa_id, string $area): bool
    {
        try {
            if (empty(trim($area))) return false;

            Database::run('DELETE FROM ' . self::table() . ' WHERE empresa_id = :e AND area = :a', [
                'e' => $empresa_id,
                'a' => trim($area),
            ]);

            return true;
        } catch (Throwable $t) {
            error_log($t);
            return false;
        }
    }

    /** Buscar área por empresa e nome */
    public static function um(int $empresa_id, string $area): ?array
    {
        try {
            return Database::one('SELECT * FROM ' . self::table() . ' WHERE empresa_id = :e AND area = :a', [
                'e' => $empresa_id,
                'a' => trim($area),
            ]) ?: null;
        } catch (Throwable $t) {
            error_log($t);
            return null;
        }
    }

    /** Contar áreas por empresa */
    public static function contar(int $empresa_id): int
    {
        try {
            return (int) Database::val('SELECT COUNT(*) FROM ' . self::table() . ' WHERE empresa_id = :e', ['e' => $empresa_id]);
        } catch (Throwable $t) {
            error_log($t);
            return 0;
        }
    }

    /** Verificar se empresa tem área cadastrada */
    public static function tem(int $empresa_id, string $area): bool
    {
        try {
            return !empty(self::um($empresa_id, $area));
        } catch (Throwable $t) {
            error_log($t);
            return false;
        }
    }
}

class_alias('CompanyArea', 'EmpresaArea');
