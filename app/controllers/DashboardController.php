<?php
/**
 * TimeControl - DashboardController
 * Muestra el dashboard según el rol del usuario
 */

class DashboardController {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $rol = $_SESSION['user_rol'];

        switch ($rol) {
            case 'admin':
                $this->dashboardAdmin();
                break;
            case 'jefe':
            case 'jefe_departamento':
                $this->dashboardJefe();
                break;
            default:
                $this->dashboardEmpleado();
        }
    }

    // ── Dashboard Empleado ────────────────────────────────────
    private function dashboardEmpleado(): void {
        $uid   = $_SESSION['user_id'];
        $today = date('Y-m-d');

        // Fichajes de hoy
        $fichajes_hoy = $this->db->query(
            "SELECT * FROM fichajes WHERE id_usuario = ? AND DATE(timestamp) = ? ORDER BY timestamp",
            [$uid, $today]
        );

        // Calcular horas trabajadas hoy
        $horas_hoy = FichajeModel::calcularHorasTrabajadas($fichajes_hoy);

        // Estado actual (dentro/fuera)
        $ultimo_fichaje = $this->db->queryOne(
            "SELECT * FROM fichajes WHERE id_usuario = ? ORDER BY timestamp DESC LIMIT 1",
            [$uid]
        );
        $dentro = $ultimo_fichaje && $ultimo_fichaje['tipo'] === 'entrada';

        // Proyectos asignados
        $proyectos = $this->db->query(
            "SELECT p.* FROM proyectos p
             INNER JOIN usuario_proyecto up ON up.id_proyecto = p.id
             WHERE up.id_usuario = ? AND p.estado = 'activo'
             ORDER BY p.nombre",
            [$uid]
        );

        // Imputaciones de la semana
        $semana_inicio = date('Y-m-d', strtotime('monday this week'));
        $imputaciones_semana = $this->db->query(
            "SELECT i.*, p.nombre as proyecto_nombre, p.color as proyecto_color
             FROM imputaciones i
             INNER JOIN proyectos p ON p.id = i.id_proyecto
             WHERE i.id_usuario = ? AND i.fecha >= ?
             ORDER BY i.fecha DESC",
            [$uid, $semana_inicio]
        );

        // Horas semana por proyecto
        $horas_semana = array_sum(array_column($imputaciones_semana, 'horas'));

        // Incidencias pendientes del usuario
        $incidencias = $this->db->query(
            "SELECT * FROM incidencias WHERE id_usuario = ? AND estado = 'pendiente' ORDER BY fecha DESC LIMIT 5",
            [$uid]
        );

        require_once __DIR__ . '/../views/employee/dashboard.php';
    }

    // ── Dashboard Jefe ────────────────────────────────────────
    private function dashboardJefe(): void {
        $today = date('Y-m-d');
        $rol   = $_SESSION['user_rol'];
        $uid   = $_SESSION['user_id'];

        // Si es jefe_departamento, solo ve su departamento
        $depto_filter = '';
        $params_base  = [];
        if ($rol === 'jefe_departamento') {
            $depto_filter = "AND u.departamento = ?";
            $params_base  = [$_SESSION['user_depto']];
        }

        // Empleados activos
        $empleados = $this->db->query(
            "SELECT u.id, u.nombre, u.apellidos, u.departamento, u.email,
                    h.hora_inicio, h.hora_fin,
                    (SELECT f.tipo FROM fichajes f WHERE f.id_usuario = u.id ORDER BY f.timestamp DESC LIMIT 1) as ultimo_tipo,
                    (SELECT f.timestamp FROM fichajes f WHERE f.id_usuario = u.id ORDER BY f.timestamp DESC LIMIT 1) as ultimo_fichaje
             FROM usuarios u
             LEFT JOIN horarios h ON h.id = u.id_horario
             WHERE u.activo = 1 AND u.rol = 'empleado' $depto_filter
             ORDER BY u.apellidos",
            $params_base
        );

        // Estadísticas del día
        $dentro_count   = 0;
        $fuera_count    = 0;
        $sin_fichar     = 0;
        $retrasos_hoy   = 0;

        foreach ($empleados as $emp) {
            if ($emp['ultimo_tipo'] === 'entrada') {
                $dentro_count++;
            } elseif ($emp['ultimo_tipo'] === 'salida') {
                $fuera_count++;
            } else {
                $sin_fichar++;
            }
        }

        // Incidencias de hoy
        $incidencias_hoy = $this->db->query(
            "SELECT i.*, u.nombre, u.apellidos FROM incidencias i
             INNER JOIN usuarios u ON u.id = i.id_usuario
             WHERE i.fecha = ? AND i.estado = 'pendiente'
             ORDER BY i.creado_en DESC",
            [$today]
        );

        $retrasos_hoy = count(array_filter($incidencias_hoy, fn($i) => $i['tipo'] === 'retraso'));

        // Proyectos activos con horas del mes
        $mes_inicio = date('Y-m-01');
        $proyectos_resumen = $this->db->query(
            "SELECT p.id, p.nombre, p.color, p.estado,
                    COALESCE(SUM(i.horas), 0) as horas_mes,
                    COUNT(DISTINCT up.id_usuario) as num_empleados
             FROM proyectos p
             LEFT JOIN imputaciones i ON i.id_proyecto = p.id AND i.fecha >= ?
             LEFT JOIN usuario_proyecto up ON up.id_proyecto = p.id
             WHERE p.estado = 'activo'
             GROUP BY p.id ORDER BY horas_mes DESC",
            [$mes_inicio]
        );

        require_once __DIR__ . '/../views/boss/dashboard.php';
    }

    // ── Dashboard Admin ───────────────────────────────────────
    private function dashboardAdmin(): void {
        $hoy = date('Y-m-d');

        // Totales
        $stats = $this->db->queryOne(
            "SELECT
                (SELECT COUNT(*) FROM usuarios WHERE activo = 1 AND rol != 'admin') as total_empleados,
                (SELECT COUNT(*) FROM proyectos WHERE estado = 'activo') as proyectos_activos,
                (SELECT COUNT(*) FROM incidencias WHERE estado = 'pendiente') as incidencias_pendientes,
                (SELECT COUNT(*) FROM fichajes WHERE DATE(timestamp) = ?) as fichajes_hoy",
            [$hoy]
        );

        // Últimas incidencias
        $incidencias_recientes = $this->db->query(
            "SELECT i.*, u.nombre, u.apellidos, u.departamento
             FROM incidencias i INNER JOIN usuarios u ON u.id = i.id_usuario
             WHERE i.estado = 'pendiente'
             ORDER BY i.creado_en DESC LIMIT 10"
        );

        // Usuarios sin fichar hoy (con horario asignado)
        $sin_fichar_hoy = $this->db->query(
            "SELECT u.id, u.nombre, u.apellidos, u.departamento, h.hora_inicio
             FROM usuarios u
             INNER JOIN horarios h ON h.id = u.id_horario
             WHERE u.activo = 1 AND u.rol != 'admin'
             AND u.id NOT IN (
                 SELECT DISTINCT id_usuario FROM fichajes WHERE DATE(timestamp) = ?
             )
             ORDER BY u.departamento, u.apellidos",
            [$hoy]
        );

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}
