<?php
/**
 * TimeControl - FichajeController
 * Gestión de entradas y salidas
 */

class FichajeController {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Registrar fichaje (AJAX/JSON)
     */
    public function fichar(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $uid = $_SESSION['user_id'];

        // Determinar tipo: si el último fichaje fue entrada → salida, y viceversa
        $ultimo = $this->db->queryOne(
            "SELECT * FROM fichajes WHERE id_usuario = ? ORDER BY timestamp DESC LIMIT 1",
            [$uid]
        );

        $tipo = (!$ultimo || $ultimo['tipo'] === 'salida') ? 'entrada' : 'salida';
        $now  = new DateTime();
        $ip   = $_SERVER['REMOTE_ADDR'] ?? null;

        // Insertar fichaje
        $this->db->execute(
            "INSERT INTO fichajes (id_usuario, tipo, timestamp, ip) VALUES (?, ?, NOW(), ?)",
            [$uid, $tipo, $ip]
        );
        $fichaje_id = $this->db->lastInsertId();

        // Comprobar incidencias
        $incidencia = null;
        $user_data  = $this->db->queryOne(
            "SELECT u.*, h.hora_inicio, h.hora_fin, h.tolerancia
             FROM usuarios u LEFT JOIN horarios h ON h.id = u.id_horario
             WHERE u.id = ?",
            [$uid]
        );

        if ($user_data && $user_data['hora_inicio']) {
            if ($tipo === 'entrada') {
                $incidencia = $this->checkRetraso($user_data, $now);
            } elseif ($tipo === 'salida') {
                $incidencia = $this->checkSalidaAnticipada($user_data, $now);
            }
        }

        // Horas trabajadas hoy (si es salida)
        $horas_hoy = null;
        if ($tipo === 'salida') {
            $fichajes_hoy = $this->db->query(
                "SELECT * FROM fichajes WHERE id_usuario = ? AND DATE(timestamp) = ? ORDER BY timestamp",
                [$uid, date('Y-m-d')]
            );
            $horas_hoy = FichajeModel::calcularHorasTrabajadas($fichajes_hoy);
        }

        echo json_encode([
            'ok'         => true,
            'tipo'       => $tipo,
            'timestamp'  => $now->format('H:i:s'),
            'fecha'      => $now->format('d/m/Y'),
            'horas_hoy'  => $horas_hoy,
            'incidencia' => $incidencia,
        ]);
        exit;
    }

    /**
     * Estado actual del usuario (dentro / fuera)
     */
    public function estadoActual(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['user_id'];

        $ultimo = $this->db->queryOne(
            "SELECT tipo, timestamp FROM fichajes WHERE id_usuario = ? ORDER BY timestamp DESC LIMIT 1",
            [$uid]
        );

        $dentro = $ultimo && $ultimo['tipo'] === 'entrada';

        $fichajes_hoy = $this->db->query(
            "SELECT * FROM fichajes WHERE id_usuario = ? AND DATE(timestamp) = ? ORDER BY timestamp",
            [$uid, date('Y-m-d')]
        );
        $horas_hoy = FichajeModel::calcularHorasTrabajadas($fichajes_hoy);

        echo json_encode([
            'dentro'    => $dentro,
            'ultimo'    => $ultimo,
            'horas_hoy' => $horas_hoy,
            'fichajes'  => $fichajes_hoy,
        ]);
        exit;
    }

