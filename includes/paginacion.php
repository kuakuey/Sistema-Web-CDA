<?php

/** Cantidad fija de filas por página en todas las tablas de registros. */
const REGISTROS_POR_PAGINA = 20;

function obtenerRegistrosPorPagina(): int
{
    return REGISTROS_POR_PAGINA;
}

function normalizarLimiteRegistros(int $limite): int
{
    if ($limite <= 0) {
        return REGISTROS_POR_PAGINA;
    }

    return min($limite, REGISTROS_POR_PAGINA);
}

function parsearPaginaRegistros(array $entrada): int
{
    return max(1, (int) ($entrada['pagina'] ?? 1));
}

function calcularOffsetRegistros(int $pagina): int
{
    return ($pagina - 1) * REGISTROS_POR_PAGINA;
}

function calcularTotalPaginasRegistros(int $totalRegistros): int
{
    if ($totalRegistros <= 0) {
        return 1;
    }

    return (int) ceil($totalRegistros / REGISTROS_POR_PAGINA);
}

function ajustarPaginaRegistros(int $pagina, int $totalRegistros): int
{
    return min($pagina, calcularTotalPaginasRegistros($totalRegistros));
}

function calcularRangoRegistros(int $pagina, int $totalRegistros): array
{
    if ($totalRegistros === 0) {
        return ['desde' => 0, 'hasta' => 0];
    }

    $desde = calcularOffsetRegistros($pagina) + 1;
    $hasta = min($pagina * REGISTROS_POR_PAGINA, $totalRegistros);

    return ['desde' => $desde, 'hasta' => $hasta];
}
