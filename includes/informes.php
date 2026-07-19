<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/estructura.php';
require_once __DIR__ . '/valores_adicionales.php';
require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/filters.php';

/**
 * @return array{
 *     desde: ?string,
 *     hasta: ?string,
 *     fecha_desde_etiqueta: string,
 *     fecha_hasta_etiqueta: string,
 *     periodo_etiqueta: string,
 *     sin_filtro_fecha: bool
 * }
 */
function resolverRangoFechasInforme(string $fechaDesde, string $fechaHasta): array
{
    $desde = trim($fechaDesde);
    $hasta = trim($fechaHasta);

    if ($desde === '' && $hasta === '') {
        return [
            'desde'                  => null,
            'hasta'                  => null,
            'fecha_desde_etiqueta'   => '—',
            'fecha_hasta_etiqueta'   => '—',
            'periodo_etiqueta'       => 'Todos los registros',
            'sin_filtro_fecha'       => true,
        ];
    }

    if ($desde === '' || $hasta === '') {
        throw new InvalidArgumentException('Indica ambas fechas (desde y hasta) o déjalas vacías para descargar todo.');
    }

    $dtDesde = DateTime::createFromFormat('Y-m-d', $desde);
    $dtHasta = DateTime::createFromFormat('Y-m-d', $hasta);

    if (!$dtDesde || !$dtHasta || $dtDesde->format('Y-m-d') !== $desde || $dtHasta->format('Y-m-d') !== $hasta) {
        throw new InvalidArgumentException('Las fechas no son válidas.');
    }

    if ($dtDesde > $dtHasta) {
        throw new InvalidArgumentException('La fecha desde no puede ser posterior a la fecha hasta.');
    }

    return [
        'desde'                  => $desde,
        'hasta'                  => $hasta,
        'fecha_desde_etiqueta'   => formatearFechaInforme($desde),
        'fecha_hasta_etiqueta'   => formatearFechaInforme($hasta),
        'periodo_etiqueta'       => formatearFechaInforme($desde) . ' al ' . formatearFechaInforme($hasta),
        'sin_filtro_fecha'       => false,
    ];
}

/**
 * @deprecated Usar resolverRangoFechasInforme()
 * @return array{desde: string, hasta: string}
 */
function validarRangoFechasInforme(string $fechaDesde, string $fechaHasta): array
{
    $rango = resolverRangoFechasInforme($fechaDesde, $fechaHasta);

    if ($rango['sin_filtro_fecha']) {
        throw new InvalidArgumentException('Selecciona la fecha desde y la fecha hasta.');
    }

    return ['desde' => (string) $rango['desde'], 'hasta' => (string) $rango['hasta']];
}

function obtenerEtiquetasSeccionInforme(): array
{
    return [
        'completo'       => 'General',
        'ofrendas'       => 'Ofrendas',
        'valores'        => 'Valores adicionales',
        'eventos'        => 'Eventos',
        'presentaciones' => 'Presentación de niños',
    ];
}

/**
 * @return array{0: string, 1: array<int, string>}
 */
