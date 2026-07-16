<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/paginacion.php';

function parsearFiltrosRegistros(array $entrada): array
{
    return [
        'buscar'      => trim((string) ($entrada['buscar'] ?? '')),
        'fecha_desde' => trim((string) ($entrada['fecha_desde'] ?? '')),
        'fecha_hasta' => trim((string) ($entrada['fecha_hasta'] ?? '')),
        'zona'        => trim((string) ($entrada['zona'] ?? '')),
        'estado'      => trim((string) ($entrada['estado'] ?? '')),
        'contactado'  => trim((string) ($entrada['contactado'] ?? 'todos')),
        'monto_min'   => trim((string) ($entrada['monto_min'] ?? '')),
        'monto_max'   => trim((string) ($entrada['monto_max'] ?? '')),
        'tipo_valor'       => trim((string) ($entrada['tipo_valor'] ?? '')),
        'tipo_consejeria'  => trim((string) ($entrada['tipo_consejeria'] ?? '')),
        'tipo_transporte'  => trim((string) ($entrada['tipo_transporte'] ?? '')),
    ];
}

function construirConsultaFiltros(array $filtros): string
{
    $parametros = [];

    foreach ($filtros as $clave => $valor) {
        if ($valor !== '' && $valor !== 'todos') {
            $parametros[$clave] = $valor;
        }
    }

    return http_build_query($parametros);
}

/**
 * @param array<int, string>      $tiposFormulario
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlInscripciones(array $tiposFormulario, ?string $tipoUnico, array $filtros): array
{
    $condiciones = [];
    $parametros = [];

    if ($tipoUnico !== null && $tipoUnico !== '') {
        $condiciones[] = 'tipo_formulario = ?';
        $parametros[] = $tipoUnico;
    } elseif (!empty($tiposFormulario)) {
        $marcadores = implode(',', array_fill(0, count($tiposFormulario), '?'));
        $condiciones[] = "tipo_formulario IN ($marcadores)";
        foreach ($tiposFormulario as $tipo) {
            $parametros[] = $tipo;
        }
    } else {
        $condiciones[] = '1 = 0';
    }

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR celular LIKE ? OR direccion LIKE ? OR zona LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'creado_en >= ?';
        $parametros[] = $filtros['fecha_desde'] . ' 00:00:00';
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'creado_en <= ?';
        $parametros[] = $filtros['fecha_hasta'] . ' 23:59:59';
    }

    if ($filtros['zona'] !== '') {
        $condiciones[] = 'zona = ?';
        $parametros[] = $filtros['zona'];
    }

    if ($filtros['contactado'] !== '' && $filtros['contactado'] !== 'todos') {
        $condiciones[] = 'contactado = ?';
        $parametros[] = (int) $filtros['contactado'];
    }

    if ($filtros['estado'] !== '' && $tipoUnico === 'bautismo') {
        $condiciones[] = 'estado_bautismo = ?';
        $parametros[] = $filtros['estado'];
    }

    $sql = 'SELECT * FROM inscripciones WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY creado_en DESC, id DESC';

    return [$sql, $parametros];
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlPresentaciones(array $filtros): array
{
    $condiciones = ['1 = 1'];
    $parametros = [];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(nombre_padre LIKE ? OR nombre_madre LIKE ? OR nombre_presentado LIKE ? OR telefono_papa LIKE ? OR telefono_mama LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda, $busqueda, $busqueda, $busqueda]);
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'creado_en >= ?';
        $parametros[] = $filtros['fecha_desde'] . ' 00:00:00';
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'creado_en <= ?';
        $parametros[] = $filtros['fecha_hasta'] . ' 23:59:59';
    }

    if ($filtros['estado'] !== '') {
        $condiciones[] = 'estado = ?';
        $parametros[] = $filtros['estado'];
    }

    $sql = 'SELECT * FROM presentaciones_ninos WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY creado_en DESC, id DESC';

    return [$sql, $parametros];
}

/**
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlOfrendas(array $filtros): array
{
    $condiciones = ['1 = 1'];
    $parametros = [];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(casa_vida LIKE ? OR lider LIKE ?)';
        $parametros = array_merge($parametros, [$busqueda, $busqueda]);
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'fecha_ofrenda >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'fecha_ofrenda <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    if ($filtros['monto_min'] !== '' && is_numeric($filtros['monto_min'])) {
        $condiciones[] = 'monto >= ?';
        $parametros[] = (float) $filtros['monto_min'];
    }

    if ($filtros['monto_max'] !== '' && is_numeric($filtros['monto_max'])) {
        $condiciones[] = 'monto <= ?';
        $parametros[] = (float) $filtros['monto_max'];
    }

    $sql = 'SELECT * FROM ofrendas WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY creado_en DESC, id DESC';

    return [$sql, $parametros];
}

/**
 * @param array<int, string> $tiposFormulario
 * @return array<int, array<string, mixed>>
 */
