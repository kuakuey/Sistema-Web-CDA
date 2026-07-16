<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/paginacion.php';
require_once __DIR__ . '/submissions.php';

function etiquetaTipoTransporteAniversario(bool $poseeMovilizacion): string
{
    return $poseeMovilizacion ? 'Tiene carro' : 'Necesita transporte';
}

function claseBadgeTipoTransporteAniversario(bool $poseeMovilizacion): string
{
    return $poseeMovilizacion ? 'bg-success' : 'bg-warning text-dark';
}

function formatearEdadTransporteAniversario($edad): string
{
    $edad = (int) $edad;

    if ($edad < 1) {
        return '—';
    }

    return $edad . ' año' . ($edad === 1 ? '' : 's');
}

/**
 * @return array<string, mixed>
 */
function validarDatosTransporteAniversario(array $entrada): array
{
    $nombreCompleto = trim((string) ($entrada['nombre_completo'] ?? ''));
    $telefono = trim((string) ($entrada['telefono'] ?? ''));
    $edad = isset($entrada['edad']) ? (int) $entrada['edad'] : 0;
    $zona = trim((string) ($entrada['zona'] ?? ''));
    $poseeMovilizacion = !empty($entrada['posee_movilizacion']);
    $zonasOk = array_keys(obtenerZonasConexion());

    if ($nombreCompleto === '' || $telefono === '') {
        throw new InvalidArgumentException('Completa nombre completo y teléfono.');
    }

    if ($edad < 1 || $edad > 120) {
        throw new InvalidArgumentException('Indica una edad válida (entre 1 y 120 años).');
    }

    if ($zona === '' || !in_array($zona, $zonasOk, true)) {
        throw new InvalidArgumentException('Selecciona una zona válida.');
    }

    $asientosDisponibles = null;

    if ($poseeMovilizacion) {
        $asientos = isset($entrada['asientos_disponibles']) ? (int) $entrada['asientos_disponibles'] : 0;

        if ($asientos < 1) {
            throw new InvalidArgumentException('Indica cuántos asientos disponibles tiene el vehículo (mínimo 1).');
        }

        $asientosDisponibles = $asientos;
    }

    return [
        'nombre_completo'      => $nombreCompleto,
        'telefono'             => $telefono,
        'edad'                 => $edad,
        'zona'                 => $zona,
        'posee_movilizacion'   => $poseeMovilizacion,
        'asientos_disponibles' => $asientosDisponibles,
    ];
}

