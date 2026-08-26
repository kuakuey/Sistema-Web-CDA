<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/eventos.php';
require_once 'includes/detalle_registro.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = (string) ($usuario['rol'] ?? '');

if (!puedeUsarCheckoutEventos($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

$eventoId = isset($_GET['evento_id']) ? max(0, (int) $_GET['evento_id']) : 0;
$numeracion = isset($_GET['numeracion']) ? trim((string) $_GET['numeracion']) : '';
$consultado = isset($_GET['consultar']);
$error = null;
$errorBd = null;
$eventos = [];
$registros = [];

if ($consultado) {
    if ($eventoId <= 0) {
        $error = 'Selecciona un evento.';
    } elseif ($numeracion === '') {
        $error = 'Ingresa la numeración del ticket.';
    }
}

try {
    $eventos = obtenerEventos();

    if ($error === null && $consultado && $eventoId > 0 && $numeracion !== '') {
        $eventoSeleccionado = obtenerEvento($eventoId);

        if (!$eventoSeleccionado) {
            $error = 'Evento no encontrado.';
        } else {
            $registros = buscarRegistrosEventoPorNumeracion($eventoId, $numeracion);
        }
    }
} catch (PDOException $e) {
    $errorBd = 'No se pudieron cargar los datos. Intenta de nuevo.';
}

view('checkout/index', [
    'tituloPagina' => 'Checkout',
    'eventos'      => $eventos,
    'eventoId'     => $eventoId,
    'numeracion'   => $numeracion,
    'consultado'   => $consultado && $error === null && $errorBd === null,
    'registros'    => $registros,
    'error'        => $error,
    'errorBd'      => $errorBd,
    'scriptsPagina'=> ['js/checkout.js'],
], 'blank');
