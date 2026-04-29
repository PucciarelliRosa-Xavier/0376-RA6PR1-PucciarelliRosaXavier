<?php
// ============================================================
// index.php - Router principal de la aplicación (Front Controller)
// ============================================================

session_name('timecontrol_session');
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Fichaje.php';
require_once __DIR__ . '/app/models/Proyecto.php';
require_once __DIR__ . '/app/models/Imputacion.php';
require_once __DIR__ . '/app/models/Incidencia.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/FichajeController.php';
require_once __DIR__ . '/app/controllers/ProyectoController.php';
require_once __DIR__ . '/app/controllers/ImputacionController.php';
require_once __DIR__ . '/app/controllers/AdminController.php';
require_once __DIR__ . '/app/controllers/ManagerController.php';
require_once __DIR__ . '/app/controllers/InformeController.php';
require_once __DIR__ . '/mail/Mailer.php';

// Obtener la acción de la URL
$action = $_GET['action'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

// Rutas públicas (sin autenticación)
$publicRoutes = ['login', 'logout', 'do_login'];

// Verificar autenticación para rutas privadas
if (!in_array($action, $publicRoutes) && !isset($_SESSION['user_id'])) {
    header('Location: ?action=login');
    exit;
}

// Router
try {
    switch ($action) {

        // ---- AUTENTICACIÓN ----
        case 'login':
            $controller = new AuthController();
            $controller->showLogin();
            break;

        case 'do_login':
            $controller = new AuthController();
            $controller->doLogin();
            break;

        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;

        // ---- DASHBOARD (redirige según rol) ----
        case 'dashboard':
            $rol = $_SESSION['user_rol'] ?? 'empleado';
            if ($rol === 'admin') {
                $controller = new AdminController();
                $controller->dashboard();
            } elseif (in_array($rol, ['jefe', 'jefe_departamento'])) {
                $controller = new ManagerController();
                $controller->dashboard();
            } else {
                $controller = new FichajeController();
                $controller->dashboard();
            }
            break;

        // ---- FICHAJES ----
        case 'fichar':
            $controller = new FichajeController();
            $controller->fichar();
            break;

        case 'historial':
            $controller = new FichajeController();
            $controller->historial();
            break;

        // ---- PROYECTOS E IMPUTACIONES ----
        case 'imputar':
            $controller = new ImputacionController();
            $controller->index();
            break;

        case 'guardar_imputacion':
            $controller = new ImputacionController();
            $controller->guardar();
            break;

        case 'mis_imputaciones':
            $controller = new ImputacionController();
            $controller->misImputaciones();
            break;

        // ---- PANEL JEFE / MANAGER ----
        case 'manager_empleados':
            requireRole(['jefe', 'jefe_departamento', 'admin']);
            $controller = new ManagerController();
            $controller->empleados();
            break;

        case 'manager_incidencias':
            requireRole(['jefe', 'jefe_departamento', 'admin']);
            $controller = new ManagerController();
            $controller->incidencias();
            break;

        case 'resolver_incidencia':
            requireRole(['jefe', 'jefe_departamento', 'admin']);
            $controller = new ManagerController();
            $controller->resolverIncidencia();
            break;

        // ---- INFORMES ----
        case 'informes':
            requireRole(['jefe', 'jefe_departamento', 'admin']);
            $controller = new InformeController();
            $controller->index();
            break;

        case 'informe_json':
            requireRole(['jefe', 'jefe_departamento', 'admin']);
            $controller = new InformeController();
            $controller->getInformeJson();
            break;

        // ---- ADMINISTRACIÓN ----
        case 'admin_usuarios':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->usuarios();
            break;

        case 'admin_nuevo_usuario':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->nuevoUsuario();
            break;

        case 'admin_guardar_usuario':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->guardarUsuario();
            break;

        case 'admin_editar_usuario':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->editarUsuario();
            break;

        case 'admin_eliminar_usuario':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->eliminarUsuario();
            break;

        case 'admin_proyectos':
            requireRole(['admin', 'jefe', 'jefe_departamento']);
            $controller = new AdminController();
            $controller->proyectos();
            break;

        case 'admin_guardar_proyecto':
            requireRole(['admin', 'jefe', 'jefe_departamento']);
            $controller = new AdminController();
            $controller->guardarProyecto();
            break;

        case 'admin_asignar_proyecto':
            requireRole(['admin', 'jefe', 'jefe_departamento']);
            $controller = new AdminController();
            $controller->asignarProyecto();
            break;

        case 'admin_horarios':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->horarios();
            break;

        case 'admin_guardar_horario':
            requireRole(['admin']);
            $controller = new AdminController();
            $controller->guardarHorario();
            break;

        default:
            http_response_code(404);
            include __DIR__ . '/app/views/shared/404.php';
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    include __DIR__ . '/app/views/shared/error.php';
}

/**
 * Función helper: verificar rol mínimo requerido
 */
function requireRole(array $roles): void {
    $userRol = $_SESSION['user_rol'] ?? '';
    if (!in_array($userRol, $roles)) {
        header('Location: ?action=dashboard');
        exit;
    }
}
