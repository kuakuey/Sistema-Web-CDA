<?php

require_once __DIR__ . '/../config/database.php';

function obtenerZonasConexion(): array
{
    return [
        'norte'       => 'Norte',
        'sur'         => 'Sur',
        'centro'      => 'Centro',
        'samborondon' => 'Samborondón',
        'duran'       => 'Durán',
        'otros'       => 'Otros',
    ];
}

function insertarInscripcion(string $tipoFormulario, array $datos): int
{
    $pdo = getConnection();
    $estadoBautismo = $tipoFormulario === 'bautismo' ? 'ingresado' : 'ingresado';

    $stmt = $pdo->prepare(
        'INSERT INTO inscripciones (
            tipo_formulario, nombre, apellido, celular, email, zona, direccion,
            estado_bautismo, ip_cliente, agente_usuario, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    $stmt->execute([
        $tipoFormulario,
        $datos['nombre'] ?? '',
        $datos['apellido'] ?? '',
        $datos['celular'] ?? '',
        $datos['email'] ?? null,
        $datos['zona'] ?? '',
        $datos['direccion'] ?? '',
        $estadoBautismo,
        $datos['ip_cliente'] ?? '',
        $datos['agente_usuario'] ?? '',
    ]);

    return (int) $pdo->lastInsertId();
}

function insertarPresentacionNino(array $datos): int
{
    $pdo = getConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO presentaciones_ninos (
            parentesco_representante_1, nombre_padre, telefono_papa,
            parentesco_representante_2, nombre_madre, telefono_mama,
            nombre_presentado, fecha_nacimiento, estado,
            ip_cliente, agente_usuario, creado_en, actualizado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
    );

    $stmt->execute([
        $datos['parentesco_representante_1'] ?? '',
        $datos['nombre_padre'] ?? '',
        $datos['telefono_papa'] ?? '',
        $datos['parentesco_representante_2'] ?? null,
        $datos['nombre_madre'] ?? null,
        $datos['telefono_mama'] ?? null,
        $datos['nombre_presentado'] ?? '',
        $datos['fecha_nacimiento'] ?? null,
        'recibido',
        $datos['ip_cliente'] ?? '',
        $datos['agente_usuario'] ?? '',
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * @param array<string, string|null> $representantes
 * @param array<int, array{nombre_presentado: string, fecha_nacimiento: string}> $presentados
 * @param array<string, mixed> $meta
 * @return array<int, int>
 */
function insertarPresentacionesNinosGrupo(array $representantes, array $presentados, array $meta): array
{
    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        $ids = [];

        foreach ($presentados as $presentado) {
            $ids[] = insertarPresentacionNino(array_merge($representantes, $presentado, $meta));
        }

        $pdo->commit();

        return $ids;
    } catch (Throwable $e) {
        $pdo->rollBack();

        throw $e;
    }
}

function obtenerMesesCalendario(): array
{
    return [
        1  => 'Enero',
        2  => 'Febrero',
        3  => 'Marzo',
        4  => 'Abril',
        5  => 'Mayo',
        6  => 'Junio',
        7  => 'Julio',
        8  => 'Agosto',
        9  => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];
}

function construirFechaNacimiento(int $dia, int $mes, int $anio): ?string
{
    if ($anio < 1900 || !checkdate($mes, $dia, $anio)) {
        return null;
    }

    $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

    if ($fecha > date('Y-m-d')) {
        return null;
    }

    return $fecha;
}

function parsearFechaNacimientoPresentacion(array $datos): ?string
{
    if (!empty($datos['fecha_nacimiento'])) {
        $fecha = trim((string) $datos['fecha_nacimiento']);
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);

        if ($dt && $dt->format('Y-m-d') === $fecha && $fecha <= date('Y-m-d') && (int) $dt->format('Y') >= 1900) {
            return $fecha;
        }
    }

    return construirFechaNacimiento(
        (int) ($datos['dia_nacimiento'] ?? 0),
        (int) ($datos['mes_nacimiento'] ?? 0),
        (int) ($datos['anio_nacimiento'] ?? 0)
    );
}

function calcularEdadDesdeFechaNacimiento(?string $fecha): ?int
{
    if ($fecha === null || $fecha === '') {
        return null;
    }

    $nacimiento = DateTime::createFromFormat('Y-m-d', $fecha);

    if (!$nacimiento) {
        return null;
    }

    $hoy = new DateTime('today');

    if ($nacimiento > $hoy) {
        return null;
    }

    return (int) $nacimiento->diff($hoy)->y;
}

function calcularMesesDesdeFechaNacimiento(?string $fecha): ?int
{
    if ($fecha === null || $fecha === '') {
        return null;
    }

    $nacimiento = DateTime::createFromFormat('Y-m-d', $fecha);

    if (!$nacimiento) {
        return null;
    }

    $hoy = new DateTime('today');

    if ($nacimiento > $hoy) {
        return null;
    }

    $meses = ((int) $hoy->format('Y') - (int) $nacimiento->format('Y')) * 12
        + ((int) $hoy->format('m') - (int) $nacimiento->format('m'));

    if ((int) $hoy->format('d') < (int) $nacimiento->format('d')) {
        $meses--;
    }

    return max(0, $meses);
}

function formatearEdadPresentacion(?string $fecha): string
{
    $edad = calcularEdadDesdeFechaNacimiento($fecha);

    if ($edad === null) {
        return '—';
    }

    if ($edad === 0) {
        $meses = calcularMesesDesdeFechaNacimiento($fecha);

        if ($meses === null) {
            return '—';
        }

        return $meses . ' mes' . ($meses === 1 ? '' : 'es');
    }

    return $edad . ' año' . ($edad === 1 ? '' : 's');
}

function formatearFechaNacimiento(?string $fecha): string
{
    if ($fecha === null || $fecha === '') {
        return '—';
    }

    $dt = DateTime::createFromFormat('Y-m-d', $fecha);

    return $dt ? $dt->format('d/m/Y') : $fecha;
}

function obtenerEtiquetasTiposFormulario(): array
{
    return [
        'escol'              => 'Escol',
        'academia'           => 'Academia',
        'bautismo'           => 'Bautismo',
        'conexion'           => 'Conexión',
        'presentacion_ninos' => 'Presentación niños',
    ];
}

function etiquetaZonaConexion(string $slug): string
{
    $zonas = obtenerZonasConexion();

    return $zonas[$slug] ?? $slug;
}

function contarInscripciones(?string $tipoFormulario = null): int
{
    $pdo = getConnection();

    if ($tipoFormulario !== null && $tipoFormulario !== '') {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM inscripciones WHERE tipo_formulario = ?');
        $stmt->execute([$tipoFormulario]);

        return (int) $stmt->fetchColumn();
    }

    return (int) $pdo->query('SELECT COUNT(*) FROM inscripciones')->fetchColumn();
}

function contarPresentacionesNinos(): int
{
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM presentaciones_ninos')->fetchColumn();
}

function contarOfrendas(): int
{
    $pdo = getConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM ofrendas')->fetchColumn();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerOfrendas(int $limite = 200): array
{
    $pdo = getConnection();
    $limite = max(1, min($limite, 500));

    return $pdo->query(
        'SELECT * FROM ofrendas ORDER BY creado_en DESC, id DESC LIMIT ' . $limite
    )->fetchAll();
}

/**
 * @param array<int, string> $tipos
 * @return array<int, array<string, mixed>>
 */
function obtenerInscripcionesPorTipos(array $tipos, int $limite = 200): array
{
    if (empty($tipos)) {
        return [];
    }

    $pdo = getConnection();
    $limite = max(1, min($limite, 500));
    $marcadores = implode(',', array_fill(0, count($tipos), '?'));

    $stmt = $pdo->prepare(
        "SELECT * FROM inscripciones WHERE tipo_formulario IN ($marcadores)
         ORDER BY creado_en DESC, id DESC LIMIT $limite"
    );
    $stmt->execute($tipos);

    return $stmt->fetchAll();
}

function eliminarInscripcion(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM inscripciones WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function actualizarInscripcion(int $id, array $datos): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE inscripciones SET nombre = ?, apellido = ?, celular = ?, email = ?, zona = ?, direccion = ?, contactado = ? WHERE id = ?'
    );

    return $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        trim($datos['celular']),
        $datos['email'] ?? null,
        trim($datos['zona'] ?? ''),
        trim($datos['direccion'] ?? ''),
        (int) ($datos['contactado'] ?? 0),
        $id,
    ]);
}

