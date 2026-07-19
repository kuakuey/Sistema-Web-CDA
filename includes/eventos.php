<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/paginacion.php';
require_once __DIR__ . '/valores_adicionales.php';

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventos(): array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    return $pdo->query(
        'SELECT e.*,
                (SELECT COUNT(*)
                 FROM valores_adicionales v
                 WHERE v.evento_id = e.id AND v.tipo = "' . TIPO_VALOR_EVENTOS_INTERNO . '") AS total_registros
         FROM eventos e
         ORDER BY e.nombre ASC, e.id ASC'
    )->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventosHabilitados(): array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    return $pdo->query(
        'SELECT * FROM eventos WHERE habilitado = 1 ORDER BY nombre ASC, id ASC'
    )->fetchAll();
}

function obtenerEvento(int $id): ?array
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM eventos WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

function obtenerEventoHabilitado(int $id): ?array
{
    $evento = obtenerEvento($id);

    if (!$evento || (int) ($evento['habilitado'] ?? 0) !== 1) {
        return null;
    }

    return $evento;
}

/**
 * @return array<string, string>
 */
function obtenerFormasPagoEvento(): array
{
    return [
        'pago'          => 'Pago',
        'gratuito'      => 'Gratuito',
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia',
    ];
}

function normalizarFormaPagoEvento(string $formaPago): string
{
    $formaPago = trim(mb_strtolower($formaPago));

    return array_key_exists($formaPago, obtenerFormasPagoEvento()) ? $formaPago : '';
}

function etiquetaFormaPagoEvento(?string $formaPago): string
{
    if ($formaPago === null || $formaPago === '') {
        return '—';
    }

    $formas = obtenerFormasPagoEvento();

    return $formas[normalizarFormaPagoEvento($formaPago)] ?? $formaPago;
}

/**
 * @param array<string, mixed> $datos
 * @return array{nombre: string, fecha: string, valor: float, habilitado: int, requiere_numeracion: int}
 */
function normalizarDatosEventoCatalogo(array $datos): array
{
    require_once __DIR__ . '/texto.php';

    $nombre = normalizarTextoOrdenado($datos['nombre'] ?? '');
    $fecha = trim((string) ($datos['fecha'] ?? ''));
    $tipoCobro = trim(mb_strtolower((string) ($datos['tipo_cobro'] ?? 'pago')));
    $valor = isset($datos['valor']) ? (float) $datos['valor'] : 0;
    $habilitado = !empty($datos['habilitado']) ? 1 : 0;
    $requiereNumeracion = !empty($datos['requiere_numeracion']) ? 1 : 0;

    if ($nombre === '' || $fecha === '') {
        throw new InvalidArgumentException('Nombre y fecha son obligatorios.');
    }

    if ($tipoCobro === 'gratuito' || ($tipoCobro !== 'pago' && $valor <= 0)) {
        $valor = 0;
    } elseif ($valor <= 0) {
        throw new InvalidArgumentException('Ingresa un valor mayor a cero o selecciona Gratuito.');
    }

    validarFechaEvento($fecha);

    return [
        'nombre'               => $nombre,
        'fecha'                => $fecha,
        'valor'                => $valor,
        'habilitado'           => $habilitado,
        'requiere_numeracion'  => $requiereNumeracion,
    ];
}

/**
 * @param array<string, mixed> $entrada
 * @return array<string, mixed>
 */
function validarDatosRegistroEvento(array $entrada, ?array $evento = null): array
{
    require_once __DIR__ . '/texto.php';

    $eventoId = isset($entrada['evento_id']) ? (int) $entrada['evento_id'] : 0;
    $nombre = normalizarTextoOrdenado($entrada['nombre'] ?? '');
    $fecha = trim((string) ($entrada['fecha'] ?? ''));
    $telefono = trim((string) ($entrada['telefono'] ?? ''));
    $valor = isset($entrada['valor']) ? (float) $entrada['valor'] : 0;
    $observacion = normalizarTextoOrdenado($entrada['observacion'] ?? '');
    $numeracion = trim((string) ($entrada['numeracion'] ?? ''));
    $formaPago = normalizarFormaPagoEvento((string) ($entrada['forma_pago'] ?? ''));

    if ($evento === null) {
        $evento = $eventoId > 0 ? obtenerEventoHabilitado($eventoId) : null;
    }

    if (!$evento) {
        throw new InvalidArgumentException('Selecciona un evento habilitado.');
    }

    $eventoEsGratuito = (float) ($evento['valor'] ?? 0) <= 0;

    if ($eventoEsGratuito) {
        $formaPago = 'gratuito';
        $valor = 0;
    } elseif (!in_array($formaPago, ['efectivo', 'transferencia'], true)) {
        throw new InvalidArgumentException('Selecciona Efectivo o Transferencia.');
    }

    if ($nombre === '' || $fecha === '' || $telefono === '') {
        throw new InvalidArgumentException('Completa todos los campos obligatorios.');
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new InvalidArgumentException('Fecha no válida.');
    }

    if ((int) ($evento['requiere_numeracion'] ?? 0) === 1 && $numeracion === '') {
        throw new InvalidArgumentException('La numeración es obligatoria para este evento.');
    }

    if ($eventoEsGratuito) {
        $valor = 0;
    } elseif ($valor <= 0) {
        throw new InvalidArgumentException('Ingresa un valor mayor a cero.');
    }

    return [
        'evento_id'    => (int) $evento['id'],
        'nombre'       => $nombre,
        'fecha'        => $fecha,
        'telefono'     => $telefono,
        'valor'        => $valor,
        'observacion'  => $observacion,
        'numeracion'   => $numeracion !== '' ? $numeracion : null,
        'forma_pago'   => $formaPago,
    ];
}

