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

/**
 * @return array<string, string>
 */
function opcionesGeneroMiembro(): array
{
    return [
        'masculino' => 'Masculino',
        'femenino'  => 'Femenino',
    ];
}

function etiquetaGeneroMiembro(?string $genero): string
{
    $genero = trim((string) $genero);

    return opcionesGeneroMiembro()[$genero] ?? ($genero !== '' ? $genero : '—');
}

function normalizarGeneroMiembro(?string $genero): string
{
    $clave = function_exists('mb_strtolower')
        ? mb_strtolower(trim((string) $genero), 'UTF-8')
        : strtolower(trim((string) $genero));
    $clave = strtr($clave, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
    ]);

    $mapa = [
        'masculino' => 'masculino',
        'hombre'    => 'masculino',
        'varon'     => 'masculino',
        'm'         => 'masculino',
        'femenino'  => 'femenino',
        'mujer'     => 'femenino',
        'f'         => 'femenino',
    ];

    if (!isset($mapa[$clave])) {
        throw new InvalidArgumentException('Selecciona el género del miembro.');
    }

    return $mapa[$clave];
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerMiembrosPorGenero(string $genero): array
{
    $genero = normalizarGeneroMiembro($genero);
    $miembros = [];

    foreach (obtenerLideres() as $miembro) {
        if ((string) ($miembro['genero'] ?? '') === $genero) {
            $miembros[] = $miembro;
        }
    }

    return $miembros;
}

function etiquetaRolTerritorio(string $rol): string
{
    return $rol === 'encargado' ? 'Encargado' : 'Coordinador';
}

function parejaDesdeGenero(?string $genero): string
{
    try {
        return normalizarGeneroMiembro($genero) === 'femenino' ? 'esposa' : 'esposo';
    } catch (InvalidArgumentException $e) {
        return 'esposo';
    }
}

/**
 * @param array<int, mixed> $ids
 * @return array<int, int>
 */
function normalizarIdsEnteros(array $ids): array
{
    $normalizados = [];

    foreach ($ids as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $normalizados[$id] = $id;
        }
    }

    return $normalizados;
}

/**
 * @param array<int, array<string, mixed>> $miembros
 * @return array<int, int>
 */
function idsMiembrosAsignados(array $miembros): array
{
    $ids = [];

    foreach ($miembros as $miembro) {
        if (!is_array($miembro)) {
            continue;
        }

        $id = (int) ($miembro['miembro_id'] ?? $miembro['id'] ?? 0);
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return $ids;
}

/**
 * @param array<int, array<string, mixed>> $miembros
 */
function nombresMiembrosAsignados(array $miembros): string
{
    $nombres = [];

    foreach ($miembros as $miembro) {
        if (!is_array($miembro)) {
            continue;
        }

        $nombre = nombreCompletoLider([
            'nombre'   => $miembro['miembro_nombre'] ?? $miembro['nombre'] ?? '',
            'apellido' => $miembro['miembro_apellido'] ?? $miembro['apellido'] ?? '',
        ]);

        if ($nombre !== '') {
            $nombres[] = $nombre;
        }
    }

    return $nombres === [] ? '—' : implode(', ', $nombres);
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
        'SELECT * FROM lideres ORDER BY nombre ASC, apellido ASC, id ASC'
    )->fetchAll();
}

