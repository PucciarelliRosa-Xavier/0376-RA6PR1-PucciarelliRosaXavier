-- ============================================================
-- TIMECONTROL - Sistema de Control Horario y Gestión de Proyectos
-- Script SQL completo - MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS timecontrol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE timecontrol;

-- ============================================================
-- TABLA: DEPARTAMENTOS
-- ============================================================
CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO departamentos (nombre) VALUES 
    ('RRHH'), ('Dirección'), ('Contabilidad'), ('Desarrollo'), ('Diseño');

-- ============================================================
-- TABLA: HORARIOS
-- ============================================================
CREATE TABLE horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_salida TIME NOT NULL,
    tolerancia_minutos INT DEFAULT 10 COMMENT 'Minutos de margen antes de marcar retraso',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_minutos) VALUES
    ('Jornada Completa Mañana', '09:00:00', '18:00:00', 15),
    ('Jornada Partida', '08:00:00', '17:00:00', 15),
    ('Jornada Tarde', '14:00:00', '22:00:00', 15),
    ('Media Jornada', '09:00:00', '13:00:00', 10);

-- ============================================================
-- TABLA: USUARIOS
-- ============================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','empleado','jefe','jefe_departamento') NOT NULL DEFAULT 'empleado',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    id_departamento INT,
    id_horario INT,
    avatar VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (id_horario) REFERENCES horarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Usuarios de ejemplo (password: Admin1234! en hash bcrypt)
INSERT INTO usuarios (nombre, apellidos, email, password, rol, id_departamento, id_horario) VALUES
    ('Admin', 'Sistema', 'admin@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 2, 1),
    ('Carlos', 'García López', 'carlos@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jefe_departamento', 4, 1),
    ('María', 'Fernández Ruiz', 'maria@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jefe', 4, 1),
    ('Pedro', 'Martínez Silva', 'pedro@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 4, 1),
    ('Ana', 'López Torres', 'ana@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 5, 2);

-- ============================================================
-- TABLA: PROYECTOS
-- ============================================================
CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo','pausado','completado') NOT NULL DEFAULT 'activo',
    fecha_inicio DATE,
    fecha_fin DATE,
    id_responsable INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_responsable) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO proyectos (nombre, descripcion, estado, fecha_inicio, id_responsable) VALUES
    ('Portal Corporativo', 'Rediseño del portal web de la empresa', 'activo', '2024-01-15', 3),
    ('App Móvil RRHH', 'Aplicación móvil para gestión de recursos humanos', 'activo', '2024-02-01', 3),
    ('Migración ERP', 'Migración del sistema ERP a la nube', 'pausado', '2024-03-01', 2);

-- ============================================================
-- TABLA: USUARIO_PROYECTO (relación N:M)
-- ============================================================
CREATE TABLE usuario_proyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_proyecto INT NOT NULL,
    fecha_asignacion DATE DEFAULT (CURRENT_DATE),
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_proyecto) REFERENCES proyectos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_proyecto (id_usuario, id_proyecto)
) ENGINE=InnoDB;

INSERT INTO usuario_proyecto (id_usuario, id_proyecto) VALUES
    (4, 1), (4, 2), (5, 1), (3, 1), (3, 2), (2, 3);

-- ============================================================
-- TABLA: FICHAJES
-- ============================================================
CREATE TABLE fichajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('entrada','salida') NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    es_tardanza TINYINT(1) DEFAULT 0,
    es_salida_anticipada TINYINT(1) DEFAULT 0,
    minutos_diferencia INT DEFAULT 0 COMMENT 'Positivo = tarde/anticipado, Negativo = puntual/tarde al salir',
    observacion TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fecha (id_usuario, timestamp)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: IMPUTACIONES
-- ============================================================
CREATE TABLE imputaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_proyecto INT NOT NULL,
    horas DECIMAL(5,2) NOT NULL,
    fecha DATE NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_proyecto) REFERENCES proyectos(id) ON DELETE CASCADE,
    INDEX idx_usuario_fecha (id_usuario, fecha)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: INCIDENCIAS
-- ============================================================
CREATE TABLE incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('retraso','olvido_fichaje','salida_anticipada','error_fichaje') NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    estado ENUM('pendiente','revisada','resuelta') NOT NULL DEFAULT 'pendiente',
    email_enviado TINYINT(1) DEFAULT 0,
    id_revisor INT,
    nota_revision TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_revisor) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario_fecha (id_usuario, fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: EMAIL_LOG (registro de emails enviados)
-- ============================================================
CREATE TABLE email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    destinatario VARCHAR(200) NOT NULL,
    asunto VARCHAR(300) NOT NULL,
    tipo ENUM('retraso','olvido','incidencia','informe') NOT NULL,
    estado ENUM('enviado','error') NOT NULL,
    error_mensaje TEXT,
    enviado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- VISTA: resumen_diario (utilidad para informes)
-- ============================================================
CREATE OR REPLACE VIEW vista_resumen_diario AS
SELECT 
    u.id AS id_usuario,
    CONCAT(u.nombre, ' ', u.apellidos) AS nombre_completo,
    DATE(f.timestamp) AS fecha,
    MIN(CASE WHEN f.tipo = 'entrada' THEN f.timestamp END) AS primera_entrada,
    MAX(CASE WHEN f.tipo = 'salida' THEN f.timestamp END) AS ultima_salida,
    ROUND(
        TIMESTAMPDIFF(MINUTE, 
            MIN(CASE WHEN f.tipo = 'entrada' THEN f.timestamp END),
            MAX(CASE WHEN f.tipo = 'salida' THEN f.timestamp END)
        ) / 60, 2
    ) AS horas_trabajadas,
    d.nombre AS departamento
FROM fichajes f
JOIN usuarios u ON u.id = f.id_usuario
LEFT JOIN departamentos d ON d.id = u.id_departamento
GROUP BY u.id, DATE(f.timestamp);

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
