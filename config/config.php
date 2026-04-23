<?php
/**
 * TimeControl - Configuración de Base de Datos
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'timecontrol');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'TimeControl');
define('APP_URL', 'http://localhost/timecontrol');
define('APP_VERSION', '1.0.0');

// Configuración de email (SMTP)
define('MAIL_HOST',     'smtp.empresa.com');
define('MAIL_PORT',     587);
define('MAIL_USER',     'timecontrol@empresa.com');
define('MAIL_PASS',     'tu_password_smtp');
define('MAIL_FROM',     'timecontrol@empresa.com');
define('MAIL_FROM_NAME','TimeControl - RRHH');
define('MAIL_SECURE',   'tls'); // tls o ssl

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Entorno (development / production)
define('APP_ENV', 'development');

// Mostrar errores en desarrollo
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
