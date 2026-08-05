<?php

require_once 'includes/auth.php';
require_once 'includes/roles.php';
require_once 'includes/eventos.php';
require_once 'includes/informe_evento_pdf.php';
require_once 'includes/actividad_log.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = (string) ($usuario['rol'] ?? '');

if (!puedeVerInformeEventos($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;
$redireccion = 'eventos.php?pestaña=catalogo';

if ($eventoId > 0) {
    $redireccion = 'eventos.php?pestaña=participantes&evento_id=' . $eventoId;
}

try {
    if ($eventoId <= 0) {
        throw new InvalidArgumentException('Selecciona un evento válido.');
    }

    $informe = generarInformeEvento($eventoId);

    registrarActividad(
        'generar_informe',
        'eventos',
        'informe',
        $eventoId,
        'Informe de evento · ' . (string) ($informe['evento']['nombre'] ?? ''),
        $usuario
    );

    enviarInformeEventoPdf($informe);
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
