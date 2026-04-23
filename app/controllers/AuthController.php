<?php
/**
 * TimeControl - AuthController
 * Gestión de autenticación y sesiones
 */

class AuthController {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function showLogin(): void {
        // Si ya está logado, redirigir
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/index.php?action=dashboard');
            exit;
        }
        require_once __DIR__ . '/../views/shared/login.php';
    }

    public function processLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?action=login');
            exit;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor, rellena todos los campos.';
            header('Location: ' . APP_URL . '/index.php?action=login');
            exit;
        }

        $user = $this->db->queryOne(
            'SELECT u.*, h.hora_inicio, h.hora_fin, h.tolerancia, h.nombre as horario_nombre
             FROM usuarios u
             LEFT JOIN horarios h ON h.id = u.id_horario
             WHERE u.email = ? AND u.activo = 1',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = 'Email o contraseña incorrectos.';
            header('Location: ' . APP_URL . '/index.php?action=login');
            exit;
        }

        // Regenerar ID de sesión (prevenir session fixation)
        session_regenerate_id(true);

        // Guardar datos en sesión
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['user_nombre']  = $user['nombre'] . ' ' . $user['apellidos'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['user_rol']     = $user['rol'];
        $_SESSION['user_depto']   = $user['departamento'];
        $_SESSION['user_horario'] = [
            'nombre'    => $user['horario_nombre'],
            'inicio'    => $user['hora_inicio'],
            'fin'       => $user['hora_fin'],
            'tolerancia'=> $user['tolerancia'],
        ];

        // Actualizar último acceso
        $this->db->execute(
            'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?',
            [$user['id']]
        );

        header('Location: ' . APP_URL . '/index.php?action=dashboard');
        exit;
    }

    public function logout(): void {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/index.php?action=login');
        exit;
    }

    /**
     * Verifica que el usuario tenga uno de los roles permitidos
     */
    public static function requireRole(array $roles): void {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], $roles)) {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . APP_URL . '/index.php?action=login');
            } else {
                http_response_code(403);
                require_once __DIR__ . '/../views/shared/403.php';
            }
            exit;
        }
    }
}