function obtenerRegistroEventoPorId(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT v.*, e.nombre AS evento_nombre, e.requiere_numeracion
         FROM valores_adicionales v
         LEFT JOIN eventos e ON e.id = v.evento_id
         WHERE v.id = ? AND v.tipo = ?
         LIMIT 1'
    );
    $stmt->execute([$id, TIPO_VALOR_EVENTOS_INTERNO]);

    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * @param array{nombre: string, fecha: string, valor: float|int|string, habilitado?: bool|int, requiere_numeracion?: bool|int} $datos
 */
function crearEvento(array $datos): int
{
    $datos = normalizarDatosEventoCatalogo($datos);

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO eventos (nombre, fecha, valor, habilitado, requiere_numeracion, creado_en)
         VALUES (?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $datos['nombre'],
        $datos['fecha'],
        $datos['valor'],
        $datos['habilitado'],
        $datos['requiere_numeracion'],
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * @param array{nombre: string, fecha: string, valor: float|int|string, habilitado?: bool|int, requiere_numeracion?: bool|int} $datos
 */
function actualizarEventoCatalogo(int $id, array $datos): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Datos de evento inválidos.');
    }

    $datos = normalizarDatosEventoCatalogo($datos);

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE eventos
         SET nombre = ?, fecha = ?, valor = ?, habilitado = ?, requiere_numeracion = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        $datos['nombre'],
        $datos['fecha'],
        $datos['valor'],
        $datos['habilitado'],
        $datos['requiere_numeracion'],
        $id,
    ]);
}

function validarFechaEvento(string $fecha): void
{
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);

    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        throw new InvalidArgumentException('Fecha no válida.');
    }
}

function eliminarEvento(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM eventos WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function contarEventos(): int
{
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM eventos')->fetchColumn();
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlRegistrosEventos(array $filtros): array
{
    $condiciones = ['v.tipo = ?'];
    $parametros = [TIPO_VALOR_EVENTOS_INTERNO];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(v.nombre LIKE ? OR v.telefono LIKE ? OR v.observacion LIKE ? OR e.nombre LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'v.fecha >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'v.fecha <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    $sql = 'SELECT v.*, e.nombre AS evento_nombre, e.requiere_numeracion
            FROM valores_adicionales v
            LEFT JOIN eventos e ON e.id = v.evento_id
            WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY v.creado_en DESC, v.id DESC';

    return [$sql, $parametros];
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarRegistrosEventos(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlRegistrosEventos($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarRegistrosEventos(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlRegistrosEventos($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace(
        '/SELECT v\.\*.*?FROM/is',
        'SELECT COUNT(*) FROM',
        $sqlConteo,
        1
    );

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

function etiquetaEstadoEvento(int $habilitado): string
{
    return $habilitado === 1 ? 'Habilitado' : 'Deshabilitado';
}

function obtenerEventoPorId(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);
    $stmt = $pdo->prepare('SELECT * FROM eventos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerRegistrosPorEvento(int $eventoId): array
{
    if ($eventoId <= 0) {
        return [];
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT v.*, e.nombre AS evento_nombre, e.requiere_numeracion
         FROM valores_adicionales v
         LEFT JOIN eventos e ON e.id = v.evento_id
         WHERE v.tipo = ? AND v.evento_id = ?
         ORDER BY v.nombre ASC, v.id ASC'
    );
    $stmt->execute([TIPO_VALOR_EVENTOS_INTERNO, $eventoId]);

    return $stmt->fetchAll();
}

function etiquetaTipoEventoCatalogo(array $evento): string
{
    return (float) ($evento['valor'] ?? 0) <= 0 ? 'Gratuito' : 'Pago';
}

/**
 * @return array<string, mixed>
 */
function generarInformeEvento(int $eventoId): array
{
    require_once __DIR__ . '/informes.php';

    $evento = obtenerEventoPorId($eventoId);

    if (!$evento) {
        throw new InvalidArgumentException('Evento no encontrado.');
    }

    $registros = obtenerRegistrosPorEvento($eventoId);
    $totalMonto = 0.0;
    $porFormaPago = [];

    foreach ($registros as $registro) {
        $monto = (float) ($registro['valor'] ?? 0);
        $totalMonto += $monto;
        $etiquetaPago = etiquetaFormaPagoEvento($registro['forma_pago'] ?? null);

        if (!isset($porFormaPago[$etiquetaPago])) {
            $porFormaPago[$etiquetaPago] = ['cantidad' => 0, 'monto' => 0.0];
        }

        $porFormaPago[$etiquetaPago]['cantidad']++;
        $porFormaPago[$etiquetaPago]['monto'] += $monto;
    }

    return [
        'evento' => $evento,
        'registros' => $registros,
        'resumen' => [
            'total_participantes' => count($registros),
            'total_monto'         => $totalMonto,
            'por_forma_pago'      => $porFormaPago,
        ],
        'evento_tipo_etiqueta'       => etiquetaTipoEventoCatalogo($evento),
        'evento_fecha_etiqueta'      => formatearFechaInforme($evento['fecha'] ?? null),
        'evento_valor_etiqueta'      => formatearMonto((float) ($evento['valor'] ?? 0)),
        'evento_numeracion_etiqueta' => (int) ($evento['requiere_numeracion'] ?? 0) === 1 ? 'Sí' : 'No',
        'evento_estado_etiqueta'     => etiquetaEstadoEvento((int) ($evento['habilitado'] ?? 0)),
        'generado_en_etiqueta'       => formatearFechaHora(date('Y-m-d H:i:s')),
    ];
}