function construirCondicionFechaRegistro(?string $fechaDesde, ?string $fechaHasta, string $columna = 'creado_en'): array
{
    if ($fechaDesde === null || $fechaHasta === null) {
        return ['1 = 1', []];
    }

    return [
        $columna . ' >= ? AND ' . $columna . ' <= ?',
        [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'],
    ];
}

function normalizarTurnoInforme(string $turno): string
{
    $turno = trim(mb_strtolower($turno));

    if (in_array($turno, ['manana', 'tarde', 'todos'], true)) {
        return $turno;
    }

    return 'todos';
}

function obtenerEtiquetasTurnoInforme(): array
{
    return [
        'manana' => 'Matutina',
        'tarde'  => 'Vespertina',
        'todos'  => 'Todas',
    ];
}

function normalizarSeccionInforme(string $seccion): string
{
    $seccion = trim(mb_strtolower($seccion));

    if (in_array($seccion, ['completo', 'ofrendas', 'eventos', 'valores', 'presentaciones'], true)) {
        return $seccion;
    }

    return 'completo';
}

function normalizarFormatoInforme(string $formato): string
{
    $formato = trim(mb_strtolower($formato));

    return in_array($formato, ['pdf', 'excel'], true) ? $formato : 'pdf';
}

function normalizarEstadoInforme(string $estado): string
{
    $estado = trim(mb_strtolower($estado));

    if (in_array($estado, ['todos', 'entregaron', 'sin_entregar'], true)) {
        return $estado;
    }

    return 'todos';
}

function obtenerEtiquetasEstadoInforme(): array
{
    return [
        'todos'         => 'Todos',
        'entregaron'    => 'Entregaron ofrenda',
        'sin_entregar'  => 'Sin entregar',
    ];
}

function etiquetaEstadoInforme(string $estado): string
{
    $etiquetas = obtenerEtiquetasEstadoInforme();

    return $etiquetas[normalizarEstadoInforme($estado)] ?? 'Todos';
}

function tituloSeccionInforme(string $seccion): string
{
    switch (normalizarSeccionInforme($seccion)) {
        case 'ofrendas':
            return 'Informe de ofrendas';
        case 'eventos':
            return 'Informe de eventos';
        case 'valores':
            return 'Informe de valores adicionales';
        case 'presentaciones':
            return 'Informe de presentación de niños';
        default:
            return 'Informe financiero CDA';
    }
}

function etiquetaTurnoInforme(string $turno): string
{
    $etiquetas = obtenerEtiquetasTurnoInforme();

    return $etiquetas[normalizarTurnoInforme($turno)] ?? 'Todos';
}

/**
 * @return array{0: string, 1: array<int, string>}
 */
function construirCondicionHoraTurno(string $turno): array
{
    switch (normalizarTurnoInforme($turno)) {
        case 'manana':
            return ['TIME(creado_en) >= ? AND TIME(creado_en) <= ?', ['00:01:00', '15:59:59']];

        case 'tarde':
            return ['TIME(creado_en) >= ? AND TIME(creado_en) <= ?', ['16:00:00', '23:59:59']];

        default:
            return ['1 = 1', []];
    }
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerOfrendasPorRangoRegistro(?string $fechaDesde, ?string $fechaHasta, string $turno = 'todos'): array
{
    $pdo = getConnection();
    [$condicionFecha, $parametrosFecha] = construirCondicionFechaRegistro($fechaDesde, $fechaHasta);
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $sql = 'SELECT * FROM ofrendas
         WHERE ' . $condicionFecha .
        ' AND ' . $condicionHora .
        ' ORDER BY fecha_ofrenda ASC, casa_vida ASC, id ASC';
    $parametros = array_merge($parametrosFecha, $parametrosHora);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerValoresAdicionalesPorRangoRegistro(?string $fechaDesde, ?string $fechaHasta, string $turno = 'todos'): array
{
    $pdo = getConnection();
    [$condicionFecha, $parametrosFecha] = construirCondicionFechaRegistro($fechaDesde, $fechaHasta, 'v.creado_en');
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $condicionHora = str_replace('creado_en', 'v.creado_en', $condicionHora);
    $sql = sqlSelectValoresAdicionales() .
        " WHERE v.tipo != '" . TIPO_VALOR_EVENTOS_INTERNO . "'
         AND " . $condicionFecha .
        ' AND ' . $condicionHora .
        ' ORDER BY v.creado_en DESC, v.id DESC';
    $parametros = array_merge($parametrosFecha, $parametrosHora);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerRegistrosEventosPorRangoRegistro(
    ?string $fechaDesde,
    ?string $fechaHasta,
    string $turno = 'todos',
    int $eventoId = 0
): array {
    require_once __DIR__ . '/eventos.php';

    $pdo = getConnection();
    [$condicionFecha, $parametrosFecha] = construirCondicionFechaRegistro($fechaDesde, $fechaHasta, 'v.creado_en');
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $condicionHora = str_replace('creado_en', 'v.creado_en', $condicionHora);

    $condiciones = [
        'v.tipo = ?',
        $condicionFecha,
        $condicionHora,
    ];
    $parametros = array_merge([TIPO_VALOR_EVENTOS_INTERNO], $parametrosFecha, $parametrosHora);

    if ($eventoId > 0) {
        $condiciones[] = 'v.evento_id = ?';
        $parametros[] = $eventoId;
    }

    $sql = 'SELECT v.*, e.nombre AS evento_nombre
            FROM valores_adicionales v
            LEFT JOIN eventos e ON e.id = v.evento_id
            WHERE ' . implode(' AND ', $condiciones) .
        ' ORDER BY v.creado_en DESC, v.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @param array<int, string> $estados
 * @return array<int, string>
 */
function normalizarEstadosPresentacionInforme(array $estados): array
{
    $validos = obtenerEstadosPresentacion();
    $filtrados = [];

    foreach ($estados as $estado) {
        $estado = trim((string) $estado);

        if ($estado !== '' && in_array($estado, $validos, true)) {
            $filtrados[] = $estado;
        }
    }

    return array_values(array_unique($filtrados));
}

function etiquetasEstadosPresentacionInforme(array $estados): string
{
    if ($estados === []) {
        return '—';
    }

    $etiquetas = obtenerEtiquetasEstadosPresentacion();
    $partes = [];

    foreach ($estados as $estado) {
        $partes[] = $etiquetas[$estado] ?? $estado;
    }

    return implode(', ', $partes);
}

/**
 * @param array<int, string> $estados
 * @return array<int, array<string, mixed>>
 */
function obtenerPresentacionesPorRangoRegistro(
    ?string $fechaDesde,
    ?string $fechaHasta,
    string $turno = 'todos',
    array $estados = []
): array {
    $estados = normalizarEstadosPresentacionInforme($estados);

    if ($estados === []) {
        return [];
    }

    $pdo = getConnection();
    [$condicionFecha, $parametrosFecha] = construirCondicionFechaRegistro($fechaDesde, $fechaHasta);
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $marcadores = implode(', ', array_fill(0, count($estados), '?'));
    $sql = 'SELECT * FROM presentaciones_ninos
         WHERE ' . $condicionFecha .
        ' AND ' . $condicionHora .
        ' AND estado IN (' . $marcadores . ')' .
        ' ORDER BY creado_en DESC, id DESC';
    $parametros = array_merge($parametrosFecha, $parametrosHora, $estados);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @param array<int, string> $estadosPresentacion
 * @return array<string, mixed>
 */
function generarInformePresentaciones(
    string $fechaDesde,
    string $fechaHasta,
    string $turno = 'todos',
    array $estadosPresentacion = []
): array {
    $rango = resolverRangoFechasInforme($fechaDesde, $fechaHasta);
    $turno = normalizarTurnoInforme($turno);
    $estados = normalizarEstadosPresentacionInforme($estadosPresentacion);

    if ($estados === []) {
        throw new InvalidArgumentException('Selecciona al menos un estado de presentación.');
    }

    $presentaciones = obtenerPresentacionesPorRangoRegistro($rango['desde'], $rango['hasta'], $turno, $estados);

    foreach ($presentaciones as $indice => $fila) {
        $presentaciones[$indice]['edad_etiqueta'] = formatearEdadPresentacion($fila['fecha_nacimiento'] ?? null);
    }

    return [
        'fecha_desde'                    => $rango['desde'] ?? 'todo',
        'fecha_hasta'                    => $rango['hasta'] ?? 'todo',
        'fecha_desde_etiqueta'           => $rango['fecha_desde_etiqueta'],
        'fecha_hasta_etiqueta'           => $rango['fecha_hasta_etiqueta'],
        'periodo_etiqueta'               => $rango['periodo_etiqueta'],
        'sin_filtro_fecha'               => $rango['sin_filtro_fecha'],
        'generado_en'                    => date('d/m/Y H:i'),
        'turno'                          => $turno,
        'turno_etiqueta'                 => etiquetaTurnoInforme($turno),
        'estados_presentacion'           => $estados,
        'estados_presentacion_etiqueta'  => etiquetasEstadosPresentacionInforme($estados),
        'seccion_exportacion'            => 'presentaciones',
        'resumen'                        => [
            'cantidad_presentaciones' => count($presentaciones),
        ],
        'presentaciones'                 => $presentaciones,
    ];
}

function formatearFechaInforme(?string $fecha): string
{
    if ($fecha === null || $fecha === '') {
        return '—';
    }

    $dt = date_create($fecha);

    return $dt ? $dt->format('d/m/Y') : $fecha;
}

function formatearFechaInformeLarga(?string $fecha): string
{
    if ($fecha === null || $fecha === '') {
        return '—';
    }

    $dt = date_create($fecha);

    if (!$dt) {
        return $fecha;
    }

    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    $dia = $dt->format('d');
    $mes = $meses[(int) $dt->format('n')] ?? $dt->format('m');

    return $dia . ' ' . ucfirst($mes);
}

/**
 * @param array<int, array<string, mixed>> $ofrendas
 * @return array<int, array<string, mixed>>
 */
function agruparOfrendasPorFecha(array $ofrendas): array
{
    $grupos = [];

    foreach ($ofrendas as $ofrenda) {
        $fecha = (string) ($ofrenda['fecha_ofrenda'] ?? '');

        if ($fecha === '') {
            continue;
        }

        if (!isset($grupos[$fecha])) {
            $grupos[$fecha] = [
                'fecha'           => $fecha,
                'fecha_etiqueta'  => formatearFechaInformeLarga($fecha),
                'ofrendas'        => [],
                'total_monto'     => 0.0,
            ];
        }

        $grupos[$fecha]['ofrendas'][] = $ofrenda;
        $grupos[$fecha]['total_monto'] += (float) $ofrenda['monto'];
    }

    ksort($grupos);

    return array_values($grupos);
}

/**
 * @return array<string, mixed>
 */
function generarInformeOfrendasYValores(
    string $fechaDesde,
    string $fechaHasta,
    bool $mostrarSinEntregar = false,
    string $turno = 'todos',
    string $estadoOfrenda = 'todos',
    int $eventoId = 0
): array {
    $rango = resolverRangoFechasInforme($fechaDesde, $fechaHasta);
    $desde = $rango['desde'];
    $hasta = $rango['hasta'];
    $turno = normalizarTurnoInforme($turno);
    $estadoOfrenda = normalizarEstadoInforme($estadoOfrenda);
    $eventoId = max(0, $eventoId);

    if ($estadoOfrenda === 'sin_entregar') {
        $mostrarSinEntregar = true;
    } elseif ($estadoOfrenda === 'entregaron') {
        $mostrarSinEntregar = false;
    }

    $casas = obtenerCasasVida();
    $ofrendas = obtenerOfrendasPorRangoRegistro($desde, $hasta, $turno);
    $registrosEventos = obtenerRegistrosEventosPorRangoRegistro($desde, $hasta, $turno, $eventoId);
    $valoresAdicionales = obtenerValoresAdicionalesPorRangoRegistro($desde, $hasta, $turno);

    $territorioPorCasaId = [];

    foreach ($casas as $casa) {
        $territorioPorCasaId[(int) $casa['id']] = $casa['territorio_nombre'] ?? '';
    }

    foreach ($ofrendas as $indice => $ofrenda) {
        $casaId = (int) ($ofrenda['casa_id'] ?? 0);
        $ofrendas[$indice]['territorio'] = $casaId > 0
            ? ($territorioPorCasaId[$casaId] ?? '')
            : '';
    }

    $ofrendasPorCasaId = [];
    $ofrendasPorNombre = [];

    foreach ($ofrendas as $ofrenda) {
        $casaId = (int) ($ofrenda['casa_id'] ?? 0);

        if ($casaId > 0) {
            $ofrendasPorCasaId[$casaId][] = $ofrenda;
            continue;
        }

        $nombre = mb_strtolower(trim((string) ($ofrenda['casa_vida'] ?? '')));

        if ($nombre !== '') {
            $ofrendasPorNombre[$nombre][] = $ofrenda;
        }
    }

    $casasDieron = [];
    $casasNoDieron = [];
    $totalMontoOfrendas = 0.0;

    foreach ($casas as $casa) {
        $casaId = (int) $casa['id'];
        $nombreKey = mb_strtolower(trim($casa['nombre']));
        $ofrendasCasa = $ofrendasPorCasaId[$casaId] ?? [];

        if (empty($ofrendasCasa) && isset($ofrendasPorNombre[$nombreKey])) {
            $ofrendasCasa = $ofrendasPorNombre[$nombreKey];
        }

        $montoCasa = 0.0;

        foreach ($ofrendasCasa as $ofrendaCasa) {
            $montoCasa += (float) $ofrendaCasa['monto'];
        }

        $lider = trim(($casa['lider_nombre'] ?? '') . ' ' . ($casa['lider_apellido'] ?? ''));

        $fila = [
            'id'                => $casaId,
            'nombre'            => $casa['nombre'],
            'territorio'        => $casa['territorio_nombre'] ?? '',
            'lider'             => $lider,
            'ofrendas'          => $ofrendasCasa,
            'total_monto'       => $montoCasa,
            'cantidad_ofrendas' => count($ofrendasCasa),
        ];

        if (!empty($ofrendasCasa)) {
            $casasDieron[] = $fila;
            $totalMontoOfrendas += $montoCasa;
        } else {
            $casasNoDieron[] = $fila;
        }
    }

    usort($casasDieron, static function (array $a, array $b): int {
        return strcasecmp($a['nombre'], $b['nombre']);
    });

    usort($casasNoDieron, static function (array $a, array $b): int {
        return strcasecmp($a['nombre'], $b['nombre']);
    });

    $totalMontoValores = 0.0;
    $totalMontoEventos = 0.0;

    foreach ($registrosEventos as $registroEvento) {
        $totalMontoEventos += (float) $registroEvento['valor'];
    }

    foreach ($valoresAdicionales as $valor) {
        $totalMontoValores += (float) $valor['valor'];
    }

    if ($estadoOfrenda === 'sin_entregar') {
        $ofrendas = [];
        $ofrendasPorFecha = [];
        $casasDieron = [];
        $totalMontoOfrendas = 0.0;
    } elseif ($estadoOfrenda === 'entregaron') {
        $casasNoDieron = [];
    }

    $eventoEtiqueta = 'Todos los eventos';

    if ($eventoId > 0) {
        require_once __DIR__ . '/eventos.php';
        $evento = obtenerEvento($eventoId);
        $eventoEtiqueta = $evento ? (string) ($evento['nombre'] ?? 'Evento #' . $eventoId) : 'Evento #' . $eventoId;
    }

    return [
        'fecha_desde'          => $desde ?? 'todo',
        'fecha_hasta'          => $hasta ?? 'todo',
        'fecha_desde_etiqueta' => $rango['fecha_desde_etiqueta'],
        'fecha_hasta_etiqueta' => $rango['fecha_hasta_etiqueta'],
        'periodo_etiqueta'     => $rango['periodo_etiqueta'],
        'sin_filtro_fecha'     => $rango['sin_filtro_fecha'],
        'generado_en'          => date('d/m/Y H:i'),
        'mostrar_sin_entregar' => $mostrarSinEntregar,
        'turno'                => $turno,
        'turno_etiqueta'       => etiquetaTurnoInforme($turno),
        'estado_ofrenda'       => $estadoOfrenda,
        'estado_ofrenda_etiqueta' => etiquetaEstadoInforme($estadoOfrenda),
        'evento_id'            => $eventoId,
        'evento_etiqueta'      => $eventoEtiqueta,
        'seccion_exportacion'  => 'completo',
        'resumen'              => [
            'total_casas'                   => count($casas),
            'casas_dieron'                  => count($casasDieron),
            'casas_no_dieron'               => count($casasNoDieron),
            'cantidad_registros_ofrendas'   => count($ofrendas),
            'cantidad_ofrendas'             => count($ofrendas),
            'total_monto_ofrendas'          => $totalMontoOfrendas,
            'cantidad_registros_eventos'    => count($registrosEventos),
            'total_monto_eventos'           => $totalMontoEventos,
            'cantidad_valores_adicionales'  => count($valoresAdicionales),
            'total_monto_valores'           => $totalMontoValores,
            'total_general'                 => $totalMontoOfrendas + $totalMontoEventos + $totalMontoValores,
        ],
        'casas_dieron'         => $casasDieron,
        'casas_no_dieron'      => $casasNoDieron,
        'ofrendas'             => $ofrendas,
        'ofrendas_por_fecha'   => agruparOfrendasPorFecha($ofrendas),
        'registros_eventos'    => $registrosEventos,
        'valores_adicionales'  => $valoresAdicionales,
    ];
}
