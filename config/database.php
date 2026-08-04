<?php

require_once __DIR__ . '/app.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'iglesiacasadeavi_bootstrap');
define('DB_USER', 'iglesiacasadeavi_kuakuey');
define('DB_PASS', 'Superadmin29@!');
define('DB_CHARSET', 'utf8mb4');

function getServerConnection(): PDO
{
    $dsn = sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET);

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '-05:00'");
    }

    return $pdo;
}

function tablaExiste(PDO $pdo, string $tabla): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = ? AND table_name = ?'
    );
    $stmt->execute([DB_NAME, $tabla]);

    return (int) $stmt->fetchColumn() > 0;
}

function renombrarColumnaSiExiste(PDO $pdo, string $tabla, string $vieja, string $nueva, string $definicion): void
{
    if (!tablaExiste($pdo, $tabla)) {
        return;
    }

    $viejaExiste = $pdo->query("SHOW COLUMNS FROM `$tabla` LIKE '$vieja'")->fetch();
    $nuevaExiste = $pdo->query("SHOW COLUMNS FROM `$tabla` LIKE '$nueva'")->fetch();

    if ($viejaExiste && !$nuevaExiste) {
        $pdo->exec("ALTER TABLE `$tabla` CHANGE `$vieja` `$nueva` $definicion");
    }
}

