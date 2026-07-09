<?php

/**
 * URLs de cada sección del panel (archivo .php dedicado, sin ?seccion=).
 */
function obtenerMapaUrlsSecciones(): array
{
    return [
        'generales'      => 'registros-generales.php',
        'conexion'       => 'conexion.php',
        'escol'          => 'escol.php',
        'academia'       => 'academia.php',
        'bautismo'       => 'bautismo.php',
        'presentaciones' => 'presentacion-ninos.php',
    ];
}

function obtenerUrlSeccion(string $seccion): string
{
    $mapa = obtenerMapaUrlsSecciones();

    return $mapa[$seccion] ?? 'registros-generales.php';
}

function obtenerSeccionDesdeArchivo(string $archivo): ?string
{
    $nombre = basename($archivo);

    foreach (obtenerMapaUrlsSecciones() as $seccion => $ruta) {
        if ($ruta === $nombre) {
            return $seccion;
        }
    }

    return null;
}

function obtenerUrlInicioPorRol(string $rol): string
{
    $secciones = obtenerSeccionesPermitidas($rol);
    $primera = $secciones[0] ?? 'generales';

    return obtenerUrlSeccion($primera);
}

function construirUrlConFiltros(string $archivo, array $filtros): string
{
    $consulta = construirConsultaFiltros($filtros);

    return $consulta !== '' ? $archivo . '?' . $consulta : $archivo;
}

function construirUrlRegistros(string $archivo, array $filtros, int $pagina = 1, string $pestaña = 'registros'): string
{
    $parametros = ['pestaña' => $pestaña];

    foreach ($filtros as $clave => $valor) {
        if ($valor !== '' && $valor !== 'todos') {
            $parametros[$clave] = $valor;
        }
    }

    if ($pagina > 1) {
        $parametros['pagina'] = $pagina;
    }

    return $archivo . '?' . http_build_query($parametros);
}
