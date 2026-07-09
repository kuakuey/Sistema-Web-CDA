<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/roles.php';

const DURACION_SESION_API_HORAS = 8;

/**
 * @return array{id:int,usuario:string,nombre:string,rol:string}|null
 */
function autenticarCredencialesApi(string $usuario, string $clave): ?array
{
    $pdo = getConnection();

    $stmt = $pdo->prepare(
        'SELECT id, usuario, clave, nombre, rol FROM usuarios WHERE usuario = ? LIMIT 1'
    );
    $stmt->execute([trim($usuario)]);
    $registro = $stmt->fetch();

    if (!$registro || !password_verify($clave, $registro['clave'])) {
        return null;
    }

    return [
        'id'      => (int) $registro['id'],
        'usuario' => $registro['usuario'],
        'nombre'  => $registro['nombre'] ?? $registro['usuario'],
        'rol'     => normalizarRolUsuario($registro['rol']),
    ];
}

function crearSesionApi(int $idUsuario): string
{
    $pdo = getConnection();
    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', time() + DURACION_SESION_API_HORAS * 3600);

    $stmt = $pdo->prepare(
        'INSERT INTO sesiones_api (usuario_id, token, expira_en, creado_en) VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$idUsuario, $token, $expira]);

    return $token;
}

/**
 * @return array{id:int,usuario:string,nombre:string,rol:string}|null
 */
function validarTokenSesionApi(string $token): ?array
{
    $token = trim($token);

    if ($token === '') {
        return null;
    }

    $pdo = getConnection();

    $stmt = $pdo->prepare(
        'SELECT s.usuario_id, u.usuario, u.nombre, u.rol
         FROM sesiones_api s
         INNER JOIN usuarios u ON u.id = s.usuario_id
         WHERE s.token = ? AND s.expira_en > NOW()
         LIMIT 1'
    );
    $stmt->execute([$token]);
    $registro = $stmt->fetch();

    if (!$registro) {
        return null;
    }

    return [
        'id'      => (int) $registro['usuario_id'],
        'usuario' => $registro['usuario'],
        'nombre'  => $registro['nombre'] ?? $registro['usuario'],
        'rol'     => normalizarRolUsuario($registro['rol']),
    ];
}

function cerrarSesionApi(string $token): void
{
    $token = trim($token);

    if ($token === '') {
        return;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM sesiones_api WHERE token = ?');
    $stmt->execute([$token]);
}

function obtenerTokenSesionDesdeRequest(array $payload = []): string
{
    $headers = [
        $_SERVER['HTTP_X_TOKEN_SESION'] ?? '',
        $_SERVER['HTTP_X_TOKEN_SESSION'] ?? '',
    ];

    foreach ($headers as $header) {
        if (trim((string) $header) !== '') {
            return trim((string) $header);
        }
    }

    return trim((string) ($payload['token_sesion'] ?? ''));
}