function migrarColumnasEspanol(PDO $pdo): void
{
    renombrarColumnaSiExiste($pdo, 'usuarios', 'username', 'usuario', 'VARCHAR(50) NOT NULL');
    renombrarColumnaSiExiste($pdo, 'usuarios', 'password', 'clave', 'VARCHAR(255) NOT NULL');
    renombrarColumnaSiExiste($pdo, 'usuarios', 'role', 'rol', 'VARCHAR(20) NOT NULL DEFAULT "administrador"');
    renombrarColumnaSiExiste($pdo, 'usuarios', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

    renombrarColumnaSiExiste($pdo, 'inscripciones', 'form_type', 'tipo_formulario', 'VARCHAR(50) NOT NULL');
    renombrarColumnaSiExiste($pdo, 'inscripciones', 'ip_address', 'ip_cliente', 'VARCHAR(45) DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'inscripciones', 'user_agent', 'agente_usuario', 'VARCHAR(255) DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'inscripciones', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

    renombrarColumnaSiExiste($pdo, 'presentaciones_ninos', 'ip_address', 'ip_cliente', 'VARCHAR(45) DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'presentaciones_ninos', 'user_agent', 'agente_usuario', 'VARCHAR(255) DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'presentaciones_ninos', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    renombrarColumnaSiExiste($pdo, 'presentaciones_ninos', 'updated_at', 'actualizado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    renombrarColumnaSiExiste($pdo, 'territorios', 'sort_order', 'orden', 'INT NOT NULL DEFAULT 0');
    renombrarColumnaSiExiste($pdo, 'territorios', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

    renombrarColumnaSiExiste($pdo, 'lideres', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    renombrarColumnaSiExiste($pdo, 'casas_vida', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

    renombrarColumnaSiExiste($pdo, 'ofrendas', 'house_id', 'casa_id', 'INT DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'ofrendas', 'offering_date', 'fecha_ofrenda', 'DATE NOT NULL');
    renombrarColumnaSiExiste($pdo, 'ofrendas', 'amount', 'monto', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
    renombrarColumnaSiExiste($pdo, 'ofrendas', 'registered_by', 'registrado_por_id', 'INT DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'ofrendas', 'registered_by_name', 'registrado_por_nombre', 'VARCHAR(100) DEFAULT NULL');
    renombrarColumnaSiExiste($pdo, 'ofrendas', 'created_at', 'creado_en', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

    if (tablaExiste($pdo, 'inscripciones')) {
        $pdo->exec("UPDATE inscripciones SET tipo_formulario = 'academia' WHERE tipo_formulario = 'academy'");

        $emailCol = $pdo->query("SHOW COLUMNS FROM inscripciones LIKE 'email'")->fetch();
        if ($emailCol && strtoupper((string) ($emailCol['Null'] ?? '')) === 'NO') {
            $pdo->exec('ALTER TABLE inscripciones MODIFY email VARCHAR(100) DEFAULT NULL');
        }
    }

    if (tablaExiste($pdo, 'usuarios')) {
        $pdo->exec("UPDATE usuarios SET rol = 'administrador' WHERE rol = 'admin'");
        $pdo->exec("UPDATE usuarios SET rol = 'counter' WHERE rol = 'contador'");
    }

    if (tablaExiste($pdo, 'presentaciones_ninos')) {
        $pdo->exec("UPDATE presentaciones_ninos SET estado = 'contactado' WHERE estado = 'presentar'");
        $pdo->exec("UPDATE presentaciones_ninos SET estado = 'presentado' WHERE estado = 'realizado'");
    }

    migrarPresentacionesFechaNacimiento($pdo);
}

function migrarPresentacionesFechaNacimiento(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'presentaciones_ninos')) {
        return;
    }

    $fechaExiste = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE 'fecha_nacimiento'")->fetch();

    if (!$fechaExiste) {
        $pdo->exec(
            'ALTER TABLE presentaciones_ninos
             ADD COLUMN fecha_nacimiento DATE NULL AFTER nombre_presentado'
        );
    }

    $anioExiste = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE 'anio_nacimiento'")->fetch();

    if ($anioExiste) {
        $pdo->exec(
            "UPDATE presentaciones_ninos
             SET fecha_nacimiento = CONCAT(anio_nacimiento, '-01-01')
             WHERE fecha_nacimiento IS NULL
               AND anio_nacimiento IS NOT NULL
               AND anio_nacimiento > 0"
        );
        $pdo->exec('ALTER TABLE presentaciones_ninos DROP COLUMN anio_nacimiento');
    }
}

function setupDatabase(): array
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
        migrarTablaTransporteAniversario($pdo);
        migrarTablaRolPermisos($pdo);
        migrarTablaActividadLog($pdo);
        migrarColumnasEspanol($pdo);
        asegurarColumnasBautismoInscripciones($pdo);
        asegurarColumnasPresentacionesNinos($pdo);

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

        return [
            'exito'   => true,
            'mensaje' => 'Base de datos y tablas creadas correctamente.',
        ];
    } catch (Throwable $e) {
        return [
            'exito'   => false,
            'mensaje' => $e->getMessage(),
        ];
    }
}

function asegurarColumnasEventos(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'eventos')) {
        return;
    }

    $columnas = [
        'fecha'               => 'ADD COLUMN fecha DATE NULL AFTER nombre',
        'valor'               => 'ADD COLUMN valor DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER fecha',
        'habilitado'          => 'ADD COLUMN habilitado TINYINT(1) NOT NULL DEFAULT 1 AFTER valor',
        'requiere_numeracion' => 'ADD COLUMN requiere_numeracion TINYINT(1) NOT NULL DEFAULT 0 AFTER habilitado',
    ];

    foreach ($columnas as $nombre => $sqlAlter) {
        $existe = $pdo->query("SHOW COLUMNS FROM eventos LIKE '$nombre'")->fetch();

        if (!$existe) {
            $pdo->exec("ALTER TABLE eventos $sqlAlter");
        }
    }

    migrarTablaEventosTiposEntrada($pdo);
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
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS eventos_tipos_entrada (
            id INT AUTO_INCREMENT PRIMARY KEY,
            evento_id INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            valor DECIMAL(12,2) NOT NULL DEFAULT 0,
            orden INT NOT NULL DEFAULT 0,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_evento_id (evento_id),
            INDEX idx_orden (orden)
        ) ENGINE=InnoDB'
    );

    if (!tablaExiste($pdo, 'eventos') || !tablaExiste($pdo, 'eventos_tipos_entrada')) {
        return;
    }

    // Migra eventos existentes sin tipos: un tipo "General" con el valor del catálogo.
    $pdo->exec(
        'INSERT INTO eventos_tipos_entrada (evento_id, nombre, valor, orden, creado_en)
         SELECT e.id, \'General\', e.valor, 0, NOW()
         FROM eventos e
         WHERE NOT EXISTS (
             SELECT 1 FROM eventos_tipos_entrada t WHERE t.evento_id = e.id
         )'
    );
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
            registrado_por_id INT DEFAULT NULL,
            registrado_por_nombre VARCHAR(100) DEFAULT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tipo (tipo),
            INDEX idx_fecha (fecha),
            INDEX idx_evento_id (evento_id),
            INDEX idx_creado_en (creado_en)
        ) ENGINE=InnoDB'
    );

    if (!tablaExiste($pdo, 'valores_adicionales')) {
        return;
    }

    $eventoCol = $pdo->query("SHOW COLUMNS FROM valores_adicionales LIKE 'evento_id'")->fetch();

    if (!$eventoCol) {
        $pdo->exec('ALTER TABLE valores_adicionales ADD COLUMN evento_id INT NULL AFTER observacion');
        $pdo->exec('ALTER TABLE valores_adicionales ADD INDEX idx_evento_id (evento_id)');
    }

    asegurarColumnasValoresAdicionales($pdo);
}

function asegurarColumnasValoresAdicionales(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'valores_adicionales')) {
        return;
    }

    $columnas = [
        'numeracion'       => 'ADD COLUMN numeracion VARCHAR(30) NULL AFTER evento_id',
        'forma_pago'       => 'ADD COLUMN forma_pago VARCHAR(20) NULL AFTER numeracion',
        'tipo_entrada_id'  => 'ADD COLUMN tipo_entrada_id INT NULL AFTER forma_pago',
        'tipo_entrada'     => 'ADD COLUMN tipo_entrada VARCHAR(100) NULL AFTER tipo_entrada_id',
        'estado_pago'      => "ADD COLUMN estado_pago VARCHAR(20) NULL DEFAULT 'por_cancelar' AFTER tipo_entrada",
    ];

    foreach ($columnas as $nombre => $sqlAlter) {
        $existe = $pdo->query("SHOW COLUMNS FROM valores_adicionales LIKE '$nombre'")->fetch();

        if (!$existe) {
            $pdo->exec("ALTER TABLE valores_adicionales $sqlAlter");
        }
    }

    // Registros de eventos previos quedan como pagados para no marcar pendientes de golpe.
    if (tablaExiste($pdo, 'valores_adicionales')) {
        $pdo->exec(
            "UPDATE valores_adicionales
             SET estado_pago = 'pagado'
             WHERE tipo = 'eventos'
               AND (estado_pago IS NULL OR estado_pago = '')"
        );
    }
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
    if (!tablaExiste($pdo, 'transporte_aniversario')) {
        return;
    }

    $columnas = [
        'edad' => 'ADD COLUMN edad TINYINT UNSIGNED NULL AFTER telefono',
        'observacion' => 'ADD COLUMN observacion VARCHAR(500) NULL AFTER edad',
    ];

    foreach ($columnas as $nombre => $sqlAlter) {
        $existe = $pdo->query("SHOW COLUMNS FROM transporte_aniversario LIKE " . $pdo->quote($nombre))->fetch();

        if (!$existe) {
            $pdo->exec("ALTER TABLE transporte_aniversario $sqlAlter");
        }
    }
}

