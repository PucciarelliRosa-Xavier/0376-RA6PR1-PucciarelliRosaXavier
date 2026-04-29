<?php
// ============================================================
// app/models/Incidencia.php
// ============================================================

class Incidencia {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function crear(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO incidencias (id_usuario, tipo, descripcion, fecha, estado, email_enviado)
             VALUES (?, ?, ?, ?, 'pendiente', ?)"
        );
        $stmt->execute([
            $data['id_usuario'], $data['tipo'],
            $data['descripcion'] ?? null, $data['fecha'],
            $data['email_enviado'] ?? 0
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getAll(array $filtros = []): array {
        $sql = "SELECT i.*, CONCAT(u.nombre, ' ', u.apellidos) AS empleado_nombre,
                       u.email AS empleado_email, d.nombre AS departamento_nombre
                FROM incidencias i
                JOIN usuarios u ON u.id = i.id_usuario
                LEFT JOIN departamentos d ON d.id = u.id_departamento
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['estado'])) { $sql .= " AND i.estado = ?"; $params[] = $filtros['estado']; }
        if (!empty($filtros['tipo'])) { $sql .= " AND i.tipo = ?"; $params[] = $filtros['tipo']; }
        if (!empty($filtros['id_usuario'])) { $sql .= " AND i.id_usuario = ?"; $params[] = $filtros['id_usuario']; }
        if (!empty($filtros['desde'])) { $sql .= " AND i.fecha >= ?"; $params[] = $filtros['desde']; }

        $sql .= " ORDER BY i.creado_en DESC LIMIT 200";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function resolver(int $id, int $idRevisor, string $nota): bool {
        $stmt = $this->db->prepare(
            "UPDATE incidencias SET estado = 'resuelta', id_revisor = ?, nota_revision = ? WHERE id = ?"
        );
        return $stmt->execute([$idRevisor, $nota, $id]);
    }

    public function contarPendientes(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM incidencias WHERE estado = 'pendiente'");
        return (int)$stmt->fetchColumn();
    }

    public function existeHoy(int $idUsuario, string $tipo): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM incidencias WHERE id_usuario = ? AND tipo = ? AND fecha = CURDATE()"
        );
        $stmt->execute([$idUsuario, $tipo]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
