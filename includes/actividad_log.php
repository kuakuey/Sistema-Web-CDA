<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/paginacion.php';

function asegurarTablaActividadLog(?PDO $pdo = null): void
{
    static $listo = false;

    if ($listo) {
        return;
    }

    $pdo = $pdo ?? getConnection();
    migrarTablaActividadLog($pdo);
    $listo = true;
}

/**
 * @return array<string, array{seccion: string, entidad: string, etiqueta: string}>
 */
function mapaMetadatosActividad(): array
{
    return [
        'login'                            => ['seccion' => 'auth', 'entidad' => 'sesion', 'etiqueta' => 'Inicio de sesión'],
        'logout'                           => ['seccion' => 'auth', 'entidad' => 'sesion', 'etiqueta' => 'Cierre de sesión'],
        'crear_valor_adicional'            => ['seccion' => 'valores_adicionales', 'entidad' => 'valor_adicional', 'etiqueta' => 'Crear valor adicional'],
        'registrar_evento'                 => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Registrar participante de evento'],
        'guardar_permisos_rol'             => ['seccion' => 'usuarios', 'entidad' => 'permisos', 'etiqueta' => 'Guardar permisos de rol'],
        'cambiar_clave_usuario'            => ['seccion' => 'usuarios', 'entidad' => 'usuario', 'etiqueta' => 'Cambiar contraseña'],
        'crear_usuario'                    => ['seccion' => 'usuarios', 'entidad' => 'usuario', 'etiqueta' => 'Crear usuario'],
        'crear_consejeria'                 => ['seccion' => 'consejeria', 'entidad' => 'consejeria', 'etiqueta' => 'Crear consejería'],
        'crear_transporte_aniversario'     => ['seccion' => 'transporte_aniversario', 'entidad' => 'transporte', 'etiqueta' => 'Crear transporte aniversario'],
        'asignar_cita_consejeria'          => ['seccion' => 'consejeria', 'entidad' => 'consejeria', 'etiqueta' => 'Asignar cita de consejería'],
        'crear_inscripcion'                => ['seccion' => 'generales', 'entidad' => 'inscripcion', 'etiqueta' => 'Crear inscripción'],
        'crear_presentacion'               => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Crear presentación'],
        'crear_ofrenda'                    => ['seccion' => 'ofrendas', 'entidad' => 'ofrenda', 'etiqueta' => 'Crear ofrenda'],
        'actualizar_inscripcion'           => ['seccion' => 'generales', 'entidad' => 'inscripcion', 'etiqueta' => 'Editar inscripción'],
        'actualizar_presentacion'          => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Editar presentación'],
        'actualizar_ofrenda'               => ['seccion' => 'ofrendas', 'entidad' => 'ofrenda', 'etiqueta' => 'Editar ofrenda'],
        'actualizar_valor_adicional'       => ['seccion' => 'valores_adicionales', 'entidad' => 'valor_adicional', 'etiqueta' => 'Editar valor adicional'],
        'actualizar_registro_evento'       => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Editar registro de evento'],
        'actualizar_consejeria'            => ['seccion' => 'consejeria', 'entidad' => 'consejeria', 'etiqueta' => 'Editar consejería'],
        'actualizar_transporte_aniversario'=> ['seccion' => 'transporte_aniversario', 'entidad' => 'transporte', 'etiqueta' => 'Editar transporte aniversario'],
        'eliminar_inscripcion'             => ['seccion' => 'generales', 'entidad' => 'inscripcion', 'etiqueta' => 'Eliminar inscripción'],
        'eliminar_presentacion'            => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Eliminar presentación'],
        'eliminar_ofrenda'                 => ['seccion' => 'ofrendas', 'entidad' => 'ofrenda', 'etiqueta' => 'Eliminar ofrenda'],
        'eliminar_valor_adicional'         => ['seccion' => 'valores_adicionales', 'entidad' => 'valor_adicional', 'etiqueta' => 'Eliminar valor adicional'],
        'eliminar_consejeria'              => ['seccion' => 'consejeria', 'entidad' => 'consejeria', 'etiqueta' => 'Eliminar consejería'],
        'eliminar_transporte_aniversario'  => ['seccion' => 'transporte_aniversario', 'entidad' => 'transporte', 'etiqueta' => 'Eliminar transporte aniversario'],
        'eliminar_usuario'                 => ['seccion' => 'usuarios', 'entidad' => 'usuario', 'etiqueta' => 'Eliminar usuario'],
        'eliminar_territorio'              => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Eliminar territorio'],
        'eliminar_lider'                   => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Eliminar líder'],
        'eliminar_casa'                    => ['seccion' => 'estructura', 'entidad' => 'casa', 'etiqueta' => 'Eliminar casa de vida'],
        'eliminar_evento'                  => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Eliminar evento'],
        'eliminar_tipo_valor'              => ['seccion' => 'valores_adicionales', 'entidad' => 'tipo_valor', 'etiqueta' => 'Eliminar tipo de valor'],
        'actualizar_estado_presentacion'   => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Cambiar estado de presentación'],
        'actualizar_estado_conexion'       => ['seccion' => 'conexion', 'entidad' => 'inscripcion', 'etiqueta' => 'Cambiar estado de conexión'],
        'actualizar_estado_bautismo'       => ['seccion' => 'bautismo', 'entidad' => 'inscripcion', 'etiqueta' => 'Cambiar estado de bautismo'],
        'restablecer_estado_bautismo'      => ['seccion' => 'bautismo', 'entidad' => 'inscripcion', 'etiqueta' => 'Restablecer estado de bautismo'],
        'restablecer_estado_presentacion'  => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Restablecer estado de presentación'],
        'crear_evento'                     => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Crear evento'],
        'actualizar_evento_catalogo'       => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Editar evento'],
        'crear_tipo_valor'                 => ['seccion' => 'valores_adicionales', 'entidad' => 'tipo_valor', 'etiqueta' => 'Crear tipo de valor'],
        'actualizar_tipo_valor'            => ['seccion' => 'valores_adicionales', 'entidad' => 'tipo_valor', 'etiqueta' => 'Editar tipo de valor'],
        'crear_territorio'                 => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Crear territorio'],
        'actualizar_territorio'            => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Editar territorio'],
        'crear_lider'                      => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Crear líder'],
        'actualizar_lider'                 => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Editar líder'],
        'crear_casa'                       => ['seccion' => 'estructura', 'entidad' => 'casa', 'etiqueta' => 'Crear casa de vida'],
        'actualizar_casa'                  => ['seccion' => 'estructura', 'entidad' => 'casa', 'etiqueta' => 'Editar casa de vida'],
        'generar_informe'                  => ['seccion' => 'generar_informe', 'entidad' => 'informe', 'etiqueta' => 'Generar informe'],
    ];
}

