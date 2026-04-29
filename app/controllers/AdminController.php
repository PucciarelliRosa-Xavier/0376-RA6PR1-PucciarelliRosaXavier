<?php
// ============================================================
// app/controllers/AdminController.php
// ============================================================

class AdminController {
    private Usuario $usuarioModel;
    private Proyecto $proyectoModel;
    private Incidencia $incidenciaModel;

    public function __construct() {
        $this->usuarioModel    = new Usuario();
        $this->proyectoModel   = new Proyecto();
        $this->incidenciaModel = new Incidencia();
    }

    public function dashboard(): void {
        $totalUsuarios    = count($this->usuarioModel->getAll(['activo' => 1]));
        $totalProyectos   = count($this->proyectoModel->getAll('activo'));
        $incidenciasPend  = $this->incidenciaModel->contarPendientes();
        $empleados        = $this->usuarioModel->getAll(['activo' => 1]);
        $proyectos        = $this->proyectoModel->getAll();
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    public function usuarios(): void {
        $usuarios      = $this->usuarioModel->getAll();
        $departamentos = $this->usuarioModel->getDepartamentos();
        $horarios      = $this->usuarioModel->getHorarios();
        $mensaje       = $_SESSION['admin_msg'] ?? null;
        $error         = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_msg'], $_SESSION['admin_error']);
        include __DIR__ . '/../views/admin/usuarios.php';
    }

    public function nuevoUsuario(): void {
        $departamentos = $this->usuarioModel->getDepartamentos();
        $horarios      = $this->usuarioModel->getHorarios();
        include __DIR__ . '/../views/admin/nuevo_usuario.php';
    }

    public function guardarUsuario(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?action=admin_usuarios'); exit; }

        $data = [
            'nombre'          => trim($_POST['nombre'] ?? ''),
            'apellidos'       => trim($_POST['apellidos'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'password'        => $_POST['password'] ?? '',
            'rol'             => $_POST['rol'] ?? 'empleado',
            'id_departamento' => (int)($_POST['id_departamento'] ?? 0),
            'id_horario'      => (int)($_POST['id_horario'] ?? 0),
            'activo'          => 1,
        ];

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->usuarioModel->update($id, $data);
            $_SESSION['admin_msg'] = 'Usuario actualizado correctamente.';
        } else {
            if (empty($data['password'])) { $_SESSION['admin_error'] = 'La contraseña es obligatoria.'; header('Location: ?action=admin_nuevo_usuario'); exit; }
            $this->usuarioModel->create($data);
            $_SESSION['admin_msg'] = 'Usuario creado correctamente.';
        }
        header('Location: ?action=admin_usuarios');
        exit;
    }

    public function editarUsuario(): void {
        $id     = (int)($_GET['id'] ?? 0);
        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) { header('Location: ?action=admin_usuarios'); exit; }
        $departamentos = $this->usuarioModel->getDepartamentos();
        $horarios      = $this->usuarioModel->getHorarios();
        include __DIR__ . '/../views/admin/editar_usuario.php';
    }

    public function eliminarUsuario(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $id !== $_SESSION['user_id']) {
            $this->usuarioModel->delete($id);
            $_SESSION['admin_msg'] = 'Usuario desactivado correctamente.';
        }
        header('Location: ?action=admin_usuarios');
        exit;
    }

    public function proyectos(): void {
        $proyectos = $this->proyectoModel->getAll();
        $usuarios  = $this->usuarioModel->getAll(['activo' => 1]);
        $mensaje   = $_SESSION['admin_msg'] ?? null;
        unset($_SESSION['admin_msg']);
        include __DIR__ . '/../views/admin/proyectos.php';
    }

    public function guardarProyecto(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?action=admin_proyectos'); exit; }
        $data = [
            'nombre'         => trim($_POST['nombre'] ?? ''),
            'descripcion'    => trim($_POST['descripcion'] ?? ''),
            'estado'         => $_POST['estado'] ?? 'activo',
            'fecha_inicio'   => $_POST['fecha_inicio'] ?: null,
            'fecha_fin'      => $_POST['fecha_fin'] ?: null,
            'id_responsable' => (int)($_POST['id_responsable'] ?? 0) ?: null,
        ];
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->proyectoModel->update($id, $data); }
        else { $this->proyectoModel->create($data); }
        $_SESSION['admin_msg'] = 'Proyecto guardado correctamente.';
        header('Location: ?action=admin_proyectos');
        exit;
    }

    public function asignarProyecto(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?action=admin_proyectos'); exit; }
        $idUsuario  = (int)($_POST['id_usuario'] ?? 0);
        $idProyecto = (int)($_POST['id_proyecto'] ?? 0);
        if ($idUsuario > 0 && $idProyecto > 0) {
            $this->proyectoModel->asignarUsuario($idUsuario, $idProyecto);
            $_SESSION['admin_msg'] = 'Usuario asignado al proyecto.';
        }
        header('Location: ?action=admin_proyectos');
        exit;
    }

    public function horarios(): void {
        $db       = Database::getConnection();
        $horarios = $db->query("SELECT * FROM horarios ORDER BY nombre")->fetchAll();
        $mensaje  = $_SESSION['admin_msg'] ?? null;
        unset($_SESSION['admin_msg']);
        include __DIR__ . '/../views/admin/horarios.php';
    }

    public function guardarHorario(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?action=admin_horarios'); exit; }
        $db = Database::getConnection();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            $_POST['nombre'], $_POST['hora_entrada'], $_POST['hora_salida'],
            (int)($_POST['tolerancia_minutos'] ?? 15)
        ];
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE horarios SET nombre=?,hora_entrada=?,hora_salida=?,tolerancia_minutos=? WHERE id=?");
            $data[] = $id;
        } else {
            $stmt = $db->prepare("INSERT INTO horarios (nombre,hora_entrada,hora_salida,tolerancia_minutos) VALUES (?,?,?,?)");
        }
        $stmt->execute($data);
        $_SESSION['admin_msg'] = 'Horario guardado.';
        header('Location: ?action=admin_horarios');
        exit;
    }
}
