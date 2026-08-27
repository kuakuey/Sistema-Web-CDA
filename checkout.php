<?php

require_once 'includes/auth.php';
require_once 'includes/view.php';
require_once 'includes/eventos.php';
require_once 'includes/detalle_registro.php';
require_once 'includes/actividad_log.php';

requerirSesion();

$usuario = obtenerUsuarioActual();
$rol = (string) ($usuario['rol'] ?? '');

if (!puedeUsarCheckoutEventos($rol)) {
    header('Location: ' . obtenerUrlInicioPorRol($rol));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');

    $accion = trim((string) ($_POST['accion'] ?? 'consultar'));

    try {
        if ($accion === 'marcar_asistencia') {
            $ticketId = max(0, (int) ($_POST['id'] ?? 0));

            if ($ticketId <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Ticket no válido.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $ticket = marcarAsistenciaRegistroEvento($ticketId, $usuario ?? []);
            registrarActividadPorAccion('marcar_asistencia_evento', $ticketId);

            echo json_encode(['ok' => true, 'ticket' => $ticket], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $eventoId = max(0, (int) ($_POST['evento_id'] ?? 0));
        $codigo = trim((string) ($_POST['codigo'] ?? $_POST['numeracion'] ?? ''));

        if ($eventoId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Selecciona un evento.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($codigo === '') {
            echo json_encode(['ok' => false, 'error' => 'Ingresa el código del ticket.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $evento = obtenerEvento($eventoId);

        if (!$evento || (int) ($evento['habilitado'] ?? 0) !== 1) {
            echo json_encode(['ok' => false, 'error' => 'Solo se puede consultar eventos habilitados.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        asegurarColumnasValoresAdicionales(getConnection());
        $registros = buscarRegistrosEventoPorNumeracion($eventoId, $codigo);

        if ($registros === []) {
            echo json_encode(['ok' => false, 'error' => 'No se encontró un ticket con ese código.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tickets = [];
        $nombres = [];

        foreach ($registros as $registro) {
            $ticket = serializarTicketCheckout($registro);
            $tickets[] = $ticket;

            if ($ticket['nombre'] !== '') {
                $nombres[] = $ticket['nombre'];
            }
        }

        echo json_encode([
            'ok'       => true,
            'repetido' => count($tickets) > 1,
            'nombres'  => $nombres,
            'tickets'  => $tickets,
        ], JSON_UNESCAPED_UNICODE);
    } catch (InvalidArgumentException $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo completar la operación. Intenta de nuevo.'], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

$errorBd = null;
$eventos = [];

try {
    asegurarColumnasValoresAdicionales(getConnection());
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