function eliminarPresentacionNino(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM presentaciones_ninos WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function resolverFechaPresentacionNino(string $estadoNuevo, string $estadoActual, ?string $fechaActual): ?string
{
    if ($estadoNuevo !== 'presentado') {
        return null;
    }

    if ($estadoActual === 'presentado' && $fechaActual !== null && $fechaActual !== '') {
        return $fechaActual;
    }

    return date('Y-m-d');
}

function actualizarPresentacionNino(int $id, array $datos): bool
{
    require_once __DIR__ . '/filters.php';

    $estadoNuevo = trim((string) ($datos['estado'] ?? ''));

    if (!esEstadoPresentacionValido($estadoNuevo)) {
        throw new InvalidArgumentException('Estado no válido.');
    }

    $pdo = getConnection();
    $stmtActual = $pdo->prepare(
        'SELECT estado, fecha_presentacion, estado_bloqueado FROM presentaciones_ninos WHERE id = ?'
    );
    $stmtActual->execute([$id]);
    $actual = $stmtActual->fetch();

    if (!$actual) {
        throw new InvalidArgumentException('Registro de presentación no encontrado.');
    }

    $estadoActual = (string) ($actual['estado'] ?? '');
    $bloqueado = !empty($actual['estado_bloqueado']);

    if ($bloqueado && $estadoNuevo !== $estadoActual) {
        throw new InvalidArgumentException('El estado ya fue marcado como Presentado y no puede modificarse desde aquí.');
    }

    $fechaPresentacion = resolverFechaPresentacionNino(
        $estadoNuevo,
        $estadoActual,
        $actual['fecha_presentacion'] ?? null
    );
    $marcarBloqueado = ($estadoNuevo === 'presentado') ? 1 : (int) $bloqueado;

    $stmt = $pdo->prepare(
        'UPDATE presentaciones_ninos SET
            parentesco_representante_1 = ?, nombre_padre = ?, telefono_papa = ?,
            parentesco_representante_2 = ?, nombre_madre = ?, telefono_mama = ?,
            nombre_presentado = ?, fecha_nacimiento = ?,
            estado = ?, fecha_presentacion = ?,
            estado_bloqueado = ?, actualizado_en = NOW()
         WHERE id = ?'
    );

    return $stmt->execute([
        trim((string) $datos['parentesco_representante_1']),
        trim((string) $datos['nombre_padre']),
        trim((string) $datos['telefono_papa']),
        $datos['parentesco_representante_2'] !== null ? trim((string) $datos['parentesco_representante_2']) : null,
        $datos['nombre_madre'] !== null ? trim((string) $datos['nombre_madre']) : null,
        $datos['telefono_mama'] !== null ? trim((string) $datos['telefono_mama']) : null,
        trim($datos['nombre_presentado']),
        $datos['fecha_nacimiento'],
        $estadoNuevo,
        $fechaPresentacion,
        $marcarBloqueado,
        $id,
    ]);
}

function eliminarOfrenda(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM ofrendas WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function actualizarOfrenda(int $id, array $datos): bool
{
    require_once __DIR__ . '/estructura.php';

    $casa = obtenerCasaVida((int) $datos['casa_id']);

    if (!$casa) {
        throw new InvalidArgumentException('Casa de vida no válida.');
    }

    $pdo = getConnection();
    $lider = trim(($casa['lider_nombre'] ?? '') . ' ' . ($casa['lider_apellido'] ?? ''));
    $stmt = $pdo->prepare(
        'UPDATE ofrendas SET casa_id = ?, casa_vida = ?, lider = ?, fecha_ofrenda = ?, monto = ? WHERE id = ?'
    );

    return $stmt->execute([
        (int) $casa['id'],
        $casa['nombre'],
        $lider,
        $datos['fecha_ofrenda'],
        (float) $datos['monto'],
        $id,
    ]);
}

function formatearMonto(float $monto): string
{
    return '$' . number_format($monto, 2, '.', ',');
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerInscripciones(?string $tipoFormulario = null, int $limite = 200): array
{
    $pdo = getConnection();
    $limite = max(1, min($limite, 500));

    if ($tipoFormulario !== null && $tipoFormulario !== '') {
        $stmt = $pdo->prepare(
            'SELECT * FROM inscripciones WHERE tipo_formulario = ? ORDER BY creado_en DESC, id DESC LIMIT ' . $limite
        );
        $stmt->execute([$tipoFormulario]);

        return $stmt->fetchAll();
    }

    return $pdo->query(
        'SELECT * FROM inscripciones ORDER BY creado_en DESC, id DESC LIMIT ' . $limite
    )->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerPresentacionesNinos(int $limite = 200): array
{
    $pdo = getConnection();
    $limite = max(1, min($limite, 500));

    return $pdo->query(
        'SELECT * FROM presentaciones_ninos ORDER BY creado_en DESC, id DESC LIMIT ' . $limite
    )->fetchAll();
}

function formatearFechaHora(?string $fechaHora): string
{
    if ($fechaHora === null || $fechaHora === '') {
        return '—';
    }

    $dt = date_create($fechaHora);

    return $dt ? $dt->format('d/m/Y H:i') : $fechaHora;
}

function actualizarEstadoPresentacionNino(int $id, string $estado, string $rol): bool
{
    require_once __DIR__ . '/filters.php';
    require_once __DIR__ . '/roles.php';

    if (!esEstadoPresentacionValido($estado)) {
        throw new InvalidArgumentException('Estado no válido.');
    }

    $pdo = getConnection();
    $stmtActual = $pdo->prepare(
        'SELECT estado, fecha_presentacion, estado_bloqueado FROM presentaciones_ninos WHERE id = ?'
    );
    $stmtActual->execute([$id]);
    $actual = $stmtActual->fetch();

    if (!$actual) {
        throw new InvalidArgumentException('Registro de presentación no encontrado.');
    }

    $esSuperadmin = $rol === ROL_SUPERADMIN;
    $bloqueado = !empty($actual['estado_bloqueado']);
    $estadoActual = (string) ($actual['estado'] ?? '');

    if (!$esSuperadmin && $bloqueado) {
        throw new InvalidArgumentException('El estado ya fue marcado como Presentado y no puede modificarse.');
    }

    if ($estado !== 'presentado' && $estadoActual === 'presentado' && !$esSuperadmin) {
        throw new InvalidArgumentException('Solo un superadministrador puede cambiar el estado desde Presentado.');
    }

    $fechaPresentacion = resolverFechaPresentacionNino(
        $estado,
        $estadoActual,
        $actual['fecha_presentacion'] ?? null
    );
    $marcarBloqueado = ($estado === 'presentado') ? 1 : ($esSuperadmin ? 0 : (int) $bloqueado);

    $stmt = $pdo->prepare(
        'UPDATE presentaciones_ninos SET
            estado = ?, fecha_presentacion = ?, estado_bloqueado = ?, actualizado_en = NOW()
         WHERE id = ?'
    );

    return $stmt->execute([$estado, $fechaPresentacion, $marcarBloqueado, $id]) && $stmt->rowCount() > 0;
}

function restablecerEstadoPresentacionNino(int $id, string $rol): bool
{
    if ($rol !== ROL_SUPERADMIN) {
        throw new InvalidArgumentException('Solo un superadministrador puede restablecer el estado de presentación.');
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE presentaciones_ninos SET
            estado = ?, fecha_presentacion = NULL, estado_bloqueado = 0, actualizado_en = NOW()
         WHERE id = ?'
    );

    return $stmt->execute(['recibido', $id]) && $stmt->rowCount() > 0;
}

function actualizarEstadoConexionInscripcion(int $id, int $contactado): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE inscripciones SET contactado = ? WHERE id = ? AND tipo_formulario = ?'
    );

    return $stmt->execute([$contactado ? 1 : 0, $id, 'conexion']);
}

function actualizarEstadoBautismoInscripcion(
    int $id,
    string $estado,
    ?string $fechaBautismo,
    string $rol
): bool {
    require_once __DIR__ . '/filters.php';
    require_once __DIR__ . '/roles.php';

    if (!esEstadoBautismoValido($estado)) {
        throw new InvalidArgumentException('Estado de bautismo no válido.');
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT estado_bautismo, estado_bautismo_bloqueado
         FROM inscripciones WHERE id = ? AND tipo_formulario = ?'
    );
    $stmt->execute([$id, 'bautismo']);
    $fila = $stmt->fetch();

    if (!$fila) {
        throw new InvalidArgumentException('Registro de bautismo no encontrado.');
    }

    $esSuperadmin = $rol === ROL_SUPERADMIN;
    $bloqueado = !empty($fila['estado_bautismo_bloqueado']);
    $estadoActual = (string) ($fila['estado_bautismo'] ?? 'ingresado');

    if (!$esSuperadmin && $bloqueado) {
        throw new InvalidArgumentException('El estado de bautismo ya fue actualizado y no puede modificarse de nuevo.');
    }

    if ($estado === 'ingresado' && $estadoActual === 'bautizado' && !$esSuperadmin) {
        throw new InvalidArgumentException('Solo un superadministrador puede volver el estado a Ingresado.');
    }

    if ($estado === 'bautizado') {
        $fecha = trim((string) ($fechaBautismo ?? ''));

        if ($fecha === '') {
            $fecha = date('Y-m-d');
        }

        $dt = DateTime::createFromFormat('Y-m-d', $fecha);

        if (!$dt || $dt->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('La fecha de bautismo no es válida.');
        }

        if ($fecha > date('Y-m-d')) {
            throw new InvalidArgumentException('La fecha de bautismo no puede ser futura.');
        }
    } else {
        $fecha = null;
    }

    if ($estado === $estadoActual && $estado === 'ingresado' && empty($fila['fecha_bautismo'])) {
        throw new InvalidArgumentException('No hay cambios por aplicar.');
    }

    $marcarBloqueado = 1;

    if ($estado === 'ingresado' && $esSuperadmin) {
        $marcarBloqueado = 0;
    } elseif ($esSuperadmin) {
        $marcarBloqueado = (int) $bloqueado;
    }

    $stmtUpdate = $pdo->prepare(
        'UPDATE inscripciones SET
            estado_bautismo = ?,
            fecha_bautismo = ?,
            estado_bautismo_bloqueado = ?
         WHERE id = ? AND tipo_formulario = ?'
    );

    return $stmtUpdate->execute([
        $estado,
        $fecha,
        $marcarBloqueado,
        $id,
        'bautismo',
    ]) && $stmtUpdate->rowCount() > 0;
}

function restablecerEstadoBautismoInscripcion(int $id, string $rol): bool
{
    if ($rol !== ROL_SUPERADMIN) {
        throw new InvalidArgumentException('Solo un superadministrador puede restablecer el estado de bautismo.');
    }

    return actualizarEstadoBautismoInscripcion($id, 'ingresado', null, $rol);
}

/**
 * @param array<int, string> $estados
 * @return array<int, array<string, mixed>>
 */
function listarPresentacionesNinosPorEstados(array $estados, int $limite = 300): array
{
    require_once __DIR__ . '/filters.php';

    $pdo = getConnection();
    $limite = max(1, min($limite, 500));

    if (empty($estados)) {
        return $pdo->query(
            'SELECT * FROM presentaciones_ninos ORDER BY creado_en DESC, id DESC LIMIT ' . $limite
        )->fetchAll();
    }

    $estadosValidos = array_values(array_filter($estados, 'esEstadoPresentacionValido'));

    if (empty($estadosValidos)) {
        return [];
    }

    $marcadores = implode(',', array_fill(0, count($estadosValidos), '?'));

    $stmt = $pdo->prepare(
        "SELECT * FROM presentaciones_ninos WHERE estado IN ($marcadores)
         ORDER BY creado_en DESC, id DESC LIMIT $limite"
    );
    $stmt->execute($estadosValidos);

    return $stmt->fetchAll();
}
