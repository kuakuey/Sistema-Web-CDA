<?php

require_once __DIR__ . '/../config/database.php';

const SECCION_PERMISOS_SUPERADMIN = 'superadmin';
const SECCION_PERMISOS_ADMIN = 'administrador';
const SECCION_PERMISOS_COUNTER = 'counter';
const SECCION_PERMISOS_CONEXION = 'conexion';

/**
 * Catálogo de permisos detallados por módulo (pestañas / acciones).
 *
 * @return array<string, array{etiqueta: string, icono: string, permisos: array<string, string>}>
 */
function obtenerCatalogoPermisosDetallados(): array
{
    return [
        'generales' => [
            'etiqueta'  => 'Registros generales',
            'icono'     => 'bi-grid',
            'permisos'  => [
                'ver' => 'Ver registros del día',
            ],
        ],
        'escol' => [
            'etiqueta'  => 'Escol',
            'icono'     => 'bi-book',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nuevo registro',
            ],
        ],
        'academia' => [
            'etiqueta'  => 'Academia',
            'icono'     => 'bi-mortarboard',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nuevo registro',
            ],
        ],
        'bautismo' => [
            'etiqueta'  => 'Bautismo',
            'icono'     => 'bi-droplet',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nuevo registro',
            ],
        ],
        'conexion' => [
            'etiqueta'  => "Conexi\u{00F3}n",
            'icono'     => 'bi-person-plus',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nuevo registro',
            ],
        ],
        'presentaciones' => [
            'etiqueta'  => "Presentaci\u{00F3}n ni\u{00F1}os",
            'icono'     => 'bi-people',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nueva presentación',
            ],
        ],
        'ofrendas' => [
            'etiqueta'  => 'Ofrendas',
            'icono'     => 'bi-cash-stack',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nueva ofrenda',
            ],
        ],
        'eventos' => [
            'etiqueta'  => 'Eventos',
            'icono'     => 'bi-calendar-event',
            'permisos'  => [
                'tabla'     => 'Tabla de eventos',
                'registrar' => 'Registro de eventos',
                'agregar'   => 'Agregar eventos',
                'catalogo'  => 'Eventos registrados',
                'informe'   => 'Informe de evento',
            ],
        ],
        'valores_adicionales' => [
            'etiqueta'  => 'Valores adicionales',
            'icono'     => 'bi-wallet2',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nuevo valor',
                'tipos'     => 'Tipos de valores',
            ],
        ],
        'consejeria' => [
            'etiqueta'  => "Consejer\u{00ED}a",
            'icono'     => 'bi-chat-heart',
            'permisos'  => [
                'registros' => 'Registros',
                'nuevo'     => 'Nueva solicitud',
            ],
        ],
        'generar_informe' => [
            'etiqueta'  => 'Generar informe',
            'icono'     => 'bi-file-earmark-bar-graph',
            'permisos'  => [
                'acceso' => 'Acceso al informe',
            ],
        ],
        'estructura' => [
            'etiqueta'  => 'Estructura CDV',
            'icono'     => 'bi-diagram-3',
            'permisos'  => [
                'acceso' => 'Gestionar estructura',
            ],
        ],
    ];
}

function codificarPermisoDetalle(string $modulo, string $detalle): string
{
    return $modulo . ':' . $detalle;
}

/**
 * @return array<int, string>
 */
function obtenerClavesPermisosConfigurables(): array
{
    $claves = [];

    foreach (obtenerCatalogoPermisosDetallados() as $modulo => $info) {
        foreach ($info['permisos'] as $detalle => $_etiqueta) {
            $claves[] = codificarPermisoDetalle($modulo, $detalle);
        }
    }

    return $claves;
}

/**
 * @return array<int, string>
 */
