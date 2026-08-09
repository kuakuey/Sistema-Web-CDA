<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/valores_adicionales.php';
require_once __DIR__ . '/detalle_registro.php';

/**
 * @return array{0: string, 1: string}
 */
function rangoHorasRegistrosGenerales(?string $fecha = null): array
{
    $fecha = trim((string) ($fecha ?? date('Y-m-d')));

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $fecha = date('Y-m-d');
    }

    return [$fecha . ' 00:00:00', $fecha . ' 23:59:59'];
}

function puedeVerRegistrosGenerales(string $rol): bool
{
    return tienePermisoDetalle($rol, 'generales', 'ver')
        || tienePermisoSeccion($rol, 'generales')
        || obtenerTiposInscripcionPermitidos($rol) !== []
        || puedeVerPresentaciones($rol)
        || puedeVerOfrendas($rol)
        || puedeVerValoresAdicionales($rol)
        || puedeGestionarEventos($rol)
        || puedeVerConsejerias($rol);
}

function obtenerNombreRegistradoPorFila(array $fila): string
{
    $registrado = trim((string) ($fila['registrado_por_nombre'] ?? ''));

    if ($registrado === '') {
        $registrado = trim((string) ($fila['agente_usuario'] ?? ''));
    }

    if ($registrado === '') {
        return '—';
    }

    if (str_starts_with($registrado, 'Sistema Web — ')) {
        return substr($registrado, strlen('Sistema Web — '));
    }

    if (str_starts_with($registrado, 'WordPress/')) {
        return 'Formulario Publico';
    }

    return $registrado;
}

/**
 * @return array<int, array<string, mixed>>
 */
function buscarRegistrosGeneralesDelDia(string $rol, ?string $fecha = null): array
{
    [$desde, $hasta] = rangoHorasRegistrosGenerales($fecha);
    $registros = [];

    $tiposInscripcion = obtenerTiposInscripcionPermitidos($rol);

    if ($tiposInscripcion !== []) {
        $pdo = getConnection();
        $marcadores = implode(',', array_fill(0, count($tiposInscripcion), '?'));
        $stmt = $pdo->prepare(
            "SELECT *, 'inscripcion' AS origen_registro
             FROM inscripciones
             WHERE tipo_formulario IN ($marcadores)
               AND creado_en >= ?
               AND creado_en <= ?"
        );
        $stmt->execute(array_merge($tiposInscripcion, [$desde, $hasta]));

        foreach ($stmt->fetchAll() as $fila) {
            $registros[] = normalizarRegistroGeneralInscripcion($fila);
        }
    }

    if (puedeVerPresentaciones($rol)) {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "SELECT *, 'presentacion' AS origen_registro
             FROM presentaciones_ninos
             WHERE creado_en >= ? AND creado_en <= ?"
        );
        $stmt->execute([$desde, $hasta]);

        foreach ($stmt->fetchAll() as $fila) {
            $registros[] = normalizarRegistroGeneralPresentacion($fila);
        }
    }

    if (puedeVerOfrendas($rol)) {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "SELECT *, 'ofrenda' AS origen_registro
             FROM ofrendas
             WHERE creado_en >= ? AND creado_en <= ?"
        );
        $stmt->execute([$desde, $hasta]);

        foreach ($stmt->fetchAll() as $fila) {
            $registros[] = normalizarRegistroGeneralOfrenda($fila);
        }
    }

    if (puedeVerValoresAdicionales($rol)) {
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "SELECT v.*, 'valor_adicional' AS origen_registro
             FROM valores_adicionales v
             WHERE v.tipo != ?
               AND v.creado_en >= ?
               AND v.creado_en <= ?"
        );
        $stmt->execute([TIPO_VALOR_EVENTOS_INTERNO, $desde, $hasta]);

        foreach ($stmt->fetchAll() as $fila) {
            $registros[] = normalizarRegistroGeneralValorAdicional($fila);
        }
    }

    if (puedeGestionarEventos($rol)) {
        require_once __DIR__ . '/eventos.php';
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "SELECT v.*, e.nombre AS evento_nombre, 'evento' AS origen_registro
             FROM valores_adicionales v
             LEFT JOIN eventos e ON e.id = v.evento_id
             WHERE v.tipo = ?
               AND v.creado_en >= ?
               AND v.creado_en <= ?"
        );
        $stmt->execute([TIPO_VALOR_EVENTOS_INTERNO, $desde, $hasta]);

        foreach ($stmt->fetchAll() as $fila) {
            $registros[] = normalizarRegistroGeneralEvento($fila);
        }
    }

    if (puedeVerConsejerias($rol)) {
        require_once __DIR__ . '/consejerias.php';
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "SELECT *, 'consejeria' AS origen_registro
             FROM consejerias
             WHERE creado_en >= ? AND creado_en <= ?"
        );
        $stmt->execute([$desde, $hasta]);

        foreach ($stmt->fetchAll() as $fila) {
            $registros[] = normalizarRegistroGeneralConsejeria($fila);
        }
    }

    usort($registros, static function (array $a, array $b): int {
        return strcmp((string) ($b['creado_en'] ?? ''), (string) ($a['creado_en'] ?? ''));
    });

    return $registros;
}

