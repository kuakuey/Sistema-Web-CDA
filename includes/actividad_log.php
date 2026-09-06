<?php

require_once __DIR__ . '/esquema.php';
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
        'importar_registros_eventos'       => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Importar registros de eventos'],
        'importar_estructura'              => ['seccion' => 'estructura', 'entidad' => 'estructura', 'etiqueta' => 'Importar estructura CDV'],
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
        'eliminar_registros_evento'        => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Eliminar registros de evento'],
        'eliminar_consejeria'              => ['seccion' => 'consejeria', 'entidad' => 'consejeria', 'etiqueta' => 'Eliminar consejería'],
        'eliminar_transporte_aniversario'  => ['seccion' => 'transporte_aniversario', 'entidad' => 'transporte', 'etiqueta' => 'Eliminar transporte aniversario'],
        'eliminar_usuario'                 => ['seccion' => 'usuarios', 'entidad' => 'usuario', 'etiqueta' => 'Eliminar usuario'],
        'sincronizar_bd'                   => ['seccion' => 'avanzado', 'entidad' => 'base_datos', 'etiqueta' => 'Sincronizar base de datos'],
        'eliminar_territorio'              => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Eliminar territorio'],
        'eliminar_lider'                   => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Eliminar líder'],
        'eliminar_todos_lideres'           => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Eliminar todos los miembros'],
        'eliminar_casa'                    => ['seccion' => 'estructura', 'entidad' => 'casa', 'etiqueta' => 'Eliminar casa de vida'],
        'eliminar_todas_casas'             => ['seccion' => 'estructura', 'entidad' => 'casa', 'etiqueta' => 'Eliminar todas las casas de vida'],
        'eliminar_evento'                  => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Eliminar evento'],
        'eliminar_evento_calendario'       => ['seccion' => 'calendario', 'entidad' => 'evento_calendario', 'etiqueta' => 'Eliminar evento del calendario'],
        'eliminar_tipo_valor'              => ['seccion' => 'valores_adicionales', 'entidad' => 'tipo_valor', 'etiqueta' => 'Eliminar tipo de valor'],
        'actualizar_estado_presentacion'   => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Cambiar estado de presentación'],
        'actualizar_estado_conexion'       => ['seccion' => 'conexion', 'entidad' => 'inscripcion', 'etiqueta' => 'Cambiar estado de conexión'],
        'actualizar_estado_bautismo'       => ['seccion' => 'bautismo', 'entidad' => 'inscripcion', 'etiqueta' => 'Cambiar estado de bautismo'],
        'actualizar_estado_pago_evento'    => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Cambiar estado de pago de evento'],
        'marcar_asistencia_evento'         => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Marcar asistencia a evento'],
        'reversar_asistencia_evento'       => ['seccion' => 'eventos', 'entidad' => 'registro_evento', 'etiqueta' => 'Reversar asistencia de evento'],
        'restablecer_estado_bautismo'      => ['seccion' => 'bautismo', 'entidad' => 'inscripcion', 'etiqueta' => 'Restablecer estado de bautismo'],
        'restablecer_estado_presentacion'  => ['seccion' => 'presentaciones', 'entidad' => 'presentacion', 'etiqueta' => 'Restablecer estado de presentación'],
        'crear_evento'                     => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Crear evento'],
        'actualizar_evento_catalogo'       => ['seccion' => 'eventos', 'entidad' => 'evento', 'etiqueta' => 'Editar evento'],
        'crear_evento_calendario'          => ['seccion' => 'calendario', 'entidad' => 'evento_calendario', 'etiqueta' => 'Crear evento del calendario'],
        'actualizar_evento_calendario'     => ['seccion' => 'calendario', 'entidad' => 'evento_calendario', 'etiqueta' => 'Editar evento del calendario'],
        'crear_tipo_valor'                 => ['seccion' => 'valores_adicionales', 'entidad' => 'tipo_valor', 'etiqueta' => 'Crear tipo de valor'],
        'actualizar_tipo_valor'            => ['seccion' => 'valores_adicionales', 'entidad' => 'tipo_valor', 'etiqueta' => 'Editar tipo de valor'],
        'crear_territorio'                 => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Crear territorio'],
        'actualizar_territorio'            => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Editar territorio'],
        'asignar_pareja_territorio'        => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Asignar pareja a territorios'],
        'conectar_parentesco'              => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Conectar parentesco'],
        'eliminar_parentesco'              => ['seccion' => 'estructura', 'entidad' => 'lider', 'etiqueta' => 'Quitar parentesco'],
        'quitar_asignacion_territorio'     => ['seccion' => 'estructura', 'entidad' => 'territorio', 'etiqueta' => 'Quitar asignación de territorio'],
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
    $etiquetas['avanzado'] = 'Avanzado';
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

