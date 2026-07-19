<?php

require_once __DIR__ . '/submissions.php';
require_once __DIR__ . '/filters.php';
require_once __DIR__ . '/presentaciones.php';

/**
 * @return array<string, mixed>
 */
function metaPresentacionesApi(): array
{
    return [
        'opciones_parentesco' => opcionesParentescoRepresentante(),
    ];
}

/**
 * @return array<string, mixed>|null
 */
function formatearRepresentantePresentacionParaApi(array $fila, int $numero): ?array
{
    $representante = representantePresentacionDesdeFila($fila, $numero);

    if ($numero === 2 && $representante['nombre'] === '') {
        return null;
    }

    return [
        'parentesco'          => $representante['parentesco'] !== '' ? $representante['parentesco'] : null,
        'parentesco_etiqueta' => etiquetaParentescoRepresentante($representante['parentesco']),
        'nombre'              => $representante['nombre'],
        'telefono'            => $representante['telefono'],
        'etiqueta'            => formatearNombreRepresentantePresentacion($fila, $numero),
    ];
}

/**
 * @return array<string, mixed>
 */
function formatearPresentacionParaApi(array $fila): array
{
    $representante1 = formatearRepresentantePresentacionParaApi($fila, 1);
    $representante2 = formatearRepresentantePresentacionParaApi($fila, 2);

    return [
        'id'                        => (int) $fila['id'],
        'nombre_presentado'         => $fila['nombre_presentado'],
        'fecha_nacimiento'          => $fila['fecha_nacimiento'] ?? null,
        'fecha_nacimiento_etiqueta' => formatearFechaNacimiento($fila['fecha_nacimiento'] ?? null),
        'edad'                      => calcularEdadDesdeFechaNacimiento($fila['fecha_nacimiento'] ?? null),
        'edad_etiqueta'             => formatearEdadPresentacion($fila['fecha_nacimiento'] ?? null),
        'representantes'            => array_values(array_filter([$representante1, $representante2])),
        'representante_1'           => $representante1,
        'representante_2'           => $representante2,
        'estado'                    => $fila['estado'],
        'estado_etiqueta'           => etiquetaEstadoPresentacion($fila['estado']),
        'creado_en'                 => $fila['creado_en'] ?? null,
    ];
}

/**
 * @return array<string, mixed>
 */
function formatearPresentacionPublicaParaApi(array $fila): array
{
    $presentacion = formatearPresentacionParaApi($fila);
    unset($presentacion['creado_en']);

    $presentacion['representante_1'] = acortarRepresentantePresentacionPublico($presentacion['representante_1'] ?? null);
    $presentacion['representante_2'] = acortarRepresentantePresentacionPublico($presentacion['representante_2'] ?? null);

    $representantes = [];
    if ($presentacion['representante_1']) {
        $representantes[] = $presentacion['representante_1'];
    }
    if ($presentacion['representante_2']) {
        $representantes[] = $presentacion['representante_2'];
    }
    $presentacion['representantes'] = $representantes;

    return $presentacion;
}

/**
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $meta
 * @return array<string, mixed>
 */
function registrarPresentacionesNinosDesdePayload(array $payload, array $meta): array
{
    $representantes = normalizarRepresentantesPresentacion($payload);
    $presentados = parsearPresentadosPresentacion($payload);
    $ids = insertarPresentacionesNinosGrupo($representantes, $presentados, $meta);
    $cantidad = count($ids);
    $registros = [];

    foreach ($ids as $indice => $id) {
        $presentado = $presentados[$indice] ?? ['nombre_presentado' => '', 'fecha_nacimiento' => null];
        $registros[] = [
            'id'                => $id,
            'nombre_presentado' => $presentado['nombre_presentado'],
            'fecha_nacimiento'  => $presentado['fecha_nacimiento'],
            'representante_1'   => formatearRepresentantePresentacionParaApiDesdeDatos($representantes, 1),
            'representante_2'   => formatearRepresentantePresentacionParaApiDesdeDatos($representantes, 2),
        ];
    }

    return [
        'exito'      => true,
        'mensaje'    => $cantidad > 1
            ? $cantidad . ' inscripciones de presentación enviadas correctamente.'
            : 'Inscripción de presentación enviada correctamente.',
        'cantidad'   => $cantidad,
        'ids'        => $ids,
        'id'         => $ids[0] ?? null,
        'registros'  => $registros,
    ];
}