function contarRegistrosGeneralesDelDia(string $rol, ?string $fecha = null): int
{
    return count(buscarRegistrosGeneralesDelDia($rol, $fecha));
}

/**
 * @param array<int, array<string, mixed>> $registros
 * @return array<int, array<string, mixed>>
 */
function paginarRegistrosGenerales(array $registros, int $limite, int $offset): array
{
    $limite = normalizarLimiteRegistros($limite);
    $offset = max(0, $offset);

    return array_slice($registros, $offset, $limite);
}

/**
 * @return array<string, mixed>
 */
function normalizarRegistroGeneralInscripcion(array $fila): array
{
    $etiquetas = obtenerEtiquetasTiposFormulario();
    $tipo = (string) ($fila['tipo_formulario'] ?? '');

    return [
        'origen'          => 'inscripcion',
        'id'              => (int) ($fila['id'] ?? 0),
        'seccion'         => $tipo,
        'seccion_etiqueta'=> $etiquetas[$tipo] ?? $tipo,
        'creado_en'       => (string) ($fila['creado_en'] ?? ''),
        'registrado_por'  => obtenerNombreRegistradoPorFila($fila),
        'titulo'          => trim(($fila['nombre'] ?? '') . ' ' . ($fila['apellido'] ?? '')),
        'resumen'         => trim((string) ($fila['celular'] ?? '')) !== '' ? (string) $fila['celular'] : '—',
        'fila'            => $fila,
    ];
}

/**
 * @return array<string, mixed>
 */
function normalizarRegistroGeneralPresentacion(array $fila): array
{
    require_once __DIR__ . '/presentaciones.php';
    $etiquetasEstados = obtenerEtiquetasEstadosPresentacion();
    $estado = (string) ($fila['estado'] ?? '');

    return [
        'origen'          => 'presentacion',
        'id'              => (int) ($fila['id'] ?? 0),
        'seccion'         => 'presentaciones',
        'seccion_etiqueta'=> 'Presentación niños',
        'creado_en'       => (string) ($fila['creado_en'] ?? ''),
        'registrado_por'  => obtenerNombreRegistradoPorFila($fila),
        'titulo'          => trim((string) ($fila['nombre_presentado'] ?? '')),
        'resumen'         => $etiquetasEstados[$estado] ?? ($estado !== '' ? $estado : '—'),
        'fila'            => $fila,
    ];
}

/**
 * @return array<string, mixed>
 */
function normalizarRegistroGeneralOfrenda(array $fila): array
{
    return [
        'origen'          => 'ofrenda',
        'id'              => (int) ($fila['id'] ?? 0),
        'seccion'         => 'ofrendas',
        'seccion_etiqueta'=> 'Ofrendas',
        'creado_en'       => (string) ($fila['creado_en'] ?? ''),
        'registrado_por'  => obtenerNombreRegistradoPorFila($fila),
        'titulo'          => trim((string) ($fila['casa_vida'] ?? '')) !== '' ? (string) $fila['casa_vida'] : '—',
        'resumen'         => formatearMonto((float) ($fila['monto'] ?? 0)),
        'fila'            => $fila,
    ];
}