function formatearFechaActividadLog(?string $fecha): string
{
    if ($fecha === null || trim($fecha) === '') {
        return '—';
    }

    $fecha = trim($fecha);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);

        return $dt ? $dt->format('d/m/Y') : $fecha;
    }

    require_once __DIR__ . '/submissions.php';

    return formatearFechaHora($fecha);
}

function terminaConSufijoActividad(string $texto, string $sufijo): bool
{
    if ($sufijo === '') {
        return true;
    }

    return substr($texto, -strlen($sufijo)) === $sufijo;
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
            $detalle !== '' ? $detalle : null,
            $datosExtraJson,
            obtenerIpClienteActividad(),
            obtenerAgenteUsuarioActividad(),
        ]);
    } catch (Throwable $e) {
        // El log nunca debe interrumpir la operación principal.
    }
}

/**
 * @param array<string, mixed>|null $datosExtra
 */
function registrarActividadPorAccion(string $accion, int $entidadId = 0, string $detalle = '', ?array $datosExtra = null): void
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
        $detalleEstado = construirDetalleActividadEstadoPagoEvento(
            (int) ($_POST['id'] ?? $entidadId),
            (string) ($_POST['estado_pago'] ?? '')
        );
        if ($detalleEstado !== '') {
            $detalle = $detalleEstado;
        }
    } elseif (in_array($accion, ['registrar_evento', 'actualizar_registro_evento'], true)) {
        $detalleEvento = construirDetalleActividadRegistroEvento($_POST);
        if ($detalleEvento !== '') {
            $detalle = $detalleEvento;
        }
    } elseif (in_array($accion, ['crear_ofrenda', 'actualizar_ofrenda'], true)) {
        $detalleOfrenda = formatearDetalleOfrenda($_POST);
        if ($detalleOfrenda !== '') {
            $detalle = $detalleOfrenda;
        }
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

    if ($datosExtra === null && in_array($accion, ['registrar_evento', 'actualizar_registro_evento', 'actualizar_estado_pago_evento'], true)) {
        $datosExtra = capturarContextoActividadEvento($accion);
    }

    registrarActividad($accion, '', '', $entidadId > 0 ? $entidadId : null, $detalle, null, $datosExtra);
}

/**
 * Resuelve nombres de evento y tipo de entrada (nunca IDs) para el detalle del log.
 *
 * @param array<string, mixed> $datos
 * @return array{evento: string, tipo_entrada: string}
 */
function resolverNombresEventoParaLog(array $datos): array
{
    require_once __DIR__ . '/eventos.php';

    $eventoNombre = trim((string) ($datos['evento_nombre'] ?? $datos['evento'] ?? ''));
    $tipoEntradaNombre = trim((string) ($datos['tipo_entrada'] ?? ''));
    $eventoId = isset($datos['evento_id']) ? (int) $datos['evento_id'] : 0;
    $tipoEntradaId = isset($datos['tipo_entrada_id']) ? (int) $datos['tipo_entrada_id'] : 0;

    if ($eventoNombre === '' && $eventoId > 0) {
        $evento = obtenerEvento($eventoId);
        if ($evento) {
            $eventoNombre = trim((string) ($evento['nombre'] ?? ''));
        }
    }

    if ($tipoEntradaNombre === '' && $tipoEntradaId > 0) {
        $tipo = obtenerTipoEntradaPorId($tipoEntradaId, $eventoId > 0 ? $eventoId : null);
        if ($tipo) {
            $tipoEntradaNombre = trim((string) ($tipo['nombre'] ?? ''));
        }
    }

    return [
        'evento'        => $eventoNombre,
        'tipo_entrada'  => $tipoEntradaNombre,
    ];
}

/**
 * @param array<string, mixed> $datos
 */