function insertarTransporteAniversario(array $datos): int
{
    $pdo = getConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO transporte_aniversario (
            nombre_completo, telefono, edad, zona, posee_movilizacion, asientos_disponibles,
            registrado_por_id, registrado_por_nombre, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    $stmt->execute([
        $datos['nombre_completo'],
        $datos['telefono'],
        $datos['edad'],
        $datos['zona'],
        !empty($datos['posee_movilizacion']) ? 1 : 0,
        $datos['asientos_disponibles'],
        $datos['registrado_por_id'],
        $datos['registrado_por_nombre'],
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarTransporteAniversario(int $id, array $datos): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE transporte_aniversario SET
            nombre_completo = ?, telefono = ?, edad = ?, zona = ?, posee_movilizacion = ?, asientos_disponibles = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        $datos['nombre_completo'],
        $datos['telefono'],
        $datos['edad'],
        $datos['zona'],
        !empty($datos['posee_movilizacion']) ? 1 : 0,
        $datos['asientos_disponibles'],
        $id,
    ]);
}

function eliminarTransporteAniversario(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM transporte_aniversario WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function contarTransporteAniversario(): int
{
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM transporte_aniversario')->fetchColumn();
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlTransporteAniversario(array $filtros): array
{
    $condiciones = ['1 = 1'];
    $parametros = [];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(nombre_completo LIKE ? OR telefono LIKE ? OR zona LIKE ? OR registrado_por_nombre LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if (($filtros['tipo_transporte'] ?? '') === 'con_carro') {
        $condiciones[] = 'posee_movilizacion = 1';
    } elseif (($filtros['tipo_transporte'] ?? '') === 'necesita_transporte') {
        $condiciones[] = 'posee_movilizacion = 0';
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'DATE(creado_en) >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'DATE(creado_en) <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    $sql = 'SELECT * FROM transporte_aniversario WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY creado_en DESC, id DESC';

    return [$sql, $parametros];
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarTransporteAniversario(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlTransporteAniversario($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarTransporteAniversarioFiltradas(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlTransporteAniversario($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/^SELECT \* FROM/i', 'SELECT COUNT(*) FROM', $sqlConteo);

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

/**
 * @return array<int, array<string, mixed>>
 */
function listarTodoTransporteAniversario(): array
{
    $pdo = getConnection();
    $stmt = $pdo->query(
        'SELECT * FROM transporte_aniversario ORDER BY creado_en ASC, id ASC'
    );

    return $stmt->fetchAll();
}

/**
 * @return array<string, int>
 */
function obtenerResumenTransporteAniversario(): array
{
    $pdo = getConnection();
    $stmt = $pdo->query(
        'SELECT
            COUNT(*) AS total,
            SUM(posee_movilizacion = 1) AS con_carro,
            SUM(posee_movilizacion = 0) AS necesitan_transporte,
            COALESCE(SUM(CASE WHEN posee_movilizacion = 1 THEN asientos_disponibles ELSE 0 END), 0) AS asientos_ofrecidos
         FROM transporte_aniversario'
    );
    $fila = $stmt->fetch() ?: [];

    return [
        'total'                 => (int) ($fila['total'] ?? 0),
        'con_carro'             => (int) ($fila['con_carro'] ?? 0),
        'necesitan_transporte'  => (int) ($fila['necesitan_transporte'] ?? 0),
        'asientos_ofrecidos'    => (int) ($fila['asientos_ofrecidos'] ?? 0),
    ];
}

/**
 * Asigna pasajeros a conductores según asientos disponibles.
 *
 * @return array{
 *     resumen: array<string, int>,
 *     conductores: array<int, array<string, mixed>>,
 *     sin_asignar: array<int, array<string, mixed>>
 * }
 */
function calcularAsignacionTransporteAniversario(): array
{
    $registros = listarTodoTransporteAniversario();
    $conductores = [];
    $pasajeros = [];

    foreach ($registros as $registro) {
        if (!empty($registro['posee_movilizacion'])) {
            $conductores[] = [
                'id'                   => (int) $registro['id'],
                'nombre_completo'      => (string) $registro['nombre_completo'],
                'telefono'             => (string) $registro['telefono'],
                'edad'                 => (int) ($registro['edad'] ?? 0),
                'zona'                 => (string) ($registro['zona'] ?? ''),
                'asientos_total'       => (int) ($registro['asientos_disponibles'] ?? 0),
                'asientos_restantes'   => (int) ($registro['asientos_disponibles'] ?? 0),
                'pasajeros'            => [],
            ];
        } else {
            $pasajeros[] = [
                'id'              => (int) $registro['id'],
                'nombre_completo' => (string) $registro['nombre_completo'],
                'telefono'        => (string) $registro['telefono'],
                'edad'            => (int) ($registro['edad'] ?? 0),
                'zona'            => (string) ($registro['zona'] ?? ''),
            ];
        }
    }

    $sinAsignar = [];
    $indiceConductor = 0;
    $totalConductores = count($conductores);

    foreach ($pasajeros as $pasajero) {
        $asignado = false;

        if ($totalConductores > 0) {
            for ($intento = 0; $intento < $totalConductores; $intento++) {
                $posicion = ($indiceConductor + $intento) % $totalConductores;

                if ($conductores[$posicion]['asientos_restantes'] > 0) {
                    $conductores[$posicion]['pasajeros'][] = $pasajero;
                    $conductores[$posicion]['asientos_restantes']--;
                    $indiceConductor = ($posicion + 1) % $totalConductores;
                    $asignado = true;
                    break;
                }
            }
        }

        if (!$asignado) {
            $sinAsignar[] = $pasajero;
        }
    }

    $asignados = count($pasajeros) - count($sinAsignar);
    $asientosRestantes = 0;

    foreach ($conductores as $conductor) {
        $asientosRestantes += (int) $conductor['asientos_restantes'];
    }

    $resumenBase = obtenerResumenTransporteAniversario();

    return [
        'resumen' => array_merge($resumenBase, [
            'pasajeros_asignados'   => $asignados,
            'pasajeros_sin_cupo'    => count($sinAsignar),
            'asientos_restantes'    => $asientosRestantes,
        ]),
        'conductores'  => $conductores,
        'sin_asignar'  => $sinAsignar,
    ];
}
