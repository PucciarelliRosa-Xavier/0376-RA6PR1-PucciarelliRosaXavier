<?php
/**
 * TimeControl - ImputacionController
 * Registro de horas por proyecto
 */

class ImputacionController {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $uid  = $_SESSION['user_id'];
        $mes  = $_GET['mes'] ?? date('Y-m');

        [$y, $m] = explode('-', $mes . '-01');
        $inicio  = "$y-$m-01";
        $fin     = date('Y-m-t', strtotime($inicio));

        // Proyectos del usuario
        $proyectos = $this->db->query(
            "SELECT p.* FROM proyectos p
             INNER JOIN usuario_proyecto up ON up.id_proyecto = p.id
             WHERE up.id_usuario = ? AND p.estado = 'activo' ORDER BY p.nombre",
            [$uid]
        );

        // Imputaciones del mes
        $imputaciones = $this->db->query(
            "SELECT i.*, p.nombre as proyecto_nombre, p.color as proyecto_color
             FROM imputaciones i INNER JOIN proyectos p ON p.id = i.id_proyecto
             WHERE i.id_usuario = ? AND i.fecha BETWEEN ? AND ?
             ORDER BY i.fecha DESC, i.creado_en DESC",
            [$uid, $inicio, $fin]
        );

        // Resumen por proyecto del mes
        $resumen = $this->db->query(
            "SELECT p.nombre, p.color, SUM(i.horas) as total_horas
             FROM imputaciones i INNER JOIN proyectos p ON p.id = i.id_proyecto
             WHERE i.id_usuario = ? AND i.fecha BETWEEN ? AND ?
             GROUP BY i.id_proyecto ORDER BY total_horas DESC",
            [$uid, $inicio, $fin]
        );

        $total_mes = array_sum(array_column($imputaciones, 'horas'));

        require_once __DIR__ . '/../views/employee/imputaciones.php';
    }

    public function guardar(): void {
        header('Content-Type: application/json');

        $uid     = $_SESSION['user_id'];
        $id      = (int)($_POST['id']          ?? 0);
        $pid     = (int)($_POST['id_proyecto'] ?? 0);
        $horas   = (float)($_POST['horas']     ?? 0);
        $fecha   = trim($_POST['fecha']         ?? date('Y-m-d'));
        $desc    = trim($_POST['descripcion']   ?? '');

        if (!$pid || $horas <= 0 || $horas > 24) {
            echo json_encode(['ok'=>false,'msg'=>'Datos inválidos. Las horas deben ser entre 0.5 y 24.']); exit;
        }

        // Verificar que el usuario pertenece al proyecto
        $asignado = $this->db->queryOne(
            "SELECT id FROM usuario_proyecto WHERE id_usuario=? AND id_proyecto=?",
            [$uid, $pid]
        );
        if (!$asignado) {
            echo json_encode(['ok'=>false,'msg'=>'No tienes acceso a ese proyecto']); exit;
        }

        // Verificar que no supera horas del día (máx 12h imputadas)
        $horas_dia = $this->db->queryOne(
            "SELECT COALESCE(SUM(horas),0) as total FROM imputaciones WHERE id_usuario=? AND fecha=? AND id != ?",
            [$uid, $fecha, $id]
        );
        if (($horas_dia['total'] + $horas) > 12) {
            echo json_encode(['ok'=>false,'msg'=>'No puedes imputar más de 12 horas en un día']); exit;
        }

        if ($id > 0) {
            $this->db->execute(
                "UPDATE imputaciones SET id_proyecto=?,horas=?,fecha=?,descripcion=? WHERE id=? AND id_usuario=?",
                [$pid, $horas, $fecha, $desc, $id, $uid]
            );
            echo json_encode(['ok'=>true,'msg'=>'Imputación actualizada']);
        } else {
            $this->db->execute(
                "INSERT INTO imputaciones (id_usuario,id_proyecto,horas,fecha,descripcion) VALUES (?,?,?,?,?)",
                [$uid, $pid, $horas, $fecha, $desc]
            );
            echo json_encode(['ok'=>true,'msg'=>'Imputación guardada','id'=>$this->db->lastInsertId()]);
        }
        exit;
    }

    public function eliminar(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['user_id'];
        $id  = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }
        $this->db->execute("DELETE FROM imputaciones WHERE id=? AND id_usuario=?", [$id, $uid]);
        echo json_encode(['ok'=>true,'msg'=>'Imputación eliminada']);
        exit;
    }
}
