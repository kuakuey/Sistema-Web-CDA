<?php

require_once 'includes/auth.php';

requerirSesion();

$usuarioActual = obtenerUsuarioActual();
$urlInicio = obtenerUrlInicioPorRol($usuarioActual['rol'] ?? ROL_ADMIN);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $urlInicio);
    exit;
}

require_once 'includes/submissions.php';
require_once 'includes/estructura.php';
require_once 'includes/valores_adicionales.php';
require_once 'includes/eventos.php';
require_once 'includes/consejerias.php';

$accion = $_POST['accion'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$redireccion = $_POST['redireccion'] ?? $urlInicio;

if ($accion === 'crear_valor_adicional') {
    if (!puedeRegistrarValoresAdicionales(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $tipo = trim((string) ($_POST['tipo'] ?? ''));
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $fecha = trim((string) ($_POST['fecha'] ?? ''));
    $telefono = trim((string) ($_POST['telefono'] ?? ''));
    $valor = isset($_POST['valor']) ? (float) $_POST['valor'] : 0;
    $observacion = trim((string) ($_POST['observacion'] ?? ''));
    $usuario = obtenerUsuarioActual();

    $redireccionBase = 'valores-adicionales.php';

    if (!esTipoValorAdicionalValido($tipo) || $fecha === '' || $telefono === '' || $valor <= 0) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Completa todos los campos obligatorios con un valor mayor a cero.'));
        exit;
    }

    if ($nombre === '') {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Completa todos los campos obligatorios con un valor mayor a cero.'));
        exit;
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Fecha no válida.'));
        exit;
    }

    try {
        insertarValorAdicional([
            'tipo'                  => $tipo,
            'nombre'                => $nombre,
            'fecha'                 => $fecha,
            'telefono'              => $telefono,
            'valor'                 => $valor,
            'observacion'           => $observacion,
            'evento_id'             => null,
            'registrado_por_id'     => (int) $usuario['id'],
            'registrado_por_nombre' => $usuario['nombre'] ?? $usuario['usuario'],
        ]);
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('No se pudo guardar. Verifica que exista la tabla valores_adicionales.'));
        exit;
    }

    header('Location: ' . $redireccionBase . '?pestaña=nuevo&ok=1');
    exit;
}

if ($accion === 'registrar_evento') {
    if (!puedeRegistrarEventos(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $usuario = obtenerUsuarioActual();
    $redireccionBase = $_POST['redireccion'] ?? 'eventos.php?pestaña=registrar';
    $sep = strpos($redireccionBase, '?') !== false ? '&' : '?';

    try {
        $validados = validarDatosRegistroEvento($_POST);
        insertarValorAdicional([
            'tipo'                  => TIPO_VALOR_EVENTOS_INTERNO,
            'nombre'                => $validados['nombre'],
            'fecha'                 => $validados['fecha'],
            'telefono'              => $validados['telefono'],
            'valor'                 => $validados['valor'],
            'observacion'           => $validados['observacion'],
            'evento_id'             => $validados['evento_id'],
            'numeracion'            => $validados['numeracion'],
            'forma_pago'            => $validados['forma_pago'],
            'registrado_por_id'     => (int) $usuario['id'],
            'registrado_por_nombre' => $usuario['nombre'] ?? $usuario['usuario'],
        ]);
    } catch (InvalidArgumentException $e) {
        header('Location: ' . $redireccionBase . $sep . 'error=' . urlencode($e->getMessage()));
        exit;
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . $sep . 'error=' . urlencode('No se pudo guardar el registro.'));
        exit;
    }

    header('Location: ' . $redireccionBase . $sep . 'ok=1');
    exit;
}

if ($accion === 'guardar_permisos_rol') {
    if (!puedeGestionarUsuarios(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    require_once 'includes/permisos.php';

    $rolPermiso = trim((string) ($_POST['rol'] ?? ''));
    $permisos = isset($_POST['permisos']) && is_array($_POST['permisos']) ? $_POST['permisos'] : [];
    $redireccionPermisos = $_POST['redireccion'] ?? 'usuarios.php?pestaña=permisos';

    try {
        guardarPermisosRol($rolPermiso, $permisos);
    } catch (InvalidArgumentException $e) {
        $sep = strpos($redireccionPermisos, '?') !== false ? '&' : '?';
        header('Location: ' . $redireccionPermisos . $sep . 'error=' . urlencode($e->getMessage()));
        exit;
    } catch (PDOException $e) {
        $sep = strpos($redireccionPermisos, '?') !== false ? '&' : '?';
        header('Location: ' . $redireccionPermisos . $sep . 'error=' . urlencode('No se pudieron guardar los permisos.'));
        exit;
    }

    $sep = strpos($redireccionPermisos, '?') !== false ? '&' : '?';
    header('Location: ' . $redireccionPermisos . $sep . 'ok=1');
    exit;
}

if ($accion === 'crear_consejeria') {
    if (!puedeRegistrarConsejerias(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $nombreCompleto = trim((string) ($_POST['nombre_completo'] ?? ''));
    $telefono = trim((string) ($_POST['telefono'] ?? ''));
    $tipoConsejeria = trim((string) ($_POST['tipo_consejeria'] ?? ''));
    $anioEnCda = isset($_POST['anio_en_cda']) ? (int) $_POST['anio_en_cda'] : 0;
    $primeraConsejeria = isset($_POST['primera_consejeria']) && (string) $_POST['primera_consejeria'] === '0' ? false : true;
    $usuario = obtenerUsuarioActual();
    $redireccionBase = 'consejeria.php';
    $anioActual = (int) date('Y');

    if (
        $nombreCompleto === ''
        || $telefono === ''
        || !esTipoConsejeriaValido($tipoConsejeria)
        || $anioEnCda < 1900
        || $anioEnCda > $anioActual
    ) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Completa todos los campos obligatorios con datos válidos.'));
        exit;
    }

    try {
        insertarConsejeria([
            'nombre_completo'       => $nombreCompleto,
            'telefono'              => $telefono,
            'tipo_consejeria'       => $tipoConsejeria,
            'anio_en_cda'           => $anioEnCda,
            'primera_consejeria'    => $primeraConsejeria,
            'registrado_por_id'     => (int) $usuario['id'],
            'registrado_por_nombre' => $usuario['nombre'] ?? $usuario['usuario'],
        ]);
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('No se pudo guardar. Verifica que exista la tabla consejerias.'));
        exit;
    }

    header('Location: ' . $redireccionBase . '?pestaña=nuevo&ok=1');
    exit;
}

if ($accion === 'asignar_cita_consejeria') {
    if (!puedeAsignarCitaConsejeria(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $citaFecha = trim((string) ($_POST['cita_fecha'] ?? ''));
    $citaHora = trim((string) ($_POST['cita_hora'] ?? ''));
    $redireccionBase = $_POST['redireccion'] ?? 'consejeria.php?pestaña=registros';

    if ($id <= 0) {
        header('Location: ' . $redireccionBase . '&error=' . urlencode('Registro no válido.'));
        exit;
    }

    try {
        actualizarCitaConsejeria($id, $citaFecha, $citaHora);
    } catch (InvalidArgumentException $e) {
        header('Location: ' . $redireccionBase . '&error=' . urlencode($e->getMessage()));
        exit;
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . '&error=' . urlencode('No se pudo guardar la asignación.'));
        exit;
    }

    $separador = strpos($redireccionBase, '?') !== false ? '&' : '?';
    header('Location: ' . $redireccionBase . $separador . 'ok=1&asignacion=1');
    exit;
}

if ($accion === 'crear_inscripcion') {
    $rol = $usuarioActual['rol'];
    $seccionOrigen = trim((string) ($_POST['seccion'] ?? ''));

    if (!puedeRegistrarEnSeccion($rol, $seccionOrigen)) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $redireccionBase = obtenerUrlSeccion($seccionOrigen);
    $tipo = $seccionOrigen === 'generales'
        ? trim((string) ($_POST['tipo_formulario'] ?? ''))
        : $seccionOrigen;

    if ($seccionOrigen !== 'generales' && $tipo !== $seccionOrigen) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Tipo de registro no válido.'));
        exit;
    }

    if (!in_array($tipo, obtenerTiposInscripcionPermitidos($rol), true)) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('No tienes permiso para este tipo de registro.'));
        exit;
    }

    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $apellido = trim((string) ($_POST['apellido'] ?? ''));
    $celular = trim((string) ($_POST['celular'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($nombre === '' || $apellido === '' || $celular === '') {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Completa todos los campos obligatorios.'));
        exit;
    }

    $zona = '';
    $direccion = '';

    if ($tipo === 'conexion') {
        $zona = trim((string) ($_POST['zona'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));
        $zonasOk = array_keys(obtenerZonasConexion());

        if ($zona === '' || !in_array($zona, $zonasOk, true)) {
            header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Selecciona una zona válida.'));
            exit;
        }

        if ($direccion === '') {
            header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('La dirección es obligatoria.'));
            exit;
        }
    }

    try {
        insertarInscripcion($tipo, [
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'celular'        => $celular,
            'email'          => $email !== '' ? $email : null,
            'zona'           => $zona,
            'direccion'      => $direccion,
            'ip_cliente'     => $_SERVER['REMOTE_ADDR'] ?? '',
            'agente_usuario' => 'Sistema Web — ' . ($usuarioActual['usuario'] ?? 'interno'),
        ]);
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('No se pudo guardar el registro.'));
        exit;
    }

    header('Location: ' . $redireccionBase . '?pestaña=nuevo&ok=1');
    exit;
}

if ($accion === 'crear_presentacion') {
    $rol = $usuarioActual['rol'];

    if (!puedeRegistrarEnSeccion($rol, 'presentaciones')) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $redireccionBase = obtenerUrlSeccion('presentaciones');
    $campos = [
        'nombre_padre'      => trim((string) ($_POST['nombre_padre'] ?? '')),
        'nombre_madre'      => trim((string) ($_POST['nombre_madre'] ?? '')),
        'nombre_presentado' => trim((string) ($_POST['nombre_presentado'] ?? '')),
        'telefono_papa'     => trim((string) ($_POST['telefono_papa'] ?? '')),
        'telefono_mama'     => trim((string) ($_POST['telefono_mama'] ?? '')),
    ];

    foreach ($campos as $valor) {
        if ($valor === '') {
            header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Completa todos los campos obligatorios.'));
            exit;
        }
    }

    $fechaNacimiento = parsearFechaNacimientoPresentacion($_POST);
    if ($fechaNacimiento === null) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Ingresa una fecha de nacimiento válida (día, mes y año).'));
        exit;
    }

    try {
        insertarPresentacionNino([
            'nombre_padre'      => $campos['nombre_padre'],
            'nombre_madre'      => $campos['nombre_madre'],
            'nombre_presentado' => $campos['nombre_presentado'],
            'fecha_nacimiento'  => $fechaNacimiento,
            'telefono_papa'     => $campos['telefono_papa'],
            'telefono_mama'     => $campos['telefono_mama'],
            'ip_cliente'        => $_SERVER['REMOTE_ADDR'] ?? '',
            'agente_usuario'    => 'Sistema Web — ' . ($usuarioActual['usuario'] ?? 'interno'),
        ]);
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('No se pudo guardar el registro.'));
        exit;
    }

    header('Location: ' . $redireccionBase . '?pestaña=nuevo&ok=1');
    exit;
}

if ($accion === 'crear_ofrenda') {
    if (!puedeRegistrarOfrendas(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    $casaId = (int) ($_POST['casa_id'] ?? 0);
    $fecha = trim((string) ($_POST['fecha_ofrenda'] ?? ''));
    $monto = isset($_POST['monto']) ? (float) $_POST['monto'] : 0;
    $usuario = obtenerUsuarioActual();
    $redireccionBase = 'ofrendas.php';

    if ($casaId <= 0 || $fecha === '' || $monto <= 0) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Completa todos los campos con un valor mayor a cero.'));
        exit;
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('Fecha no válida.'));
        exit;
    }

    try {
        insertarOfrendaDesdeApi([
            'casa_id'               => $casaId,
            'fecha_ofrenda'         => $fecha,
            'monto'                 => $monto,
            'registrado_por_id'     => (int) $usuario['id'],
            'registrado_por_nombre' => $usuario['nombre'] ?? $usuario['usuario'],
        ]);
    } catch (InvalidArgumentException $e) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode($e->getMessage()));
        exit;
    } catch (PDOException $e) {
        header('Location: ' . $redireccionBase . '?pestaña=nuevo&error=' . urlencode('No se pudo guardar la ofrenda.'));
        exit;
    }

    header('Location: ' . $redireccionBase . '?pestaña=nuevo&ok=1');
    exit;
}

