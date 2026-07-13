<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/roles.php';

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerTodosUsuarios(): array
{
    $pdo = getConnection();

    return $pdo->query(
        'SELECT id, usuario, nombre, rol, creado_en FROM usuarios ORDER BY creado_en DESC, id DESC'
    )->fetchAll();
}

function crearUsuario(string $usuario, string $clave, string $nombre, string $rol): array
{
    $usuario = trim($usuario);
    $nombre  = trim($nombre);

    if ($usuario === '' || $clave === '') {
        return ['exito' => false, 'mensaje' => 'Usuario y contraseña son obligatorios.'];
    }

    if (!esRolValido($rol)) {
        return ['exito' => false, 'mensaje' => 'Rol no válido.'];
    }

    if (strlen($clave) < 6) {
        return ['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    $pdo = getConnection();

    $existe = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = ?');
    $existe->execute([$usuario]);

    if ((int) $existe->fetchColumn() > 0) {
        return ['exito' => false, 'mensaje' => 'Ese usuario ya existe.'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (usuario, clave, nombre, rol, creado_en) VALUES (?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $usuario,
        password_hash($clave, PASSWORD_DEFAULT),
        $nombre !== '' ? $nombre : $usuario,
        $rol,
    ]);

    return ['exito' => true, 'mensaje' => 'Usuario creado correctamente.', 'id' => (int) $pdo->lastInsertId()];
}

function cambiarClaveUsuario(int $id, string $clave, string $claveConfirmacion): array
{
    $clave = trim($clave);
    $claveConfirmacion = trim($claveConfirmacion);

    if ($id <= 0) {
        return ['exito' => false, 'mensaje' => 'Usuario no válido.'];
    }

    if ($clave === '' || $claveConfirmacion === '') {
        return ['exito' => false, 'mensaje' => 'Ingresa y confirma la nueva contraseña.'];
    }

    if ($clave !== $claveConfirmacion) {
        return ['exito' => false, 'mensaje' => 'Las contraseñas no coinciden.'];
    }

    if (strlen($clave) < 6) {
        return ['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    $pdo = getConnection();
    $existe = $pdo->prepare('SELECT id FROM usuarios WHERE id = ? LIMIT 1');
    $existe->execute([$id]);

    if (!$existe->fetch()) {
        return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET clave = ? WHERE id = ?');
    $stmt->execute([
        password_hash($clave, PASSWORD_DEFAULT),
        $id,
    ]);

    return ['exito' => true, 'mensaje' => 'Contraseña actualizada correctamente.'];
}

function eliminarUsuario(int $id, int $idUsuarioActual): array
{
    if ($id === $idUsuarioActual) {
        return ['exito' => false, 'mensaje' => 'No puedes eliminar tu propio usuario.'];
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        return ['exito' => false, 'mensaje' => 'Usuario no encontrado.'];
    }

    return ['exito' => true, 'mensaje' => 'Usuario eliminado.'];
}
