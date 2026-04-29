<?php
// ============================================================
// app/models/Fichaje.php
// ============================================================

class Fichaje {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Registrar fichaje de entrada o salida
     * Devuelve array con resultado y si hay incidencia
     */
    public function registrar(int $idUsuario, string $tipo, array $horario): array {
        $ahora = new DateTime();
        $esTardanza = false;
        $esSalidaAnticipada = false;
        $minutosDiferencia = 0;

        if ($tipo === 'entrada' && $horario['hora_entrada']) {
            $horaEsperada = new DateTime(date('Y-m-d') . ' ' . $horario['hora_entrada']);
            $tolerancia = (int)($horario['tolerancia_minutos'] ?? 15);
            $horaLimite = clone $horaEsperada;
            $horaLimite->modify("+{$tolerancia} minutes");

            if ($ahora > $horaLimite) {
                $esTardanza = true;
                $minutosDiferencia = (int)($ahora->getTimestamp() - $horaEsperada->getTimestamp()) / 60;
            }
        }

        if ($tipo === 'salida' && $horario['hora_salida']) {
            $horaEsperada = new DateTime(date('Y-m-d') . ' ' . $horario['hora_salida']);
            $tolerancia = (int)($horario['tolerancia_minutos'] ?? 15);
            $horaLimite = clone $horaEsperada;
            $horaLimite->modify("-{$tolerancia} minutes");

            if ($ahora < $horaLimite) {
                $esSalidaAnticipada = true;
                $minutosDiferencia = (int)($horaEsperada->getTimestamp() - $ahora->getTimestamp()) / 60;
            }
        }

        $stmt = $this->db->prepare(
            "INSERT INTO fichajes (id_usuario, tipo, timestamp, ip, es_tardanza, es_salida_anticipada, minutos_diferencia)
             VALUES (?, ?, NOW(), ?, ?, ?, ?)"
        );
        $stmt->execute([
            $idUsuario, $tipo,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $esTardanza ? 1 : 0,
            $esSalidaAnticipada ? 1 : 0,
            $minutosDiferencia
        ]);

        $fichajeId = (int)$this->db->lastInsertId();

        return [
            'id'                   => $fichajeId,
            'tipo'                 => $tipo,
            'timestamp'            => $ahora->format('Y-m-d H:i:s'),
            'es_tardanza'          => $esTardanza,
            'es_salida_anticipada' => $esSalidaAnticipada,
            'minutos_diferencia'   => $minutosDiferencia,
        ];
    }

    /**
     * Obtener último fichaje del usuario hoy
     */
    public function getUltimoHoy(int $idUsuario): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM fichajes 
             WHERE id_usuario = ? AND DATE(timestamp) = CURDATE()
             ORDER BY timestamp DESC LIMIT 1"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtener estado actual del usuario (dentro/fuera)
     */
    public function getEstadoActual(int $idUsuario): string {
        $ultimo = $this->getUltimoHoy($idUsuario);
        if (!$ultimo) return 'sin_fichar';
        return $ultimo['tipo'] === 'entrada' ? 'dentro' : 'fuera';
    }

    /**
     * Historial de fichajes de un usuario con filtros
     */
    public function getHistorial(int $idUsuario, string $desde = null, string $hasta = null): array {
        $desde = $desde ?? date('Y-m-01');
        $hasta = $hasta ?? date('Y-m-d');

        $stmt = $this->db->prepare(
            "SELECT *, DATE(timestamp) AS fecha, TIME(timestamp) AS hora
             FROM fichajes
             WHERE id_usuario = ? AND DATE(timestamp) BETWEEN ? AND ?
             ORDER BY timestamp DESC"
        );
        $stmt->execute([$idUsuario, $desde, $hasta]);
        return $stmt->fetchAll();
    }

    /**
     * Resumen de horas trabajadas por día
     */
    public function getResumenDiario(int $idUsuario, string $desde = null, string $hasta = null): array {
        $desde = $desde ?? date('Y-m-01');
        $hasta = $hasta ?? date('Y-m-d');

        $stmt = $this->db->prepare(
            "SELECT 
                DATE(timestamp) AS fecha,
                MIN(CASE WHEN tipo = 'entrada' THEN timestamp END) AS primera_entrada,
                MAX(CASE WHEN tipo = 'salida' THEN timestamp END) AS ultima_salida,
                COUNT(CASE WHEN tipo = 'entrada' THEN 1 END) AS num_entradas,
                COUNT(CASE WHEN tipo = 'salida' THEN 1 END) AS num_salidas,
                MAX(es_tardanza) AS hubo_tardanza,
                MAX(es_salida_anticipada) AS hubo_salida_anticipada
             FROM fichajes
             WHERE id_usuario = ? AND DATE(timestamp) BETWEEN ? AND ?
             GROUP BY DATE(timestamp)
             ORDER BY fecha DESC"
        );
        $stmt->execute([$idUsuario, $desde, $hasta]);
        $rows = $stmt->fetchAll();

        // Calcular horas trabajadas
        foreach ($rows as &$row) {
            if ($row['primera_entrada'] && $row['ultima_salida']) {
                $entrada = new DateTime($row['primera_entrada']);
                $salida  = new DateTime($row['ultima_salida']);
                $diff = $entrada->diff($salida);
                $row['horas_trabajadas'] = $diff->h + round($diff->i / 60, 2);
            } else {
                $row['horas_trabajadas'] = null;
            }
        }

        return $rows;
    }

    /**
     * Fichajes de todos los empleados hoy (para manager)
     */
    public function getFichajesHoy(int $idDepartamento = null): array {
        $sql = "SELECT f.*, u.nombre, u.apellidos, u.email, d.nombre AS departamento_nombre,
                       TIME(f.timestamp) AS hora_str
                FROM fichajes f
                JOIN usuarios u ON u.id = f.id_usuario
                LEFT JOIN departamentos d ON d.id = u.id_departamento
                WHERE DATE(f.timestamp) = CURDATE()";
        $params = [];

        if ($idDepartamento) {
            $sql .= " AND u.id_departamento = ?";
            $params[] = $idDepartamento;
        }
        $sql .= " ORDER BY f.timestamp DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Detectar empleados que no ficharon salida ayer (para cron)
     */
    public function getOlvidosFichajeAyer(): array {
        $stmt = $this->db->query(
            "SELECT u.id, u.nombre, u.apellidos, u.email
             FROM usuarios u
             WHERE u.activo = 1
             AND u.id IN (
                 SELECT DISTINCT id_usuario FROM fichajes
                 WHERE DATE(timestamp) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND tipo = 'entrada'
             )
             AND u.id NOT IN (
                 SELECT DISTINCT id_usuario FROM fichajes
                 WHERE DATE(timestamp) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND tipo = 'salida'
             )"
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtener tardanzas recientes para manager
     */
    public function getTardanzas(string $desde = null, int $idDepartamento = null): array {
        $desde = $desde ?? date('Y-m-01');
        $sql = "SELECT f.*, u.nombre, u.apellidos, d.nombre AS departamento_nombre,
                       DATE(f.timestamp) AS fecha, TIME(f.timestamp) AS hora
                FROM fichajes f
                JOIN usuarios u ON u.id = f.id_usuario
                LEFT JOIN departamentos d ON d.id = u.id_departamento
                WHERE f.es_tardanza = 1 AND DATE(f.timestamp) >= ?";
        $params = [$desde];

        if ($idDepartamento) {
            $sql .= " AND u.id_departamento = ?";
            $params[] = $idDepartamento;
        }
        $sql .= " ORDER BY f.timestamp DESC LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