function asegurarColumnasPresentacionesNinos(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'presentaciones_ninos')) {
        return;
    }

    $existe = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE 'fecha_presentacion'")->fetch();

    if (!$existe) {
        $pdo->exec(
            'ALTER TABLE presentaciones_ninos
             ADD COLUMN fecha_presentacion DATE NULL AFTER estado'
        );
        $pdo->exec(
            "UPDATE presentaciones_ninos
             SET fecha_presentacion = DATE(COALESCE(actualizado_en, creado_en))
             WHERE estado = 'presentado' AND fecha_presentacion IS NULL"
        );
    }

    $bloqueadoExiste = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE 'estado_bloqueado'")->fetch();

    if (!$bloqueadoExiste) {
        $pdo->exec(
            'ALTER TABLE presentaciones_ninos
             ADD COLUMN estado_bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER fecha_presentacion'
        );
        $pdo->exec(
            "UPDATE presentaciones_ninos SET estado_bloqueado = 1 WHERE estado = 'presentado'"
        );
    }

    $parentesco1Existe = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE 'parentesco_representante_1'")->fetch();
    if (!$parentesco1Existe) {
        $pdo->exec(
            'ALTER TABLE presentaciones_ninos
             ADD COLUMN parentesco_representante_1 VARCHAR(30) NOT NULL DEFAULT "" AFTER id'
        );
        $pdo->exec(
            "UPDATE presentaciones_ninos
             SET parentesco_representante_1 = 'padre'
             WHERE parentesco_representante_1 = '' AND TRIM(COALESCE(nombre_padre, '')) <> ''"
        );
    }

    $parentesco2Existe = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE 'parentesco_representante_2'")->fetch();
    if (!$parentesco2Existe) {
        $pdo->exec(
            'ALTER TABLE presentaciones_ninos
             ADD COLUMN parentesco_representante_2 VARCHAR(30) DEFAULT NULL AFTER telefono_papa'
        );
        $pdo->exec(
            "UPDATE presentaciones_ninos
             SET parentesco_representante_2 = 'madre'
             WHERE parentesco_representante_2 IS NULL AND TRIM(COALESCE(nombre_madre, '')) <> ''"
        );
    }

    $columnasRepresentante2 = [
        'nombre_madre'  => 'VARCHAR(100) DEFAULT NULL',
        'telefono_mama' => 'VARCHAR(30) DEFAULT NULL',
    ];

    foreach ($columnasRepresentante2 as $columna => $definicion) {
        $info = $pdo->query("SHOW COLUMNS FROM presentaciones_ninos LIKE " . $pdo->quote($columna))->fetch();
        if (!$info) {
            continue;
        }

        if (strtoupper((string) ($info['Null'] ?? '')) === 'NO') {
            $pdo->exec("ALTER TABLE presentaciones_ninos MODIFY COLUMN {$columna} {$definicion}");
            $pdo->exec(
                "UPDATE presentaciones_ninos
                 SET {$columna} = NULL
                 WHERE TRIM(COALESCE({$columna}, '')) = ''"
            );
        }
    }
}

