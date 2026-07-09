<?php

require_once __DIR__ . '/../config/database.php';

session_start();

if (!headers_sent() && PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=UTF-8');
}

require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/rutas.php';
require_once __DIR__ . '/submissions.php';

function estaLogueado(): bool
{
    return isset($_SESSION['id_usuario']);
}

function requerirSesion(): void
{
    if (!estaLogueado()) {
        header('Location: index.php');
        exit;
    }
}

function requerirRol(array $rolesPermitidos): void
{
    requerirSesion();

    $usuario = obtenerUsuarioActual();

    if (!$usuario || !in_array($usuario['rol'], $rolesPermitidos, true)) {
        header('Location: ' . obtenerUrlInicioPorRol($usuario['rol'] ?? ROL_ADMIN));
        exit;
    }
}

function requerirSuperadmin(): void
{
    requerirRol([ROL_SUPERADMIN]);
}

function iniciarSesion(string $usuario, string $clave): bool
{
    $pdo = getConnection();

    $stmt = $pdo->prepare(
        'SELECT id, usuario, clave, nombre, rol FROM usuarios WHERE usuario = ? LIMIT 1'
    );
    $stmt->execute([$usuario]);
    $registro = $stmt->fetch();

    if (!$registro || !password_verify($clave, $registro['clave'])) {
        return false;
    }

    $_SESSION['id_usuario'] = $registro['id'];
    $_SESSION['usuario'] = $registro['usuario'];
    $_SESSION['nombre'] = $registro['nombre'];
    $_SESSION['rol'] = normalizarRolUsuario($registro['rol'] ?? ROL_ADMIN);

    return true;
}

function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();
}

function obtenerUsuarioActual(): ?array
{
    if (!estaLogueado()) {
        return null;
    }

    return [
        'id'     => $_SESSION['id_usuario'],
        'usuario' => $_SESSION['usuario'],
        'nombre' => $_SESSION['nombre'],
        'rol'    => normalizarRolUsuario($_SESSION['rol'] ?? ROL_ADMIN),
    ];
}
