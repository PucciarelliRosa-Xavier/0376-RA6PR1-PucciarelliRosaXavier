<?php
// ============================================================
// config/database.php - Configuración de base de datos
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'timecontrol');
define('DB_USER', 'timecontrol_user');       // Cambiar en producción
define('DB_PASS', 'TuPasswordSegura123!');           // Cambiar en producción
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'TimeControl');
define('APP_URL', 'http://localhost/timecontrol');
define('APP_TIMEZONE', 'Europe/Madrid');

// Configuración de email
define('MAIL_HOST', 'smtp.empresa.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'noreply@empresa.com');
define('MAIL_PASS', 'password_smtp');
define('MAIL_FROM_NAME', 'TimeControl Sistema');
define('MAIL_FROM_EMAIL', 'noreply@empresa.com');

// Configuración de sesión
define('SESSION_NAME', 'timecontrol_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 horas

date_default_timezone_set(APP_TIMEZONE);

/**
 * Clase singleton para conexión PDO a MySQL
 */
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Error de conexión a la base de datos']));
            }
        }
        return self::$instance;
    }
}