function construirDetalleActividadRegistroEvento(array $datos): string
{
    require_once __DIR__ . '/submissions.php';
    require_once __DIR__ . '/eventos.php';

    $nombres = resolverNombresEventoParaLog($datos);
    $lineas = [];

    if ($nombres['evento'] !== '') {
        $lineas[] = 'Evento: ' . $nombres['evento'];
    }
    if ($nombres['tipo_entrada'] !== '') {
        $lineas[] = 'Tipo de entrada: ' . $nombres['tipo_entrada'];
    }
    if (!empty($datos['nombre'])) {
        $lineas[] = 'Nombre completo: ' . trim((string) $datos['nombre']);
    }
    if (!empty($datos['telefono'])) {
        $lineas[] = 'Teléfono: ' . trim((string) $datos['telefono']);
    }
    if (!empty($datos['fecha'])) {
        $lineas[] = 'Fecha: ' . formatearFechaActividadLog((string) $datos['fecha']);
    }
    if (isset($datos['valor']) && $datos['valor'] !== '') {
        $lineas[] = 'Valor: ' . formatearMonto((float) $datos['valor']);
    }
    if (!empty($datos['forma_pago'])) {
        $lineas[] = 'Forma de pago: ' . etiquetaFormaPagoEvento((string) $datos['forma_pago']);
    }
    if (!empty($datos['estado_pago'])) {
        $lineas[] = 'Estado: ' . etiquetaEstadoPagoRegistroEvento($datos);
    }
    if (!empty($datos['numeracion'])) {
        $lineas[] = 'Numeración: ' . trim((string) $datos['numeracion']);
    }
    $infoAdicionalTexto = formatearInfoAdicionalRegistro($datos['info_adicional'] ?? '');
    if ($infoAdicionalTexto !== '') {
        $lineas[] = 'Información adicional: ' . $infoAdicionalTexto;
    }
    if (!empty($datos['observacion'])) {
        $lineas[] = 'Observación: ' . trim((string) $datos['observacion']);
    }

    return implode("\n", $lineas);
}

/**
 * @param array<string, mixed> $datos
 * @return array<string, string>
 */
function construirCamposDetalleOfrenda(array $datos): array
{
    require_once __DIR__ . '/submissions.php';
    require_once __DIR__ . '/estructura.php';

    $campos = [];
    $casaNombre = trim((string) ($datos['casa_vida'] ?? ''));
    $lider = trim((string) ($datos['lider'] ?? ''));
    $casaId = isset($datos['casa_id']) ? (int) $datos['casa_id'] : 0;

    if (($casaNombre === '' || $lider === '') && $casaId > 0) {
        $casa = obtenerCasaVida($casaId);
        if ($casa) {
            if ($casaNombre === '') {
                $casaNombre = trim((string) ($casa['nombre'] ?? ''));
            }
            if ($lider === '') {
                $lider = nombreCompletoLider([
                    'nombre'   => $casa['lider_nombre'] ?? '',
                    'apellido' => $casa['lider_apellido'] ?? '',
                ]);
            }
        }
    }

    if ($casaNombre !== '') {
        $campos['Casa de vida'] = $casaNombre;
    }
    if ($lider !== '') {
        $campos['Líder'] = $lider;
    }

    $fechaOfrenda = trim((string) ($datos['fecha_ofrenda'] ?? ''));
    if ($fechaOfrenda !== '') {
        $campos['Fecha de ofrenda'] = formatearFechaActividadLog($fechaOfrenda);
    }

    if (isset($datos['monto']) && $datos['monto'] !== '') {
        $campos['Monto'] = formatearMonto((float) $datos['monto']);
    }

    $registradoPor = trim((string) ($datos['registrado_por_nombre'] ?? ''));
    if ($registradoPor !== '') {
        $campos['Registrado por'] = $registradoPor;
    }

    return $campos;
}

/**
 * @param array<string, mixed> $datos
 */
function formatearDetalleOfrenda(array $datos): string
{
    $campos = construirCamposDetalleOfrenda($datos);
    if ($campos === []) {
        return '';
    }

    $lineas = [];
    foreach ($campos as $etiqueta => $valor) {
        $lineas[] = $etiqueta . ': ' . $valor;
    }

    return implode("\n", $lineas);
}

/**
 * Contexto POST enriquecido: nombres de evento/entrada en lugar de IDs.
 *
 * @return array<string, mixed>
 */
