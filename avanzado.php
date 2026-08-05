<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/users.php';
require_once 'includes/permisos.php';
require_once 'includes/actividad_log.php';
require_once 'includes/paginacion.php';

requerirSuperadmin();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];
$mensaje = null;
$error = isset($_GET['error']) ? (string) $_GET['error'] : null;

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'usuarios';
$pestañasPermitidas = ['usuarios', 'permisos', 'logs'];

if (!in_array($pestaña, $pestañasPermitidas, true)) {
    $pestaña = 'usuarios';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim((string) ($_POST['accion'] ?? ''));

    if ($accion === 'crear_usuario') {
        $resultado = crearUsuario(
            $_POST['usuario'] ?? '',
            $_POST['clave'] ?? '',
            $_POST['nombre'] ?? '',
            $_POST['rol'] ?? ''
        );

        if ($resultado['exito']) {
            $detalleUsuario = 'Crear usuario · ' . trim((string) ($_POST['usuario'] ?? ''));
            salirConActividad('avanzado.php?pestaña=usuarios&ok=1', 'crear_usuario', 0, $detalleUsuario);
        }

        header('Location: avanzado.php?pestaña=usuarios&error=' . urlencode($resultado['mensaje']));
        exit;
    }

    if ($pestaña === 'logs' || in_array($accion, ['limpiar_actividad_filtrada', 'limpiar_actividad_todo'], true)) {
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
                header('Location: avanzado.php?pestaña=logs&ok=1&limpiados=' . $eliminados);
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
                header('Location: avanzado.php?pestaña=logs&ok=1&limpiados=' . $eliminados);
                exit;
            }
        } catch (PDOException $e) {
            header('Location: avanzado.php?pestaña=logs&error=' . urlencode('No se pudo limpiar el log de actividad.'));
            exit;
        }
    }
}

if (isset($_GET['ok'])) {
    if ($pestaña === 'permisos') {
        $mensaje = 'Permisos actualizados correctamente.';
    } elseif ($pestaña === 'logs') {
        $mensaje = isset($_GET['limpiados'])
            ? ('Se eliminaron ' . (int) $_GET['limpiados'] . ' registro(s) del log.')
            : 'Operación realizada correctamente.';
    } elseif (isset($_GET['clave'])) {
        $mensaje = 'Contraseña actualizada correctamente.';
    } else {
        $mensaje = 'Usuario creado correctamente.';
    }
}

$usuarios = obtenerTodosUsuarios();
$etiquetasRoles = obtenerEtiquetasRoles();
$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$catalogoPermisos = obtenerCatalogoPermisosDetallados();
$rolesPermisos = obtenerRolesConfigurablesPermisos();
$matrizPermisos = cargarMatrizPermisosRoles();
$rolPermisosActivo = isset($_GET['rol']) ? trim((string) $_GET['rol']) : ROL_ADMIN;

if (!array_key_exists($rolPermisosActivo, $rolesPermisos)) {
    $rolPermisosActivo = ROL_ADMIN;
}

$permisosActivosRol = normalizarPermisosParaUi($matrizPermisos[$rolPermisosActivo] ?? []);

$filtrosLog = parsearFiltrosActividad($_GET);
$pagina = parsearPaginaRegistros($_GET);
$errorBdLog = null;
$registrosLog = [];
$totalRegistrosLog = 0;
$totalPaginasLog = 1;
$offsetRegistrosLog = 0;

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);

    if ($pestaña === 'logs') {
        $totalRegistrosLog = contarActividadLog($filtrosLog);
        $pagina = ajustarPaginaRegistros($pagina, $totalRegistrosLog);
        $offsetRegistrosLog = calcularOffsetRegistros($pagina);
        $totalPaginasLog = calcularTotalPaginasRegistros($totalRegistrosLog);
        $registrosLog = buscarActividadLog($filtrosLog, REGISTROS_POR_PAGINA, $offsetRegistrosLog);
    }
} catch (PDOException $e) {
    $estadisticas = [];
    if ($pestaña === 'logs') {
        $errorBdLog = 'No se pudo cargar el log de actividad. Usa «Crear tablas» en el login si aún no existen.';
    }
}

view('avanzado/index', [
    'tituloPagina'           => 'Avanzado',
    'usuario'                => $usuario,
    'seccionActiva'          => 'avanzado',
    'seccion'                => '',
    'usuarios'               => $usuarios,
    'etiquetasRoles'         => $etiquetasRoles,
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'estadisticas'           => $estadisticas ?? [],
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'pestaña'                => $pestaña,
    'puedeEliminar'          => true,
    'puedeGestionarUsuarios' => true,
    'catalogoPermisos'       => $catalogoPermisos,
    'rolesPermisos'          => $rolesPermisos,
    'rolPermisosActivo'      => $rolPermisosActivo,
    'permisosActivosRol'     => $permisosActivosRol,
    'filtrosLog'             => $filtrosLog,
    'registrosLog'           => $registrosLog,
    'totalRegistrosLog'      => $totalRegistrosLog,
    'paginaActual'           => $pagina,
    'totalPaginasLog'        => $totalPaginasLog,
    'offsetRegistrosLog'     => $offsetRegistrosLog,
    'errorBdLog'             => $errorBdLog,
    'etiquetasAcciones'      => obtenerEtiquetasAccionesActividad(),
    'etiquetasSeccionesLog'  => obtenerEtiquetasSeccionesActividad(),
    'archivoPagina'          => 'avanzado.php',
], 'app');
