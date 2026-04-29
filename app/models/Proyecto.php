<?php
// ============================================================
// app/models/Proyecto.php
// ============================================================

class Proyecto {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(string $estado = null): array {
        $sql = "SELECT p.*, CONCAT(u.nombre, ' ', u.apellidos) AS responsable_nombre
                FROM proyectos p LEFT JOIN usuarios u ON u.id = p.id_responsable WHERE 1=1";
        $params = [];
        if ($estado) { $sql .= " AND p.estado = ?"; $params[] = $estado; }
        $sql .= " ORDER BY p.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM proyectos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUsuario(int $idUsuario): array {
        $stmt = $this->db->prepare(
            "SELECT p.* FROM proyectos p
             JOIN usuario_proyecto up ON up.id_proyecto = p.id
             WHERE up.id_usuario = ? AND up.activo = 1 AND p.estado = 'activo'
             ORDER BY p.nombre"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO proyectos (nombre, descripcion, estado, fecha_inicio, fecha_fin, id_responsable)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'], $data['descripcion'] ?? null, $data['estado'] ?? 'activo',
            $data['fecha_inicio'] ?? null, $data['fecha_fin'] ?? null, $data['id_responsable'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE proyectos SET nombre=?, descripcion=?, estado=?, fecha_inicio=?, fecha_fin=?, id_responsable=? WHERE id=?"
        );
        return $stmt->execute([
            $data['nombre'], $data['descripcion'] ?? null, $data['estado'],
            $data['fecha_inicio'] ?? null, $data['fecha_fin'] ?? null, $data['id_responsable'] ?? null, $id
        ]);
    }

    public function asignarUsuario(int $idUsuario, int $idProyecto): bool {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO usuario_proyecto (id_usuario, id_proyecto) VALUES (?, ?)"
        );
        return $stmt->execute([$idUsuario, $idProyecto]);
    }

    public function desasignarUsuario(int $idUsuario, int $idProyecto): bool {
        $stmt = $this->db->prepare(
            "UPDATE usuario_proyecto SET activo = 0 WHERE id_usuario = ? AND id_proyecto = ?"
        );
        return $stmt->execute([$idUsuario, $idProyecto]);
    }

    public function getUsuariosByProyecto(int $idProyecto): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nombre, u.apellidos, u.email, up.activo
             FROM usuarios u JOIN usuario_proyecto up ON up.id_usuario = u.id
             WHERE up.id_proyecto = ? ORDER BY u.apellidos"
        );
        $stmt->execute([$idProyecto]);
        return $stmt->fetchAll();
    }
}
