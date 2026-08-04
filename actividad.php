<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/actividad_log.php';
require_once 'includes/paginacion.php';

requerirSuperadmin();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];
$mensaje = null;
$error = isset($_GET['error']) ? (string) $_GET['error'] : null;

if (isset($_GET['ok'])) {
    $mensaje = isset($_GET['limpiados'])
        ? ('Se eliminaron ' . (int) $_GET['limpiados'] . ' registro(s) del log.')
        : 'Operación realizada correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim((string) ($_POST['accion'] ?? ''));
    $filtrosPost = parsearFiltrosActividad([
        'buscar'      => $_POST['filtro_buscar'] ?? '',
        'accion'      => $_POST['filtro_accion'] ?? '',
        'seccion'     => $_POST['filtro_seccion'] ?? '',
        'fecha_desde' => $_POST['filtro_fecha_desde'] ?? '',
        'fecha_hasta' => $_POST['filtro_fecha_hasta'] ?? '',
    ]);

    try {
        if ($accion === 'limpiar_actividad_filtrada') {
            $eliminados = limpiarActividadLog($filtrosPost, true);
            registrarActividad(
                'limpiar_actividad_log',
                'actividad',
                'actividad_log',
                null,
                'Limpiar log filtrado · ' . $eliminados . ' registro(s)',
                $usuario,
                ['modo' => 'filtrados', 'eliminados' => $eliminados, 'filtros' => $filtrosPost]
            );
            header('Location: actividad.php?ok=1&limpiados=' . $eliminados);
            exit;
        }

        if ($accion === 'limpiar_actividad_todo') {
            $eliminados = limpiarActividadLog([], false);
            registrarActividad(
                'limpiar_actividad_log',
                'actividad',
                'actividad_log',
                null,
                'Limpiar todo el log · ' . $eliminados . ' registro(s)',
                $usuario,
                ['modo' => 'todo', 'eliminados' => $eliminados]
            );
            header('Location: actividad.php?ok=1&limpiados=' . $eliminados);
            exit;
        }
    } catch (PDOException $e) {
        header('Location: actividad.php?error=' . urlencode('No se pudo limpiar el log de actividad.'));
        exit;
    }

    header('Location: actividad.php');
    exit;
}

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
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'etiquetasAcciones'      => obtenerEtiquetasAccionesActividad(),
    'etiquetasSeccionesLog'  => obtenerEtiquetasSeccionesActividad(),
], 'app');
