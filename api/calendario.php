<?php

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/calendario_api.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$claveApi = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($claveApi === '' || $claveApi !== API_KEY) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'Clave API inválida.']);
    exit;
}

try {
    $accion = trim((string) ($_GET['accion'] ?? 'calendario_text'));

    if (in_array($accion, ['calendario_text', 'texto', 'html'], true)) {
        $datos = obtenerProximosEventosCalendarioApi();

        echo json_encode([
            'exito'  => true,
            'accion' => 'calendario_text',
            'html'   => renderizarHtmlCalendarioTextoApi(),
            'meses'  => $datos['meses'],
            'total'  => $datos['total'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($accion === 'listar') {
        $datos = obtenerProximosEventosCalendarioApi();

        echo json_encode([
            'exito' => true,
            'meses' => $datos['meses'],
            'total' => $datos['total'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Acción no válida. Usa calendario_text o listar.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al cargar el calendario.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al procesar la solicitud.']);
}
