<?php

require_once __DIR__ . '/esquema.php';

/**
 * @return array<int, string>
 */
function obtenerTablasEsperadasSistema(): array
{
    return [
        'usuarios',
        'inscripciones',
        'presentaciones_ninos',
        'ofrendas',
        'territorios',
        'lideres',
        'territorio_asignaciones',
        'casas_vida',
        'sesiones_api',
        'eventos',
        'eventos_tipos_entrada',
        'eventos_campos_adicionales',
        'valores_adicionales',
        'tipos_valor_adicional',
        'consejerias',
        'calendario_eventos',
        'transporte_aniversario',
        'rol_permisos',
        'actividad_log',
    ];
}

/**
 * @return array<int, array{archivo: string, etiqueta: string, tabla: ?string}>
 */
function obtenerCatalogoMigracionesBd(): array
{
    return [
        ['archivo' => '001_base_datos.sql', 'etiqueta' => 'Base de datos', 'tabla' => null],
        ['archivo' => '002_usuarios.sql', 'etiqueta' => 'Tabla usuarios', 'tabla' => 'usuarios'],
        ['archivo' => '003_inscripciones.sql', 'etiqueta' => 'Tabla inscripciones', 'tabla' => 'inscripciones'],
        ['archivo' => '004_presentaciones_ninos.sql', 'etiqueta' => 'Tabla presentaciones niños', 'tabla' => 'presentaciones_ninos'],
        ['archivo' => '005_ofrendas.sql', 'etiqueta' => 'Tabla ofrendas', 'tabla' => 'ofrendas'],
        ['archivo' => '006_estructura.sql', 'etiqueta' => 'Territorios, líderes y casas de vida', 'tabla' => 'territorios'],
        ['archivo' => '007_sesiones_api.sql', 'etiqueta' => 'Tabla sesiones API', 'tabla' => 'sesiones_api'],
        ['archivo' => '008_eventos.sql', 'etiqueta' => 'Tablas de eventos', 'tabla' => 'eventos'],
        ['archivo' => '016_eventos_tipos_entrada.sql', 'etiqueta' => 'Tipos de entrada eventos', 'tabla' => 'eventos_tipos_entrada'],
        ['archivo' => '018_eventos_campos_adicionales.sql', 'etiqueta' => 'Campos adicionales de eventos', 'tabla' => 'eventos_campos_adicionales'],
        ['archivo' => '009_valores_adicionales.sql', 'etiqueta' => 'Valores adicionales', 'tabla' => 'valores_adicionales'],
        ['archivo' => '010_tipos_valor_adicional.sql', 'etiqueta' => 'Tipos valor adicional', 'tabla' => 'tipos_valor_adicional'],
        ['archivo' => '011_consejerias.sql', 'etiqueta' => 'Tabla consejerías', 'tabla' => 'consejerias'],
        ['archivo' => '017_calendario_eventos.sql', 'etiqueta' => 'Calendario de eventos', 'tabla' => 'calendario_eventos'],
        ['archivo' => '012_transporte_aniversario.sql', 'etiqueta' => 'Transporte aniversario', 'tabla' => 'transporte_aniversario'],
        ['archivo' => '013_rol_permisos.sql', 'etiqueta' => 'Permisos por rol', 'tabla' => 'rol_permisos'],
        ['archivo' => '014_actividad_log.sql', 'etiqueta' => 'Log de actividad', 'tabla' => 'actividad_log'],
    ];
}