function asegurarColumnasBautismoInscripciones(PDO $pdo): void
{
    if (!tablaExiste($pdo, 'inscripciones')) {
        return;
    }

    $columnas = [
        'estado_bautismo' => 'ADD COLUMN estado_bautismo VARCHAR(20) NOT NULL DEFAULT "ingresado" AFTER contactado',
        'fecha_bautismo'  => 'ADD COLUMN fecha_bautismo DATE NULL AFTER estado_bautismo',
        'estado_bautismo_bloqueado' => 'ADD COLUMN estado_bautismo_bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER fecha_bautismo',
    ];

    foreach ($columnas as $nombre => $sqlAlter) {
        $existe = $pdo->query("SHOW COLUMNS FROM inscripciones LIKE '$nombre'")->fetch();

        if (!$existe) {
            $pdo->exec("ALTER TABLE inscripciones $sqlAlter");
        }
    }
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

    require_once __DIR__ . '/../includes/valores_adicionales.php';
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

    require_once __DIR__ . '/../includes/permisos.php';
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
            detalle VARCHAR(500) NULL,
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

    if (!tablaExiste($pdo, 'actividad_log')) {
        return;
    }

    $columnas = [
        'usuario_login'  => 'ADD COLUMN usuario_login VARCHAR(50) NULL AFTER usuario_nombre',
        'rol_usuario'    => 'ADD COLUMN rol_usuario VARCHAR(20) NULL AFTER usuario_login',
        'datos_extra'    => 'ADD COLUMN datos_extra TEXT NULL AFTER detalle',
        'agente_usuario' => 'ADD COLUMN agente_usuario VARCHAR(255) NULL AFTER ip_cliente',
    ];

    foreach ($columnas as $nombre => $sqlAlter) {
        $existe = $pdo->query("SHOW COLUMNS FROM actividad_log LIKE " . $pdo->quote($nombre))->fetch();
        if (!$existe) {
            $pdo->exec("ALTER TABLE actividad_log $sqlAlter");
        }
    }

    $detalleCol = $pdo->query("SHOW COLUMNS FROM actividad_log LIKE 'detalle'")->fetch();
    if ($detalleCol && isset($detalleCol['Type']) && stripos((string) $detalleCol['Type'], 'varchar(255)') !== false) {
        $pdo->exec('ALTER TABLE actividad_log MODIFY COLUMN detalle VARCHAR(500) NULL');
    }
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

    if (!tablaExiste($pdo, 'ofrendas')) {
        return;
    }

    $casaCol = $pdo->query("SHOW COLUMNS FROM ofrendas LIKE 'casa_id'")->fetch();
    if (!$casaCol) {
        $viejaCol = $pdo->query("SHOW COLUMNS FROM ofrendas LIKE 'house_id'")->fetch();
        if (!$viejaCol) {
            $pdo->exec('ALTER TABLE ofrendas ADD COLUMN casa_id INT NULL AFTER id');
        }
    }

    $nombreCol = $pdo->query("SHOW COLUMNS FROM ofrendas LIKE 'registrado_por_nombre'")->fetch();
    if (!$nombreCol) {
        $viejaNombre = $pdo->query("SHOW COLUMNS FROM ofrendas LIKE 'registered_by_name'")->fetch();
        if (!$viejaNombre) {
            $pdo->exec('ALTER TABLE ofrendas ADD COLUMN registrado_por_nombre VARCHAR(100) NULL');
        }
    }
}

function getDatabaseStatus(): array
{
    try {
        $server = getServerConnection();
        $version = $server->query('SELECT VERSION()')->fetchColumn();
        $servidorOk = true;
    } catch (PDOException $e) {
        return [
            'exito'      => false,
            'servidor_ok' => false,
            'host'       => DB_HOST,
            'base_datos' => DB_NAME,
            'error'      => $e->getMessage(),
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