    /**
     * Historial de fichajes del empleado
     */
    public function historial(): void {
        $uid  = $_SESSION['user_id'];
        $mes  = $_GET['mes']  ?? date('Y-m');
        $page = max(1, (int)($_GET['page'] ?? 1));

        [$year, $month] = explode('-', $mes . '-01');
        $inicio = "$year-$month-01";
        $fin    = date('Y-m-t', strtotime($inicio));

        // Todos los fichajes del mes agrupados por día
        $fichajes = $this->db->query(
            "SELECT DATE(timestamp) as fecha,
                    GROUP_CONCAT(tipo ORDER BY timestamp SEPARATOR ',') as tipos,
                    GROUP_CONCAT(TIME(timestamp) ORDER BY timestamp SEPARATOR ',') as horas,
                    COUNT(*) as total
             FROM fichajes
             WHERE id_usuario = ? AND DATE(timestamp) BETWEEN ? AND ?
             GROUP BY DATE(timestamp)
             ORDER BY fecha DESC",
            [$uid, $inicio, $fin]
        );

        // Calcular horas trabajadas por día
        $resumen = [];
        foreach ($fichajes as &$f) {
            $tipos = explode(',', $f['tipos']);
            $horas_arr = explode(',', $f['horas']);
            $mins_total = 0;
            $entrada = null;
            for ($i = 0; $i < count($tipos); $i++) {
                if ($tipos[$i] === 'entrada') {
                    $entrada = strtotime($f['fecha'] . ' ' . $horas_arr[$i]);
                } elseif ($tipos[$i] === 'salida' && $entrada) {
                    $salida = strtotime($f['fecha'] . ' ' . $horas_arr[$i]);
                    $mins_total += ($salida - $entrada) / 60;
                    $entrada = null;
                }
            }
            $f['horas_trabajadas'] = round($mins_total / 60, 2);
            $f['tipos_arr'] = $tipos;
            $f['horas_arr'] = $horas_arr;
        }

        // Incidencias del mes
        $incidencias = $this->db->query(
            "SELECT * FROM incidencias WHERE id_usuario = ? AND fecha BETWEEN ? AND ? ORDER BY fecha DESC",
            [$uid, $inicio, $fin]
        );

        // Total horas del mes
        $total_horas = array_sum(array_column($fichajes, 'horas_trabajadas'));

        require_once __DIR__ . '/../views/employee/historial.php';
    }

    // ── Checks de incidencias ─────────────────────────────────

    private function checkRetraso(array $user, DateTime $now): ?array {
        $tolerancia  = (int)$user['tolerancia'];
        $hora_inicio = DateTime::createFromFormat('H:i:s', $user['hora_inicio']);
        $hora_limite = clone $hora_inicio;
        $hora_limite->modify("+{$tolerancia} minutes");

        if ($now > $hora_limite) {
            $mins = round(($now->getTimestamp() - $hora_inicio->getTimestamp()) / 60);
            $desc = "El empleado fichó entrada con {$mins} minutos de retraso (límite: {$hora_limite->format('H:i')})";

            $this->db->execute(
                "INSERT INTO incidencias (id_usuario, tipo, descripcion, fecha) VALUES (?, 'retraso', ?, ?)",
                [$user['id'], $desc, date('Y-m-d')]
            );

            // Enviar email
            EmailHelper::enviar(
                $user['email'],
                $user['nombre'] . ' ' . $user['apellidos'],
                'Retraso registrado - ' . date('d/m/Y'),
                EmailHelper::plantillaRetraso($user, $mins, $now->format('H:i')),
                $user['id'],
                'retraso'
            );

            return ['tipo' => 'retraso', 'minutos' => $mins];
        }
        return null;
    }

    private function checkSalidaAnticipada(array $user, DateTime $now): ?array {
        $hora_fin = DateTime::createFromFormat('H:i:s', $user['hora_fin']);
        if ($now < $hora_fin) {
            $mins = round(($hora_fin->getTimestamp() - $now->getTimestamp()) / 60);
            $desc = "El empleado salió {$mins} minutos antes de su hora de fin ({$hora_fin->format('H:i')})";

            $this->db->execute(
                "INSERT INTO incidencias (id_usuario, tipo, descripcion, fecha) VALUES (?, 'salida_anticipada', ?, ?)",
                [$user['id'], $desc, date('Y-m-d')]
            );

            return ['tipo' => 'salida_anticipada', 'minutos' => $mins];
        }
        return null;
    }
}
