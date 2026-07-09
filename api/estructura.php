<?php

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/estructura.php';

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
    $datos = obtenerEstructuraParaApi();
    echo json_encode(array_merge(['exito' => true], $datos));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al cargar estructura CDV.']);
}
