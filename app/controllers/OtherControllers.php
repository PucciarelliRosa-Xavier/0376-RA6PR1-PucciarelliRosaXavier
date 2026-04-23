<?php
/**
 * TimeControl - InformeController
 */

class InformeController {

    private Database $db;

    public function __construct() {
        AuthController::requireRole(['admin','jefe','jefe_departamento']);
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $proyectos = $this->db->query("SELECT id, nombre FROM proyectos ORDER BY nombre");
        $usuarios  = $this->db->query(
            "SELECT id, nombre, apellidos, departamento FROM usuarios WHERE activo=1 AND rol='empleado' ORDER BY apellidos"
        );
        require_once __DIR__ . '/../views/boss/informes.php';
    }

    /**
     * Datos del informe en JSON (llamada AJAX)
     */
    public function datos(): void {
        header('Content-Type: application/json');

        $tipo    = $_GET['tipo']    ?? 'diario';   // diario, semanal, mensual
        $fecha   = $_GET['fecha']   ?? date('Y-m-d');
        $uid_fil = (int)($_GET['usuario']  ?? 0);
        $pid_fil = (int)($_GET['proyecto'] ?? 0);

        // Calcular rango de fechas
        switch ($tipo) {
            case 'semanal':
                $inicio = date('Y-m-d', strtotime('monday this week', strtotime($fecha)));
                $fin    = date('Y-m-d', strtotime('sunday this week', strtotime($fecha)));
                break;
            case 'mensual':
                $inicio = date('Y-m-01', strtotime($fecha));
                $fin    = date('Y-m-t',  strtotime($fecha));
                break;
            default: // diario
                $inicio = $fin = $fecha;
        }

        // Construcción de filtros
        $where_u  = $uid_fil ? "AND i.id_usuario = $uid_fil"  : '';
        $where_p  = $pid_fil ? "AND i.id_proyecto = $pid_fil" : '';

        // Horas por usuario y proyecto
        $rows = $this->db->query(
            "SELECT u.id as uid, u.nombre, u.apellidos, u.departamento,
                    p.id as pid, p.nombre as proyecto, p.color,
                    SUM(i.horas) as horas, COUNT(i.id) as registros
             FROM imputaciones i
             INNER JOIN usuarios u ON u.id = i.id_usuario
             INNER JOIN proyectos p ON p.id = i.id_proyecto
             WHERE i.fecha BETWEEN ? AND ? $where_u $where_p
             GROUP BY i.id_usuario, i.id_proyecto
             ORDER BY u.apellidos, p.nombre",
            [$inicio, $fin]
        );

        // Fichajes (asistencia)
        $fichajes_rows = $this->db->query(
            "SELECT u.id as uid, u.nombre, u.apellidos,
                    DATE(f.timestamp) as fecha,
                    MIN(CASE WHEN f.tipo='entrada' THEN f.timestamp END) as primera_entrada,
                    MAX(CASE WHEN f.tipo='salida'  THEN f.timestamp END) as ultima_salida
             FROM fichajes f INNER JOIN usuarios u ON u.id = f.id_usuario
             WHERE DATE(f.timestamp) BETWEEN ? AND ? " . ($uid_fil ? "AND f.id_usuario = $uid_fil" : '') . "
             GROUP BY f.id_usuario, DATE(f.timestamp)
             ORDER BY fecha, u.apellidos",
            [$inicio, $fin]
        );

        // Calcular horas para cada row de fichajes
        foreach ($fichajes_rows as &$fr) {
            if ($fr['primera_entrada'] && $fr['ultima_salida']) {
                $secs = strtotime($fr['ultima_salida']) - strtotime($fr['primera_entrada']);
                $fr['horas_presencia'] = round($secs / 3600, 2);
            } else {
                $fr['horas_presencia'] = null;
            }
        }

        // Incidencias del período
        $incidencias = $this->db->query(
            "SELECT i.*, u.nombre, u.apellidos FROM incidencias i
             INNER JOIN usuarios u ON u.id = i.id_usuario
             WHERE i.fecha BETWEEN ? AND ? $where_u
             ORDER BY i.fecha DESC",
            [$inicio, $fin]
        );

        echo json_encode([
            'ok'           => true,
            'periodo'      => ['inicio' => $inicio, 'fin' => $fin, 'tipo' => $tipo],
            'imputaciones' => $rows,
            'fichajes'     => $fichajes_rows,
            'incidencias'  => $incidencias,
            'totales'      => [
                'horas'      => array_sum(array_column($rows, 'horas')),
                'empleados'  => count(array_unique(array_column($rows, 'uid'))),
                'proyectos'  => count(array_unique(array_column($rows, 'pid'))),
            ]
        ]);
        exit;
    }
}

