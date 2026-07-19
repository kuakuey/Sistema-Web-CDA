<?php

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/api_sesion.php';
require_once __DIR__ . '/../includes/presentaciones_api.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$claveApi = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($claveApi === '' || $claveApi !== API_KEY) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'Clave API inválida.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    $payload = $_POST;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $accion = trim((string) ($_GET['accion'] ?? 'listar'));

        if ($accion === 'meta') {
            echo json_encode([
                'exito' => true,
                'meta'  => metaPresentacionesApi(),
            ]);
            exit;
        }

        responderListaPresentacionesPublica();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
        exit;
    }

    $accion = trim((string) ($payload['accion'] ?? ''));

    if ($accion === 'registrar' || esPayloadRegistroPresentacion($payload)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $agente = $_SERVER['HTTP_USER_AGENT'] ?? '';

        responderRegistroPresentacionesPublico($payload, [
            'ip_cliente'     => $ip,
            'agente_usuario' => $agente,
        ]);
        exit;
    }

    $token = obtenerTokenSesionDesdeRequest($payload);
    $usuarioSesion = validarTokenSesionApi($token);

    if (!$usuarioSesion || !puedeVerPresentaciones($usuarioSesion['rol'])) {
        http_response_code(401);
        echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión para actualizar estados.']);
        exit;
    }

    $id = (int) ($payload['id'] ?? 0);
    $estado = trim((string) ($payload['estado'] ?? ''));

    if ($id <= 0 || $estado === '') {
        throw new InvalidArgumentException('ID y estado son obligatorios.');
    }

    $estadosPlugin = ['contactado', 'confirmado'];

    if (!in_array($estado, $estadosPlugin, true)) {
        throw new InvalidArgumentException('Desde el portal solo puedes marcar Contactado o Confirmado.');
    }

    if (!actualizarEstadoPresentacionNino($id, $estado)) {
        throw new InvalidArgumentException('No se encontró el registro.');
    }

    echo json_encode([
        'exito'           => true,
        'mensaje'         => 'Estado actualizado a ' . etiquetaEstadoPresentacion($estado) . '.',
        'id'              => $id,
        'estado'          => $estado,
        'estado_etiqueta' => etiquetaEstadoPresentacion($estado),
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al procesar la solicitud.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al procesar la solicitud.']);
}
