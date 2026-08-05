<?php

require_once 'includes/auth.php';
require_once 'includes/roles.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = (string) ($usuario['rol'] ?? '');

if (!puedeVerInformeEventos($rol) && !puedeGenerarInforme($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$eventoId = isset($_GET['evento_id']) ? (int) $_GET['evento_id'] : 0;
$formato = isset($_GET['formato']) ? trim((string) $_GET['formato']) : 'pdf';

$params = [
    'generar'    => '1',
    'seccion'    => 'eventos',
    'formato'    => in_array($formato, ['pdf', 'excel'], true) ? $formato : 'pdf',
    'evento_id'  => $eventoId,
];

header('Location: generar-informe.php?' . http_build_query($params));
exit;
