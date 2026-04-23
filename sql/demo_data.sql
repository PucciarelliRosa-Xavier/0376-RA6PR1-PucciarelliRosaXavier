-- ============================================================
-- TimeControl — Datos de Demo
-- Importar DESPUÉS del schema.sql principal
-- ============================================================

USE timecontrol;

-- ── Usuarios de demo (password: Demo1234! para todos) ────────
-- Hash bcrypt de "Demo1234!"
SET @hash = '$2y$12$LcRkfU.r9v7MWYDsG2mVzeijIXfZ5AQFEVEBTxB.nKFMlyqfNNGEa';

INSERT INTO usuarios (nombre, apellidos, email, password, rol, departamento, id_horario, activo) VALUES
-- Administrador (ya existe admin@empresa.com)
-- Jefes
('Carlos',   'Martínez López',    'carlos.jefe@empresa.com',    @hash, 'jefe',              'desarrollo',   1, 1),
('Laura',    'Sánchez Ruiz',      'laura.jd@empresa.com',       @hash, 'jefe_departamento', 'diseno',       1, 1),
-- Empleados - Desarrollo
('María',    'García Fernández',  'maria.garcia@empresa.com',   @hash, 'empleado', 'desarrollo',   1, 1),
('Pedro',    'López Martínez',    'pedro.lopez@empresa.com',    @hash, 'empleado', 'desarrollo',   1, 1),
('Ana',      'Rodríguez Torres',  'ana.rodriguez@empresa.com',  @hash, 'empleado', 'desarrollo',   2, 1),
('Javier',   'González Castro',   'javier.gonzalez@empresa.com',@hash, 'empleado', 'desarrollo',   1, 1),
('Elena',    'Martín Díaz',       'elena.martin@empresa.com',   @hash, 'empleado', 'desarrollo',   1, 1),
-- Empleados - Diseño
('Sofia',    'Jiménez Moreno',    'sofia.jimenez@empresa.com',  @hash, 'empleado', 'diseno',       1, 1),
('Diego',    'Álvarez Romero',    'diego.alvarez@empresa.com',  @hash, 'empleado', 'diseno',       1, 1),
-- Empleados - Contabilidad
('Carmen',   'Ruiz Navarro',      'carmen.ruiz@empresa.com',    @hash, 'empleado', 'contabilidad', 1, 1),
('Roberto',  'Torres Soria',      'roberto.torres@empresa.com', @hash, 'empleado', 'contabilidad', 2, 1),
-- Empleados - RRHH
('Patricia', 'Núñez Herrero',     'patricia.nunez@empresa.com', @hash, 'empleado', 'rrhh',         1, 1);

-- ── Asignar proyectos a empleados ────────────────────────────
INSERT INTO usuario_proyecto (id_usuario, id_proyecto) VALUES
-- María García → Portal Web + App Móvil
(3, 1), (3, 2),
-- Pedro López → Portal Web + ERP
(4, 1), (4, 3),
-- Ana Rodríguez → App Móvil + Cloud
(5, 2), (5, 5),
-- Javier González → ERP + Cloud
(6, 3), (6, 5),
-- Elena Martín → Portal Web
(7, 1),
-- Sofia → Marketing
(8, 4),
-- Diego → Marketing + Portal Web
(9, 4), (9, 1),
-- Carmen → ERP
(10, 3),
-- Roberto → ERP
(11, 3),
-- Carlos (jefe) → Todos los proyectos de desarrollo
(2, 1), (2, 2), (2, 3), (2, 5);

-- ── Fichajes de ejemplo (últimos 7 días laborables) ──────────
-- Usamos procedimiento para generar datos realistas