function etiquetaAccionActividad(string $accion): string
{
    $mapa = mapaMetadatosActividad();

    return $mapa[$accion]['etiqueta'] ?? $accion;
}

/**
 * @return array<string, string>
 */
function obtenerEtiquetasAccionesActividad(): array
{
    $etiquetas = [];
    foreach (mapaMetadatosActividad() as $clave => $meta) {
        $etiquetas[$clave] = $meta['etiqueta'];
    }
    asort($etiquetas, SORT_NATURAL | SORT_FLAG_CASE);

    return $etiquetas;
}

/**
 * @return array<string, string>
 */
function obtenerEtiquetasSeccionesActividad(): array
{
    require_once __DIR__ . '/roles.php';

    $etiquetas = obtenerEtiquetasSecciones();
    $etiquetas['auth'] = 'Autenticación';
    $etiquetas['generales'] = $etiquetas['generales'] ?? 'Registros generales';
    asort($etiquetas, SORT_NATURAL | SORT_FLAG_CASE);

    return $etiquetas;
}

function obtenerIpClienteActividad(): ?string
{
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

    return $ip !== '' ? mb_substr($ip, 0, 45) : null;
}

/**
 * @param array<string, mixed>|null $usuario
 */
function registrarActividad(
    string $accion,
    string $seccion = '',
    string $entidad = '',
    ?int $entidadId = null,
    string $detalle = '',
    ?array $usuario = null
): void {
    try {
        asegurarTablaActividadLog();

        if ($usuario === null && function_exists('obtenerUsuarioActual') && function_exists('estaLogueado') && estaLogueado()) {
            $usuario = obtenerUsuarioActual();
        }

        $meta = mapaMetadatosActividad()[$accion] ?? null;
        if ($seccion === '' && $meta) {
            $seccion = $meta['seccion'];
        }
        if ($entidad === '' && $meta) {
            $entidad = $meta['entidad'];
        }
        if ($detalle === '' && $meta) {
            $detalle = $meta['etiqueta'];
            if ($entidadId !== null && $entidadId > 0) {
                $detalle .= ' #' . $entidadId;
            }
        }

        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO actividad_log (
                usuario_id, usuario_nombre, accion, seccion, entidad, entidad_id, detalle, ip_cliente, creado_en
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $stmt->execute([
            isset($usuario['id']) ? (int) $usuario['id'] : null,
            $usuario
                ? mb_substr(trim((string) (($usuario['nombre'] ?? '') !== '' ? $usuario['nombre'] : ($usuario['usuario'] ?? ''))), 0, 100)
                : null,
            mb_substr($accion, 0, 80),
            $seccion !== '' ? mb_substr($seccion, 0, 50) : null,
            $entidad !== '' ? mb_substr($entidad, 0, 50) : null,
            $entidadId !== null && $entidadId > 0 ? $entidadId : null,
            $detalle !== '' ? mb_substr($detalle, 0, 255) : null,
            obtenerIpClienteActividad(),
        ]);
    } catch (Throwable $e) {
        // El log nunca debe interrumpir la operación principal.
    }
}

function registrarActividadPorAccion(string $accion, int $entidadId = 0, string $detalle = ''): void
{
    if ($accion === '') {
        return;
    }

    $extra = '';
    if ($accion === 'guardar_permisos_rol') {
        $rol = trim((string) ($_POST['rol'] ?? ''));
        if ($rol !== '') {
            $extra = 'Rol: ' . $rol;
        }
    } elseif ($accion === 'crear_inscripcion') {
        $tipo = trim((string) ($_POST['tipo_formulario'] ?? ''));
        if ($tipo !== '') {
            $extra = 'Tipo: ' . $tipo;
        }
    } elseif (in_array($accion, ['actualizar_estado_presentacion', 'actualizar_estado_bautismo'], true)) {
        $estado = trim((string) ($_POST['estado'] ?? $_POST['estado_bautismo'] ?? ''));
        if ($estado !== '') {
            $extra = 'Estado: ' . $estado;
        }
    } elseif ($accion === 'actualizar_estado_conexion') {
        $extra = !empty($_POST['contactado']) ? 'Contactado' : 'No contactado';
    }

    if ($detalle === '') {
        $detalle = etiquetaAccionActividad($accion);
        if ($entidadId > 0) {
            $detalle .= ' #' . $entidadId;
        }
        if ($extra !== '') {
            $detalle .= ' · ' . $extra;
        }
    }

    registrarActividad($accion, '', '', $entidadId > 0 ? $entidadId : null, $detalle);
}

function salirConActividad(string $url, string $accion, int $entidadId = 0, string $detalle = ''): void
{
    registrarActividadPorAccion($accion, $entidadId, $detalle);
    header('Location: ' . $url);
    exit;
}

/**
 * @param array<string, mixed> $entrada
 * @return array{buscar: string, accion: string, seccion: string, fecha_desde: string, fecha_hasta: string}
 */
function parsearFiltrosActividad(array $entrada): array
{
    return [
        'buscar'      => trim((string) ($entrada['buscar'] ?? '')),
        'accion'      => trim((string) ($entrada['accion'] ?? '')),
        'seccion'     => trim((string) ($entrada['seccion'] ?? '')),
        'fecha_desde' => trim((string) ($entrada['fecha_desde'] ?? '')),
        'fecha_hasta' => trim((string) ($entrada['fecha_hasta'] ?? '')),
    ];
}

/**
 * @param array{buscar: string, accion: string, seccion: string, fecha_desde: string, fecha_hasta: string} $filtros
 * @return array{0: string, 1: array<int, mixed>}
 */
function construirSqlActividadLog(array $filtros): array
{
    $condiciones = ['1=1'];
    $parametros = [];

    if ($filtros['buscar'] !== '') {
        $busqueda = '%' . $filtros['buscar'] . '%';
        $condiciones[] = '(usuario_nombre LIKE ? OR detalle LIKE ? OR accion LIKE ? OR ip_cliente LIKE ?)';
        array_push($parametros, $busqueda, $busqueda, $busqueda, $busqueda);
    }

    if ($filtros['accion'] !== '') {
        $condiciones[] = 'accion = ?';
        $parametros[] = $filtros['accion'];
    }

    if ($filtros['seccion'] !== '') {
        $condiciones[] = 'seccion = ?';
        $parametros[] = $filtros['seccion'];
    }

    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'DATE(creado_en) >= ?';
        $parametros[] = $filtros['fecha_desde'];
    }

    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'DATE(creado_en) <= ?';
        $parametros[] = $filtros['fecha_hasta'];
    }

    $sql = 'SELECT * FROM actividad_log WHERE ' . implode(' AND ', $condiciones)
        . ' ORDER BY creado_en DESC, id DESC';

    return [$sql, $parametros];
}

/**
 * @param array{buscar: string, accion: string, seccion: string, fecha_desde: string, fecha_hasta: string} $filtros
 * @return array<int, array<string, mixed>>
 */
function buscarActividadLog(array $filtros, int $limite = REGISTROS_POR_PAGINA, int $offset = 0): array
{
    asegurarTablaActividadLog();
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlActividadLog($filtros);
    $limite = normalizarLimiteRegistros($limite);
    $sql .= ' LIMIT ' . $limite . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

/**
 * @param array{buscar: string, accion: string, seccion: string, fecha_desde: string, fecha_hasta: string} $filtros
 */
function contarActividadLog(array $filtros): int
{
    asegurarTablaActividadLog();
    $pdo = getConnection();
    [$sql, $parametros] = construirSqlActividadLog($filtros);
    $sqlConteo = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlConteo = preg_replace('/SELECT \*.*?FROM/is', 'SELECT COUNT(*) FROM', $sqlConteo, 1);
    $stmt = $pdo->prepare($sqlConteo);
    $stmt->execute($parametros);

    return (int) $stmt->fetchColumn();
}

function puedeVerActividadLog(string $rol): bool
{
    require_once __DIR__ . '/roles.php';

    return $rol === ROL_SUPERADMIN;
}
