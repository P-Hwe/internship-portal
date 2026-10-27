<?php

class Paginador
{
    public static function calc(int $total, int $pagina, int $por): array
    {
        $paginas = max(1, (int) ceil($total / $por));
        $pagina = max(1, min($pagina, $paginas));

        return [($pagina - 1) * $por, $pagina, $paginas];
    }
}