function permisosDetalladosPorModulo(string $modulo, ?string $rol = null): array
{
    $catalogo = obtenerCatalogoPermisosDetallados();

    if (!isset($catalogo[$modulo])) {
        return [$modulo];
    }

    $permisos = [];

    foreach ($catalogo[$modulo]['permisos'] as $detalle => $_etiqueta) {
        if ($modulo === 'eventos' && $detalle === 'agregar' && $rol !== SECCION_PERMISOS_SUPERADMIN) {
            continue;
        }

        if ($modulo === 'valores_adicionales' && $detalle === 'tipos' && $rol !== SECCION_PERMISOS_SUPERADMIN) {
            continue;
        }

        $permisos[] = codificarPermisoDetalle($modulo, $detalle);
    }

    return $permisos;
}

/**
 * @param array<int, string> $modulos
 * @return array<int, string>
 */
function expandirModulosAPermisosDetallados(array $modulos, ?string $rol = null): array
{
    $permisos = [];

    foreach ($modulos as $modulo) {
        $permisos = array_merge($permisos, permisosDetalladosPorModulo($modulo, $rol));
    }

    return array_values(array_unique($permisos));
}

/**
 * @param array<int, string> $permisos
 * @return array<int, string>
 */
function normalizarPermisosParaUi(array $permisos): array
{
    $activos = [];

    foreach (obtenerCatalogoPermisosDetallados() as $modulo => $info) {
        $moduloCompleto = in_array($modulo, $permisos, true);

        foreach ($info['permisos'] as $detalle => $_etiqueta) {
            $clave = codificarPermisoDetalle($modulo, $detalle);

            if ($moduloCompleto || in_array($clave, $permisos, true)) {
                $activos[] = $clave;
            }
        }
    }

    return array_values(array_unique($activos));
}

/**
 * Secciones que se pueden asignar a un rol desde el panel.
 *
 * @return array<string, string>
 */
function obtenerSeccionesConfigurablesPermisos(): array
{
    return [
        'generales'           => 'Registros generales',
        'escol'               => 'Escol',
        'academia'            => 'Academia',
        'bautismo'            => 'Bautismo',
        'conexion'            => "Conexi\u{00F3}n",
        'presentaciones'      => "Presentaci\u{00F3}n ni\u{00F1}os",
        'ofrendas'            => 'Ofrendas',
        'valores_adicionales' => 'Valores adicionales',
        'eventos'             => 'Eventos',
        'consejeria'          => "Consejer\u{00ED}a",
        'generar_informe'     => 'Generar informe',
        'estructura'          => 'Estructura CDV',
        'usuarios'            => 'Usuarios',
    ];
}

/**
 * @return array<string, string>
 */
function obtenerRolesConfigurablesPermisos(): array
{
    return [
        SECCION_PERMISOS_ADMIN    => 'Administrador',
        SECCION_PERMISOS_COUNTER  => 'Counter',
        SECCION_PERMISOS_CONEXION => "Conexi\u{00F3}n",
    ];
}

/**
 * @return array<int, string>
 */
function obtenerPermisosPorDefectoRol(string $rol): array
{
    switch ($rol) {
        case SECCION_PERMISOS_ADMIN:
            return expandirModulosAPermisosDetallados([
                'generales',
                'escol',
                'academia',
                'bautismo',
                'conexion',
                'presentaciones',
                'ofrendas',
                'valores_adicionales',
                'eventos',
                'consejeria',
                'generar_informe',
                'estructura',
            ], $rol);

        case SECCION_PERMISOS_COUNTER:
            return expandirModulosAPermisosDetallados([
                'generales',
                'escol',
                'academia',
                'bautismo',
                'presentaciones',
                'ofrendas',
                'valores_adicionales',
                'consejeria',
                'generar_informe',
            ], $rol);

        case SECCION_PERMISOS_CONEXION:
            return expandirModulosAPermisosDetallados([
                'generales',
                'conexion',
            ], $rol);

        default:
            return [];
    }
}

function rolTienePermisosPersonalizados(string $rol): bool
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM rol_permisos WHERE rol = ?');
    $stmt->execute([$rol]);

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * @return array<int, string>
 */
