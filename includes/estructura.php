<?php

require_once __DIR__ . '/../config/database.php';

function nombreCompletoLider(array $registro): string
{
    return trim(($registro['nombre'] ?? '') . ' ' . ($registro['apellido'] ?? ''));
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerTerritorios(): array
{
    $pdo = getConnection();

    return $pdo->query(
        'SELECT * FROM territorios ORDER BY orden ASC, nombre ASC, id ASC'
    )->fetchAll();
}

function crearTerritorio(string $nombre): int
{
    $pdo = getConnection();
    $maximo = (int) $pdo->query('SELECT COALESCE(MAX(orden), 0) FROM territorios')->fetchColumn();

    $stmt = $pdo->prepare(
        'INSERT INTO territorios (nombre, orden, creado_en) VALUES (?, ?, NOW())'
    );
    $stmt->execute([trim($nombre), $maximo + 1]);

    return (int) $pdo->lastInsertId();
}

function actualizarTerritorio(int $id, string $nombre): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE territorios SET nombre = ? WHERE id = ?');

    return $stmt->execute([trim($nombre), $id]);
}

function eliminarTerritorio(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM territorios WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerLideres(): array
{
    $pdo = getConnection();

    return $pdo->query(
        'SELECT * FROM lideres ORDER BY creado_en DESC, id DESC'
    )->fetchAll();
}

function crearLider(array $datos): int
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO lideres (nombre, apellido, cedula, celular, email, notas, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        trim($datos['cedula'] ?? ''),
        trim($datos['celular'] ?? ''),
        trim($datos['email'] ?? ''),
        trim($datos['notas'] ?? ''),
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarLider(int $id, array $datos): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE lideres SET nombre = ?, apellido = ?, cedula = ?, celular = ?, email = ?, notas = ? WHERE id = ?'
    );

    return $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        trim($datos['cedula'] ?? ''),
        trim($datos['celular'] ?? ''),
        trim($datos['email'] ?? ''),
        trim($datos['notas'] ?? ''),
        $id,
    ]);
}

function eliminarLider(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM lideres WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerCasasVida(): array
{
    $pdo = getConnection();

    return $pdo->query(
        'SELECT c.*, t.nombre AS territorio_nombre,
                l.nombre AS lider_nombre, l.apellido AS lider_apellido
         FROM casas_vida c
         INNER JOIN territorios t ON t.id = c.territorio_id
         INNER JOIN lideres l ON l.id = c.lider_id
         ORDER BY c.creado_en DESC, c.id DESC'
    )->fetchAll();
}

function obtenerCasaVida(int $id): ?array
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT c.*, t.nombre AS territorio_nombre,
                l.nombre AS lider_nombre, l.apellido AS lider_apellido
         FROM casas_vida c
         INNER JOIN territorios t ON t.id = c.territorio_id
         INNER JOIN lideres l ON l.id = c.lider_id
         WHERE c.id = ?'
    );
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

function crearCasaVida(array $datos): int
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO casas_vida (territorio_id, lider_id, nombre, direccion, creado_en)
         VALUES (?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        (int) $datos['territorio_id'],
        (int) $datos['lider_id'],
        trim($datos['nombre']),
        trim($datos['direccion']),
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarCasaVida(int $id, array $datos): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE casas_vida SET territorio_id = ?, lider_id = ?, nombre = ?, direccion = ? WHERE id = ?'
    );

    return $stmt->execute([
        (int) $datos['territorio_id'],
        (int) $datos['lider_id'],
        trim($datos['nombre']),
        trim($datos['direccion']),
        $id,
    ]);
}

function eliminarCasaVida(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM casas_vida WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

/**
 * Datos para API del formulario de ofrendas en WordPress.
 */
function obtenerEstructuraParaApi(): array
{
    $territorios = obtenerTerritorios();
    $casas = obtenerCasasVida();

    $listaTerritorios = [];
    foreach ($territorios as $territorio) {
        $listaTerritorios[] = [
            'id'     => (int) $territorio['id'],
            'nombre' => $territorio['nombre'],
        ];
    }

    $listaCasas = [];
    foreach ($casas as $casa) {
        $listaCasas[] = [
            'id'            => (int) $casa['id'],
            'territorio_id' => (int) $casa['territorio_id'],
            'nombre'        => $casa['nombre'],
            'lider'         => trim($casa['lider_nombre'] . ' ' . $casa['lider_apellido']),
            'direccion'     => $casa['direccion'],
        ];
    }

    usort($listaTerritorios, static function (array $a, array $b): int {
        return strcasecmp($a['nombre'], $b['nombre']);
    });

    usort($listaCasas, static function (array $a, array $b): int {
        return strcasecmp($a['nombre'], $b['nombre']);
    });

    return [
        'territorios' => $listaTerritorios,
        'casas'       => $listaCasas,
    ];
}

function insertarOfrendaDesdeApi(array $datos): int
{
    $pdo = getConnection();
    $casa = obtenerCasaVida((int) $datos['casa_id']);

    if (!$casa) {
        throw new InvalidArgumentException('Casa de vida no encontrada.');
    }

    $lider = nombreCompletoLider([
        'nombre' => $casa['lider_nombre'],
        'apellido' => $casa['lider_apellido'],
    ]);

    $stmt = $pdo->prepare(
        'INSERT INTO ofrendas (casa_id, casa_vida, lider, fecha_ofrenda, monto, registrado_por_id, registrado_por_nombre, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        (int) $casa['id'],
        $casa['nombre'],
        $lider,
        $datos['fecha_ofrenda'],
        (float) $datos['monto'],
        isset($datos['registrado_por_id']) ? (int) $datos['registrado_por_id'] : null,
        trim($datos['registrado_por_nombre'] ?? ''),
    ]);

    return (int) $pdo->lastInsertId();
}