DELIMITER //
CREATE PROCEDURE generar_fichajes_demo()
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE uid INT;
    DECLARE fecha_base DATE;
    DECLARE hora_e TIME;
    DECLARE hora_s TIME;
    DECLARE minutos_retraso INT;

    -- Para cada uno de los 7 últimos días laborables
    WHILE i < 7 DO
        SET fecha_base = DATE_SUB(CURDATE(), INTERVAL i DAY);

        -- Saltar fines de semana
        IF DAYOFWEEK(fecha_base) NOT IN (1, 7) THEN

            -- Para cada empleado (id 3..12)
            SET uid = 3;
            WHILE uid <= 12 DO
                -- Retraso aleatorio entre -5 y +30 minutos
                SET minutos_retraso = FLOOR(RAND() * 36) - 5;

                -- Entrada
                SET hora_e = ADDTIME('09:00:00', SEC_TO_TIME(minutos_retraso * 60));

                -- Salida: 8h después de la entrada ±30min
                SET hora_s = ADDTIME(hora_e, SEC_TO_TIME((480 + FLOOR(RAND()*60) - 30) * 60));

                -- No insertar salida en un 10% de los casos (simular olvido)
                IF RAND() > 0.10 THEN
                    INSERT IGNORE INTO fichajes (id_usuario, tipo, timestamp, ip)
                        VALUES (uid, 'entrada', CONCAT(fecha_base, ' ', hora_e), '192.168.1.1');
                    INSERT IGNORE INTO fichajes (id_usuario, tipo, timestamp, ip)
                        VALUES (uid, 'salida',  CONCAT(fecha_base, ' ', hora_s), '192.168.1.1');

                    -- Registrar retraso si aplica
                    IF minutos_retraso > 10 THEN
                        INSERT IGNORE INTO incidencias (id_usuario, tipo, descripcion, fecha, estado)
                        VALUES (uid, 'retraso',
                            CONCAT('Fichaje entrada con ', minutos_retraso, ' minutos de retraso'),
                            fecha_base, 'pendiente');
                    END IF;
                ELSE
                    -- Solo entrada, sin salida (olvido)
                    INSERT IGNORE INTO fichajes (id_usuario, tipo, timestamp, ip)
                        VALUES (uid, 'entrada', CONCAT(fecha_base, ' ', hora_e), '192.168.1.1');
                    INSERT IGNORE INTO incidencias (id_usuario, tipo, descripcion, fecha, estado)
                    VALUES (uid, 'olvido_salida',
                        CONCAT('El empleado no fichó la salida el día ', fecha_base),
                        fecha_base, 'pendiente');
                END IF;

                SET uid = uid + 1;
            END WHILE;
        END IF;

        SET i = i + 1;
    END WHILE;
END//
DELIMITER ;

CALL generar_fichajes_demo();
DROP PROCEDURE IF EXISTS generar_fichajes_demo;

-- ── Imputaciones de ejemplo (mes actual) ─────────────────────
INSERT INTO imputaciones (id_usuario, id_proyecto, horas, fecha, descripcion) VALUES
(3, 1, 4.0, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  'Desarrollo de componentes React para el portal'),
(3, 2, 3.5, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  'Revisión de diseño de la app móvil'),
(4, 1, 5.0, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  'Integración de API REST en el portal'),
(4, 3, 2.5, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  'Reunión de planificación ERP'),
(5, 2, 6.0, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  'Desarrollo módulo de notificaciones push'),
(5, 5, 2.0, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  'Migración de servicios a AWS'),
(6, 3, 7.5, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  'Análisis de módulo financiero ERP'),
(7, 1, 4.0, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  'Diseño de páginas de producto'),
(8, 4, 5.0, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  'Maquetación de banners para campaña Q2'),
(9, 4, 3.0, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  'Diseño de piezas para redes sociales'),
(9, 1, 2.0, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  'Revisión de estilos del portal'),
(3, 1, 5.5, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  'Testing y corrección de bugs en portal'),
(4, 1, 4.0, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  'Optimización de rendimiento frontend'),
(6, 5, 3.0, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  'Configuración de contenedores Docker'),
(5, 2, 7.0, DATE_SUB(CURDATE(), INTERVAL 5 DAY),  'Desarrollo de pantalla de onboarding'),
(10, 3, 5.0, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Mapeo de procesos contables en ERP'),
(11, 3, 6.0, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Validación de cálculos tributarios'),
(3, 2, 2.0, CURDATE(),                             'Revisión de pull requests app móvil'),
(4, 1, 3.5, CURDATE(),                             'Implementación de autenticación OAuth');

SELECT '✓ Datos de demo importados correctamente' AS resultado;
