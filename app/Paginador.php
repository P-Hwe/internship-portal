<?php

/**
 * Calcula a paginação para consultas SQL.
 *
 * @return array [offset, paginaAtual, totalPaginas]
 */
class Paginador
{
    /**
     * Calcula o offset e número total de páginas.
     *
     * @param int $total Total de registros
     * @param int $pagina Número da página atual (1-based)
     * @param int $por Quantos registros por página
     * @return array [offset, paginaAtual, totalPaginas]
     */
    public static function calc(int $total, int $pagina, int $por): array
    {
        $paginas = max(1, (int) ceil($total / $por));
        $pagina = max(1, min($pagina, $paginas));

        return [($pagina - 1) * $por, $pagina, $paginas];
    }
}
