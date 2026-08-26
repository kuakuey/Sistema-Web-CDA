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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');

    $eventoId = max(0, (int) ($_POST['evento_id'] ?? 0));
    $numeracion = trim((string) ($_POST['numeracion'] ?? ''));

    try {
        if ($eventoId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Selecciona un evento.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($numeracion === '') {
            echo json_encode(['ok' => false, 'error' => 'Ingresa la numeración del ticket.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $evento = obtenerEvento($eventoId);

        if (!$evento || (int) ($evento['habilitado'] ?? 0) !== 1) {
            echo json_encode(['ok' => false, 'error' => 'Solo se puede consultar eventos habilitados.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $registros = buscarRegistrosEventoPorNumeracion($eventoId, $numeracion);

        if ($registros === []) {
            echo json_encode(['ok' => false, 'error' => 'No se encontró un ticket con esa numeración.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tickets = [];
        $nombres = [];

        foreach ($registros as $registro) {
            $nombre = trim((string) ($registro['nombre'] ?? ''));
            $tipoEntrada = trim((string) ($registro['tipo_entrada'] ?? ''));
            $numeracionRegistro = trim((string) ($registro['numeracion'] ?? ''));

            $tickets[] = [
                'nombre'       => $nombre,
                'numeracion'   => $numeracionRegistro,
                'tipo_entrada' => $tipoEntrada,
                'estado'       => etiquetaEstadoPagoRegistroEvento($registro),
            ];

            if ($nombre !== '') {
                $nombres[] = $nombre;
            }
        }

        echo json_encode([
            'ok'       => true,
            'repetido' => count($tickets) > 1,
            'nombres'  => $nombres,
            'tickets'  => $tickets,
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo consultar el ticket. Intenta de nuevo.'], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

$errorBd = null;
$eventos = [];

try {
    $eventos = obtenerEventosHabilitados();
} catch (PDOException $e) {
    $errorBd = 'No se pudieron cargar los eventos. Intenta de nuevo.';
}

view('checkout/index', [
    'tituloPagina'  => 'Checkout',
    'eventos'       => $eventos,
    'errorBd'       => $errorBd,
    'scriptsPagina' => ['js/checkout.js'],
], 'blank');