function buscarInscripciones(
    array $tiposFormulario,
    ?string $tipoUnico,
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlInscripciones($tiposFormulario, $tipoUnico, $filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarInscripcionesFiltradas(array $tiposFormulario, ?string $tipoUnico, array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlInscripciones($tiposFormulario, $tipoUnico, $filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/^SELECT \* FROM/i', 'SELECT COUNT(*) FROM', $sqlConteo);

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarPresentacionesNinos(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlPresentaciones($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarPresentacionesFiltradas(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlPresentaciones($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/^SELECT \* FROM/i', 'SELECT COUNT(*) FROM', $sqlConteo);

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarOfrendas(
    array $filtros,
    int $limite = REGISTROS_POR_PAGINA,
    int $offset = 0
): array {
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlOfrendas($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

function contarOfrendasFiltradas(array $filtros): int
{
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlOfrendas($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/^SELECT \* FROM/i', 'SELECT COUNT(*) FROM', $sqlConteo);

    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

function obtenerEstadosPresentacion(): array
{
    return ['recibido', 'contactado', 'confirmado', 'presentado'];
}

function obtenerEtiquetasEstadosPresentacion(): array
{
    return [
        'recibido'    => 'Recibido',
        'contactado'  => 'Contactado',
        'confirmado'  => 'Confirmado',
        'presentado'  => 'Presentado',
    ];
}

function esEstadoPresentacionValido(string $estado): bool
{
    return in_array($estado, obtenerEstadosPresentacion(), true);
}

function etiquetaEstadoPresentacion(string $estado): string
{
    $etiquetas = obtenerEtiquetasEstadosPresentacion();

    return $etiquetas[$estado] ?? $estado;
}

function obtenerEstadosBautismo(): array
{
    return ['ingresado', 'bautizado'];
}

function obtenerEtiquetasEstadosBautismo(): array
{
    return [
        'ingresado'  => 'Ingresado',
        'bautizado'  => 'Bautizado',
    ];
}

function esEstadoBautismoValido(string $estado): bool
{
    return in_array($estado, obtenerEstadosBautismo(), true);
}

function etiquetaEstadoBautismo(string $estado): string
{
    $etiquetas = obtenerEtiquetasEstadosBautismo();

    return $etiquetas[$estado] ?? $estado;
}

function etiquetaEstadoBautismoRegistro(array $fila): string
{
    $estado = (string) ($fila['estado_bautismo'] ?? 'ingresado');

    if ($estado !== 'bautizado') {
        return etiquetaEstadoBautismo($estado);
    }

    $fecha = $fila['fecha_bautismo'] ?? null;

    if ($fecha) {
        $dt = date_create((string) $fecha);
        $fechaEtiqueta = $dt ? $dt->format('d/m/Y') : '—';
    } else {
        $fechaEtiqueta = date('d/m/Y');
    }

    return 'Bautizado - ' . $fechaEtiqueta;
}
