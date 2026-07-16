-- Sistema Web CDA — esquema en español
-- Ejecutar tras crear la BD o usar «Crear tablas» en el login

CREATE DATABASE IF NOT EXISTS sistema_web_cda
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_web_cda;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) DEFAULT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'administrador',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO usuarios (usuario, clave, nombre, rol) VALUES
('admin', '$2y$12$IAeuaVZ.DxfMzkDongA4ouBkTyb5fVAp0gSsKiqu2EuTJAFBT7TZW', 'Administrador', 'superadmin')
ON DUPLICATE KEY UPDATE clave = VALUES(clave), nombre = VALUES(nombre), rol = VALUES(rol);

CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_formulario VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    celular VARCHAR(30) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    zona VARCHAR(50) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    contactado TINYINT(1) NOT NULL DEFAULT 0,
    ip_cliente VARCHAR(45) DEFAULT NULL,
    agente_usuario VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo_formulario (tipo_formulario),
    INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS presentaciones_ninos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_padre VARCHAR(100) NOT NULL,
    nombre_madre VARCHAR(100) NOT NULL,
    nombre_presentado VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NULL,
    telefono_papa VARCHAR(30) NOT NULL,
    telefono_mama VARCHAR(30) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'recibido',
    ip_cliente VARCHAR(45) DEFAULT NULL,
    agente_usuario VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS territorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lideres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cedula VARCHAR(30) DEFAULT NULL,
    celular VARCHAR(30) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    notas TEXT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS casas_vida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    territorio_id INT NOT NULL,
    lider_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_territorio (territorio_id),
    INDEX idx_lider (lider_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ofrendas (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sesiones_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expira_en DATETIME NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expira (expira_en),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS valores_adicionales (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    fecha DATE NULL,
    valor DECIMAL(12,2) NOT NULL DEFAULT 0,
    habilitado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_fecha (fecha),
    INDEX idx_habilitado (habilitado),
    INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tipos_valor_adicional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    etiqueta VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_etiqueta (etiqueta)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rol_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(20) NOT NULL,
    seccion VARCHAR(50) NOT NULL,
    UNIQUE KEY uk_rol_seccion (rol, seccion),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consejerias (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transporte_aniversario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(200) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    edad TINYINT UNSIGNED NOT NULL,
    zona VARCHAR(50) DEFAULT NULL,
    posee_movilizacion TINYINT(1) NOT NULL DEFAULT 0,
    asientos_disponibles SMALLINT UNSIGNED DEFAULT NULL,
    registrado_por_id INT DEFAULT NULL,
    registrado_por_nombre VARCHAR(100) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_posee_movilizacion (posee_movilizacion),
    INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB;