function capturarContextoActividadEvento(string $accion = ''): array
{
    $contexto = capturarContextoActividad($accion);
    $nombres = resolverNombresEventoParaLog($_POST);

    if ($accion === 'actualizar_estado_pago_evento') {
        $registroId = (int) ($_POST['id'] ?? 0);
        if ($registroId > 0) {
            require_once __DIR__ . '/eventos.php';
            $registro = obtenerRegistroEventoPorId($registroId);
            if ($registro) {
                $nombres = resolverNombresEventoParaLog($registro);
            }
        }
    }

    if (!empty($contexto['datos']) && is_array($contexto['datos'])) {
        unset($contexto['datos']['evento_id'], $contexto['datos']['tipo_entrada_id']);
        if ($nombres['evento'] !== '') {
            $contexto['datos']['evento'] = $nombres['evento'];
        }
        if ($nombres['tipo_entrada'] !== '') {
            $contexto['datos']['tipo_entrada'] = $nombres['tipo_entrada'];
        }
    }

    return $contexto;
}

function construirDetalleActividadEstadoPagoEvento(int $registroId, string $estadoPago): string
{
    require_once __DIR__ . '/eventos.php';

    $lineas = [];
    $registro = null;
    if ($registroId > 0) {
        $registro = obtenerRegistroEventoPorId($registroId);
        if ($registro) {
            $nombres = resolverNombresEventoParaLog($registro);
            if ($nombres['evento'] !== '') {
                $lineas[] = 'Evento: ' . $nombres['evento'];
            }
            if ($nombres['tipo_entrada'] !== '') {
                $lineas[] = 'Tipo de entrada: ' . $nombres['tipo_entrada'];
            }
            if (!empty($registro['nombre'])) {
                $lineas[] = 'Nombre completo: ' . trim((string) $registro['nombre']);
            }
        }
    }

    $estadoPago = trim($estadoPago);
    if ($estadoPago !== '') {
        $etiquetaEstado = is_array($registro)
            ? etiquetaEstadoPagoRegistroEvento($registro)
            : etiquetaEstadoPagoEvento($estadoPago);
        $lineas[] = 'Estado: ' . $etiquetaEstado;
    }

    return implode("\n", $lineas);
}

/**
 * @param array<string, mixed>|null $datosExtra
 */
