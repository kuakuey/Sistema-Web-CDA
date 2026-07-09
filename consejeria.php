<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/filters.php';
require_once 'includes/paginacion.php';
require_once 'includes/consejerias.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeVerConsejerias($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$etiquetasRoles = obtenerEtiquetasRoles();
$tiposConsejeria = obtenerTiposConsejeria();

$filtros = parsearFiltrosRegistros($_GET);
$pagina = parsearPaginaRegistros($_GET);
$consultaFiltros = http_build_query(array_filter([
    'buscar'           => $filtros['buscar'],
    'fecha_desde'      => $filtros['fecha_desde'],
    'fecha_hasta'      => $filtros['fecha_hasta'],
    'tipo_consejeria'  => $filtros['tipo_consejeria'],
]));

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'registros';
if (!in_array($pestaña, ['registros', 'nuevo'], true)) {
    $pestaña = 'registros';
}

if ($pestaña === 'nuevo' && !puedeRegistrarConsejerias($rol)) {
    $pestaña = 'registros';
}

$mensaje = null;
if (isset($_GET['ok'])) {
    if (isset($_GET['asignacion'])) {
        $mensaje = 'Cita asignada correctamente.';
    } elseif ($pestaña === 'nuevo') {
        $mensaje = 'Solicitud de consejería registrada correctamente.';
    } else {
        $mensaje = 'Operación realizada correctamente.';
    }
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

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $totalRegistros = contarConsejeriasFiltradas($filtros);
    $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
    $offsetRegistros = calcularOffsetRegistros($pagina);
    $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);
    $registros = buscarConsejerias($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudieron cargar los registros. Usa «Crear tablas» en el login si aún no existen.';
}

view('consejeria/index', [
    'tituloPagina'           => 'Consejería',
    'usuario'                => $usuario,
    'seccionActiva'          => 'consejeria',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'puedeEliminar'          => puedeEliminarRegistros($rol),
    'puedeEditar'            => puedeEditarRegistros($rol),
    'puedeAsignar'           => puedeAsignarCitaConsejeria($rol),
    'puedeRegistrar'         => puedeRegistrarConsejerias($rol),
    'tiposConsejeria'        => $tiposConsejeria,
    'registros'              => $registros,
    'totalRegistros'         => $totalRegistros,
    'filtros'                => $filtros,
    'consultaFiltros'        => $consultaFiltros,
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'errorBd'                => $errorBd,
    'pestaña'                => $pestaña,
    'paginaActual'           => $pagina,
    'totalPaginas'           => $totalPaginas,
    'offsetRegistros'        => $offsetRegistros,
    'archivoPagina'          => 'consejeria.php',
], 'app');
