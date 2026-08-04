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
        'eliminar_registro_evento'         => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Eliminar registro de evento'],
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
        'actualizar_estado_pago_evento'    => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Cambiar estado de pago de evento'],
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
        'limpiar_actividad_log'            => ['seccion' => 'actividad', 'entidad' => 'actividad_log', 'etiqueta' => 'Limpiar log de actividad'],
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

function obtenerAgenteUsuarioActividad(): ?string
{
    $agente = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    return $agente !== '' ? mb_substr($agente, 0, 255) : null;
}

/**
 * @param array<string, mixed> $origen
 * @return array<string, mixed>
 */
function sanitizarDatosExtraActividad(array $origen, int $profundidad = 0): array
{
    if ($profundidad > 2) {
        return [];
    }

    $bloqueados = [
        'clave', 'password', 'pass', 'clave_confirmacion', 'password_confirmation',
        'token', 'm', 'accion',
    ];
    $resultado = [];

    foreach ($origen as $clave => $valor) {
        $claveStr = (string) $clave;
        if (in_array(mb_strtolower($claveStr), $bloqueados, true)) {
            $resultado[$claveStr] = '[oculto]';
            continue;
        }

        if (is_array($valor)) {
            $resultado[$claveStr] = sanitizarDatosExtraActividad($valor, $profundidad + 1);
            continue;
        }

        if (is_bool($valor)) {
            $resultado[$claveStr] = $valor ? 'sí' : 'no';
            continue;
        }

        if ($valor === null) {
            continue;
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            continue;
        }

        $resultado[$claveStr] = mb_substr($texto, 0, 200);
    }

    return $resultado;
}

/**
 * @return array<string, mixed>
 */
function capturarContextoActividad(string $accion = ''): array
{
    $contexto = [
        'metodo' => strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
        'ruta'   => mb_substr((string) ($_SERVER['REQUEST_URI'] ?? ''), 0, 255),
    ];

    if (!empty($_POST) && is_array($_POST)) {
        $post = sanitizarDatosExtraActividad($_POST);
        unset($post['redireccion']);
        if ($post !== []) {
            $contexto['datos'] = $post;
        }
    }

    if ($accion !== '') {
        $contexto['accion_interna'] = $accion;
    }

    return $contexto;
}

/**
 * @param array<string, mixed>|null $usuario
 * @param array<string, mixed>|null $datosExtra
 */
