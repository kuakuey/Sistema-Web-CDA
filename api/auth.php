<?php

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/api_sesion.php';

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
        $token = obtenerTokenSesionDesdeRequest();
        $usuario = validarTokenSesionApi($token);

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['exito' => false, 'mensaje' => 'Sesión no válida o expirada.']);
            exit;
        }

        echo json_encode([
            'exito'   => true,
            'usuario' => $usuario['usuario'],
            'nombre'  => $usuario['nombre'],
            'rol'     => $usuario['rol'],
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
        exit;
    }

    $accion = trim((string) ($payload['accion'] ?? 'iniciar'));

    if ($accion === 'cerrar') {
        $token = obtenerTokenSesionDesdeRequest($payload);
        cerrarSesionApi($token);

        echo json_encode([
            'exito'   => true,
            'mensaje' => 'Sesión cerrada correctamente.',
        ]);
        exit;
    }

    $usuarioLogin = trim((string) ($payload['usuario'] ?? ''));
    $clave = (string) ($payload['clave'] ?? '');

    if ($usuarioLogin === '' || $clave === '') {
        throw new InvalidArgumentException('Usuario y contraseña son obligatorios.');
    }

    $usuario = autenticarCredencialesApi($usuarioLogin, $clave);

    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['exito' => false, 'mensaje' => 'Usuario o contraseña incorrectos.']);
        exit;
    }

    if (!puedeAccederPortalPlugin($usuario['rol'])) {
        http_response_code(403);
        echo json_encode([
            'exito'   => false,
            'mensaje' => 'Tu rol no tiene permiso para usar el portal.',
        ]);
        exit;
    }

    $contexto = trim((string) ($payload['contexto'] ?? 'ofrendas'));

    if ($contexto === 'presentaciones' && !puedeVerPresentaciones($usuario['rol'])) {
        http_response_code(403);
        echo json_encode([
            'exito'   => false,
            'mensaje' => 'Tu rol no tiene permiso para gestionar presentaciones.',
        ]);
        exit;
    }

    if ($contexto === 'ofrendas' && !puedeRegistrarOfrendas($usuario['rol'])) {
        http_response_code(403);
        echo json_encode([
            'exito'   => false,
            'mensaje' => 'Tu rol no tiene permiso para registrar ofrendas.',
        ]);
        exit;
    }

    $token = crearSesionApi($usuario['id']);

    echo json_encode([
        'exito'        => true,
        'mensaje'      => 'Sesión iniciada correctamente.',
        'token_sesion' => $token,
        'usuario'      => $usuario['usuario'],
        'nombre'       => $usuario['nombre'],
        'rol'          => $usuario['rol'],
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al procesar la autenticación.']);
}
