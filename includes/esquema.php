<?php

require_once __DIR__ . '/conexion.php';

/**
 * Esquema actual de la base de datos.
 * CREATE TABLE IF NOT EXISTS crea instalaciones nuevas.
 * asegurarColumnasTabla agrega columnas faltantes en bases ya existentes.
 */

function asegurarColumnasTabla(PDO $pdo, string $tabla, array $columnas): void
{
    if (!tablaExiste($pdo, $tabla) || $columnas === []) {
        return;
    }

    foreach ($columnas as $nombre => $sqlAlter) {
        $existe = $pdo->query('SHOW COLUMNS FROM `' . $tabla . '` LIKE ' . $pdo->quote($nombre))->fetch();
        if (!$existe) {
            $pdo->exec('ALTER TABLE `' . $tabla . '` ' . $sqlAlter);
        }
    }
}

function crearBaseDatosSistema(): array
{
    try {
        $pdo = getServerConnection();
        $pdo->exec(
            'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` '
            . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );

        return [
            'exito'   => true,
            'mensaje' => 'Base de datos creada o verificada correctamente.',
        ];
    } catch (Throwable $e) {
        return [
            'exito'   => false,
            'mensaje' => $e->getMessage(),
        ];
    }
}

function sincronizarTablasSistema(bool $asegurarAdmin = true): array
{
    try {
        $pdo = getServerConnection();

        try {
            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` '
                . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        } catch (PDOException $e) {
            // En hosting compartido la BD ya existe y no se puede crear otra.
        }

        $pdo->exec('USE `' . DB_NAME . '`');

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario VARCHAR(50) NOT NULL UNIQUE,
                clave VARCHAR(255) NOT NULL,
                nombre VARCHAR(100) DEFAULT NULL,
                rol VARCHAR(20) NOT NULL DEFAULT "administrador",
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS inscripciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo_formulario VARCHAR(50) NOT NULL,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                celular VARCHAR(30) NOT NULL,
                email VARCHAR(100) DEFAULT NULL,
                zona VARCHAR(50) DEFAULT NULL,
                direccion VARCHAR(255) DEFAULT NULL,
                contactado TINYINT(1) NOT NULL DEFAULT 0,
                estado_bautismo VARCHAR(20) NOT NULL DEFAULT "ingresado",
                fecha_bautismo DATE DEFAULT NULL,
                estado_bautismo_bloqueado TINYINT(1) NOT NULL DEFAULT 0,
                ip_cliente VARCHAR(45) DEFAULT NULL,
                agente_usuario VARCHAR(255) DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tipo_formulario (tipo_formulario),
                INDEX idx_creado_en (creado_en),
                INDEX idx_estado_bautismo (estado_bautismo)
            ) ENGINE=InnoDB'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS presentaciones_ninos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                parentesco_representante_1 VARCHAR(30) NOT NULL DEFAULT "",
                nombre_padre VARCHAR(100) NOT NULL,
                telefono_papa VARCHAR(30) NOT NULL,
                parentesco_representante_2 VARCHAR(30) DEFAULT NULL,
                nombre_madre VARCHAR(100) DEFAULT NULL,
                telefono_mama VARCHAR(30) DEFAULT NULL,
                nombre_presentado VARCHAR(100) NOT NULL,
                fecha_nacimiento DATE NULL,
                estado VARCHAR(20) NOT NULL DEFAULT "recibido",
                fecha_presentacion DATE NULL,
                estado_bloqueado TINYINT(1) NOT NULL DEFAULT 0,
                ip_cliente VARCHAR(45) DEFAULT NULL,
                agente_usuario VARCHAR(255) DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_estado (estado)
            ) ENGINE=InnoDB'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS ofrendas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                casa_id INT DEFAULT NULL,
                casa_vida VARCHAR(100) DEFAULT NULL,
                lider VARCHAR(100) DEFAULT NULL,
                fecha_ofrenda DATE NOT NULL,
                monto DECIMAL(12,2) NOT NULL DEFAULT 0,
                registrado_por_id INT DEFAULT NULL,
                registrado_por_nombre VARCHAR(100) DEFAULT NULL,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_fecha_ofrenda (fecha_ofrenda),
                INDEX idx_casa_id (casa_id)
            ) ENGINE=InnoDB'
        );

        migrateEstructuraTables($pdo);
        migrarTablaSesionesApi($pdo);
        migrarTablaEventos($pdo);
        migrarTablaValoresAdicionales($pdo);
        migrarTablaTiposValorAdicional($pdo);
        migrarTablaConsejerias($pdo);
        migrarTablaCalendarioEventos($pdo);
        migrarTablaTransporteAniversario($pdo);
        migrarTablaRolPermisos($pdo);
        migrarTablaActividadLog($pdo);
        asegurarColumnasBautismoInscripciones($pdo);
        asegurarColumnasPresentacionesNinos($pdo);

        if ($asegurarAdmin) {
            $adminHash = '$2y$12$IAeuaVZ.DxfMzkDongA4ouBkTyb5fVAp0gSsKiqu2EuTJAFBT7TZW';
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ?');
            $stmt->execute(['admin']);

            if ($stmt->fetch()) {
                $update = $pdo->prepare(
                    'UPDATE usuarios SET clave = ?, nombre = ?, rol = ? WHERE usuario = ?'
                );
                $update->execute([$adminHash, 'Administrador', 'superadmin', 'admin']);
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO usuarios (usuario, clave, nombre, rol) VALUES (?, ?, ?, ?)'
                );
                $insert->execute(['admin', $adminHash, 'Administrador', 'superadmin']);
            }
        }

        return [
            'exito'   => true,
            'mensaje' => $asegurarAdmin
                ? 'Instalación completa: tablas sincronizadas y usuario admin verificado.'
                : 'Base de datos actualizada: tablas y columnas pendientes aplicadas.',
        ];
    } catch (Throwable $e) {
        return [
            'exito'   => false,
            'mensaje' => $e->getMessage(),
        ];
    }
}

function setupDatabase(): array
{
    return sincronizarTablasSistema(true);
}

function asegurarColumnasEventos(PDO $pdo): void
{
    asegurarColumnasTabla($pdo, 'eventos', [
        'fecha'               => 'ADD COLUMN fecha DATE NULL AFTER nombre',
        'valor'               => 'ADD COLUMN valor DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER fecha',
        'habilitado'          => 'ADD COLUMN habilitado TINYINT(1) NOT NULL DEFAULT 1 AFTER valor',
        'requiere_numeracion' => 'ADD COLUMN requiere_numeracion TINYINT(1) NOT NULL DEFAULT 0 AFTER habilitado',
    ]);

    migrarTablaEventosTiposEntrada($pdo);
    migrarTablaEventosCamposAdicionales($pdo);
}

function migrarTablaEventos(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS eventos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(150) NOT NULL,
            fecha DATE NULL,
            valor DECIMAL(12,2) NOT NULL DEFAULT 0,
            habilitado TINYINT(1) NOT NULL DEFAULT 1,
            requiere_numeracion TINYINT(1) NOT NULL DEFAULT 0,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_nombre (nombre),
            INDEX idx_fecha (fecha),
            INDEX idx_habilitado (habilitado),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasEventos($pdo);
}

function migrarTablaEventosTiposEntrada(PDO $pdo): void
{
    static $migrado = false;

    if ($migrado) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS eventos_tipos_entrada (
            id INT AUTO_INCREMENT PRIMARY KEY,
            evento_id INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            valor DECIMAL(12,2) NOT NULL DEFAULT 0,
            orden INT NOT NULL DEFAULT 0,
            visible_publico TINYINT(1) NOT NULL DEFAULT 1,
            es_gratis TINYINT(1) NOT NULL DEFAULT 0,
            prefijo VARCHAR(10) DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_evento_id (evento_id),
            INDEX idx_orden (orden)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasTabla($pdo, 'eventos_tipos_entrada', [
        'visible_publico' => 'ADD COLUMN visible_publico TINYINT(1) NOT NULL DEFAULT 1 AFTER orden',
        'es_gratis'       => 'ADD COLUMN es_gratis TINYINT(1) NOT NULL DEFAULT 0 AFTER visible_publico',
        'prefijo'         => 'ADD COLUMN prefijo VARCHAR(10) NULL AFTER es_gratis',
    ]);

    if (tablaExiste($pdo, 'eventos') && tablaExiste($pdo, 'eventos_tipos_entrada')) {
        $pdo->exec(
            'INSERT INTO eventos_tipos_entrada (evento_id, nombre, valor, orden, creado_en)
             SELECT e.id, \'General\', e.valor, 0, NOW()
             FROM eventos e
             WHERE NOT EXISTS (
                 SELECT 1 FROM eventos_tipos_entrada t WHERE t.evento_id = e.id
             )'
        );
    }

    $migrado = true;
}

function migrarTablaEventosCamposAdicionales(PDO $pdo): void
{
    static $migrado = false;

    if ($migrado) {
        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS eventos_campos_adicionales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            evento_id INT NOT NULL,
            etiqueta VARCHAR(100) NOT NULL,
            tipo VARCHAR(20) NOT NULL DEFAULT \'texto\',
            opciones TEXT NULL,
            obligatorio TINYINT(1) NOT NULL DEFAULT 1,
            orden INT NOT NULL DEFAULT 0,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_evento_id (evento_id),
            INDEX idx_orden (orden)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasTabla($pdo, 'eventos_campos_adicionales', [
        'tipo'     => "ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'texto' AFTER etiqueta",
        'opciones' => 'ADD COLUMN opciones TEXT NULL AFTER tipo',
    ]);

    $migrado = true;
}

function migrarTablaValoresAdicionales(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS valores_adicionales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(50) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            fecha DATE NOT NULL,
            telefono VARCHAR(30) NOT NULL,
            valor DECIMAL(12,2) NOT NULL DEFAULT 0,
            observacion TEXT DEFAULT NULL,
            evento_id INT DEFAULT NULL,
            numeracion VARCHAR(30) DEFAULT NULL,
            forma_pago VARCHAR(20) DEFAULT NULL,
            tipo_entrada_id INT DEFAULT NULL,
            tipo_entrada VARCHAR(100) DEFAULT NULL,
            estado_pago VARCHAR(20) DEFAULT \'por_cancelar\',
            info_adicional TEXT DEFAULT NULL,
            asistio TINYINT(1) NOT NULL DEFAULT 0,
            asistio_en DATETIME DEFAULT NULL,
            asistio_por_id INT DEFAULT NULL,
            asistio_por_nombre VARCHAR(100) DEFAULT NULL,
            registrado_por_id INT DEFAULT NULL,
            registrado_por_nombre VARCHAR(100) DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tipo (tipo),
            INDEX idx_fecha (fecha),
            INDEX idx_evento_id (evento_id),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasValoresAdicionales($pdo);
}

function asegurarColumnasValoresAdicionales(PDO $pdo): void
{
    asegurarColumnasTabla($pdo, 'valores_adicionales', [
        'evento_id'        => 'ADD COLUMN evento_id INT NULL AFTER observacion',
        'numeracion'       => 'ADD COLUMN numeracion VARCHAR(30) NULL AFTER evento_id',
        'forma_pago'       => 'ADD COLUMN forma_pago VARCHAR(20) NULL AFTER numeracion',
        'tipo_entrada_id'  => 'ADD COLUMN tipo_entrada_id INT NULL AFTER forma_pago',
        'tipo_entrada'     => 'ADD COLUMN tipo_entrada VARCHAR(100) NULL AFTER tipo_entrada_id',
        'estado_pago'      => "ADD COLUMN estado_pago VARCHAR(20) NULL DEFAULT 'por_cancelar' AFTER tipo_entrada",
        'info_adicional'   => 'ADD COLUMN info_adicional TEXT NULL AFTER estado_pago',
        'asistio'          => 'ADD COLUMN asistio TINYINT(1) NOT NULL DEFAULT 0 AFTER info_adicional',
        'asistio_en'       => 'ADD COLUMN asistio_en DATETIME NULL AFTER asistio',
        'asistio_por_id'   => 'ADD COLUMN asistio_por_id INT NULL AFTER asistio_en',
        'asistio_por_nombre' => 'ADD COLUMN asistio_por_nombre VARCHAR(100) NULL AFTER asistio_por_id',
    ]);
}

function migrarTablaTransporteAniversario(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS transporte_aniversario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_completo VARCHAR(200) NOT NULL,
            telefono VARCHAR(30) NOT NULL,
            edad TINYINT UNSIGNED NOT NULL,
            observacion VARCHAR(500) DEFAULT NULL,
            posee_movilizacion TINYINT(1) NOT NULL DEFAULT 0,
            asientos_disponibles SMALLINT UNSIGNED DEFAULT NULL,
            registrado_por_id INT DEFAULT NULL,
            registrado_por_nombre VARCHAR(100) DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_posee_movilizacion (posee_movilizacion),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasTransporteAniversario($pdo);
}

function asegurarColumnasTransporteAniversario(PDO $pdo): void
{
    asegurarColumnasTabla($pdo, 'transporte_aniversario', [
        'edad'        => 'ADD COLUMN edad TINYINT UNSIGNED NULL AFTER telefono',
        'observacion' => 'ADD COLUMN observacion VARCHAR(500) NULL AFTER edad',
    ]);
}

function asegurarColumnasPresentacionesNinos(PDO $pdo): void
{
    asegurarColumnasTabla($pdo, 'presentaciones_ninos', [
        'fecha_nacimiento'            => 'ADD COLUMN fecha_nacimiento DATE NULL AFTER nombre_presentado',
        'fecha_presentacion'          => 'ADD COLUMN fecha_presentacion DATE NULL AFTER estado',
        'estado_bloqueado'            => 'ADD COLUMN estado_bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER fecha_presentacion',
        'parentesco_representante_1'  => 'ADD COLUMN parentesco_representante_1 VARCHAR(30) NOT NULL DEFAULT "" AFTER id',
        'parentesco_representante_2'  => 'ADD COLUMN parentesco_representante_2 VARCHAR(30) DEFAULT NULL AFTER telefono_papa',
    ]);
}

function asegurarColumnasBautismoInscripciones(PDO $pdo): void
{
    asegurarColumnasTabla($pdo, 'inscripciones', [
        'estado_bautismo'           => 'ADD COLUMN estado_bautismo VARCHAR(20) NOT NULL DEFAULT "ingresado" AFTER contactado',
        'fecha_bautismo'            => 'ADD COLUMN fecha_bautismo DATE NULL AFTER estado_bautismo',
        'estado_bautismo_bloqueado' => 'ADD COLUMN estado_bautismo_bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER fecha_bautismo',
    ]);
}

function migrarTablaConsejerias(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS consejerias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_completo VARCHAR(200) NOT NULL,
            telefono VARCHAR(30) NOT NULL,
            tipo_consejeria VARCHAR(30) NOT NULL,
            anio_en_cda SMALLINT UNSIGNED NOT NULL,
            primera_consejeria TINYINT(1) NOT NULL DEFAULT 1,
            cita_fecha DATE DEFAULT NULL,
            cita_hora TIME DEFAULT NULL,
            registrado_por_id INT DEFAULT NULL,
            registrado_por_nombre VARCHAR(100) DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tipo_consejeria (tipo_consejeria),
            INDEX idx_cita_fecha (cita_fecha),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );
}

function migrarTablaCalendarioEventos(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS calendario_eventos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(150) NOT NULL,
            descripcion VARCHAR(255) NOT NULL DEFAULT "",
            fecha DATE NOT NULL,
            fecha_fin DATE NULL,
            foto VARCHAR(255) NOT NULL DEFAULT "",
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_fecha (fecha),
            INDEX idx_fecha_fin (fecha_fin),
            INDEX idx_activo (activo),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasTabla($pdo, 'calendario_eventos', [
        'descripcion' => 'ADD COLUMN descripcion VARCHAR(255) NOT NULL DEFAULT "" AFTER titulo',
        'fecha_fin'   => 'ADD COLUMN fecha_fin DATE NULL AFTER fecha',
    ]);
}

function migrarTablaTiposValorAdicional(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tipos_valor_adicional (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(50) NOT NULL UNIQUE,
            etiqueta VARCHAR(100) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_etiqueta (etiqueta)
        ) ENGINE=InnoDB'
    );

    require_once __DIR__ . '/valores_adicionales.php';
    sembrarTiposValorAdicionalPorDefecto($pdo);
}

function migrarTablaRolPermisos(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS rol_permisos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(20) NOT NULL,
            seccion VARCHAR(50) NOT NULL,
            UNIQUE KEY uk_rol_seccion (rol, seccion),
            INDEX idx_rol (rol)
        ) ENGINE=InnoDB'
    );

    require_once __DIR__ . '/permisos.php';
    sembrarPermisosPorDefecto($pdo);
}

function migrarTablaActividadLog(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS actividad_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NULL,
            usuario_nombre VARCHAR(100) NULL,
            usuario_login VARCHAR(50) NULL,
            rol_usuario VARCHAR(20) NULL,
            accion VARCHAR(80) NOT NULL,
            seccion VARCHAR(50) NULL,
            entidad VARCHAR(50) NULL,
            entidad_id INT NULL,
            detalle TEXT NULL,
            datos_extra TEXT NULL,
            ip_cliente VARCHAR(45) NULL,
            agente_usuario VARCHAR(255) NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_accion (accion),
            INDEX idx_seccion (seccion),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasTabla($pdo, 'actividad_log', [
        'usuario_login'  => 'ADD COLUMN usuario_login VARCHAR(50) NULL AFTER usuario_nombre',
        'rol_usuario'    => 'ADD COLUMN rol_usuario VARCHAR(20) NULL AFTER usuario_login',
        'datos_extra'    => 'ADD COLUMN datos_extra TEXT NULL AFTER detalle',
        'agente_usuario' => 'ADD COLUMN agente_usuario VARCHAR(255) NULL AFTER ip_cliente',
    ]);
}

function migrarTablaSesionesApi(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS sesiones_api (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expira_en DATETIME NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_expira (expira_en),
            INDEX idx_usuario (usuario_id)
        ) ENGINE=InnoDB'
    );
}

function migrateEstructuraTables(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS territorios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            orden INT NOT NULL DEFAULT 0,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS lideres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            cedula VARCHAR(30) DEFAULT NULL,
            celular VARCHAR(30) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            notas TEXT DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS casas_vida (
            id INT AUTO_INCREMENT PRIMARY KEY,
            territorio_id INT NOT NULL,
            lider_id INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            direccion VARCHAR(255) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_territorio (territorio_id),
            INDEX idx_lider (lider_id)
        ) ENGINE=InnoDB'
    );

    asegurarColumnasTabla($pdo, 'lideres', [
        'pareja' => "ADD COLUMN pareja VARCHAR(20) NOT NULL DEFAULT 'esposo' AFTER apellido",
        'genero' => "ADD COLUMN genero VARCHAR(20) NOT NULL DEFAULT '' AFTER apellido",
    ]);

    if (tablaExiste($pdo, 'lideres')) {
        $pdo->exec(
            "UPDATE lideres SET genero = 'masculino' WHERE (genero = '' OR genero IS NULL) AND pareja = 'esposo'"
        );
        $pdo->exec(
            "UPDATE lideres SET genero = 'femenino' WHERE (genero = '' OR genero IS NULL) AND pareja = 'esposa'"
        );
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS territorio_asignaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            territorio_id INT NOT NULL,
            miembro_id INT NOT NULL,
            rol VARCHAR(20) NOT NULL,
            pareja VARCHAR(20) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_territorio_rol_pareja (territorio_id, rol, pareja),
            INDEX idx_miembro (miembro_id),
            INDEX idx_territorio (territorio_id),
            INDEX idx_rol (rol)
        ) ENGINE=InnoDB'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS miembro_parentescos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            miembro_id INT NOT NULL,
            pariente_id INT NOT NULL,
            parentesco VARCHAR(30) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_miembro_pariente (miembro_id, pariente_id),
            INDEX idx_pariente (pariente_id),
            INDEX idx_parentesco (parentesco)
        ) ENGINE=InnoDB'
    );
}

function getDatabaseStatus(): array
{
    try {
        $server = getServerConnection();
        $version = $server->query('SELECT VERSION()')->fetchColumn();
        $servidorOk = true;
    } catch (PDOException $e) {
        return [
            'exito'       => false,
            'servidor_ok' => false,
            'host'        => DB_HOST,
            'base_datos'  => DB_NAME,
            'error'       => $e->getMessage(),
        ];
    }

    try {
        $pdo = getConnection();
        $cantidadUsuarios = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();

        return [
            'exito'       => true,
            'servidor_ok' => true,
            'host'        => DB_HOST,
            'base_datos'  => DB_NAME,
            'version'     => $version,
            'usuarios'    => $cantidadUsuarios,
            'tablas_ok'   => true,
        ];
    } catch (PDOException $e) {
        return [
            'exito'       => false,
            'servidor_ok' => $servidorOk,
            'host'        => DB_HOST,
            'base_datos'  => DB_NAME,
            'version'     => $version,
            'tablas_ok'   => false,
            'error'       => $e->getMessage(),
        ];
    }
}
