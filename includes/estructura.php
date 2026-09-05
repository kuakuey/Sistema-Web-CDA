<?php

require_once __DIR__ . '/esquema.php';

function asegurarTablasEstructura(): void
{
    static $listo = false;

    if ($listo) {
        return;
    }

    migrateEstructuraTables(getConnection());
    $listo = true;
}

function nombreCompletoLider(array $registro): string
{
    return trim(($registro['nombre'] ?? '') . ' ' . ($registro['apellido'] ?? ''));
}

function etiquetaParejaMiembro(string $pareja): string
{
    return $pareja === 'esposa' ? 'Esposa' : 'Esposo';
}

function etiquetaRolTerritorio(string $rol): string
{
    return $rol === 'encargado' ? 'Encargado' : 'Coordinador';
}

function normalizarParejaMiembro(?string $pareja): string
{
    $clave = function_exists('mb_strtolower')
        ? mb_strtolower(trim((string) $pareja), 'UTF-8')
        : strtolower(trim((string) $pareja));
    $clave = strtr($clave, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
    ]);

    $mapa = [
        'esposo'     => 'esposo',
        'hombre'     => 'esposo',
        'masculino'  => 'esposo',
        'm'          => 'esposo',
        'esposa'     => 'esposa',
        'mujer'      => 'esposa',
        'femenino'   => 'esposa',
        'f'          => 'esposa',
    ];

    if (!isset($mapa[$clave])) {
        throw new InvalidArgumentException('Indica si el miembro es esposo o esposa.');
    }

    return $mapa[$clave];
}

function normalizarRolTerritorio(?string $rol): string
{
    $clave = function_exists('mb_strtolower')
        ? mb_strtolower(trim((string) $rol), 'UTF-8')
        : strtolower(trim((string) $rol));
    $clave = strtr($clave, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
    ]);

    $mapa = [
        'coordinador'  => 'coordinador',
        'coordinadora' => 'coordinador',
        'coord'        => 'coordinador',
        'encargado'    => 'encargado',
        'encargada'    => 'encargado',
        'enc'          => 'encargado',
    ];

    if (!isset($mapa[$clave])) {
        throw new InvalidArgumentException('El rol debe ser coordinador o encargado.');
    }

    return $mapa[$clave];
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerTerritorios(): array
{
    asegurarTablasEstructura();
    $pdo = getConnection();

    return $pdo->query(
        'SELECT * FROM territorios ORDER BY orden ASC, nombre ASC, id ASC'
    )->fetchAll();
}

function crearTerritorio(string $nombre): int
{
    asegurarTablasEstructura();
    require_once __DIR__ . '/texto.php';
    $nombre = normalizarTextoOrdenado($nombre);

    $pdo = getConnection();
    $maximo = (int) $pdo->query('SELECT COALESCE(MAX(orden), 0) FROM territorios')->fetchColumn();

    $stmt = $pdo->prepare(
        'INSERT INTO territorios (nombre, orden, creado_en) VALUES (?, ?, NOW())'
    );
    $stmt->execute([trim($nombre), $maximo + 1]);

    return (int) $pdo->lastInsertId();
}

function actualizarTerritorio(int $id, string $nombre): bool
{
    asegurarTablasEstructura();
    require_once __DIR__ . '/texto.php';
    $nombre = normalizarTextoOrdenado($nombre);

    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE territorios SET nombre = ? WHERE id = ?');

    return $stmt->execute([trim($nombre), $id]);
}

