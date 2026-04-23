<?php
/**
 * TimeControl - ProyectoController
 */

class ProyectoController {

    private Database $db;

    public function __construct() {
        AuthController::requireRole(['admin', 'jefe', 'jefe_departamento']);
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $proyectos = $this->db->query(
            "SELECT p.*,
                COUNT(DISTINCT up.id_usuario) as num_empleados,
                COALESCE(SUM(i.horas), 0) as horas_totales
             FROM proyectos p
             LEFT JOIN usuario_proyecto up ON up.id_proyecto = p.id
             LEFT JOIN imputaciones i ON i.id_proyecto = p.id
             GROUP BY p.id ORDER BY p.nombre"
        );
        $usuarios = $this->db->query(
            "SELECT id, nombre, apellidos, departamento FROM usuarios WHERE activo=1 AND rol='empleado' ORDER BY apellidos"
        );
        require_once __DIR__ . '/../views/admin/proyectos.php';
    }

    public function guardar(): void {
        header('Content-Type: application/json');
        AuthController::requireRole(['admin']);

        $id     = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');
        $estado = $_POST['estado'] ?? 'activo';
        $color  = $_POST['color']  ?? '#4F6EF7';
        $f_ini  = $_POST['fecha_inicio'] ?? null;
        $f_fin  = $_POST['fecha_fin']    ?? null;

        if (!$nombre) { echo json_encode(['ok'=>false,'msg'=>'El nombre es obligatorio']); exit; }

        if ($id > 0) {
            $this->db->execute(
                "UPDATE proyectos SET nombre=?,descripcion=?,estado=?,color=?,fecha_inicio=?,fecha_fin=? WHERE id=?",
                [$nombre, $desc, $estado, $color, $f_ini ?: null, $f_fin ?: null, $id]
            );
            echo json_encode(['ok'=>true,'msg'=>'Proyecto actualizado','id'=>$id]);
        } else {
            $this->db->execute(
                "INSERT INTO proyectos (nombre,descripcion,estado,color,fecha_inicio,fecha_fin) VALUES (?,?,?,?,?,?)",
                [$nombre, $desc, $estado, $color, $f_ini ?: null, $f_fin ?: null]
            );
            echo json_encode(['ok'=>true,'msg'=>'Proyecto creado','id'=>$this->db->lastInsertId()]);
        }
        exit;
    }

    public function eliminar(): void {
        header('Content-Type: application/json');
        AuthController::requireRole(['admin']);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }
        $this->db->execute("UPDATE proyectos SET estado='completado' WHERE id=?", [$id]);
        echo json_encode(['ok'=>true,'msg'=>'Proyecto marcado como completado']);
        exit;
    }
}
