<?php

require_once 'includes/auth.php';
require_once 'includes/roles.php';
require_once 'includes/informes.php';
require_once 'includes/eventos.php';
require_once 'includes/informe_pdf.php';
require_once 'includes/informe_excel.php';
require_once 'includes/actividad_log.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = (string) ($usuario['rol'] ?? '');

if (!puedeVerInformeEventos($rol) && !puedeGenerarInforme($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;
$formato = isset($_GET['formato']) ? trim((string) $_GET['formato']) : 'pdf';
$fechaDesde = isset($_GET['fecha_desde']) ? trim((string) $_GET['fecha_desde']) : '';
$fechaHasta = isset($_GET['fecha_hasta']) ? trim((string) $_GET['fecha_hasta']) : '';
$turno = isset($_GET['turno']) ? trim((string) $_GET['turno']) : 'todos';
$redireccion = 'eventos.php?pestaña=catalogo';

if ($eventoId > 0) {
    $redireccion = 'eventos.php?pestaña=participantes&evento_id=' . $eventoId;
}

try {
    if ($eventoId <= 0) {
        throw new InvalidArgumentException('Selecciona un evento válido.');
    }

    if (!obtenerEvento($eventoId)) {
        throw new InvalidArgumentException('Evento no encontrado.');
    }

    $informe = generarInformeEvento($eventoId, $fechaDesde, $fechaHasta, $turno);
    $informe['seccion_exportacion'] = 'eventos';
    $formato = normalizarFormatoInforme($formato);

    registrarActividad(
        'generar_informe',
        'eventos',
        'informe',
        $eventoId,
        'Informe de evento · ' . (string) ($informe['evento']['nombre'] ?? '') . ' · ' . $formato,
        $usuario
    );

    if ($formato === 'excel') {
        enviarInformeExcel($informe, 'eventos');
    } else {
        enviarInformePdf($informe, 'eventos');
    }
} catch (InvalidArgumentException $e) {
    header('Location: ' . $redireccion . '&error=' . urlencode($e->getMessage()));
    exit;
} catch (RuntimeException $e) {
    header('Location: ' . $redireccion . '&error=' . urlencode($e->getMessage()));
    exit;
} catch (PDOException $e) {
    header('Location: ' . $redireccion . '&error=' . urlencode('No se pudo generar el informe del evento.'));
    exit;
}
