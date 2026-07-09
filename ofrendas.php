<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/filters.php';
require_once 'includes/paginacion.php';
require_once 'includes/estructura.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeVerOfrendas($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$etiquetasRoles = obtenerEtiquetasRoles();

$filtros = parsearFiltrosRegistros($_GET);
$pagina = parsearPaginaRegistros($_GET);
$consultaFiltros = http_build_query(array_filter([
    'buscar'      => $filtros['buscar'],
    'fecha_desde' => $filtros['fecha_desde'],
    'fecha_hasta' => $filtros['fecha_hasta'],
    'monto_min'   => $filtros['monto_min'],
    'monto_max'   => $filtros['monto_max'],
]));

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'registros';
if (!in_array($pestaña, ['registros', 'nuevo'], true)) {
    $pestaña = 'registros';
}

if ($pestaña === 'nuevo' && !puedeRegistrarOfrendas($rol)) {
    $pestaña = 'registros';
}

$mensaje = null;
if (isset($_GET['ok'])) {
    $mensaje = $pestaña === 'nuevo'
        ? 'Ofrenda registrada correctamente.'
        : 'Operación realizada correctamente.';
}
if (isset($_GET['actualizado'])) {
    $mensaje = 'Registro actualizado correctamente.';
}
$error = isset($_GET['error']) ? (string) $_GET['error'] : null;
$errorBd = null;
$registros = [];
$totalRegistros = 0;
$totalPaginas = 1;
$offsetRegistros = 0;
$estructura = ['territorios' => [], 'casas' => []];

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $totalRegistros = contarOfrendasFiltradas($filtros);
    $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
    $offsetRegistros = calcularOffsetRegistros($pagina);
    $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);
    $registros = buscarOfrendas($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
    $estructura = obtenerEstructuraParaApi();
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudieron cargar los registros. Usa «Crear tablas» en el login si aún no existen.';
}

view('ofrendas/index', [
    'tituloPagina'           => 'Ofrendas',
    'usuario'                => $usuario,
    'seccionActiva'          => 'ofrendas',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'puedeEliminar'          => puedeEliminarRegistros($rol),
    'puedeEditar'            => puedeEditarRegistros($rol),
    'puedeRegistrar'         => puedeRegistrarOfrendas($rol),
    'registros'              => $registros,
    'totalRegistros'         => $totalRegistros,
    'filtros'                => $filtros,
    'consultaFiltros'        => $consultaFiltros,
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'errorBd'                => $errorBd,
    'pestaña'                => $pestaña,
    'territorios'            => $estructura['territorios'] ?? [],
    'casas'                  => $estructura['casas'] ?? [],
    'paginaActual'           => $pagina,
    'totalPaginas'           => $totalPaginas,
    'offsetRegistros'        => $offsetRegistros,
    'archivoPagina'          => 'ofrendas.php',
], 'app');
