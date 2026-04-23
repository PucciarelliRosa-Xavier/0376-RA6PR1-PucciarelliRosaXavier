<?php
/**
 * TimeControl - Front Controller / Router
 * Punto de entrada único de la aplicación
 */

// Iniciar sesión antes de cualquier output
session_start();

// Cargar configuración y dependencias
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// Autoloader simple para controllers y models
spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/models/'      . $class . '.php',
        __DIR__ . '/app/helpers/'     . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ── Routing ──────────────────────────────────────────────────
$action = $_GET['action'] ?? 'dashboard';

// Rutas públicas (sin autenticación)
$public_routes = ['login', 'logout', 'login_process'];

if (!in_array($action, $public_routes) && !isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/index.php?action=login');
    exit;
}

// Despachar al controlador correcto
switch ($action) {

    // AUTH
    case 'login':
        (new AuthController())->showLogin();
        break;
    case 'login_process':
        (new AuthController())->processLogin();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;

    // DASHBOARD (según rol)
    case 'dashboard':
        (new DashboardController())->index();
        break;

    // FICHAJES (API JSON)
    case 'fichar':
        (new FichajeController())->fichar();
        break;
    case 'fichajes_historial':
        (new FichajeController())->historial();
        break;

    // IMPUTACIONES
    case 'imputaciones':
        (new ImputacionController())->index();
        break;
    case 'imputacion_guardar':
        (new ImputacionController())->guardar();
        break;
    case 'imputacion_eliminar':
        (new ImputacionController())->eliminar();
        break;

    // PROYECTOS
    case 'proyectos':
        (new ProyectoController())->index();
        break;
    case 'proyecto_guardar':
        (new ProyectoController())->guardar();
        break;
    case 'proyecto_eliminar':
        (new ProyectoController())->eliminar();
        break;

    // USUARIOS (admin)
    case 'usuarios':
        (new UsuarioController())->index();
        break;
    case 'usuario_guardar':
        (new UsuarioController())->guardar();
        break;
    case 'usuario_eliminar':
        (new UsuarioController())->eliminar();
        break;
    case 'usuario_asignar_proyecto':
        (new UsuarioController())->asignarProyecto();
        break;

    // INFORMES
    case 'informes':
        (new InformeController())->index();
        break;
    case 'informe_datos':
        (new InformeController())->datos();
        break;

    // INCIDENCIAS
    case 'incidencias':
        (new IncidenciaController())->index();
        break;
    case 'incidencia_resolver':
        (new IncidenciaController())->resolver();
        break;

    // HORARIOS (admin)
    case 'horarios':
        (new HorarioController())->index();
        break;
    case 'horario_guardar':
        (new HorarioController())->guardar();
        break;

    // API: estado actual (JSON)
    case 'api_estado':
        (new FichajeController())->estadoActual();
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/app/views/shared/404.php';
}