function registrarActividad(
    string $accion,
    string $seccion = '',
    string $entidad = '',
    ?int $entidadId = null,
    string $detalle = '',
    ?array $usuario = null,
    ?array $datosExtra = null
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

        if ($datosExtra === null) {
            $datosExtra = capturarContextoActividad($accion);
        }

        $datosExtraJson = null;
        if ($datosExtra !== []) {
            $json = json_encode($datosExtra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($json)) {
                $datosExtraJson = $json;
            }
        }

        $pdo = getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO actividad_log (
                usuario_id, usuario_nombre, usuario_login, rol_usuario,
                accion, seccion, entidad, entidad_id, detalle, datos_extra,
                ip_cliente, agente_usuario, creado_en
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $nombreMostrar = null;
        $login = null;
        $rol = null;
        if ($usuario) {
            $nombreMostrar = mb_substr(
                trim((string) (($usuario['nombre'] ?? '') !== '' ? $usuario['nombre'] : ($usuario['usuario'] ?? ''))),
                0,
                100
            );
            $login = trim((string) ($usuario['usuario'] ?? ''));
            $login = $login !== '' ? mb_substr($login, 0, 50) : null;
            $rol = trim((string) ($usuario['rol'] ?? ''));
            $rol = $rol !== '' ? mb_substr($rol, 0, 20) : null;
        }

        $stmt->execute([
            isset($usuario['id']) ? (int) $usuario['id'] : null,
            $nombreMostrar,
            $login,
            $rol,
            mb_substr($accion, 0, 80),
            $seccion !== '' ? mb_substr($seccion, 0, 50) : null,
            $entidad !== '' ? mb_substr($entidad, 0, 50) : null,
            $entidadId !== null && $entidadId > 0 ? $entidadId : null,
            $detalle !== '' ? mb_substr($detalle, 0, 500) : null,
            $datosExtraJson,
            obtenerIpClienteActividad(),
            obtenerAgenteUsuarioActividad(),
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
        $tipo = trim((string) ($_POST['tipo_formulario'] ?? $_POST['seccion'] ?? ''));
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
    } elseif ($accion === 'actualizar_estado_pago_evento') {
        $estadoPago = trim((string) ($_POST['estado_pago'] ?? ''));
        if ($estadoPago !== '') {
            $extra = 'Estado: ' . $estadoPago;
        }
    } elseif ($accion === 'registrar_evento') {
        $partes = [];
        if (!empty($_POST['nombre'])) {
            $partes[] = 'Participante: ' . trim((string) $_POST['nombre']);
        }
        if (!empty($_POST['tipo_entrada_id'])) {
            $partes[] = 'Tipo entrada #' . (int) $_POST['tipo_entrada_id'];
        }
        if (isset($_POST['valor']) && $_POST['valor'] !== '') {
            $partes[] = 'Valor: ' . $_POST['valor'];
        }
        if (!empty($_POST['estado_pago'])) {
            $partes[] = 'Estado: ' . trim((string) $_POST['estado_pago']);
        }
        $extra = implode(' · ', $partes);
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
        $condiciones[] = '(usuario_nombre LIKE ? OR usuario_login LIKE ? OR detalle LIKE ? OR accion LIKE ? OR ip_cliente LIKE ? OR datos_extra LIKE ?)';
        array_push($parametros, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda);
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

function obtenerActividadLogPorId(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    asegurarTablaActividadLog();
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM actividad_log WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * @param array{buscar: string, accion: string, seccion: string, fecha_desde: string, fecha_hasta: string} $filtros
 */
function limpiarActividadLog(array $filtros = [], bool $soloFiltrados = false): int
{
    asegurarTablaActividadLog();
    $pdo = getConnection();

    if (!$soloFiltrados) {
        return (int) $pdo->exec('DELETE FROM actividad_log');
    }

    [$sql, $parametros] = construirSqlActividadLog($filtros);
    $sqlDelete = preg_replace('/\s+ORDER BY.*$/i', '', $sql);
    $sqlDelete = preg_replace('/SELECT \*.*?FROM/is', 'DELETE FROM', $sqlDelete, 1);
    $stmt = $pdo->prepare($sqlDelete);
    $stmt->execute($parametros);

    return (int) $stmt->rowCount();
}

/**
 * @param array<string, mixed> $fila
 * @return array<int, array{etiqueta: string, valor: string, html?: bool}>
 */
function construirDetalleActividadLog(array $fila, array $etiquetasSeccionesLog = []): array
{
    require_once __DIR__ . '/roles.php';
    require_once __DIR__ . '/submissions.php';

    if ($etiquetasSeccionesLog === []) {
        $etiquetasSeccionesLog = obtenerEtiquetasSeccionesActividad();
    }

    $etiquetasRoles = obtenerEtiquetasRoles();
    $seccion = (string) ($fila['seccion'] ?? '');
    $rol = (string) ($fila['rol_usuario'] ?? '');
    $datosExtra = trim((string) ($fila['datos_extra'] ?? ''));
    $datosFormateados = '—';

    if ($datosExtra !== '') {
        $decoded = json_decode($datosExtra, true);
        if (is_array($decoded)) {
            $datosFormateados = formatearDatosExtraActividad($decoded);
        } else {
            $datosFormateados = $datosExtra;
        }
    }

    return [
        ['etiqueta' => 'ID', 'valor' => '#' . (int) ($fila['id'] ?? 0)],
        ['etiqueta' => 'Fecha y hora', 'valor' => formatearFechaHora($fila['creado_en'] ?? null)],
        ['etiqueta' => 'Usuario', 'valor' => trim((string) ($fila['usuario_nombre'] ?? '')) !== '' ? (string) $fila['usuario_nombre'] : '—'],
        ['etiqueta' => 'Login', 'valor' => trim((string) ($fila['usuario_login'] ?? '')) !== '' ? (string) $fila['usuario_login'] : '—'],
        ['etiqueta' => 'Rol', 'valor' => $etiquetasRoles[$rol] ?? ($rol !== '' ? $rol : '—')],
        ['etiqueta' => 'ID usuario', 'valor' => !empty($fila['usuario_id']) ? (string) (int) $fila['usuario_id'] : '—'],
        ['etiqueta' => 'Acción', 'valor' => etiquetaAccionActividad((string) ($fila['accion'] ?? ''))],
        ['etiqueta' => 'Clave acción', 'valor' => (string) ($fila['accion'] ?? '—')],
        ['etiqueta' => 'Sección', 'valor' => $etiquetasSeccionesLog[$seccion] ?? ($seccion !== '' ? $seccion : '—')],
        ['etiqueta' => 'Entidad', 'valor' => trim((string) ($fila['entidad'] ?? '')) !== '' ? (string) $fila['entidad'] : '—'],
        ['etiqueta' => 'ID entidad', 'valor' => !empty($fila['entidad_id']) ? (string) (int) $fila['entidad_id'] : '—'],
        ['etiqueta' => 'Resumen', 'valor' => trim((string) ($fila['detalle'] ?? '')) !== '' ? (string) $fila['detalle'] : '—'],
        ['etiqueta' => 'IP', 'valor' => trim((string) ($fila['ip_cliente'] ?? '')) !== '' ? (string) $fila['ip_cliente'] : '—'],
        ['etiqueta' => 'Navegador / dispositivo', 'valor' => trim((string) ($fila['agente_usuario'] ?? '')) !== '' ? (string) $fila['agente_usuario'] : '—'],
        ['etiqueta' => 'Datos de la operación', 'valor' => $datosFormateados],
    ];
}

/**
 * @param array<string, mixed> $datos
 */
function formatearDatosExtraActividad(array $datos, int $nivel = 0): string
{
    $lineas = [];
    foreach ($datos as $clave => $valor) {
        $prefijo = str_repeat('  ', $nivel) . $clave . ': ';
        if (is_array($valor)) {
            $lineas[] = rtrim($prefijo);
            $lineas[] = formatearDatosExtraActividad($valor, $nivel + 1);
            continue;
        }
        $lineas[] = $prefijo . (string) $valor;
    }

    return trim(implode("\n", array_filter($lineas, static fn ($l) => trim((string) $l) !== '')));
}
