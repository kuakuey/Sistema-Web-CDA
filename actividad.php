<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/actividad_log.php';
require_once 'includes/paginacion.php';

requerirSuperadmin();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

$filtros = parsearFiltrosActividad($_GET);
$pagina = parsearPaginaRegistros($_GET);
$errorBd = null;
$registros = [];
$totalRegistros = 0;
$totalPaginas = 1;
$offsetRegistros = 0;

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $totalRegistros = contarActividadLog($filtros);
    $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
    $offsetRegistros = calcularOffsetRegistros($pagina);
    $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);
    $registros = buscarActividadLog($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudo cargar el log de actividad. Usa «Crear tablas» en el login si aún no existen.';
}

view('actividad/index', [
    'tituloPagina'           => 'Log de actividad',
    'usuario'                => $usuario,
    'seccionActiva'          => 'actividad',
    'seccion'                => '',
    'seccionesPermitidas'    => obtenerSeccionesPermitidas($rol),
    'etiquetasSecciones'     => obtenerEtiquetasSecciones(),
    'etiquetasRoles'         => obtenerEtiquetasRoles(),
    'estadisticas'           => $estadisticas ?? [],
    'filtros'                => $filtros,
    'registros'              => $registros,
    'totalRegistros'         => $totalRegistros,
    'paginaActual'           => $pagina,
    'totalPaginas'           => $totalPaginas,
    'offsetRegistros'        => $offsetRegistros,
    'errorBd'                => $errorBd,
    'etiquetasAcciones'      => obtenerEtiquetasAccionesActividad(),
    'etiquetasSeccionesLog'  => obtenerEtiquetasSeccionesActividad(),
], 'app');
