<?php
/**
 * TimeControl - UsuarioController
 * CRUD de usuarios (solo admin)
 */

class UsuarioController {

    private Database $db;

    public function __construct() {
        AuthController::requireRole(['admin']);
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $search = trim($_GET['search'] ?? '');
        $depto  = $_GET['departamento'] ?? '';
        $rol    = $_GET['rol'] ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = "(u.nombre LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ?)";
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like]);
        }
        if ($depto) { $where[] = "u.departamento = ?"; $params[] = $depto; }
        if ($rol)   { $where[] = "u.rol = ?";           $params[] = $rol; }

        $sql = "SELECT u.*, h.nombre as horario_nombre
                FROM usuarios u
                LEFT JOIN horarios h ON h.id = u.id_horario
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.apellidos, u.nombre";

        $usuarios  = $this->db->query($sql, $params);
        $horarios  = $this->db->query("SELECT * FROM horarios ORDER BY nombre");
        $proyectos = $this->db->query("SELECT * FROM proyectos WHERE estado = 'activo' ORDER BY nombre");

        require_once __DIR__ . '/../views/admin/usuarios.php';
    }

    public function guardar(): void {
        header('Content-Type: application/json');

        $id       = (int)($_POST['id'] ?? 0);
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellidos= trim($_POST['apellidos'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $rol      = $_POST['rol']           ?? 'empleado';
        $depto    = $_POST['departamento']  ?? 'desarrollo';
        $horario  = (int)($_POST['id_horario'] ?? 0) ?: null;
        $activo   = isset($_POST['activo']) ? 1 : 0;
        $password = trim($_POST['password'] ?? '');

        if (!$nombre || !$apellidos || !$email) {
            echo json_encode(['ok' => false, 'msg' => 'Faltan campos obligatorios']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'msg' => 'Email inválido']);
            exit;
        }

        $roles_validos = ['admin','empleado','jefe','jefe_departamento'];
        if (!in_array($rol, $roles_validos)) {
            echo json_encode(['ok' => false, 'msg' => 'Rol inválido']); exit;
        }

        if ($id > 0) {
            // ACTUALIZAR
            $sql    = "UPDATE usuarios SET nombre=?, apellidos=?, email=?, rol=?, departamento=?, id_horario=?, activo=?";
            $params = [$nombre, $apellidos, $email, $rol, $depto, $horario, $activo];
            if ($password) {
                $sql .= ", password=?";
                $params[] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            $sql .= " WHERE id=?";
            $params[] = $id;
            $this->db->execute($sql, $params);
            echo json_encode(['ok' => true, 'msg' => 'Usuario actualizado', 'id' => $id]);
        } else {
            // CREAR
            if (!$password) {
                echo json_encode(['ok' => false, 'msg' => 'La contraseña es obligatoria al crear un usuario']); exit;
            }
            // Verificar email único
            $exists = $this->db->queryOne("SELECT id FROM usuarios WHERE email = ?", [$email]);
            if ($exists) {
                echo json_encode(['ok' => false, 'msg' => 'Ya existe un usuario con ese email']); exit;
            }
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->db->execute(
                "INSERT INTO usuarios (nombre, apellidos, email, password, rol, departamento, id_horario, activo) VALUES (?,?,?,?,?,?,?,?)",
                [$nombre, $apellidos, $email, $hash, $rol, $depto, $horario, $activo]
            );
            $new_id = $this->db->lastInsertId();
            echo json_encode(['ok' => true, 'msg' => 'Usuario creado', 'id' => $new_id]);
        }
        exit;
    }

    public function eliminar(): void {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['ok' => false, 'msg' => 'ID inválido']); exit; }

        // No eliminar el último admin
        $admin_count = $this->db->queryOne("SELECT COUNT(*) as cnt FROM usuarios WHERE rol='admin' AND activo=1");
        $user = $this->db->queryOne("SELECT rol FROM usuarios WHERE id=?", [$id]);
        if ($user['rol'] === 'admin' && $admin_count['cnt'] <= 1) {
            echo json_encode(['ok' => false, 'msg' => 'No puedes eliminar el único administrador']); exit;
        }

        $this->db->execute("UPDATE usuarios SET activo = 0 WHERE id = ?", [$id]);
        echo json_encode(['ok' => true, 'msg' => 'Usuario desactivado']);
        exit;
    }

    public function asignarProyecto(): void {
        header('Content-Type: application/json');
        $uid     = (int)($_POST['id_usuario']  ?? 0);
        $pid     = (int)($_POST['id_proyecto'] ?? 0);
        $accion  = $_POST['accion'] ?? 'asignar'; // asignar | quitar

        if (!$uid || !$pid) {
            echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']); exit;
        }

        if ($accion === 'asignar') {
            // INSERT IGNORE para evitar duplicados
            try {
                $this->db->execute(
                    "INSERT IGNORE INTO usuario_proyecto (id_usuario, id_proyecto) VALUES (?,?)",
                    [$uid, $pid]
                );
                echo json_encode(['ok' => true, 'msg' => 'Asignación realizada']);
            } catch (Exception $e) {
                echo json_encode(['ok' => false, 'msg' => 'Error al asignar']);
            }
        } else {
            $this->db->execute(
                "DELETE FROM usuario_proyecto WHERE id_usuario = ? AND id_proyecto = ?",
                [$uid, $pid]
            );
            echo json_encode(['ok' => true, 'msg' => 'Asignación eliminada']);
        }
        exit;
    }
}
