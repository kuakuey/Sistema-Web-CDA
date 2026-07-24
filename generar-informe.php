<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/submissions.php';
require_once 'includes/informes.php';
require_once 'includes/informe_pdf.php';
require_once 'includes/informe_excel.php';
require_once 'includes/eventos.php';
require_once 'includes/filters.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = $usuario['rol'];

if (!puedeGenerarInforme($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$seccionesPermitidas = obtenerSeccionesPermitidas($rol);
$etiquetasSecciones = obtenerEtiquetasSecciones();
$etiquetasRoles = obtenerEtiquetasRoles();

$fechaDesde = isset($_GET['fecha_desde']) ? trim((string) $_GET['fecha_desde']) : '';
$fechaHasta = isset($_GET['fecha_hasta']) ? trim((string) $_GET['fecha_hasta']) : '';
$mostrarSinEntregar = isset($_GET['mostrar_sin_entregar']) && $_GET['mostrar_sin_entregar'] === '1';
$turno = isset($_GET['turno']) ? trim((string) $_GET['turno']) : 'todos';
$seccion = isset($_GET['seccion']) ? trim((string) $_GET['seccion']) : 'completo';
$formato = isset($_GET['formato']) ? trim((string) $_GET['formato']) : 'pdf';
$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;
$estadosPresentacionInforme = isset($_GET['estados_presentacion']) && is_array($_GET['estados_presentacion'])
    ? $_GET['estados_presentacion']
    : obtenerEstadosPresentacion();
$estadoBautismoInforme = isset($_GET['estado_bautismo']) ? trim((string) $_GET['estado_bautismo']) : 'todos';
$generar = isset($_GET['generar']);

$error = isset($_GET['error']) ? trim((string) $_GET['error']) : null;
$errorBd = null;

try {
    $estadisticas = obtenerEstadisticasPorRol($rol);

    if ($generar) {
        $seccion = normalizarSeccionInforme($seccion);

        if ($seccion !== 'ofrendas') {
            $mostrarSinEntregar = false;
        }

        if ($seccion !== 'eventos') {
            $eventoId = 0;
        } elseif ($eventoId > 0 && !obtenerEvento($eventoId)) {
            throw new InvalidArgumentException('Selecciona un evento válido.');
        }

        if ($seccion === 'presentaciones') {
            $informe = generarInformePresentaciones(
                $fechaDesde,
                $fechaHasta,
                $turno,
                $estadosPresentacionInforme
            );
        } elseif ($seccion === 'bautismos') {
            $informe = generarInformeBautismos(
                $fechaDesde,
                $fechaHasta,
                $turno,
                $estadoBautismoInforme
            );
        } else {
            $informe = generarInformeOfrendasYValores(
                $fechaDesde,
                $fechaHasta,
                $mostrarSinEntregar,
                $turno,
                'todos',
                $eventoId
            );
        }
        $informe['seccion_exportacion'] = $seccion;

        if (normalizarFormatoInforme($formato) === 'excel') {
            enviarInformeExcel($informe, $seccion);
        } else {
            enviarInformePdf($informe, $seccion);
        }
        exit;
    }
} catch (InvalidArgumentException $e) {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $error = $e->getMessage();
} catch (RuntimeException $e) {
    $estadisticas = obtenerEstadisticasPorRol($rol);
    $error = $e->getMessage();
} catch (PDOException $e) {
    $estadisticas = [];
    $errorBd = 'No se pudo generar el informe. Verifica que existan las tablas necesarias.';
}

view('informes/generar', [
    'tituloPagina'           => 'Generar informe',
    'usuario'                => $usuario,
    'seccionActiva'          => 'generar_informe',
    'seccion'                => '',
    'seccionesPermitidas'    => $seccionesPermitidas,
    'etiquetasSecciones'     => $etiquetasSecciones,
    'etiquetasRoles'         => $etiquetasRoles,
    'estadisticas'           => $estadisticas ?? [],
    'puedeGestionarUsuarios' => puedeGestionarUsuarios($rol),
    'fechaDesde'             => $fechaDesde,
    'fechaHasta'             => $fechaHasta,
    'mostrarSinEntregar'     => $mostrarSinEntregar,
    'turno'                  => normalizarTurnoInforme($turno),
    'seccion'                => normalizarSeccionInforme($seccion),
    'eventoId'               => $eventoId,
    'eventos'                => obtenerEventos(),
    'etiquetasTurno'         => obtenerEtiquetasTurnoInforme(),
    'etiquetasSeccionInforme'=> obtenerEtiquetasSeccionInforme(),
    'etiquetasEstadosPresentacion' => obtenerEtiquetasEstadosPresentacion(),
    'estadosPresentacionInforme' => normalizarEstadosPresentacionInforme($estadosPresentacionInforme),
    'etiquetasEstadoBautismoInforme' => obtenerEtiquetasEstadoBautismoInforme(),
    'estadoBautismoInforme'  => normalizarEstadoBautismoInforme($estadoBautismoInforme),
    'error'                  => $error,
    'errorBd'                => $errorBd,
], 'app');
