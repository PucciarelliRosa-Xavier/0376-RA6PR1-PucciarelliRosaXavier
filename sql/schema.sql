-- ============================================================
-- TimeControl - Schema de Base de Datos
-- Sistema de Control Horario y Gestión de Proyectos
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS timecontrol
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE timecontrol;

-- ------------------------------------------------------------
-- TABLA: horarios
-- Define los turnos de trabajo disponibles
-- ------------------------------------------------------------
CREATE TABLE horarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)  NOT NULL,
    hora_inicio TIME          NOT NULL,
    hora_fin    TIME          NOT NULL,
    tolerancia  INT           NOT NULL DEFAULT 10 COMMENT 'Minutos de tolerancia para considerar retraso',
    creado_en   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: usuarios
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100)  NOT NULL,
    apellidos        VARCHAR(150)  NOT NULL,
    email            VARCHAR(255)  NOT NULL UNIQUE,
    password         VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash',
    rol              ENUM('admin','empleado','jefe','jefe_departamento') NOT NULL DEFAULT 'empleado',
    activo           TINYINT(1)    NOT NULL DEFAULT 1,
    departamento     ENUM('rrhh','direccion','contabilidad','desarrollo','diseno') NOT NULL DEFAULT 'desarrollo',
    id_horario       INT           NULL,
    avatar           VARCHAR(255)  NULL,
    telefono         VARCHAR(20)   NULL,
    creado_en        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso    DATETIME      NULL,
    CONSTRAINT fk_usuarios_horario FOREIGN KEY (id_horario) REFERENCES horarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: proyectos
-- ------------------------------------------------------------
CREATE TABLE proyectos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(200)  NOT NULL,
    descripcion TEXT          NULL,
    estado      ENUM('activo','pausado','completado') NOT NULL DEFAULT 'activo',
    color       VARCHAR(7)    NOT NULL DEFAULT '#4F6EF7' COMMENT 'Color HEX para la UI',
    fecha_inicio DATE          NULL,
    fecha_fin   DATE          NULL,
    creado_en   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: usuario_proyecto (N:M)
-- ------------------------------------------------------------
CREATE TABLE usuario_proyecto (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario   INT NOT NULL,
    id_proyecto  INT NOT NULL,
    rol_proyecto VARCHAR(100) NULL COMMENT 'Rol específico en el proyecto',
    asignado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_proyecto (id_usuario, id_proyecto),
    CONSTRAINT fk_up_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_up_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: fichajes
-- Registros de entrada/salida
-- ------------------------------------------------------------
CREATE TABLE fichajes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario   INT          NOT NULL,
    tipo         ENUM('entrada','salida') NOT NULL,
    timestamp    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip           VARCHAR(45)  NULL,
    es_manual    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = corregido manualmente por admin',
    nota         VARCHAR(255) NULL,
    CONSTRAINT fk_fichajes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_fichajes_usuario_fecha (id_usuario, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: imputaciones
-- Horas trabajadas por proyecto
-- ------------------------------------------------------------
CREATE TABLE imputaciones (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario  INT           NOT NULL,
    id_proyecto INT           NOT NULL,
    horas       DECIMAL(4,2)  NOT NULL COMMENT 'Ej: 1.5 = 1h 30min',
    fecha       DATE          NOT NULL,
    descripcion TEXT          NULL,
    creado_en   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imp_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_imp_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos(id) ON DELETE CASCADE,
    INDEX idx_imputaciones_usuario_fecha (id_usuario, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: incidencias
-- ------------------------------------------------------------
CREATE TABLE incidencias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario  INT          NOT NULL,
    tipo        ENUM('retraso','olvido_salida','olvido_entrada','salida_anticipada','error') NOT NULL,
    descripcion TEXT         NULL,
    estado      ENUM('pendiente','revisada','resuelta') NOT NULL DEFAULT 'pendiente',
    email_enviado TINYINT(1) NOT NULL DEFAULT 0,
    fecha       DATE         NOT NULL,
    creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inc_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_incidencias_usuario_fecha (id_usuario, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- TABLA: email_log
-- Registro de emails enviados
-- ------------------------------------------------------------
CREATE TABLE email_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario   INT          NULL,
    destinatario VARCHAR(255) NOT NULL,
    asunto       VARCHAR(255) NOT NULL,
    tipo         VARCHAR(100) NOT NULL,
    estado       ENUM('enviado','error') NOT NULL,
    error_msg    TEXT         NULL,
    enviado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_emaillog_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Horarios base
INSERT INTO horarios (nombre, hora_inicio, hora_fin, tolerancia) VALUES
('Jornada Completa 9-18',   '09:00:00', '18:00:00', 10),
('Jornada Completa 8-17',   '08:00:00', '17:00:00', 10),
('Turno Mañana 7-15',       '07:00:00', '15:00:00', 10),
('Turno Tarde 14-22',       '14:00:00', '22:00:00', 10),
('Media Jornada 9-13',      '09:00:00', '13:00:00', 10),
('Flexible 8-9 entrada',    '08:00:00', '09:00:00', 60);

-- Admin por defecto: admin@empresa.com / Admin1234!
INSERT INTO usuarios (nombre, apellidos, email, password, rol, departamento, id_horario) VALUES
('Admin', 'Sistema', 'admin@empresa.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', 'rrhh', 1);

-- Proyectos de ejemplo
INSERT INTO proyectos (nombre, descripcion, estado, color, fecha_inicio) VALUES
('Portal Web Corporativo',    'Rediseño completo del portal web',       'activo',    '#4F6EF7', '2025-01-01'),
('App Móvil Clientes',        'Aplicación nativa iOS y Android',        'activo',    '#22C55E', '2025-02-01'),
('ERP Interno',               'Migración del sistema ERP',              'pausado',   '#F59E0B', '2024-11-01'),
('Campaña Marketing Q2',      'Campaña digital segundo trimestre',      'activo',    '#EC4899', '2025-04-01'),
('Infraestructura Cloud',     'Migración a infraestructura cloud',      'activo',    '#8B5CF6', '2025-03-01');

SET FOREIGN_KEY_CHECKS = 1;
