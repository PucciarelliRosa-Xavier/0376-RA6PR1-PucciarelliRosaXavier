<?php
// ============================================================
// app/controllers/AuthController.php
// ============================================================

class AuthController {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function showLogin(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: ?action=dashboard');
            exit;
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        include __DIR__ . '/../views/auth/login.php';
    }

    public function doLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor, introduce email y contraseña.';
            header('Location: ?action=login');
            exit;
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        // Password de demo: "password" (hash de Laravel/PHP por defecto en los seeds)
        // El hash de los seeds es password_hash('password', PASSWORD_BCRYPT)
        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $_SESSION['login_error'] = 'Credenciales incorrectas. Inténtalo de nuevo.';
            header('Location: ?action=login');
            exit;
        }

        // Crear sesión
        session_regenerate_id(true);
        $_SESSION['user_id']       = $usuario['id'];
        $_SESSION['user_nombre']   = $usuario['nombre'] . ' ' . $usuario['apellidos'];
        $_SESSION['user_email']    = $usuario['email'];
        $_SESSION['user_rol']      = $usuario['rol'];
        $_SESSION['user_depto']    = $usuario['id_departamento'];
        $_SESSION['user_horario']  = [
            'hora_entrada'       => $usuario['hora_entrada'],
            'hora_salida'        => $usuario['hora_salida'],
            'tolerancia_minutos' => $usuario['tolerancia_minutos'],
        ];

        header('Location: ?action=dashboard');
        exit;
    }

    public function logout(): void {
        session_destroy();
        header('Location: ?action=login');
        exit;
    }
}
