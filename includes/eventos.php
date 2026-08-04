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

    $eventos = $pdo->query(
        'SELECT e.*,
                (SELECT COUNT(*)
                 FROM valores_adicionales v
                 WHERE v.evento_id = e.id AND v.tipo = "' . TIPO_VALOR_EVENTOS_INTERNO . '") AS total_registros
         FROM eventos e
         ORDER BY e.nombre ASC, e.id ASC'
    )->fetchAll();

    return adjuntarTiposEntradaAEventos($eventos);
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerEventosHabilitados(): array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $eventos = $pdo->query(
        'SELECT * FROM eventos WHERE habilitado = 1 ORDER BY nombre ASC, id ASC'
    )->fetchAll();

    return adjuntarTiposEntradaAEventos($eventos);
}

function obtenerEvento(int $id): ?array
{
    $pdo = getConnection();
    asegurarColumnasEventos($pdo);
    $stmt = $pdo->prepare('SELECT * FROM eventos WHERE id = ?');
    $stmt->execute([$id]);
    $evento = $stmt->fetch();

    if (!$evento) {
        return null;
    }

    $evento['tipos_entrada'] = obtenerTiposEntradaPorEvento((int) $evento['id']);

    return $evento;
}

/**
 * @param array<int, array<string, mixed>> $eventos
 * @return array<int, array<string, mixed>>
 */
