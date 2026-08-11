<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/calendario.php';
require_once 'includes/actividad_log.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeGestionarCalendario($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$pestaña = isset($_GET['pestaña']) ? trim((string) $_GET['pestaña']) : 'calendario';
if (!in_array($pestaña, ['calendario', 'gestionar', 'nuevo'], true)) {
    $pestaña = 'calendario';
}

$periodo = parsearMesCalendario($_GET);
$anio = $periodo['anio'];
$mes = $periodo['mes'];

$mensaje = null;
if (isset($_GET['ok'])) {
    if ($pestaña === 'nuevo') {
        $mensaje = 'Evento del calendario creado correctamente.';
    } elseif ($pestaña === 'gestionar') {
        $mensaje = 'Evento del calendario actualizado correctamente.';
    } else {
        $mensaje = 'Operación realizada correctamente.';
    }
}
$error = isset($_GET['error']) ? (string) $_GET['error'] : null;
$errorBd = null;
$eventosMes = [];
$eventosTodos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        switch ($accion) {
            case 'crear_evento_calendario':
                $eventoId = crearEventoCalendario([
                    'titulo'      => $_POST['titulo'] ?? '',
                    'descripcion' => $_POST['descripcion'] ?? '',
                    'fecha'       => $_POST['fecha'] ?? '',
                    'estado'      => $_POST['estado'] ?? 'activo',
                ], $_FILES['foto'] ?? null);
                salirConActividad('calendario.php?pestaña=nuevo&ok=1', 'crear_evento_calendario', $eventoId);

            case 'actualizar_evento_calendario':
                $eventoId = (int) ($_POST['id'] ?? 0);
                actualizarEventoCalendario($eventoId, [
                    'titulo'      => $_POST['titulo'] ?? '',
                    'descripcion' => $_POST['descripcion'] ?? '',
                    'fecha'       => $_POST['fecha'] ?? '',
                    'estado'      => $_POST['estado'] ?? 'activo',
                ], $_FILES['foto'] ?? null);
                salirConActividad(
                    'calendario.php?pestaña=gestionar&ok=1&anio=' . $anio . '&mes=' . $mes,
                    'actualizar_evento_calendario',
                    $eventoId
                );
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'No se pudo guardar el evento del calendario. Intenta de nuevo.';
    }
}

try {
    $eventosMes = obtenerEventosCalendario($anio, $mes);
    $eventosTodos = obtenerEventosCalendario();
} catch (PDOException $e) {
    $errorBd = 'No se pudieron cargar los eventos del calendario. Usa «Crear tablas» en el login si aún no existen.';
}

$etiquetasRoles = obtenerEtiquetasRoles();
$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);
} catch (PDOException $e) {
    $estadisticas = [];
}

view('calendario/index', [
    'tituloPagina'           => 'Calendario',
    'usuario'                => $usuario,
    'seccionActiva'          => 'calendario',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'puedeEliminar'          => puedeEliminarRegistros($rol),
    'puedeEditar'            => puedeEditarRegistros($rol),
    'eventosMes'             => $eventosMes,
    'eventosTodos'           => $eventosTodos,
    'eventosPorFecha'        => agruparEventosCalendarioPorFecha($eventosMes),
    'anio'                   => $anio,
    'mes'                    => $mes,
    'pestaña'                => $pestaña,
    'mensaje'                => $mensaje,
    'error'                  => $error,
    'errorBd'                => $errorBd,
], 'app');