/**
 * @return array<string, mixed>
 */
function normalizarRegistroGeneralValorAdicional(array $fila): array
{
    $tipo = (string) ($fila['tipo'] ?? '');

    return [
        'origen'          => 'valor_adicional',
        'id'              => (int) ($fila['id'] ?? 0),
        'seccion'         => 'valores_adicionales',
        'seccion_etiqueta'=> 'Valores adicionales',
        'creado_en'       => (string) ($fila['creado_en'] ?? ''),
        'registrado_por'  => obtenerNombreRegistradoPorFila($fila),
        'titulo'          => trim((string) ($fila['nombre'] ?? '')) !== '' ? (string) $fila['nombre'] : '—',
        'resumen'         => etiquetaTipoValorAdicional($tipo) . ' · ' . formatearMonto((float) ($fila['valor'] ?? 0)),
        'fila'            => $fila,
    ];
}

/**
 * @return array<string, mixed>
 */
function normalizarRegistroGeneralEvento(array $fila): array
{
    require_once __DIR__ . '/eventos.php';
    $eventoNombre = trim((string) ($fila['evento_nombre'] ?? ''));

    return [
        'origen'          => 'evento',
        'id'              => (int) ($fila['id'] ?? 0),
        'seccion'         => 'eventos',
        'seccion_etiqueta'=> 'Eventos',
        'creado_en'       => (string) ($fila['creado_en'] ?? ''),
        'registrado_por'  => obtenerNombreRegistradoPorFila($fila),
        'titulo'          => trim((string) ($fila['nombre'] ?? '')) !== '' ? (string) $fila['nombre'] : '—',
        'resumen'         => ($eventoNombre !== '' ? $eventoNombre : 'Evento') . ' · ' . formatearMonto((float) ($fila['valor'] ?? 0)),
        'fila'            => $fila,
    ];
}

/**
 * @return array<string, mixed>
 */
function normalizarRegistroGeneralConsejeria(array $fila): array
{
    require_once __DIR__ . '/consejerias.php';

    return [
        'origen'          => 'consejeria',
        'id'              => (int) ($fila['id'] ?? 0),
        'seccion'         => 'consejeria',
        'seccion_etiqueta'=> 'Consejería',
        'creado_en'       => (string) ($fila['creado_en'] ?? ''),
        'registrado_por'  => obtenerNombreRegistradoPorFila($fila),
        'titulo'          => trim((string) ($fila['nombre_completo'] ?? '')) !== '' ? (string) $fila['nombre_completo'] : '—',
        'resumen'         => etiquetaTipoConsejeria((string) ($fila['tipo_consejeria'] ?? '')),
        'fila'            => $fila,
    ];
}

/**
 * @param array<string, mixed> $registro
 * @return array<int, array{etiqueta: string, valor: string}>
 */
function construirDetalleRegistroGeneral(array $registro, array $etiquetasFormulario, array $etiquetasEstadosPresentacion): array
{
    $fila = $registro['fila'] ?? [];

    switch ($registro['origen'] ?? '') {
        case 'inscripcion':
            return construirDetalleInscripcion($fila, $etiquetasFormulario);
        case 'presentacion':
            return construirDetallePresentacion($fila, $etiquetasEstadosPresentacion);
        case 'ofrenda':
            return construirDetalleOfrenda($fila);
        case 'valor_adicional':
            return construirDetalleValorAdicional($fila);
        case 'evento':
            return construirDetalleRegistroEvento($fila);
        case 'consejeria':
            return construirDetalleConsejeria($fila);
        default:
            return [];
    }
}

function tituloDetalleRegistroGeneral(array $registro): string
{
    $etiquetas = [
        'inscripcion'     => 'Inscripción',
        'presentacion'    => 'Presentación',
        'ofrenda'         => 'Ofrenda',
        'valor_adicional' => 'Valor adicional',
        'evento'          => 'Registro de evento',
        'consejeria'      => 'Consejería',
    ];

    $origen = (string) ($registro['origen'] ?? '');
    $prefijo = $etiquetas[$origen] ?? 'Registro';

    return $prefijo . ' #' . (int) ($registro['id'] ?? 0);
}
