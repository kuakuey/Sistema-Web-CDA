<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/paginacion.php';

function obtenerTiposConsejeria(): array
{
    return [
        'personal'     => 'Personal',
        'matrimonial'  => 'Matrimonial',
        'familiar'     => 'Familiar',
        'jovenes'      => 'Jóvenes',
    ];
}

function esTipoConsejeriaValido(string $tipo): bool
{
    return array_key_exists($tipo, obtenerTiposConsejeria());
}

function etiquetaTipoConsejeria(string $tipo): string
{
    $tipos = obtenerTiposConsejeria();

    return $tipos[$tipo] ?? $tipo;
}

function insertarConsejeria(array $datos): int
{
    $pdo = getConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO consejerias (
            nombre_completo, telefono, tipo_consejeria, anio_en_cda, primera_consejeria,
            registrado_por_id, registrado_por_nombre, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    $stmt->execute([
        $datos['nombre_completo'],
        $datos['telefono'],
        $datos['tipo_consejeria'],
        $datos['anio_en_cda'],
        $datos['primera_consejeria'] ? 1 : 0,
        $datos['registrado_por_id'],
        $datos['registrado_por_nombre'],
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarCitaConsejeria(int $id, ?string $fecha, ?string $hora): bool
{
    $pdo = getConnection();

    if ($fecha !== null && $fecha !== '') {
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('Fecha de asignación no válida.');
        }
    }

    if ($hora !== null && $hora !== '') {
        $horaObj = DateTime::createFromFormat('H:i', $hora);
        if (!$horaObj || $horaObj->format('H:i') !== $hora) {
            throw new InvalidArgumentException('Hora de asignación no válida.');
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE consejerias SET cita_fecha = ?, cita_hora = ? WHERE id = ?'
    );

    $fechaDb = ($fecha !== null && $fecha !== '') ? $fecha : null;
    $horaDb = ($hora !== null && $hora !== '') ? $hora . ':00' : null;

    return $stmt->execute([$fechaDb, $horaDb, $id]) && $stmt->rowCount() >= 0;
}

function actualizarConsejeria(int $id, array $datos): bool
{
    $pdo = getConnection();

    $fecha = trim((string) ($datos['cita_fecha'] ?? ''));
    $hora = trim((string) ($datos['cita_hora'] ?? ''));

    if ($fecha !== '') {
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('Fecha de cita no válida.');
        }
    }

    if ($hora !== '') {
        $horaObj = DateTime::createFromFormat('H:i', $hora);
        if (!$horaObj || $horaObj->format('H:i') !== $hora) {
            throw new InvalidArgumentException('Hora de cita no válida.');
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE consejerias SET
            nombre_completo = ?, telefono = ?, tipo_consejeria = ?, anio_en_cda = ?,
            primera_consejeria = ?, cita_fecha = ?, cita_hora = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        trim($datos['nombre_completo']),
        trim($datos['telefono']),
        trim($datos['tipo_consejeria']),
        (int) $datos['anio_en_cda'],
        !empty($datos['primera_consejeria']) ? 1 : 0,
        $fecha !== '' ? $fecha : null,
        $hora !== '' ? $hora . ':00' : null,
        $id,
    ]);
}

function eliminarConsejeria(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM consejerias WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function contarConsejerias(): int
{
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM consejerias')->fetchColumn();
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlConsejerias(array $filtros): array
{
    $condiciones = ['1 = 1'];
    $parametros = [];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(nombre_completo LIKE ? OR telefono LIKE ? OR registrado_por_nombre LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda]);
    }

    if ($filtros['tipo_consejeria'] !== '') {
        $condiciones[] = 'tipo_consejeria = ?';
        $parametros[] = $filtros['tipo_consejeria'];
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'cita_fecha >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'cita_fecha <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    $sql = 'SELECT * FROM consejerias WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY creado_en DESC, id DESC';

    return [$sql, $parametros];
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarConsejerias(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlConsejerias($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarConsejeriasFiltradas(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlConsejerias($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/^SELECT \* FROM/i', 'SELECT COUNT(*) FROM', $sqlConteo);

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

function formatearHoraConsejeria(?string $hora): string
{
    if ($hora === null || $hora === '') {
        return '';
    }

    $horaObj = DateTime::createFromFormat('H:i:s', $hora);
    if (!$horaObj) {
        $horaObj = DateTime::createFromFormat('H:i', $hora);
    }

    return $horaObj ? $horaObj->format('H:i') : $hora;
}

function formatearCitaConsejeria(?string $fecha, ?string $hora): string
{
    if ($fecha === null || $fecha === '') {
        return 'Sin asignar';
    }

    $texto = $fecha;
    $horaFormateada = formatearHoraConsejeria($hora);

    if ($horaFormateada !== '') {
        $texto .= ' ' . $horaFormateada;
    }

    return $texto;
}