$accionesActualizar = [
    'actualizar_inscripcion',
    'actualizar_presentacion',
    'actualizar_ofrenda',
    'actualizar_valor_adicional',
    'actualizar_registro_evento',
    'actualizar_consejeria',
];

if (in_array($accion, $accionesActualizar, true)) {
    if (!puedeEditarRegistros(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    if ($id <= 0) {
        header('Location: ' . $redireccion);
        exit;
    }

    $sepRedireccion = strpos($redireccion, '?') !== false ? '&' : '?';

    try {
        switch ($accion) {
            case 'actualizar_inscripcion':
                $nombre = trim((string) ($_POST['nombre'] ?? ''));
                $apellido = trim((string) ($_POST['apellido'] ?? ''));
                $celular = trim((string) ($_POST['celular'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));

                if ($nombre === '' || $apellido === '' || $celular === '') {
                    throw new InvalidArgumentException('Completa todos los campos obligatorios.');
                }

                actualizarInscripcion($id, [
                    'nombre'      => $nombre,
                    'apellido'    => $apellido,
                    'celular'     => $celular,
                    'email'       => $email !== '' ? $email : null,
                    'zona'        => trim((string) ($_POST['zona'] ?? '')),
                    'direccion'   => trim((string) ($_POST['direccion'] ?? '')),
                    'contactado'  => (int) ($_POST['contactado'] ?? 0),
                ]);
                break;

            case 'actualizar_presentacion':
                $campos = [
                    'nombre_padre'      => trim((string) ($_POST['nombre_padre'] ?? '')),
                    'nombre_madre'      => trim((string) ($_POST['nombre_madre'] ?? '')),
                    'nombre_presentado' => trim((string) ($_POST['nombre_presentado'] ?? '')),
                    'telefono_papa'     => trim((string) ($_POST['telefono_papa'] ?? '')),
                    'telefono_mama'     => trim((string) ($_POST['telefono_mama'] ?? '')),
                    'estado'            => trim((string) ($_POST['estado'] ?? '')),
                ];

                foreach ($campos as $valor) {
                    if ($valor === '') {
                        throw new InvalidArgumentException('Completa todos los campos obligatorios.');
                    }
                }

                if (!esEstadoPresentacionValido($campos['estado'])) {
                    throw new InvalidArgumentException('Estado no válido.');
                }

                $fechaNacimiento = parsearFechaNacimientoPresentacion($_POST);
                if ($fechaNacimiento === null) {
                    throw new InvalidArgumentException('Ingresa una fecha de nacimiento válida.');
                }

                actualizarPresentacionNino($id, array_merge($campos, ['fecha_nacimiento' => $fechaNacimiento]));
                break;

            case 'actualizar_ofrenda':
                $casaId = (int) ($_POST['casa_id'] ?? 0);
                $fecha = trim((string) ($_POST['fecha_ofrenda'] ?? ''));
                $monto = isset($_POST['monto']) ? (float) $_POST['monto'] : 0;

                if ($casaId <= 0 || $fecha === '' || $monto <= 0) {
                    throw new InvalidArgumentException('Completa todos los campos con un valor mayor a cero.');
                }

                $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                    throw new InvalidArgumentException('Fecha no válida.');
                }

                actualizarOfrenda($id, [
                    'casa_id'       => $casaId,
                    'fecha_ofrenda' => $fecha,
                    'monto'         => $monto,
                ]);
                break;

            case 'actualizar_valor_adicional':
                $tipo = trim((string) ($_POST['tipo'] ?? ''));
                $nombre = trim((string) ($_POST['nombre'] ?? ''));
                $fecha = trim((string) ($_POST['fecha'] ?? ''));
                $telefono = trim((string) ($_POST['telefono'] ?? ''));
                $valor = isset($_POST['valor']) ? (float) $_POST['valor'] : 0;
                $observacion = trim((string) ($_POST['observacion'] ?? ''));

                if (!esTipoValorAdicionalValido($tipo) || $fecha === '' || $telefono === '' || $valor <= 0) {
                    throw new InvalidArgumentException('Completa todos los campos obligatorios con un valor mayor a cero.');
                }

                if ($nombre === '') {
                    throw new InvalidArgumentException('Completa todos los campos obligatorios.');
                }

                $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                    throw new InvalidArgumentException('Fecha no válida.');
                }

                actualizarValorAdicional($id, [
                    'tipo'        => $tipo,
                    'nombre'      => $nombre,
                    'fecha'       => $fecha,
                    'telefono'    => $telefono,
                    'valor'       => $valor,
                    'observacion' => $observacion,
                    'evento_id'   => null,
                    'numeracion'  => null,
                    'forma_pago'  => null,
                ]);
                break;

            case 'actualizar_registro_evento':
                actualizarRegistroEvento($id, $_POST);
                break;

            case 'actualizar_consejeria':
                $nombreCompleto = trim((string) ($_POST['nombre_completo'] ?? ''));
                $telefono = trim((string) ($_POST['telefono'] ?? ''));
                $tipoConsejeria = trim((string) ($_POST['tipo_consejeria'] ?? ''));
                $anioEnCda = isset($_POST['anio_en_cda']) ? (int) $_POST['anio_en_cda'] : 0;
                $anioActual = (int) date('Y');

                if (
                    $nombreCompleto === ''
                    || $telefono === ''
                    || !esTipoConsejeriaValido($tipoConsejeria)
                    || $anioEnCda < 1900
                    || $anioEnCda > $anioActual
                ) {
                    throw new InvalidArgumentException('Completa todos los campos obligatorios con datos válidos.');
                }

                actualizarConsejeria($id, [
                    'nombre_completo'   => $nombreCompleto,
                    'telefono'          => $telefono,
                    'tipo_consejeria'   => $tipoConsejeria,
                    'anio_en_cda'       => $anioEnCda,
                    'primera_consejeria'=> isset($_POST['primera_consejeria']) && (string) $_POST['primera_consejeria'] === '1',
                    'cita_fecha'        => trim((string) ($_POST['cita_fecha'] ?? '')),
                    'cita_hora'         => trim((string) ($_POST['cita_hora'] ?? '')),
                ]);
                break;
        }
    } catch (InvalidArgumentException $e) {
        header('Location: ' . $redireccion . $sepRedireccion . 'error=' . urlencode($e->getMessage()));
        exit;
    } catch (PDOException $e) {
        header('Location: ' . $redireccion . $sepRedireccion . 'error=' . urlencode('No se pudo actualizar el registro.'));
        exit;
    }

    header('Location: ' . $redireccion . $sepRedireccion . 'actualizado=1');
    exit;
}

if ($id <= 0 && $accion !== '' && !in_array($accion, ['asignar_cita_consejeria', 'actualizar_estado_conexion', 'actualizar_estado_presentacion'], true)) {
    header('Location: ' . $redireccion);
    exit;
}

$accionesEliminar = [
    'eliminar_inscripcion',
    'eliminar_presentacion',
    'eliminar_ofrenda',
    'eliminar_valor_adicional',
    'eliminar_consejeria',
    'eliminar_usuario',
    'eliminar_territorio',
    'eliminar_lider',
    'eliminar_casa',
    'eliminar_evento',
    'eliminar_tipo_valor',
];

if (in_array($accion, $accionesEliminar, true)) {
    if (!puedeEliminarRegistros(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    switch ($accion) {
        case 'eliminar_inscripcion':
            eliminarInscripcion($id);
            break;
        case 'eliminar_presentacion':
            eliminarPresentacionNino($id);
            break;
        case 'eliminar_ofrenda':
            eliminarOfrenda($id);
            break;
        case 'eliminar_valor_adicional':
            if (!puedeEditarValoresAdicionales(obtenerUsuarioActual()['rol'])) {
                header('Location: ' . $urlInicio);
                exit;
            }
            eliminarValorAdicional($id);
            break;
        case 'eliminar_consejeria':
            eliminarConsejeria($id);
            break;
        case 'eliminar_usuario':
            require_once 'includes/users.php';
            $usuario = obtenerUsuarioActual();
            eliminarUsuario($id, (int) $usuario['id']);
            $redireccion = 'usuarios.php';
            break;
        case 'eliminar_territorio':
            eliminarTerritorio($id);
            break;
        case 'eliminar_lider':
            eliminarLider($id);
            break;
        case 'eliminar_casa':
            eliminarCasaVida($id);
            break;
        case 'eliminar_evento':
            eliminarEvento($id);
            break;
        case 'eliminar_tipo_valor':
            try {
                eliminarTipoValorAdicional($id);
            } catch (InvalidArgumentException $e) {
                $destino = $redireccion ?: 'valores-adicionales.php?pestaña=tipos';
                $sep = strpos($destino, '?') !== false ? '&' : '?';
                header('Location: ' . $destino . $sep . 'error=' . urlencode($e->getMessage()));
                exit;
            }
            break;
    }

    header('Location: ' . $redireccion);
    exit;
}

if ($accion === 'actualizar_estado_presentacion') {
    if (!puedeVerPresentaciones(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    if ($id <= 0) {
        header('Location: ' . $redireccion);
        exit;
    }

    $estado = trim((string) ($_POST['estado'] ?? ''));

    try {
        actualizarEstadoPresentacionNino($id, $estado);
    } catch (InvalidArgumentException $e) {
        // Sin mensaje flash; redirige igual
    }

    header('Location: ' . $redireccion);
    exit;
}

if ($accion === 'actualizar_estado_conexion') {
    if (!puedeGestionarEstadoConexion(obtenerUsuarioActual()['rol'])) {
        header('Location: ' . $urlInicio);
        exit;
    }

    if ($id <= 0) {
        header('Location: ' . $redireccion);
        exit;
    }

    $contactado = isset($_POST['contactado']) && (string) $_POST['contactado'] === '1';

    try {
        actualizarEstadoConexionInscripcion($id, $contactado ? 1 : 0);
    } catch (PDOException $e) {
        // Redirige igual
    }

    header('Location: ' . $redireccion);
    exit;
}

header('Location: ' . $urlInicio);
exit;