function esPayloadRegistroPresentacion(array $payload): bool
{
    if (isset($payload['presentados']) && is_array($payload['presentados'])) {
        return true;
    }

    if (trim((string) ($payload['nombre_presentado'] ?? '')) !== '') {
        return true;
    }

    if (trim((string) ($payload['representante_1_nombre'] ?? '')) !== '') {
        return true;
    }

    if (isset($payload['representantes']) && is_array($payload['representantes']) && $payload['representantes'] !== []) {
        return true;
    }

    return trim((string) ($payload['nombre_padre'] ?? '')) !== '';
}

/**
 * Lista pública: solo niños contactados o confirmados.
 */
function responderListaPresentacionesPublica(): void
{
    $registros = listarPresentacionesNinosPorEstados(['contactado', 'confirmado']);
    $lista = [];

    foreach ($registros as $fila) {
        $lista[] = formatearPresentacionPublicaParaApi($fila);
    }

    echo json_encode([
        'exito'     => true,
        'meta'      => metaPresentacionesApi(),
        'registros' => $lista,
        'total'     => count($lista),
    ]);
}

/**
 * Marca Entregar Diploma desde el portal privado (solo clave API, sin sesión de usuario).
 */
function responderEntregarDiplomaDesdeApi(int $id): void
{
    require_once __DIR__ . '/roles.php';

    if ($id <= 0) {
        throw new InvalidArgumentException('ID no válido.');
    }

    $pdo = getConnection();
    $stmtActual = $pdo->prepare('SELECT estado FROM presentaciones_ninos WHERE id = ?');
    $stmtActual->execute([$id]);
    $filaActual = $stmtActual->fetch();

    if (!$filaActual) {
        throw new InvalidArgumentException('No se encontró el registro.');
    }

    $estadoActual = (string) ($filaActual['estado'] ?? '');

    if (!in_array($estadoActual, ['contactado', 'confirmado'], true)) {
        throw new InvalidArgumentException('Solo puedes marcar Entregar Diploma desde Contactado o Confirmado.');
    }

    $estado = 'entregar_diploma';

    if (!actualizarEstadoPresentacionNino($id, $estado, ROL_ADMIN)) {
        throw new InvalidArgumentException('No se pudo actualizar el estado.');
    }

    echo json_encode([
        'exito'           => true,
        'mensaje'         => 'Estado actualizado a ' . etiquetaEstadoPresentacion($estado) . '.',
        'id'              => $id,
        'estado'          => $estado,
        'estado_etiqueta' => etiquetaEstadoPresentacion($estado),
    ]);
}

/**
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $meta
 */
function responderRegistroPresentacionesPublico(array $payload, array $meta): void
{
    $resultado = registrarPresentacionesNinosDesdePayload($payload, $meta);
    $resultado['meta'] = metaPresentacionesApi();

    echo json_encode($resultado);
}

/**
 * Formatea datos normalizados de representantes para respuesta API de registro.
 *
 * @param array<string, string|null> $representantes
 * @return array<string, mixed>|null
 */
function formatearRepresentantePresentacionParaApiDesdeDatos(array $representantes, int $numero): ?array
{
    if ($numero === 1) {
        $fila = [
            'parentesco_representante_1' => $representantes['parentesco_representante_1'] ?? '',
            'nombre_padre'               => $representantes['nombre_padre'] ?? '',
            'telefono_papa'              => $representantes['telefono_papa'] ?? '',
        ];

        return formatearRepresentantePresentacionParaApi($fila, 1);
    }

    if (trim((string) ($representantes['nombre_madre'] ?? '')) === '') {
        return null;
    }

    $fila = [
        'parentesco_representante_2' => $representantes['parentesco_representante_2'] ?? '',
        'nombre_madre'               => $representantes['nombre_madre'] ?? '',
        'telefono_mama'              => $representantes['telefono_mama'] ?? '',
    ];

    return formatearRepresentantePresentacionParaApi($fila, 2);
}