function baseDatosExiste(): bool
{
    try {
        $pdo = getServerConnection();
        $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1');
        $stmt->execute([DB_NAME]);

        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function obtenerFechaCreacionTabla(PDO $pdo, string $tabla): ?string
{
    $stmt = $pdo->prepare(
        'SELECT CREATE_TIME
         FROM information_schema.tables
         WHERE table_schema = ? AND table_name = ?
         LIMIT 1'
    );
    $stmt->execute([DB_NAME, $tabla]);
    $fecha = $stmt->fetchColumn();

    if (!$fecha) {
        return null;
    }

    $dt = DateTime::createFromFormat('Y-m-d H:i:s', (string) $fecha);

    return $dt ? $dt->format('d/m/Y H:i') : null;
}

/**
 * @return array<string, mixed>
 */
function obtenerEstadoInstalacionBd(): array
{
    $tablasEsperadas = obtenerTablasEsperadasSistema();
    $catalogoMigraciones = obtenerCatalogoMigracionesBd();

    $estado = [
        'servidor_conectado'    => false,
        'bd_conectada'          => false,
        'bd_existe'             => false,
        'host'                  => DB_HOST,
        'base_datos'            => DB_NAME,
        'usuario'               => DB_USER,
        'version_mysql'         => null,
        'error'                 => null,
        'tablas'                => [],
        'migraciones'           => [],
        'total_tablas'          => count($tablasEsperadas),
        'tablas_instaladas'     => 0,
        'migraciones_aplicadas' => 0,
        'total_migraciones'     => count($catalogoMigraciones),
        'sistema_listo'         => false,
    ];

    try {
        $server = getServerConnection();
        $estado['servidor_conectado'] = true;
        $estado['version_mysql'] = (string) $server->query('SELECT VERSION()')->fetchColumn();
        $estado['bd_existe'] = baseDatosExiste();
    } catch (Throwable $e) {
        $estado['error'] = $e->getMessage();

        foreach ($catalogoMigraciones as $migracion) {
            $estado['migraciones'][] = array_merge($migracion, [
                'aplicada' => false,
                'fecha'    => null,
            ]);
        }

        foreach ($tablasEsperadas as $tabla) {
            $estado['tablas'][] = [
                'nombre'    => $tabla,
                'instalada' => false,
                'registros' => null,
            ];
        }

        return $estado;
    }

    $pdo = null;

    try {
        $pdo = getConnection();
        $estado['bd_conectada'] = true;
    } catch (Throwable $e) {
        $estado['error'] = $e->getMessage();
    }

    foreach ($tablasEsperadas as $tabla) {
        $instalada = false;
        $registros = null;

        if ($pdo instanceof PDO) {
            $instalada = tablaExiste($pdo, $tabla);
            if ($instalada) {
                $estado['tablas_instaladas']++;
                try {
                    $registros = (int) $pdo->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $tabla) . '`')->fetchColumn();
                } catch (Throwable $e) {
                    $registros = null;
                }
            }
        }

        $estado['tablas'][] = [
            'nombre'    => $tabla,
            'instalada' => $instalada,
            'registros' => $registros,
        ];
    }

    foreach ($catalogoMigraciones as $migracion) {
        $aplicada = false;
        $fecha = null;

        if ($migracion['tabla'] === null) {
            $aplicada = $estado['bd_existe'];
        } elseif ($pdo instanceof PDO) {
            $aplicada = tablaExiste($pdo, (string) $migracion['tabla']);
            if ($aplicada) {
                $fecha = obtenerFechaCreacionTabla($pdo, (string) $migracion['tabla']);
            }
        }

        if ($aplicada) {
            $estado['migraciones_aplicadas']++;
        }

        $estado['migraciones'][] = array_merge($migracion, [
            'aplicada' => $aplicada,
            'fecha'    => $fecha,
        ]);
    }

    $estado['sistema_listo'] = $estado['servidor_conectado']
        && $estado['bd_conectada']
        && $estado['tablas_instaladas'] === $estado['total_tablas'];

    return $estado;
}

/**
 * @return array{exito: bool, mensaje: string}
 */
function ejecutarAccionInstalacionBd(string $accion): array
{
    switch ($accion) {
        case 'bd_crear':
            return crearBaseDatosSistema();
        case 'bd_actualizar':
            return sincronizarTablasSistema(false);
        case 'bd_instalacion_completa':
            return sincronizarTablasSistema(true);
        default:
            return [
                'exito'   => false,
                'mensaje' => 'Acción de base de datos no válida.',
            ];
    }
}
