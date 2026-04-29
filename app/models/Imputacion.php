<?php
// ============================================================
// app/models/Imputacion.php
// ============================================================

class Imputacion {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function crear(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO imputaciones (id_usuario, id_proyecto, horas, fecha, descripcion)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['id_usuario'], $data['id_proyecto'],
            $data['horas'], $data['fecha'], $data['descripcion'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByUsuario(int $idUsuario, string $desde = null, string $hasta = null): array {
        $desde = $desde ?? date('Y-m-01');
        $hasta = $hasta ?? date('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT i.*, p.nombre AS proyecto_nombre
             FROM imputaciones i JOIN proyectos p ON p.id = i.id_proyecto
             WHERE i.id_usuario = ? AND i.fecha BETWEEN ? AND ?
             ORDER BY i.fecha DESC, i.creado_en DESC"
        );
        $stmt->execute([$idUsuario, $desde, $hasta]);
        return $stmt->fetchAll();
    }

    public function getResumenByProyecto(int $idUsuario, string $desde = null, string $hasta = null): array {
        $desde = $desde ?? date('Y-m-01');
        $hasta = $hasta ?? date('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT p.nombre AS proyecto_nombre, SUM(i.horas) AS total_horas, COUNT(*) AS num_imputaciones
             FROM imputaciones i JOIN proyectos p ON p.id = i.id_proyecto
             WHERE i.id_usuario = ? AND i.fecha BETWEEN ? AND ?
             GROUP BY i.id_proyecto, p.nombre ORDER BY total_horas DESC"
        );
        $stmt->execute([$idUsuario, $desde, $hasta]);
        return $stmt->fetchAll();
    }

    public function getInformeGeneral(array $filtros): array {
        $sql = "SELECT i.*, CONCAT(u.nombre, ' ', u.apellidos) AS empleado_nombre,
                       p.nombre AS proyecto_nombre, d.nombre AS departamento_nombre
                FROM imputaciones i
                JOIN usuarios u ON u.id = i.id_usuario
                JOIN proyectos p ON p.id = i.id_proyecto
                LEFT JOIN departamentos d ON d.id = u.id_departamento
                WHERE i.fecha BETWEEN ? AND ?";
        $params = [$filtros['desde'], $filtros['hasta']];

        if (!empty($filtros['id_usuario'])) { $sql .= " AND i.id_usuario = ?"; $params[] = $filtros['id_usuario']; }
        if (!empty($filtros['id_proyecto'])) { $sql .= " AND i.id_proyecto = ?"; $params[] = $filtros['id_proyecto']; }

        $sql .= " ORDER BY i.fecha DESC, u.apellidos";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTotalHorasByUsuario(int $idUsuario, string $desde, string $hasta): float {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(horas), 0) FROM imputaciones WHERE id_usuario = ? AND fecha BETWEEN ? AND ?"
        );
        $stmt->execute([$idUsuario, $desde, $hasta]);
        return (float)$stmt->fetchColumn();
    }
}
