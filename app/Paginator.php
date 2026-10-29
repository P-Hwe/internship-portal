<?php
class Paginator
{
    /** @return array [offset, currentPage, totalPages] */
    public static function calc(int $total, int $page, int $perPage): array
    {
        $totalPages  = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($page, $totalPages));
        return [($currentPage - 1) * $perPage, $currentPage, $totalPages];
    }
}