function adjuntarTiposEntradaAEventos(array $eventos): array
{
    if ($eventos === []) {
        return [];
    }

    $ids = array_map(static fn (array $evento): int => (int) $evento['id'], $eventos);
    $tiposPorEvento = obtenerTiposEntradaPorEventos($ids);

    foreach ($eventos as &$evento) {
        $eventoId = (int) $evento['id'];
        $evento['tipos_entrada'] = $tiposPorEvento[$eventoId] ?? [];
    }
    unset($evento);

    return $eventos;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerTiposEntradaPorEvento(int $eventoId): array
{
    if ($eventoId <= 0) {
        return [];
    }

    $porEvento = obtenerTiposEntradaPorEventos([$eventoId]);

    return $porEvento[$eventoId] ?? [];
}

/**
 * @param array<int, int> $eventoIds
 * @return array<int, array<int, array<string, mixed>>>
 */
function obtenerTiposEntradaPorEventos(array $eventoIds): array
{
    $eventoIds = array_values(array_unique(array_filter(array_map('intval', $eventoIds))));

    if ($eventoIds === []) {
        return [];
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $placeholders = implode(',', array_fill(0, count($eventoIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, evento_id, nombre, valor, orden
         FROM eventos_tipos_entrada
         WHERE evento_id IN ($placeholders)
         ORDER BY orden ASC, id ASC"
    );
    $stmt->execute($eventoIds);

    $resultado = [];
    foreach ($stmt->fetchAll() as $fila) {
        $eventoId = (int) $fila['evento_id'];
        $resultado[$eventoId][] = $fila;
    }

    return $resultado;
}

function obtenerTipoEntradaPorId(int $id, ?int $eventoId = null): ?array
{
    if ($id <= 0) {
        return null;
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    if ($eventoId !== null && $eventoId > 0) {
        $stmt = $pdo->prepare(
            'SELECT id, evento_id, nombre, valor, orden
             FROM eventos_tipos_entrada
             WHERE id = ? AND evento_id = ?
             LIMIT 1'
        );
        $stmt->execute([$id, $eventoId]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, evento_id, nombre, valor, orden
             FROM eventos_tipos_entrada
             WHERE id = ?
             LIMIT 1'
        );
        $stmt->execute([$id]);
    }

    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * @param array<int, array<string, mixed>>|array<string, mixed> $tiposEntrada
 * @return array<int, array{nombre: string, valor: float}>
 */
function normalizarTiposEntradaCatalogo($tiposEntrada, string $tipoCobro): array
{
    require_once __DIR__ . '/texto.php';

    if (!is_array($tiposEntrada)) {
        $tiposEntrada = [];
    }

    // Soporta arrays indexados o campos POST tipo_entrada[nombre][] / tipo_entrada[valor][].
    if (isset($tiposEntrada['nombre']) || isset($tiposEntrada['valor'])) {
        $nombres = $tiposEntrada['nombre'] ?? [];
        $valores = $tiposEntrada['valor'] ?? [];
        $tiposEntrada = [];

        if (!is_array($nombres)) {
            $nombres = [$nombres];
        }
        if (!is_array($valores)) {
            $valores = [$valores];
        }

        $total = max(count($nombres), count($valores));
        for ($i = 0; $i < $total; $i++) {
            $tiposEntrada[] = [
                'nombre' => $nombres[$i] ?? '',
                'valor'  => $valores[$i] ?? 0,
            ];
        }
    }

    $normalizados = [];
    $esGratuito = $tipoCobro === 'gratuito';

    foreach ($tiposEntrada as $tipo) {
        if (!is_array($tipo)) {
            continue;
        }

        $nombre = normalizarTextoOrdenado($tipo['nombre'] ?? '');
        if ($nombre === '') {
            continue;
        }

        $valor = isset($tipo['valor']) ? (float) $tipo['valor'] : 0.0;
        if ($esGratuito || $valor < 0) {
            $valor = 0.0;
        }

        if (!$esGratuito && $valor <= 0) {
            throw new InvalidArgumentException(
                'Cada tipo de entrada de pago debe tener un valor mayor a cero.'
            );
        }

        $normalizados[] = [
            'nombre' => $nombre,
            'valor'  => $valor,
        ];
    }

    if ($normalizados === []) {
        throw new InvalidArgumentException('Agrega al menos un tipo de entrada.');
    }

    return $normalizados;
}

/**
 * @param array<int, array{nombre: string, valor: float}> $tiposEntrada
 */
function guardarTiposEntradaEvento(int $eventoId, array $tiposEntrada): void
{
    if ($eventoId <= 0) {
        throw new InvalidArgumentException('Evento inválido.');
    }

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);

    $pdo->prepare('DELETE FROM eventos_tipos_entrada WHERE evento_id = ?')->execute([$eventoId]);

    $stmt = $pdo->prepare(
        'INSERT INTO eventos_tipos_entrada (evento_id, nombre, valor, orden, creado_en)
         VALUES (?, ?, ?, ?, NOW())'
    );

    foreach ($tiposEntrada as $orden => $tipo) {
        $stmt->execute([
            $eventoId,
            $tipo['nombre'],
            $tipo['valor'],
            (int) $orden,
        ]);
    }
}

/**
 * @param array<int, array{nombre: string, valor: float}> $tiposEntrada
 */
function valorCatalogoDesdeTiposEntrada(array $tiposEntrada): float
{
    if ($tiposEntrada === []) {
        return 0.0;
    }

    $valores = array_map(static fn (array $tipo): float => (float) $tipo['valor'], $tiposEntrada);
    $maximo = max($valores);

    // Si todos son gratuitos, el catálogo queda en 0; si hay pagos, usa el menor pago.
    if ($maximo <= 0) {
        return 0.0;
    }

    $pagos = array_values(array_filter($valores, static fn (float $valor): bool => $valor > 0));

    return $pagos === [] ? 0.0 : min($pagos);
}

function formatearTiposEntradaEvento(array $evento): string
{
    $tipos = $evento['tipos_entrada'] ?? [];

    if (!is_array($tipos) || $tipos === []) {
        $valor = (float) ($evento['valor'] ?? 0);
        return $valor <= 0 ? 'Gratuito' : formatearMonto($valor);
    }

    $partes = [];
    foreach ($tipos as $tipo) {
        $nombre = trim((string) ($tipo['nombre'] ?? ''));
        $valor = (float) ($tipo['valor'] ?? 0);
        if ($nombre === '') {
            continue;
        }
        $partes[] = $nombre . ': ' . ($valor <= 0 ? 'Gratuito' : formatearMonto($valor));
    }

    return $partes === [] ? '—' : implode(' · ', $partes);
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

/**
 * @return array<string, string>
 */
function obtenerEstadosPagoEvento(): array
{
    return [
        'por_cancelar' => 'Por cancelar',
        'pagado'       => 'Pagado',
    ];
}

function normalizarEstadoPagoEvento(string $estado): string
{
    $estado = trim(mb_strtolower($estado));

    return array_key_exists($estado, obtenerEstadosPagoEvento()) ? $estado : '';
}

function etiquetaEstadoPagoEvento(?string $estado): string
{
    if ($estado === null || $estado === '') {
        return '—';
    }

    $estados = obtenerEstadosPagoEvento();

    return $estados[normalizarEstadoPagoEvento($estado)] ?? $estado;
}

function claseBadgeEstadoPagoEvento(?string $estado): string
{
    return normalizarEstadoPagoEvento((string) $estado) === 'pagado'
        ? 'bg-success'
        : 'bg-warning text-dark';
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
 * @return array{
 *   nombre: string,
 *   fecha: string,
 *   valor: float,
 *   habilitado: int,
 *   requiere_numeracion: int,
 *   tipos_entrada: array<int, array{nombre: string, valor: float}>
 * }
 */
function normalizarDatosEventoCatalogo(array $datos): array
{
    require_once __DIR__ . '/texto.php';

    $nombre = normalizarTextoOrdenado($datos['nombre'] ?? '');
    $fecha = trim((string) ($datos['fecha'] ?? ''));
    $tipoCobro = trim(mb_strtolower((string) ($datos['tipo_cobro'] ?? 'pago')));
    if ($tipoCobro !== 'gratuito') {
        $tipoCobro = 'pago';
    }
    $habilitado = !empty($datos['habilitado']) ? 1 : 0;
    $requiereNumeracion = !empty($datos['requiere_numeracion']) ? 1 : 0;

    if ($nombre === '' || $fecha === '') {
        throw new InvalidArgumentException('Nombre y fecha son obligatorios.');
    }

    $tiposEntrada = $datos['tipos_entrada'] ?? null;

    // Compatibilidad: si no vienen tipos, arma uno desde valor suelto.
    if ($tiposEntrada === null || $tiposEntrada === []) {
        $valorLegacy = isset($datos['valor']) ? (float) $datos['valor'] : 0.0;
        if ($tipoCobro === 'gratuito') {
            $valorLegacy = 0.0;
        } elseif ($valorLegacy <= 0) {
            throw new InvalidArgumentException('Agrega al menos un tipo de entrada con valor.');
        }
        $tiposEntrada = [['nombre' => 'General', 'valor' => $valorLegacy]];
    }

    $tiposEntrada = normalizarTiposEntradaCatalogo($tiposEntrada, $tipoCobro);
    $valor = valorCatalogoDesdeTiposEntrada($tiposEntrada);

    validarFechaEvento($fecha);

    return [
        'nombre'               => $nombre,
        'fecha'                => $fecha,
        'valor'                => $valor,
        'habilitado'           => $habilitado,
        'requiere_numeracion'  => $requiereNumeracion,
        'tipos_entrada'        => $tiposEntrada,
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
    $tipoEntradaId = isset($entrada['tipo_entrada_id']) ? (int) $entrada['tipo_entrada_id'] : 0;
    $estadoPago = normalizarEstadoPagoEvento((string) ($entrada['estado_pago'] ?? ''));

    if ($evento === null) {
        $evento = $eventoId > 0 ? obtenerEventoHabilitado($eventoId) : null;
    } elseif (!isset($evento['tipos_entrada'])) {
        $evento['tipos_entrada'] = obtenerTiposEntradaPorEvento((int) ($evento['id'] ?? 0));
    }

    if (!$evento) {
        throw new InvalidArgumentException('Selecciona un evento habilitado.');
    }

    $tiposEntrada = $evento['tipos_entrada'] ?? [];
    $tipoEntrada = null;
    $tipoEntradaNombre = null;

    if (is_array($tiposEntrada) && $tiposEntrada !== []) {
        if ($tipoEntradaId <= 0) {
            throw new InvalidArgumentException('Selecciona un tipo de entrada.');
        }

        $tipoEntrada = obtenerTipoEntradaPorId($tipoEntradaId, (int) $evento['id']);
        if (!$tipoEntrada) {
            throw new InvalidArgumentException('Tipo de entrada no válido para este evento.');
        }

        $tipoEntradaNombre = (string) $tipoEntrada['nombre'];
        $valorTipoCatalogo = (float) $tipoEntrada['valor'];

        // Si el tipo es gratuito en catálogo, el registro queda en 0.
        // Si es de pago, se respeta el valor enviado (permite promociones).
        if ($valorTipoCatalogo <= 0) {
            $valor = 0;
        } elseif (!isset($entrada['valor']) || $entrada['valor'] === '' || $entrada['valor'] === null) {
            $valor = $valorTipoCatalogo;
        }
    }

    $eventoEsGratuito = $valor <= 0;

    if ($eventoEsGratuito) {
        $formaPago = 'gratuito';
        $valor = 0;
        if ($estadoPago === '') {
            $estadoPago = 'pagado';
        }
    } elseif (!in_array($formaPago, ['efectivo', 'transferencia'], true)) {
        throw new InvalidArgumentException('Selecciona Efectivo o Transferencia.');
    }

    if ($estadoPago === '') {
        $estadoPago = 'por_cancelar';
    }

    if (!array_key_exists($estadoPago, obtenerEstadosPagoEvento())) {
        throw new InvalidArgumentException('Selecciona un estado válido: Por cancelar o Pagado.');
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
        'evento_id'        => (int) $evento['id'],
        'nombre'           => $nombre,
        'fecha'            => $fecha,
        'telefono'         => $telefono,
        'valor'            => $valor,
        'observacion'      => $observacion,
        'numeracion'       => $numeracion !== '' ? $numeracion : null,
        'forma_pago'       => $formaPago,
        'tipo_entrada_id'  => $tipoEntrada !== null ? (int) $tipoEntrada['id'] : null,
        'tipo_entrada'     => $tipoEntradaNombre,
        'estado_pago'      => $estadoPago,
    ];
}

function actualizarEstadoPagoRegistroEvento(int $id, string $estadoPago): bool
{
    $estadoPago = normalizarEstadoPagoEvento($estadoPago);

    if ($id <= 0 || $estadoPago === '') {
        throw new InvalidArgumentException('Estado de pago no válido.');
    }

    $registro = obtenerRegistroEventoPorId($id);

    if (!$registro) {
        throw new InvalidArgumentException('Registro de evento no encontrado.');
    }

    $pdo = getConnection();
    asegurarColumnasValoresAdicionales($pdo);
    $stmt = $pdo->prepare(
        'UPDATE valores_adicionales SET estado_pago = ? WHERE id = ? AND tipo = ?'
    );

    return $stmt->execute([$estadoPago, $id, TIPO_VALOR_EVENTOS_INTERNO]) && $stmt->rowCount() > 0;
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
 * @param array{nombre: string, fecha: string, valor?: float|int|string, habilitado?: bool|int, requiere_numeracion?: bool|int, tipos_entrada?: array} $datos
 */
function crearEvento(array $datos): int
{
    $datos = normalizarDatosEventoCatalogo($datos);

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);
    $pdo->beginTransaction();

    try {
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

        $eventoId = (int) $pdo->lastInsertId();
        guardarTiposEntradaEvento($eventoId, $datos['tipos_entrada']);
        $pdo->commit();

        return $eventoId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * @param array{nombre: string, fecha: string, valor?: float|int|string, habilitado?: bool|int, requiere_numeracion?: bool|int, tipos_entrada?: array} $datos
 */
function actualizarEventoCatalogo(int $id, array $datos): bool
{
    if ($id <= 0) {
        throw new InvalidArgumentException('Datos de evento inválidos.');
    }

    $datos = normalizarDatosEventoCatalogo($datos);

    $pdo = getConnection();
    asegurarColumnasEventos($pdo);
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'UPDATE eventos
             SET nombre = ?, fecha = ?, valor = ?, habilitado = ?, requiere_numeracion = ?
             WHERE id = ?'
        );
        $ok = $stmt->execute([
            $datos['nombre'],
            $datos['fecha'],
            $datos['valor'],
            $datos['habilitado'],
            $datos['requiere_numeracion'],
            $id,
        ]);

        guardarTiposEntradaEvento($id, $datos['tipos_entrada']);
        $pdo->commit();

        return $ok;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
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
    asegurarColumnasEventos($pdo);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM eventos_tipos_entrada WHERE evento_id = ?')->execute([$id]);
        $stmt = $pdo->prepare('DELETE FROM eventos WHERE id = ?');
        $ok = $stmt->execute([$id]) && $stmt->rowCount() > 0;
        $pdo->commit();

        return $ok;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
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
    return obtenerEvento($id);
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
        'evento_valor_etiqueta'      => formatearTiposEntradaEvento($evento),
        'evento_numeracion_etiqueta' => (int) ($evento['requiere_numeracion'] ?? 0) === 1 ? 'Sí' : 'No',
        'evento_estado_etiqueta'     => etiquetaEstadoEvento((int) ($evento['habilitado'] ?? 0)),
        'generado_en_etiqueta'       => formatearFechaHora(date('Y-m-d H:i:s')),
    ];
}