function cargarPermisosRolDesdeBd(string $rol): array
{
    asegurarTablaPermisos();

    if (!rolTienePermisosPersonalizados($rol)) {
        return obtenerPermisosPorDefectoRol($rol);
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT seccion FROM rol_permisos WHERE rol = ? ORDER BY seccion ASC');
    $stmt->execute([$rol]);

    return array_column($stmt->fetchAll(), 'seccion');
}

/**
 * @return array<string, array<int, string>>
 */
function cargarMatrizPermisosRoles(): array
{
    $matriz = [];

    foreach (array_keys(obtenerRolesConfigurablesPermisos()) as $rol) {
        $matriz[$rol] = cargarPermisosRolDesdeBd($rol);
    }

    return $matriz;
}

/**
 * @param array<int, string> $permisos
 */
function guardarPermisosRol(string $rol, array $permisos): void
{
    asegurarTablaPermisos();

    if (!array_key_exists($rol, obtenerRolesConfigurablesPermisos())) {
        throw new InvalidArgumentException('Rol no configurable.');
    }

    $permitidas = obtenerClavesPermisosConfigurables();
    $permisos = array_values(array_unique(array_filter(
        $permisos,
        static fn(string $permiso): bool => in_array($permiso, $permitidas, true)
    )));

    $pdo = getConnection();
    $pdo->beginTransaction();

    try {
        $eliminar = $pdo->prepare('DELETE FROM rol_permisos WHERE rol = ?');
        $eliminar->execute([$rol]);

        if ($permisos !== []) {
            $insertar = $pdo->prepare('INSERT INTO rol_permisos (rol, seccion) VALUES (?, ?)');

            foreach ($permisos as $permiso) {
                $insertar->execute([$rol, $permiso]);
            }
        }

        $pdo->commit();
        limpiarCachePermisos();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function limpiarCachePermisos(): void
{
    $GLOBALS['cdaPermisosCache'] = [];
}

/**
 * @return array<int, string>
 */
function obtenerPermisosRol(string $rol): array
{
    if ($rol === SECCION_PERMISOS_SUPERADMIN) {
        return obtenerClavesPermisosConfigurables();
    }

    if (!isset($GLOBALS['cdaPermisosCache'])) {
        $GLOBALS['cdaPermisosCache'] = [];
    }

    if (!isset($GLOBALS['cdaPermisosCache'][$rol])) {
        $GLOBALS['cdaPermisosCache'][$rol] = cargarPermisosRolDesdeBd($rol);
    }

    return $GLOBALS['cdaPermisosCache'][$rol];
}

function tienePermisoSeccion(string $rol, string $seccion): bool
{
    if ($rol === SECCION_PERMISOS_SUPERADMIN) {
        return true;
    }

    $permisos = obtenerPermisosRol($rol);

    if (in_array($seccion, $permisos, true)) {
        return true;
    }

    $prefijo = $seccion . ':';

    foreach ($permisos as $permiso) {
        if (strpos($permiso, $prefijo) === 0) {
            return true;
        }
    }

    return false;
}

function tienePermisoDetalle(string $rol, string $modulo, string $detalle): bool
{
    if ($rol === SECCION_PERMISOS_SUPERADMIN) {
        return true;
    }

    $permisos = obtenerPermisosRol($rol);
    $clave = codificarPermisoDetalle($modulo, $detalle);

    if (in_array($clave, $permisos, true)) {
        return true;
    }

    return in_array($modulo, $permisos, true);
}

function sembrarPermisosPorDefecto(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'rol_permisos')) {
        return;
    }

    $total = (int) $pdo->query('SELECT COUNT(*) FROM rol_permisos')->fetchColumn();

    if ($total > 0) {
        return;
    }

    foreach (obtenerRolesConfigurablesPermisos() as $rol => $_etiqueta) {
        $insertar = $pdo->prepare('INSERT INTO rol_permisos (rol, seccion) VALUES (?, ?)');

        foreach (obtenerPermisosPorDefectoRol($rol) as $seccion) {
            $insertar->execute([$rol, $seccion]);
        }
    }
}

function asegurarTablaPermisos(): void
{
    static $listo = false;

    if ($listo) {
        return;
    }

    require_once __DIR__ . '/../config/database.php';

    $pdo = getConnection();
    migrarTablaRolPermisos($pdo);
    $listo = true;
}