// ============================================================

/**
 * TimeControl - IncidenciaController
 */

class IncidenciaController {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthController::requireRole(['admin','jefe','jefe_departamento']);

        $estado = $_GET['estado'] ?? '';
        $tipo   = $_GET['tipo']   ?? '';
        $desde  = $_GET['desde']  ?? date('Y-m-01');
        $hasta  = $_GET['hasta']  ?? date('Y-m-d');

        $where  = ['i.fecha BETWEEN ? AND ?'];
        $params = [$desde, $hasta];

        if ($estado) { $where[] = "i.estado = ?"; $params[] = $estado; }
        if ($tipo)   { $where[] = "i.tipo = ?";   $params[] = $tipo; }

        $incidencias = $this->db->query(
            "SELECT i.*, u.nombre, u.apellidos, u.departamento, u.email
             FROM incidencias i INNER JOIN usuarios u ON u.id = i.id_usuario
             WHERE " . implode(' AND ', $where) . "
             ORDER BY i.creado_en DESC",
            $params
        );

        require_once __DIR__ . '/../views/boss/incidencias.php';
    }

    public function resolver(): void {
        header('Content-Type: application/json');
        AuthController::requireRole(['admin','jefe','jefe_departamento']);

        $id     = (int)($_POST['id']     ?? 0);
        $estado = $_POST['estado']       ?? 'resuelta';
        if (!$id) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }

        $this->db->execute(
            "UPDATE incidencias SET estado=? WHERE id=?",
            [$estado, $id]
        );
        echo json_encode(['ok'=>true,'msg'=>'Incidencia actualizada']);
        exit;
    }
}

// ============================================================

/**
 * TimeControl - HorarioController
 */

class HorarioController {

    private Database $db;

    public function __construct() {
        AuthController::requireRole(['admin']);
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $horarios = $this->db->query(
            "SELECT h.*, COUNT(u.id) as num_usuarios
             FROM horarios h LEFT JOIN usuarios u ON u.id_horario = h.id
             GROUP BY h.id ORDER BY h.nombre"
        );
        require_once __DIR__ . '/../views/admin/horarios.php';
    }

    public function guardar(): void {
        header('Content-Type: application/json');

        $id          = (int)($_POST['id'] ?? 0);
        $nombre      = trim($_POST['nombre']      ?? '');
        $hora_inicio = trim($_POST['hora_inicio'] ?? '');
        $hora_fin    = trim($_POST['hora_fin']    ?? '');
        $tolerancia  = (int)($_POST['tolerancia'] ?? 10);

        if (!$nombre || !$hora_inicio || !$hora_fin) {
            echo json_encode(['ok'=>false,'msg'=>'Rellena todos los campos']); exit;
        }

        if ($id > 0) {
            $this->db->execute(
                "UPDATE horarios SET nombre=?,hora_inicio=?,hora_fin=?,tolerancia=? WHERE id=?",
                [$nombre, $hora_inicio, $hora_fin, $tolerancia, $id]
            );
            echo json_encode(['ok'=>true,'msg'=>'Horario actualizado']);
        } else {
            $this->db->execute(
                "INSERT INTO horarios (nombre,hora_inicio,hora_fin,tolerancia) VALUES (?,?,?,?)",
                [$nombre, $hora_inicio, $hora_fin, $tolerancia]
            );
            echo json_encode(['ok'=>true,'msg'=>'Horario creado','id'=>$this->db->lastInsertId()]);
        }
        exit;
    }
}
