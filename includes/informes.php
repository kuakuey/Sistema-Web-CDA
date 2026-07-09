<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/estructura.php';
require_once __DIR__ . '/valores_adicionales.php';
require_once __DIR__ . '/submissions.php';

/**
 * @return array{desde: string, hasta: string}
 */
function validarRangoFechasInforme(string $fechaDesde, string $fechaHasta): array
{
    $desde = trim($fechaDesde);
    $hasta = trim($fechaHasta);

    if ($desde === '' || $hasta === '') {
        throw new InvalidArgumentException('Selecciona la fecha desde y la fecha hasta.');
    }

    $dtDesde = DateTime::createFromFormat('Y-m-d', $desde);
    $dtHasta = DateTime::createFromFormat('Y-m-d', $hasta);

    if (!$dtDesde || !$dtHasta || $dtDesde->format('Y-m-d') !== $desde || $dtHasta->format('Y-m-d') !== $hasta) {
        throw new InvalidArgumentException('Las fechas no son válidas.');
    }

    if ($dtDesde > $dtHasta) {
        throw new InvalidArgumentException('La fecha desde no puede ser posterior a la fecha hasta.');
    }

    return ['desde' => $desde, 'hasta' => $hasta];
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
        'manana' => 'Mañana',
        'tarde'  => 'Tarde',
        'todos'  => 'Todos',
    ];
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
function obtenerOfrendasPorRangoRegistro(string $fechaDesde, string $fechaHasta, string $turno = 'todos'): array
{
    $pdo = getConnection();
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $sql = 'SELECT * FROM ofrendas
         WHERE creado_en >= ? AND creado_en <= ?
         AND ' . $condicionHora .
        ' ORDER BY fecha_ofrenda ASC, casa_vida ASC, id ASC';
    $parametros = array_merge(
        [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'],
        $parametrosHora
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerValoresAdicionalesPorRangoRegistro(string $fechaDesde, string $fechaHasta, string $turno = 'todos'): array
{
    $pdo = getConnection();
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $condicionHora = str_replace('creado_en', 'v.creado_en', $condicionHora);
    $sql = sqlSelectValoresAdicionales() .
        " WHERE v.tipo != '" . TIPO_VALOR_EVENTOS_INTERNO . "'
         AND v.creado_en >= ? AND v.creado_en <= ?
         AND " . $condicionHora .
        ' ORDER BY v.creado_en DESC, v.id DESC';
    $parametros = array_merge(
        [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'],
        $parametrosHora
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerRegistrosEventosPorRangoRegistro(string $fechaDesde, string $fechaHasta, string $turno = 'todos'): array
{
    require_once __DIR__ . '/eventos.php';

    $pdo = getConnection();
    [$condicionHora, $parametrosHora] = construirCondicionHoraTurno($turno);
    $condicionHora = str_replace('creado_en', 'v.creado_en', $condicionHora);
    $sql = 'SELECT v.*, e.nombre AS evento_nombre
            FROM valores_adicionales v
            LEFT JOIN eventos e ON e.id = v.evento_id
            WHERE v.tipo = ?
              AND v.creado_en >= ? AND v.creado_en <= ?
              AND ' . $condicionHora .
        ' ORDER BY v.creado_en DESC, v.id DESC';
    $parametros = array_merge(
        [TIPO_VALOR_EVENTOS_INTERNO, $fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'],
        $parametrosHora
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
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
    string $turno = 'todos'
): array {
    $rango = validarRangoFechasInforme($fechaDesde, $fechaHasta);
    $desde = $rango['desde'];
    $hasta = $rango['hasta'];
    $turno = normalizarTurnoInforme($turno);

    $casas = obtenerCasasVida();
    $ofrendas = obtenerOfrendasPorRangoRegistro($desde, $hasta, $turno);
    $registrosEventos = obtenerRegistrosEventosPorRangoRegistro($desde, $hasta, $turno);
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

    return [
        'fecha_desde'          => $desde,
        'fecha_hasta'          => $hasta,
        'fecha_desde_etiqueta' => formatearFechaInforme($desde),
        'fecha_hasta_etiqueta' => formatearFechaInforme($hasta),
        'generado_en'          => date('d/m/Y H:i'),
        'mostrar_sin_entregar' => $mostrarSinEntregar,
        'turno'                => $turno,
        'turno_etiqueta'       => etiquetaTurnoInforme($turno),
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