function urlFichaMiembro(int $id): string
{
    return 'estructura.php?pestaña=lideres&miembro=' . $id;
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

    $genero = normalizarGeneroMiembro($datos['genero'] ?? '');

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO lideres (nombre, apellido, genero, cedula, celular, email, notas, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        $genero,
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

    $genero = normalizarGeneroMiembro($datos['genero'] ?? ($datos['genero_actual'] ?? ''));

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE lideres SET nombre = ?, apellido = ?, genero = ?, cedula = ?, celular = ?, email = ?, notas = ? WHERE id = ?'
    );

    return $stmt->execute([
        trim($datos['nombre']),
        trim($datos['apellido']),
        $genero,
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
    $pdo->prepare('DELETE FROM miembro_parentescos WHERE miembro_id = ? OR pariente_id = ?')->execute([$id, $id]);
    $stmt = $pdo->prepare('DELETE FROM lideres WHERE id = ?');

    return $stmt->execute([$id]) && $stmt->rowCount() > 0;
}

function eliminarTodosLideres(): int
{
    asegurarTablasEstructura();
    $pdo = getConnection();
    $total = (int) $pdo->query('SELECT COUNT(*) FROM lideres')->fetchColumn();

    if ($total === 0) {
        return 0;
    }

    $pdo->beginTransaction();

    try {
        $pdo->exec('DELETE FROM territorio_asignaciones');
        $pdo->exec('DELETE FROM miembro_parentescos');
        $pdo->exec('DELETE FROM casas_vida');
        $pdo->exec('DELETE FROM lideres');
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return $total;
}

/**
 * @return array<string, string>
 */
function opcionesParentescoMiembro(): array
{
    return [
        'esposo' => 'Esposo',
        'esposa' => 'Esposa',
    ];
}

function etiquetaParentescoMiembro(string $parentesco): string
{
    return opcionesParentescoMiembro()[$parentesco] ?? $parentesco;
}

function normalizarParentescoMiembro(?string $parentesco): string
{
    $clave = function_exists('mb_strtolower')
        ? mb_strtolower(trim((string) $parentesco), 'UTF-8')
        : strtolower(trim((string) $parentesco));

    if (!isset(opcionesParentescoMiembro()[$clave])) {
        throw new InvalidArgumentException('Selecciona un parentesco válido.');
    }

    return $clave;
}

function parentescoInversoMiembro(string $parentesco): string
{
    return $parentesco === 'esposo' ? 'esposa' : 'esposo';
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerParentescosMiembros(): array
{
    asegurarTablasEstructura();
    $pdo = getConnection();

    return $pdo->query(
        'SELECT p.*,
                m.nombre AS miembro_nombre, m.apellido AS miembro_apellido,
                r.nombre AS pariente_nombre, r.apellido AS pariente_apellido
         FROM miembro_parentescos p
         INNER JOIN lideres m ON m.id = p.miembro_id
         INNER JOIN lideres r ON r.id = p.pariente_id
         ORDER BY m.nombre ASC, m.apellido ASC, p.id ASC'
    )->fetchAll();
}

/**
 * @return array<int, array<string, mixed>>
 */
function obtenerParentescoPorMiembro(): array
{
    $porMiembro = [];

    foreach (obtenerParentescosMiembros() as $fila) {
        $porMiembro[(int) $fila['miembro_id']] = $fila;
    }

    return $porMiembro;
}

/**
 * Parejas esposo/esposa ya conectadas por parentesco.
 *
 * @return array<int, array{esposo: array<string, mixed>, esposa: array<string, mixed>}>
 */
function obtenerParejasParentesco(): array
{
    $parejas = [];
    $vistos = [];

    foreach (obtenerParentescosMiembros() as $fila) {
        if ((string) $fila['parentesco'] !== 'esposo') {
            continue;
        }

        $esposoId = (int) $fila['miembro_id'];
        $esposaId = (int) $fila['pariente_id'];
        $clave = min($esposoId, $esposaId) . '-' . max($esposoId, $esposaId);
        if (isset($vistos[$clave])) {
            continue;
        }

        $vistos[$clave] = true;
        $parejas[] = [
            'clave'  => $esposoId . ':' . $esposaId,
            'esposo' => [
                'id'       => $esposoId,
                'nombre'   => $fila['miembro_nombre'],
                'apellido' => $fila['miembro_apellido'],
            ],
            'esposa' => [
                'id'       => $esposaId,
                'nombre'   => $fila['pariente_nombre'],
                'apellido' => $fila['pariente_apellido'],
            ],
        ];
    }

    return $parejas;
}

function asegurarParentescoEsposos(int $esposoId, int $esposaId): void
{
    foreach (obtenerParejasParentesco() as $pareja) {
        if ((int) $pareja['esposo']['id'] === $esposoId && (int) $pareja['esposa']['id'] === $esposaId) {
            return;
        }
    }

    conectarParentescoMiembros($esposoId, $esposaId, 'esposo');
}

function conectarParentescoMiembros(int $miembroId, int $parienteId, string $parentesco): int
{
    asegurarTablasEstructura();
    $parentesco = normalizarParentescoMiembro($parentesco);

    if ($miembroId <= 0 || $parienteId <= 0) {
        throw new InvalidArgumentException('Selecciona los dos miembros a conectar.');
    }

    if ($miembroId === $parienteId) {
        throw new InvalidArgumentException('El parentesco debe ser entre dos miembros distintos.');
    }

    $miembro = obtenerLider($miembroId);
    $pariente = obtenerLider($parienteId);

    if ($miembro === null || $pariente === null) {
        throw new InvalidArgumentException('Uno de los miembros no existe.');
    }

    $generoMiembro = (string) ($miembro['genero'] ?? '');
    $generoPariente = (string) ($pariente['genero'] ?? '');

    if ($parentesco === 'esposo' && $generoMiembro !== '' && $generoMiembro !== 'masculino') {
        throw new InvalidArgumentException('El esposo debe ser un miembro de género masculino.');
    }

    if ($parentesco === 'esposa' && $generoMiembro !== '' && $generoMiembro !== 'femenino') {
        throw new InvalidArgumentException('La esposa debe ser un miembro de género femenino.');
    }

    if ($parentesco === 'esposo' && $generoPariente !== '' && $generoPariente !== 'femenino') {
        throw new InvalidArgumentException('La esposa debe ser un miembro de género femenino.');
    }

    if ($parentesco === 'esposa' && $generoPariente !== '' && $generoPariente !== 'masculino') {
        throw new InvalidArgumentException('El esposo debe ser un miembro de género masculino.');
    }

    $pdo = getConnection();
    $existe = $pdo->prepare(
        'SELECT id FROM miembro_parentescos
         WHERE miembro_id IN (?, ?) OR pariente_id IN (?, ?)
         LIMIT 1'
    );
    $existe->execute([$miembroId, $parienteId, $miembroId, $parienteId]);
    if ($existe->fetch()) {
        throw new InvalidArgumentException('Uno de los miembros ya tiene un parentesco. Quítalo antes de crear otro.');
    }

    $inverso = parentescoInversoMiembro($parentesco);
    $stmt = $pdo->prepare(
        'INSERT INTO miembro_parentescos (miembro_id, pariente_id, parentesco, creado_en)
         VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$miembroId, $parienteId, $parentesco]);
    $id = (int) $pdo->lastInsertId();
    $stmt->execute([$parienteId, $miembroId, $inverso]);

    return $id;
}

function eliminarParentescoMiembro(int $miembroId, int $parienteId): bool
{
    asegurarTablasEstructura();
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'DELETE FROM miembro_parentescos
         WHERE (miembro_id = ? AND pariente_id = ?)
            OR (miembro_id = ? AND pariente_id = ?)'
    );

    return $stmt->execute([$miembroId, $parienteId, $parienteId, $miembroId]) && $stmt->rowCount() > 0;
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
 * @return array<int, array{coordinador: array<int, array<string, mixed>>, encargado: array<int, array<string, mixed>>}>
 */
function obtenerAsignacionesAgrupadasPorTerritorio(): array
{
    $agrupadas = [];

    foreach (obtenerAsignacionesTerritorio() as $fila) {
        $territorioId = (int) $fila['territorio_id'];
        $rol = (string) $fila['rol'];

        if (!isset($agrupadas[$territorioId])) {
            $agrupadas[$territorioId] = [
                'coordinador' => [],
                'encargado'   => [],
            ];
        }

        if (!isset($agrupadas[$territorioId][$rol])) {
            $agrupadas[$territorioId][$rol] = [];
        }

        $agrupadas[$territorioId][$rol][] = $fila;
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
            'coordinador' => [],
            'encargado'   => [],
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
            $lista = array_values($roles[$rol] ?? []);
            if ($lista === []) {
                continue;
            }

            $ids = idsMiembrosAsignados($lista);
            sort($ids);
            $clave = $rol . ':' . implode('-', $ids);
            if (!isset($resumen[$clave])) {
                $resumen[$clave] = [
                    'rol'         => $rol,
                    'miembros'    => $lista,
                    'territorios' => [],
                    'total'       => 0,
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

    $parejaValida = false;
    foreach (obtenerParejasParentesco() as $pareja) {
        if ((int) $pareja['esposo']['id'] === $esposoId && (int) $pareja['esposa']['id'] === $esposaId) {
            $parejaValida = true;
            break;
        }
    }

    if (!$parejaValida) {
        throw new InvalidArgumentException('Primero conecta el parentesco (esposo y esposa) entre esos miembros.');
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
         ON DUPLICATE KEY UPDATE pareja = VALUES(pareja)'
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
 * @param array<int, int|string> $coordinadorIds
 * @param array<int, int|string> $encargadoIds
 */
function guardarTerritorioConAsignaciones(int $id, string $nombre, array $coordinadorIds, array $encargadoIds): void
{
    asegurarTablasEstructura();

    if ($id <= 0) {
        throw new InvalidArgumentException('Territorio no válido.');
    }

    if (trim($nombre) === '') {
        throw new InvalidArgumentException('El nombre del territorio es obligatorio.');
    }

    $existe = false;
    foreach (obtenerTerritorios() as $territorio) {
        if ((int) $territorio['id'] === $id) {
            $existe = true;
            break;
        }
    }

    if (!$existe) {
        throw new InvalidArgumentException('El territorio no existe.');
    }

    $coordinadores = normalizarIdsEnteros($coordinadorIds);
    $encargados = normalizarIdsEnteros($encargadoIds);

    foreach ($encargados as $miembroId => $_) {
        if (isset($coordinadores[$miembroId])) {
            unset($encargados[$miembroId]);
        }
    }

    actualizarTerritorio($id, $nombre);

    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        $pdo->prepare('DELETE FROM territorio_asignaciones WHERE territorio_id = ?')->execute([$id]);
        $stmt = $pdo->prepare(
            'INSERT INTO territorio_asignaciones (territorio_id, miembro_id, rol, pareja, creado_en)
             VALUES (?, ?, ?, ?, NOW())'
        );

        foreach (['coordinador' => $coordinadores, 'encargado' => $encargados] as $rol => $ids) {
            foreach ($ids as $miembroId) {
                $miembro = obtenerLider($miembroId);
                if ($miembro === null) {
                    throw new InvalidArgumentException('Uno de los miembros seleccionados no existe.');
                }

                $stmt->execute([$id, $miembroId, $rol, parejaDesdeGenero($miembro['genero'] ?? '')]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * @return array<int, array<string, mixed>>
 */
function sqlSelectCasasVida(): string
{
    return 'SELECT c.*, t.nombre AS territorio_nombre,
                   l.nombre AS lider_nombre, l.apellido AS lider_apellido,
                   col.nombre AS colaborador_nombre, col.apellido AS colaborador_apellido,
                   anf.nombre AS anfitrion_nombre, anf.apellido AS anfitrion_apellido
            FROM casas_vida c
            INNER JOIN territorios t ON t.id = c.territorio_id
            INNER JOIN lideres l ON l.id = c.lider_id
            LEFT JOIN lideres col ON col.id = c.colaborador_id
            LEFT JOIN lideres anf ON anf.id = c.anfitrion_id';
}

function nombreVisibleCasaVida(array $casa): string
{
    $nombre = trim((string) ($casa['nombre'] ?? ''));
    if ($nombre !== '') {
        return $nombre;
    }

    $anfitrion = trim((string) ($casa['anfitrion_nombre'] ?? '') . ' ' . (string) ($casa['anfitrion_apellido'] ?? ''));
    if ($anfitrion !== '') {
        return $anfitrion;
    }

    $direccion = trim((string) ($casa['direccion'] ?? ''));

    return $direccion !== '' ? $direccion : 'Casa de vida';
}

/**
 * @return array{territorio_id: int, lider_id: int, colaborador_id: int, anfitrion_id: int, nombre: string, direccion: string}
 */
function normalizarDatosCasaVida(array $datos): array
{
    require_once __DIR__ . '/texto.php';
    $datos = normalizarCamposTextoOrdenado($datos, ['nombre', 'direccion']);

    $territorioId = (int) ($datos['territorio_id'] ?? 0);
    $liderId = (int) ($datos['lider_id'] ?? 0);
    $colaboradorId = (int) ($datos['colaborador_id'] ?? 0);
    $anfitrionId = (int) ($datos['anfitrion_id'] ?? 0);
    $direccion = trim((string) ($datos['direccion'] ?? ''));

    if ($territorioId <= 0) {
        throw new InvalidArgumentException('Selecciona el territorio de la casa de vida.');
    }
    $nombre = trim((string) ($datos['nombre'] ?? ''));
    if ($nombre === '') {
        throw new InvalidArgumentException('El nombre de la casa de vida es obligatorio.');
    }
    if ($direccion === '') {
        throw new InvalidArgumentException('La dirección de la casa de vida es obligatoria.');
    }
    if ($liderId <= 0 || $colaboradorId <= 0 || $anfitrionId <= 0) {
        throw new InvalidArgumentException('La casa de vida necesita líder, colaborador y anfitrión.');
    }

    $roles = [
        'líder'       => $liderId,
        'colaborador' => $colaboradorId,
        'anfitrión'   => $anfitrionId,
    ];
    $vistos = [];
    foreach ($roles as $etiqueta => $miembroId) {
        if (isset($vistos[$miembroId])) {
            throw new InvalidArgumentException('El líder, el colaborador y el anfitrión deben ser miembros distintos.');
        }
        $vistos[$miembroId] = $etiqueta;

        if (obtenerLider($miembroId) === null) {
            throw new InvalidArgumentException('El ' . $etiqueta . ' seleccionado no existe.');
        }
    }

    return [
        'territorio_id'   => $territorioId,
        'lider_id'        => $liderId,
        'colaborador_id'  => $colaboradorId,
        'anfitrion_id'    => $anfitrionId,
        'nombre'          => $nombre,
        'direccion'       => $direccion,
    ];
}

function obtenerCasasVida(): array
{
    $pdo = getConnection();

    return $pdo->query(
        sqlSelectCasasVida() . ' ORDER BY c.creado_en DESC, c.id DESC'
    )->fetchAll();
}

function obtenerCasaVida(int $id): ?array
{
    $pdo = getConnection();
    $stmt = $pdo->prepare(sqlSelectCasasVida() . ' WHERE c.id = ?');
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

function crearCasaVida(array $datos): int
{
    $datos = normalizarDatosCasaVida($datos);

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO casas_vida (territorio_id, lider_id, colaborador_id, anfitrion_id, nombre, direccion, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $datos['territorio_id'],
        $datos['lider_id'],
        $datos['colaborador_id'],
        $datos['anfitrion_id'],
        $datos['nombre'],
        $datos['direccion'],
    ]);

    return (int) $pdo->lastInsertId();
}

function actualizarCasaVida(int $id, array $datos): bool
{
    $datos = normalizarDatosCasaVida($datos);

    $pdo = getConnection();
    $stmt = $pdo->prepare(
        'UPDATE casas_vida
         SET territorio_id = ?, lider_id = ?, colaborador_id = ?, anfitrion_id = ?, nombre = ?, direccion = ?
         WHERE id = ?'
    );

    return $stmt->execute([
        $datos['territorio_id'],
        $datos['lider_id'],
        $datos['colaborador_id'],
        $datos['anfitrion_id'],
        $datos['nombre'],
        $datos['direccion'],
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
            'nombre'        => nombreVisibleCasaVida($casa),
            'lider'         => trim($casa['lider_nombre'] . ' ' . $casa['lider_apellido']),
            'colaborador'   => trim(($casa['colaborador_nombre'] ?? '') . ' ' . ($casa['colaborador_apellido'] ?? '')),
            'anfitrion'     => trim(($casa['anfitrion_nombre'] ?? '') . ' ' . ($casa['anfitrion_apellido'] ?? '')),
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
