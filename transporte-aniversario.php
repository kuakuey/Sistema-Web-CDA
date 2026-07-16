<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/filters.php';
require_once 'includes/paginacion.php';
require_once 'includes/transporte_aniversario.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeVerTransporteAniversario($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$etiquetasRoles = obtenerEtiquetasRoles();

$filtros = parsearFiltrosRegistros($_GET);
$pagina = parsearPaginaRegistros($_GET);
$consultaFiltros = http_build_query(array_filter([
    'buscar'           => $filtros['buscar'],
    'fecha_desde'      => $filtros['fecha_desde'],
    'fecha_hasta'      => $filtros['fecha_hasta'],
    'tipo_transporte'  => $filtros['tipo_transporte'],
]));

$pestañasPermitidas = ['registros'];

if (puedeRegistrarTransporteAniversario($rol)) {
    $pestañasPermitidas[] = 'nuevo';
}

if (puedeVerReporteTransporteAniversario($rol)) {
    $pestañasPermitidas[] = 'reporte';
}

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'registros';

if (!in_array($pestaña, $pestañasPermitidas, true)) {
    $pestaña = $pestañasPermitidas[0];
}

$exportar = isset($_GET['exportar']) ? trim((string) $_GET['exportar']) : '';

if ($exportar !== '') {
    if (!puedeVerReporteTransporteAniversario($rol)) {
        header('Location: transporte-aniversario.php?pestaña=reporte&error=' . urlencode('No tienes permiso para descargar el reporte.'));
        exit;
    }

    try {
        require_once 'includes/informe_transporte_aniversario.php';
        $reporteExportacion = calcularAsignacionTransporteAniversario();

        if ($exportar === 'pdf') {
            enviarInformeTransporteAniversarioPdf($reporteExportacion);
            exit;
        }

        if ($exportar === 'excel') {
            enviarInformeTransporteAniversarioExcel($reporteExportacion);
            exit;
        }
    } catch (RuntimeException $e) {
        header('Location: transporte-aniversario.php?pestaña=reporte&error=' . urlencode($e->getMessage()));
        exit;
    } catch (Throwable $e) {
        header('Location: transporte-aniversario.php?pestaña=reporte&error=' . urlencode('No se pudo generar el archivo de reporte.'));
        exit;
    }

    header('Location: transporte-aniversario.php?pestaña=reporte&error=' . urlencode('Formato de exportación no válido.'));
    exit;
}

$mensaje = null;

if (isset($_GET['ok'])) {
    if ($pestaña === 'nuevo') {
        $mensaje = 'Registro de transporte guardado correctamente.';
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
$reporteAsignacion = null;

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $totalRegistros = contarTransporteAniversarioFiltradas($filtros);
    $pagina = ajustarPaginaRegistros($pagina, $totalRegistros);
    $offsetRegistros = calcularOffsetRegistros($pagina);
    $totalPaginas = calcularTotalPaginasRegistros($totalRegistros);
    $registros = buscarTransporteAniversario($filtros, REGISTROS_POR_PAGINA, $offsetRegistros);

    if ($pestaña === 'reporte' && puedeVerReporteTransporteAniversario($rol)) {
        $reporteAsignacion = calcularAsignacionTransporteAniversario();
    }
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudieron cargar los registros. Usa «Crear tablas» en el login si aún no existen.';
}

view('transporte-aniversario/index', [
    'tituloPagina'           => 'Transporte Aniversario',
    'usuario'                => $usuario,
    'seccionActiva'          => 'transporte_aniversario',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'puedeEliminar'          => puedeEliminarRegistros($rol),
    'puedeEditar'            => puedeEditarRegistros($rol),
    'puedeRegistrar'         => puedeRegistrarTransporteAniversario($rol),
    'puedeVerReporte'        => puedeVerReporteTransporteAniversario($rol),
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
    'archivoPagina'          => 'transporte-aniversario.php',
    'reporteAsignacion'      => $reporteAsignacion,
], 'app');
