<?php

require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/submissions.php';
require_once __DIR__ . '/../includes/estructura.php';
require_once __DIR__ . '/../includes/api_sesion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$claveApi = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['recurso'] ?? '') === 'presentaciones') {
    if ($claveApi === '' || $claveApi !== API_KEY) {
        http_response_code(401);
        echo json_encode(['exito' => false, 'mensaje' => 'Clave API inválida.']);
        exit;
    }

    require_once __DIR__ . '/../includes/presentaciones_api.php';

    try {
        responderListaPresentacionesPublica();
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error al procesar la solicitud.']);
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

if ($claveApi === '' || $claveApi !== API_KEY) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'Clave API inválida.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    $payload = $_POST;
}

$tipoFormulario = isset($payload['tipo_formulario']) ? trim((string) $payload['tipo_formulario']) : '';

$tiposInscripcion = ['escol', 'academia', 'bautismo', 'conexion'];
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$agente = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    if (in_array($tipoFormulario, $tiposInscripcion, true)) {
        $nombre   = trim((string) ($payload['nombre'] ?? ''));
        $apellido = trim((string) ($payload['apellido'] ?? ''));
        $celular  = trim((string) ($payload['celular'] ?? ''));
        $email    = trim((string) ($payload['email'] ?? ''));

        if ($nombre === '' || $apellido === '' || $celular === '' || $email === '') {
            throw new InvalidArgumentException('Completa todos los campos obligatorios.');
        }

        $zona      = '';
        $direccion = '';

        if ($tipoFormulario === 'conexion') {
            $zona      = trim((string) ($payload['zona'] ?? ''));
            $direccion = trim((string) ($payload['direccion'] ?? ''));
            $zonasOk   = array_keys(obtenerZonasConexion());

            if ($zona === '' || !in_array($zona, $zonasOk, true)) {
                throw new InvalidArgumentException('Selecciona una zona válida.');
            }

            if ($direccion === '') {
                throw new InvalidArgumentException('La dirección es obligatoria.');
            }
        }

        $id = insertarInscripcion($tipoFormulario, [
            'nombre'         => $nombre,
            'apellido'       => $apellido,
            'celular'        => $celular,
            'email'          => $email,
            'zona'           => $zona,
            'direccion'      => $direccion,
            'ip_cliente'     => $ip,
            'agente_usuario' => $agente,
        ]);

        $mensaje = $tipoFormulario === 'conexion'
            ? '¡Gracias por registrarte! Nos comunicaremos contigo pronto.'
            : '¡Registro enviado correctamente!';

        echo json_encode([
            'exito'   => true,
            'mensaje' => $mensaje,
            'id'      => $id,
        ]);
        exit;
    }

    if ($tipoFormulario === 'presentacion_ninos') {
        $campos = [
            'nombre_padre',
            'nombre_madre',
            'nombre_presentado',
            'telefono_papa',
            'telefono_mama',
        ];

        foreach ($campos as $campo) {
            if (trim((string) ($payload[$campo] ?? '')) === '') {
                throw new InvalidArgumentException('Completa todos los campos obligatorios.');
            }
        }

        $fechaNacimiento = parsearFechaNacimientoPresentacion($payload);
        if ($fechaNacimiento === null) {
            throw new InvalidArgumentException('Ingresa una fecha de nacimiento válida (día, mes y año).');
        }

        $id = insertarPresentacionNino([
            'nombre_padre'      => trim((string) $payload['nombre_padre']),
            'nombre_madre'      => trim((string) $payload['nombre_madre']),
            'nombre_presentado' => trim((string) $payload['nombre_presentado']),
            'fecha_nacimiento'  => $fechaNacimiento,
            'telefono_papa'     => trim((string) $payload['telefono_papa']),
            'telefono_mama'     => trim((string) $payload['telefono_mama']),
            'ip_cliente'        => $ip,
            'agente_usuario'    => $agente,
        ]);

        echo json_encode([
            'exito'   => true,
            'mensaje' => 'Inscripción de presentación enviada correctamente.',
            'id'      => $id,
        ]);
        exit;
    }

    if ($tipoFormulario === 'ofrenda') {
        $token = obtenerTokenSesionDesdeRequest($payload);
        $usuarioSesion = validarTokenSesionApi($token);

        if (!$usuarioSesion) {
            http_response_code(401);
            echo json_encode(['exito' => false, 'mensaje' => 'Debes iniciar sesión para registrar ofrendas.']);
            exit;
        }

        if (!puedeRegistrarOfrendas($usuarioSesion['rol'])) {
            http_response_code(403);
            echo json_encode(['exito' => false, 'mensaje' => 'Tu rol no tiene permiso para registrar ofrendas.']);
            exit;
        }

        $casaId = (int) ($payload['casa_id'] ?? 0);
        $fecha = trim((string) ($payload['fecha_ofrenda'] ?? ''));
        $monto = isset($payload['monto']) ? (float) $payload['monto'] : 0;

        if ($casaId <= 0 || $fecha === '' || $monto <= 0) {
            throw new InvalidArgumentException('Completa casa de vida, fecha y un valor mayor a cero.');
        }

        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('Fecha no válida.');
        }

        $id = insertarOfrendaDesdeApi([
            'casa_id'                => $casaId,
            'fecha_ofrenda'          => $fecha,
            'monto'                  => $monto,
            'registrado_por_id'      => $usuarioSesion['id'],
            'registrado_por_nombre'  => $usuarioSesion['nombre'],
        ]);

        echo json_encode([
            'exito'   => true,
            'mensaje' => 'Ofrenda registrada correctamente.',
            'id'      => $id,
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Tipo de formulario no válido.']);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar en la base de datos.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno del servidor.']);
}