function salirConActividad(string $url, string $accion, int $entidadId = 0, string $detalle = '', ?array $datosExtra = null): void
{
    try {
        registrarActividadPorAccion($accion, $entidadId, $detalle, $datosExtra);
    } catch (Throwable $e) {
        // El log nunca debe impedir la redirección principal.
    }

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
 * @return array<string, mixed>|null
 */
function obtenerFilaSimplePorId(string $tabla, int $id): ?array
{
    static $tablasPermitidas = [
        'inscripciones',
        'presentaciones_ninos',
        'ofrendas',
        'valores_adicionales',
        'consejerias',
        'transporte_aniversario',
        'usuarios',
        'territorios',
        'lideres',
        'casas_vida',
        'tipos_valor_adicional',
    ];

    if ($id <= 0 || !in_array($tabla, $tablasPermitidas, true)) {
        return null;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM {$tabla} WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $fila = $stmt->fetch();

    return $fila ?: null;
}

/**
 * Obtiene el registro completo antes de eliminarlo para el log.
 *
 * @return array<string, mixed>|null
 */
function obtenerSnapshotEliminacion(string $accion, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    switch ($accion) {
        case 'eliminar_evento':
            require_once __DIR__ . '/eventos.php';
            return obtenerEvento($id);

        case 'eliminar_evento_calendario':
            require_once __DIR__ . '/calendario.php';
            return obtenerEventoCalendario($id);

        case 'eliminar_registro_evento':
            require_once __DIR__ . '/eventos.php';
            return obtenerRegistroEventoPorId($id);

        case 'eliminar_registros_evento':
            require_once __DIR__ . '/eventos.php';
            $evento = obtenerEvento($id);
            if (!$evento) {
                return null;
            }
            $evento['registros_a_eliminar'] = contarRegistrosPorEvento($id);

            return $evento;

        case 'eliminar_inscripcion':
            return obtenerFilaSimplePorId('inscripciones', $id);

        case 'eliminar_presentacion':
            return obtenerFilaSimplePorId('presentaciones_ninos', $id);

        case 'eliminar_ofrenda':
            return obtenerFilaSimplePorId('ofrendas', $id);

        case 'eliminar_valor_adicional':
            return obtenerFilaSimplePorId('valores_adicionales', $id);

        case 'eliminar_consejeria':
            return obtenerFilaSimplePorId('consejerias', $id);

        case 'eliminar_transporte_aniversario':
            return obtenerFilaSimplePorId('transporte_aniversario', $id);

        case 'eliminar_usuario':
            $usuario = obtenerFilaSimplePorId('usuarios', $id);
            if ($usuario) {
                unset($usuario['clave'], $usuario['password']);
            }
            return $usuario;

        case 'eliminar_territorio':
            return obtenerFilaSimplePorId('territorios', $id);

        case 'eliminar_lider':
            return obtenerFilaSimplePorId('lideres', $id);

        case 'eliminar_casa':
            require_once __DIR__ . '/estructura.php';
            return obtenerCasaVida($id);

        case 'eliminar_tipo_valor':
            return obtenerFilaSimplePorId('tipos_valor_adicional', $id);

        default:
            return null;
    }
}

/**
 * @param array<string, mixed> $registro
 * @return array<string, string>
 */
function construirCamposDetalleEliminacion(string $accion, array $registro): array
{
    require_once __DIR__ . '/submissions.php';

    $campos = [];
    $valorTexto = static function ($valor): string {
        $texto = trim((string) $valor);
        return $texto !== '' ? $texto : '—';
    };

    if ($accion === 'eliminar_evento') {
        require_once __DIR__ . '/eventos.php';
        $campos['ID'] = '#' . (int) ($registro['id'] ?? 0);
        $campos['Nombre'] = $valorTexto($registro['nombre'] ?? '');
        $campos['Fecha'] = formatearFechaActividadLog($registro['fecha'] ?? null);
        $campos['Valor'] = formatearMonto((float) ($registro['valor'] ?? 0));
        $campos['Tipo'] = etiquetaTipoEventoCatalogo($registro);
        $campos['Estado'] = etiquetaEstadoEvento((int) ($registro['habilitado'] ?? 0));
        $campos['Numeración'] = !empty($registro['requiere_numeracion']) ? 'Requerida' : 'No requerida';

        $tipos = $registro['tipos_entrada'] ?? [];
        if (is_array($tipos) && $tipos !== []) {
            $partesTipos = [];
            foreach ($tipos as $tipo) {
                if (!is_array($tipo)) {
                    continue;
                }
                $nombreTipo = trim((string) ($tipo['nombre'] ?? ''));
                $valorTipo = (float) ($tipo['valor'] ?? 0);
                $partesTipos[] = ($nombreTipo !== '' ? $nombreTipo : 'Tipo')
                    . ' (' . ($valorTipo <= 0 ? 'Gratuito' : formatearMonto($valorTipo)) . ')';
            }
            if ($partesTipos !== []) {
                $campos['Tipos de entrada'] = implode(' · ', $partesTipos);
            }
        }

        $camposExtra = $registro['campos_adicionales'] ?? [];
        if (is_array($camposExtra) && $camposExtra !== []) {
            $partesExtra = [];
            foreach ($camposExtra as $campoExtra) {
                if (!is_array($campoExtra)) {
                    continue;
                }
                $etiquetaExtra = trim((string) ($campoExtra['etiqueta'] ?? ''));
                if ($etiquetaExtra === '') {
                    continue;
                }
                $partesExtra[] = $etiquetaExtra . (!empty($campoExtra['obligatorio']) ? ' (obligatorio)' : '');
            }
            if ($partesExtra !== []) {
                $campos['Información adicional'] = implode(' · ', $partesExtra);
            }
        }

        return $campos;
    }

    if ($accion === 'eliminar_registros_evento') {
        require_once __DIR__ . '/eventos.php';
        $campos['Evento'] = $valorTexto($registro['nombre'] ?? '');
        $campos['Fecha'] = formatearFechaActividadLog($registro['fecha'] ?? null);
        $campos['Registros eliminados'] = (string) (int) ($registro['registros_a_eliminar'] ?? 0);
        $campos['Nota'] = 'El evento se conservó en el catálogo.';

        return $campos;
    }

    if ($accion === 'eliminar_evento_calendario') {
        require_once __DIR__ . '/calendario.php';
        $campos['ID'] = '#' . (int) ($registro['id'] ?? 0);
        $campos['Título'] = $valorTexto($registro['titulo'] ?? '');
        $campos['Descripción'] = $valorTexto($registro['descripcion'] ?? '');
        $campos['Fecha'] = formatearFechaActividadLog($registro['fecha'] ?? null);
        if (!empty($registro['fecha_fin']) && (string) $registro['fecha_fin'] !== (string) ($registro['fecha'] ?? '')) {
            $campos['Fecha fin'] = formatearFechaActividadLog($registro['fecha_fin'] ?? null);
        }
        $campos['Estado'] = etiquetaEstadoEventoCalendario((int) ($registro['activo'] ?? 0));
        $campos['Foto'] = $valorTexto($registro['foto'] ?? '');

        return $campos;
    }

    if ($accion === 'eliminar_registro_evento') {
        require_once __DIR__ . '/eventos.php';
        $nombres = resolverNombresEventoParaLog($registro);
        $campos['Evento'] = $nombres['evento'] !== '' ? $nombres['evento'] : $valorTexto($registro['evento_nombre'] ?? '');
        $campos['Tipo de entrada'] = $nombres['tipo_entrada'] !== '' ? $nombres['tipo_entrada'] : $valorTexto($registro['tipo_entrada'] ?? '');
        $campos['Nombre completo'] = $valorTexto($registro['nombre'] ?? '');
        $campos['Teléfono'] = $valorTexto($registro['telefono'] ?? '');
        $campos['Fecha'] = formatearFechaActividadLog($registro['fecha'] ?? null);
        $campos['Valor'] = formatearMonto((float) ($registro['valor'] ?? 0));
        $campos['Forma de pago'] = etiquetaFormaPagoEvento($registro['forma_pago'] ?? null);
        $campos['Estado'] = etiquetaEstadoPagoRegistroEvento($registro);
        $numeracion = trim((string) ($registro['numeracion'] ?? ''));
        if ($numeracion !== '' || !empty($registro['requiere_numeracion'])) {
            $campos['Numeración'] = $numeracion !== '' ? $numeracion : '—';
        }
        $infoAdicionalTexto = formatearInfoAdicionalRegistro($registro['info_adicional'] ?? '');
        if ($infoAdicionalTexto !== '') {
            $campos['Información adicional'] = $infoAdicionalTexto;
        }
        $observacion = trim((string) ($registro['observacion'] ?? ''));
        if ($observacion !== '') {
            $campos['Observación'] = $observacion;
        }

        return $campos;
    }

    if ($accion === 'eliminar_ofrenda') {
        return construirCamposDetalleOfrenda($registro);
    }

    $mapaEtiquetas = [
        'id'                   => 'ID',
        'nombre'               => 'Nombre',
        'apellido'             => 'Apellido',
        'nombre_completo'      => 'Nombre completo',
        'usuario'              => 'Usuario',
        'rol'                  => 'Rol',
        'telefono'             => 'Teléfono',
        'celular'              => 'Celular',
        'email'                => 'Email',
        'fecha'                => 'Fecha',
        'fecha_ofrenda'        => 'Fecha ofrenda',
        'fecha_presentacion'   => 'Fecha presentación',
        'monto'                => 'Monto',
        'valor'                => 'Valor',
        'tipo'                 => 'Tipo',
        'etiqueta'             => 'Etiqueta',
        'clave'                => 'Clave',
        'casa_vida'            => 'Casa de vida',
        'lider'                => 'Líder',
        'territorio_id'        => 'Territorio ID',
        'lider_id'             => 'Líder ID',
        'direccion'            => 'Dirección',
        'zona'                 => 'Zona',
        'cedula'               => 'Cédula',
        'observacion'          => 'Observación',
        'notas'                => 'Notas',
        'tipo_consejeria'      => 'Tipo consejería',
        'cita_fecha'           => 'Cita fecha',
        'cita_hora'            => 'Cita hora',
        'estado'               => 'Estado',
        'forma_pago'           => 'Forma de pago',
        'estado_pago'          => 'Estado pago',
        'tipo_entrada'         => 'Tipo de entrada',
        'numeracion'           => 'Numeración',
        'info_adicional'       => 'Información adicional',
    ];

    // Si hay IDs de evento/entrada, mostrar nombres legibles.
    if (!empty($registro['evento_id']) || !empty($registro['tipo_entrada_id']) || !empty($registro['tipo_entrada'])) {
        $nombres = resolverNombresEventoParaLog($registro);
        if ($nombres['evento'] !== '') {
            $campos['Evento'] = $nombres['evento'];
        }
        if ($nombres['tipo_entrada'] !== '') {
            $campos['Tipo de entrada'] = $nombres['tipo_entrada'];
        }
    }

    foreach ($mapaEtiquetas as $clave => $etiqueta) {
        if (!array_key_exists($clave, $registro)) {
            continue;
        }
        if (isset($campos[$etiqueta])) {
            continue;
        }
        $valor = $registro[$clave];
        if ($valor === null || $valor === '') {
            continue;
        }
        if (in_array($clave, ['monto', 'valor'], true)) {
            $campos[$etiqueta] = formatearMonto((float) $valor);
            continue;
        }
        if ($clave === 'id' || terminaConSufijoActividad($clave, '_id')) {
            continue;
        }
        $campos[$etiqueta] = $valorTexto($valor);
    }

    return $campos;
}

/**
 * @param array<string, mixed> $registro
 */
function formatearDetalleEliminacion(string $accion, array $registro): string
{
    $campos = construirCamposDetalleEliminacion($accion, $registro);
    if ($campos === []) {
        return etiquetaAccionActividad($accion) . ' #' . (int) ($registro['id'] ?? 0);
    }

    $lineas = [];
    foreach ($campos as $etiqueta => $valor) {
        $lineas[] = $etiqueta . ': ' . $valor;
    }

    return implode("\n", $lineas);
}

/**
 * @param array<string, mixed>|null $registroEliminado
 * @return array<string, mixed>
 */
function armarDatosExtraEliminacion(string $accion, ?array $registroEliminado): array
{
    $datosExtra = capturarContextoActividad($accion);
    if ($registroEliminado) {
        $datosExtra['registro_eliminado'] = construirCamposDetalleEliminacion($accion, $registroEliminado);
    }

    return $datosExtra;
}

/**
 * Texto legible para la columna Detalle del log.
 *
 * @param array<string, mixed> $fila
 */
function obtenerTextoDetalleActividad(array $fila): string
{
    $detalle = trim((string) ($fila['detalle'] ?? ''));
    if ($detalle !== '') {
        return $detalle;
    }

    $datosExtra = trim((string) ($fila['datos_extra'] ?? ''));
    if ($datosExtra === '') {
        return '—';
    }

    $decoded = json_decode($datosExtra, true);
    if (!is_array($decoded)) {
        return $datosExtra;
    }

    if (!empty($decoded['registro_eliminado']) && is_array($decoded['registro_eliminado'])) {
        $lineas = [];
        foreach ($decoded['registro_eliminado'] as $etiqueta => $valor) {
            $lineas[] = $etiqueta . ': ' . $valor;
        }
        return $lineas !== [] ? implode("\n", $lineas) : '—';
    }

    return formatearDatosExtraActividad($decoded);
}

/**
 * @param array<string, mixed> $fila
 * @return array<int, array{etiqueta: string, valor: string, html?: bool}>
 */
function construirDetalleActividadLog(array $fila, array $etiquetasSeccionesLog = []): array
{
    require_once __DIR__ . '/submissions.php';

    if ($etiquetasSeccionesLog === []) {
        $etiquetasSeccionesLog = obtenerEtiquetasSeccionesActividad();
    }

    $seccion = (string) ($fila['seccion'] ?? '');
    $usuario = trim((string) ($fila['usuario_nombre'] ?? ''));
    if ($usuario === '') {
        $usuario = trim((string) ($fila['usuario_login'] ?? ''));
    }

    return [
        ['etiqueta' => 'Fecha y hora', 'valor' => formatearFechaHora($fila['creado_en'] ?? null)],
        ['etiqueta' => 'Usuario', 'valor' => $usuario !== '' ? $usuario : '—'],
        ['etiqueta' => 'Acción', 'valor' => etiquetaAccionActividad((string) ($fila['accion'] ?? ''))],
        ['etiqueta' => 'Sección', 'valor' => $etiquetasSeccionesLog[$seccion] ?? ($seccion !== '' ? $seccion : '—')],
        ['etiqueta' => 'Detalle', 'valor' => obtenerTextoDetalleActividad($fila)],
    ];
}

/**
 * @param array<string, mixed> $datos
 */
function formatearDatosExtraActividad(array $datos, int $nivel = 0): string
{
    $lineas = [];
    foreach ($datos as $clave => $valor) {
        if (in_array((string) $clave, ['metodo', 'ruta', 'accion_interna', 'datos'], true) && $nivel === 0) {
            continue;
        }
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