function eliminarTerritorio(int $id): bool
{
    asegurarTablasEstructura();
    $pdo = getConnection();
    $pdo->prepare('DELETE FROM territorio_asignaciones WHERE territorio_id = ?')->execute([$id]);
    $stmt = $pdo->prepare('DELETE FROM territorios WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerLideres(): array
{
    asegurarTablasEstructura();
    $pdo = getConnection();

    return $pdo->query(
        'SELECT * FROM lideres ORDER BY pareja ASC, nombre ASC, apellido ASC, id ASC'
    )->fetchAll();
}

function obtenerLider(int $id): ?array
{
    asegurarTablasEstructura();
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM lideres WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerMiembrosPorPareja(string $pareja): array
{
    $pareja = normalizarParejaMiembro($pareja);
    $miembros = [];

    foreach (obtenerLideres() as $miembro) {
        $tipo = (string) ($miembro['pareja'] ?? 'esposo');
        if ($tipo === $pareja) {
            $miembros[] = $miembro;
        }
    }

    return $miembros;
}

function crearLider(array $datos): int
{
    asegurarTablasEstructura();
    require_once __DIR__ . '/texto.php';
    $datos = normalizarDatosPersona($datos);
    $pareja = normalizarParejaMiembro($datos['pareja'] ?? '');

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO lideres (nombre, apellido, pareja, cedula, celular, email, notas, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        $pareja,
        trim($datos['cedula'] ?? ''),
        trim($datos['celular'] ?? ''),
        trim($datos['email'] ?? ''),
        trim($datos['notas'] ?? ''),
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarLider(int $id, array $datos): bool
{
    asegurarTablasEstructura();
    require_once __DIR__ . '/texto.php';
    $datos = normalizarDatosPersona($datos);
    $pareja = normalizarParejaMiembro($datos['pareja'] ?? 'esposo');

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE lideres SET nombre = ?, apellido = ?, pareja = ?, cedula = ?, celular = ?, email = ?, notas = ? WHERE id = ?'
    );

    return $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        $pareja,
        trim($datos['cedula'] ?? ''),
        trim($datos['celular'] ?? ''),
        trim($datos['email'] ?? ''),
        trim($datos['notas'] ?? ''),
        $id,
    ]);
}

function eliminarLider(int $id): bool
{
    asegurarTablasEstructura();
    $pdo = getConnection();
    $pdo->prepare('DELETE FROM territorio_asignaciones WHERE miembro_id = ?')->execute([$id]);
    $stmt = $pdo->prepare('DELETE FROM lideres WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerAsignacionesTerritorio(): array
{
    asegurarTablasEstructura();
    $pdo = getConnection();

    return $pdo->query(
        'SELECT a.*, l.nombre AS miembro_nombre, l.apellido AS miembro_apellido, l.cedula AS miembro_cedula,
                t.nombre AS territorio_nombre
         FROM territorio_asignaciones a
         INNER JOIN lideres l ON l.id = a.miembro_id
         INNER JOIN territorios t ON t.id = a.territorio_id
         ORDER BY t.orden ASC, t.nombre ASC, a.rol ASC, a.pareja ASC'
    )->fetchAll();
}

/**
 * @return array<int, array<string, array<string, array<string, mixed>|null>>>
 */
function obtenerAsignacionesAgrupadasPorTerritorio(): array
{
    $vacio = [
        'coordinador' => ['esposo' => null, 'esposa' => null],
        'encargado'   => ['esposo' => null, 'esposa' => null],
    ];
    $agrupadas = [];

    foreach (obtenerAsignacionesTerritorio() as $fila) {
        $territorioId = (int) $fila['territorio_id'];
        $rol = (string) $fila['rol'];
        $pareja = (string) $fila['pareja'];

        if (!isset($agrupadas[$territorioId])) {
            $agrupadas[$territorioId] = $vacio;
        }

        if (!isset($agrupadas[$territorioId][$rol])) {
            $agrupadas[$territorioId][$rol] = ['esposo' => null, 'esposa' => null];
        }

        $agrupadas[$territorioId][$rol][$pareja] = $fila;
    }

    return $agrupadas;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerTerritoriosConAsignaciones(): array
{
    $asignaciones = obtenerAsignacionesAgrupadasPorTerritorio();
    $territorios = obtenerTerritorios();

    foreach ($territorios as &$territorio) {
        $territorio['asignaciones'] = $asignaciones[(int) $territorio['id']] ?? [
            'coordinador' => ['esposo' => null, 'esposa' => null],
            'encargado'   => ['esposo' => null, 'esposa' => null],
        ];
    }
    unset($territorio);

    return $territorios;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerResumenParejasTerritorio(): array
{
    $porTerritorio = obtenerAsignacionesAgrupadasPorTerritorio();
    $territorios = [];
    foreach (obtenerTerritorios() as $territorio) {
        $territorios[(int) $territorio['id']] = $territorio;
    }

    $resumen = [];

    foreach ($porTerritorio as $territorioId => $roles) {
        $territorio = $territorios[$territorioId] ?? null;
        if ($territorio === null) {
            continue;
        }

        foreach (['coordinador', 'encargado'] as $rol) {
            $esposo = $roles[$rol]['esposo'] ?? null;
            $esposa = $roles[$rol]['esposa'] ?? null;
            if ($esposo === null && $esposa === null) {
                continue;
            }

            $clave = $rol . ':' . (int) ($esposo['miembro_id'] ?? 0) . ':' . (int) ($esposa['miembro_id'] ?? 0);
            if (!isset($resumen[$clave])) {
                $resumen[$clave] = [
                    'rol'            => $rol,
                    'esposo'         => $esposo,
                    'esposa'         => $esposa,
                    'territorios'    => [],
                    'total'          => 0,
                ];
            }

            $resumen[$clave]['territorios'][] = $territorio;
            $resumen[$clave]['total']++;
        }
    }

    usort($resumen, static function (array $a, array $b): int {
        if ($a['rol'] !== $b['rol']) {
            return $a['rol'] === 'coordinador' ? -1 : 1;
        }

        return $b['total'] <=> $a['total'];
    });

    return array_values($resumen);
}

/**
 * @return array<int, array{coordinador: int, encargado: int}>
 */
function obtenerConteoAsignacionesPorMiembro(): array
{
    $conteos = [];

    foreach (obtenerAsignacionesTerritorio() as $fila) {
        $id = (int) $fila['miembro_id'];
        if (!isset($conteos[$id])) {
            $conteos[$id] = ['coordinador' => 0, 'encargado' => 0];
        }

        $rol = (string) $fila['rol'];
        if (isset($conteos[$id][$rol])) {
            $conteos[$id][$rol]++;
        }
    }

    return $conteos;
}

function nombreParejaAsignada(?array $esposo, ?array $esposa): string
{
    $partes = [];
    if ($esposo !== null) {
        $partes[] = nombreCompletoLider([
            'nombre'   => $esposo['miembro_nombre'] ?? $esposo['nombre'] ?? '',
            'apellido' => $esposo['miembro_apellido'] ?? $esposo['apellido'] ?? '',
        ]);
    }
    if ($esposa !== null) {
        $partes[] = nombreCompletoLider([
            'nombre'   => $esposa['miembro_nombre'] ?? $esposa['nombre'] ?? '',
            'apellido' => $esposa['miembro_apellido'] ?? $esposa['apellido'] ?? '',
        ]);
    }

    return $partes === [] ? '—' : implode(' · ', $partes);
}

/**
 * @param array<int, int|string> $territorioIds
 */
function asignarParejaATerritorios(string $rol, int $esposoId, int $esposaId, array $territorioIds): int
{
    asegurarTablasEstructura();
    $rol = normalizarRolTerritorio($rol);

    if ($esposoId <= 0 || $esposaId <= 0) {
        throw new InvalidArgumentException('Selecciona esposo y esposa para la asignación.');
    }

    if ($esposoId === $esposaId) {
        throw new InvalidArgumentException('El esposo y la esposa deben ser miembros distintos.');
    }

    $esposo = obtenerLider($esposoId);
    $esposa = obtenerLider($esposaId);

    if ($esposo === null || $esposa === null) {
        throw new InvalidArgumentException('Uno de los miembros no existe.');
    }

    if (normalizarParejaMiembro($esposo['pareja'] ?? 'esposo') !== 'esposo') {
        throw new InvalidArgumentException('El miembro seleccionado como esposo está marcado como esposa.');
    }

    if (normalizarParejaMiembro($esposa['pareja'] ?? 'esposa') !== 'esposa') {
        throw new InvalidArgumentException('El miembro seleccionado como esposa está marcado como esposo.');
    }

    $ids = [];
    foreach ($territorioIds as $territorioId) {
        $id = (int) $territorioId;
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    if ($ids === []) {
        throw new InvalidArgumentException('Selecciona al menos un territorio.');
    }

    $existentes = [];
    foreach (obtenerTerritorios() as $territorio) {
        $existentes[(int) $territorio['id']] = true;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO territorio_asignaciones (territorio_id, miembro_id, rol, pareja, creado_en)
         VALUES (?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE miembro_id = VALUES(miembro_id)'
    );

    foreach ($ids as $territorioId) {
        if (!isset($existentes[$territorioId])) {
            throw new InvalidArgumentException('Uno de los territorios seleccionados no existe.');
        }

        $stmt->execute([$territorioId, $esposoId, $rol, 'esposo']);
        $stmt->execute([$territorioId, $esposaId, $rol, 'esposa']);
    }

    return count($ids);
}

function quitarAsignacionTerritorio(int $territorioId, string $rol): bool
{
    asegurarTablasEstructura();
    $rol = normalizarRolTerritorio($rol);
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM territorio_asignaciones WHERE territorio_id = ? AND rol = ?');

    return $stmt->execute([$territorioId, $rol]) && $stmt->rowCount() > 0;
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerCasasVida(): array
{
    $pdo = getConnection();

    return $pdo->query(
        'SELECT c.*, t.nombre AS territorio_nombre,
                l.nombre AS lider_nombre, l.apellido AS lider_apellido
         FROM casas_vida c
         INNER JOIN territorios t ON t.id = c.territorio_id
         INNER JOIN lideres l ON l.id = c.lider_id
         ORDER BY c.creado_en DESC, c.id DESC'
    )->fetchAll();
}

function obtenerCasaVida(int $id): ?array
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'SELECT c.*, t.nombre AS territorio_nombre,
                l.nombre AS lider_nombre, l.apellido AS lider_apellido
         FROM casas_vida c
         INNER JOIN territorios t ON t.id = c.territorio_id
         INNER JOIN lideres l ON l.id = c.lider_id
         WHERE c.id = ?'
    );
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

function crearCasaVida(array $datos): int
{
    require_once __DIR__ . '/texto.php';
    $datos = normalizarCamposTextoOrdenado($datos, ['nombre', 'direccion']);

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO casas_vida (territorio_id, lider_id, nombre, direccion, creado_en)
         VALUES (?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        (int) $datos['territorio_id'],
        (int) $datos['lider_id'],
        trim($datos['nombre']),
        trim($datos['direccion']),
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarCasaVida(int $id, array $datos): bool
{
    require_once __DIR__ . '/texto.php';
    $datos = normalizarCamposTextoOrdenado($datos, ['nombre', 'direccion']);

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE casas_vida SET territorio_id = ?, lider_id = ?, nombre = ?, direccion = ? WHERE id = ?'
    );

    return $stmt->execute([
        (int) $datos['territorio_id'],
        (int) $datos['lider_id'],
        trim($datos['nombre']),
        trim($datos['direccion']),
        $id,
    ]);
}

function eliminarCasaVida(int $id): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM casas_vida WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

/**
 * Datos para API del formulario de ofrendas en WordPress.
 */
function obtenerEstructuraParaApi(): array
{
    $territorios = obtenerTerritorios();
    $casas = obtenerCasasVida();

    $listaTerritorios = [];
    foreach ($territorios as $territorio) {
        $listaTerritorios[] = [
            'id'     => (int) $territorio['id'],
            'nombre' => $territorio['nombre'],
        ];
    }

    $listaCasas = [];
    foreach ($casas as $casa) {
        $listaCasas[] = [
            'id'            => (int) $casa['id'],
            'territorio_id' => (int) $casa['territorio_id'],
            'nombre'        => $casa['nombre'],
            'lider'         => trim($casa['lider_nombre'] . ' ' . $casa['lider_apellido']),
            'direccion'     => $casa['direccion'],
        ];
    }

    usort($listaTerritorios, static function (array $a, array $b): int {
        return strcasecmp($a['nombre'], $b['nombre']);
    });

    usort($listaCasas, static function (array $a, array $b): int {
        return strcasecmp($a['nombre'], $b['nombre']);
    });

    return [
        'territorios' => $listaTerritorios,
        'casas'       => $listaCasas,
    ];
}

function insertarOfrendaDesdeApi(array $datos): int
{
    $pdo = getConnection();
    $casa = obtenerCasaVida((int) $datos['casa_id']);

    if (!$casa) {
        throw new InvalidArgumentException('Casa de vida no encontrada.');
    }

    $lider = nombreCompletoLider([
        'nombre' => $casa['lider_nombre'],
        'apellido' => $casa['lider_apellido'],
    ]);

    $stmt = $pdo->prepare(
        'INSERT INTO ofrendas (casa_id, casa_vida, lider, fecha_ofrenda, monto, registrado_por_id, registrado_por_nombre, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        (int) $casa['id'],
        $casa['nombre'],
        $lider,
        $datos['fecha_ofrenda'],
        (float) $datos['monto'],
        isset($datos['registrado_por_id']) ? (int) $datos['registrado_por_id'] : null,
        trim($datos['registrado_por_nombre'] ?? ''),
    ]);

    return (int) $pdo->lastInsertId();
}
