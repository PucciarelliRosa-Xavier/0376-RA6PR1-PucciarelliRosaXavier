<?php
// ============================================================
// app/models/Usuario.php
// ============================================================

class Usuario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Buscar usuario por email para login
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, d.nombre AS departamento_nombre, h.hora_entrada, h.hora_salida, h.tolerancia_minutos
             FROM usuarios u
             LEFT JOIN departamentos d ON d.id = u.id_departamento
             LEFT JOIN horarios h ON h.id = u.id_horario
             WHERE u.email = ? AND u.activo = 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Buscar usuario por ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, d.nombre AS departamento_nombre, h.nombre AS horario_nombre,
                    h.hora_entrada, h.hora_salida, h.tolerancia_minutos
             FROM usuarios u
             LEFT JOIN departamentos d ON d.id = u.id_departamento
             LEFT JOIN horarios h ON h.id = u.id_horario
             WHERE u.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Listar todos los usuarios activos
     */
    public function getAll(array $filtros = []): array {
        $sql = "SELECT u.*, d.nombre AS departamento_nombre, h.nombre AS horario_nombre
                FROM usuarios u
                LEFT JOIN departamentos d ON d.id = u.id_departamento
                LEFT JOIN horarios h ON h.id = u.id_horario
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['departamento'])) {
            $sql .= " AND u.id_departamento = ?";
            $params[] = $filtros['departamento'];
        }
        if (!empty($filtros['rol'])) {
            $sql .= " AND u.rol = ?";
            $params[] = $filtros['rol'];
        }
        if (isset($filtros['activo'])) {
            $sql .= " AND u.activo = ?";
            $params[] = $filtros['activo'];
        }

        $sql .= " ORDER BY u.apellidos, u.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Crear nuevo usuario
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre, apellidos, email, password, rol, id_departamento, id_horario, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        );
        $stmt->execute([
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['rol'],
            $data['id_departamento'] ?: null,
            $data['id_horario'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualizar usuario
     */
    public function update(int $id, array $data): bool {
        $fields = ['nombre=?', 'apellidos=?', 'email=?', 'rol=?', 'id_departamento=?', 'id_horario=?', 'activo=?'];
        $params = [
            $data['nombre'], $data['apellidos'], $data['email'],
            $data['rol'], $data['id_departamento'] ?: null,
            $data['id_horario'] ?: null, $data['activo'] ?? 1,
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password=?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $params[] = $id;
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Desactivar usuario (soft delete)
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtener departamentos
     */
    public function getDepartamentos(): array {
        $stmt = $this->db->query("SELECT * FROM departamentos ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Obtener horarios
     */
    public function getHorarios(): array {
        $stmt = $this->db->query("SELECT * FROM horarios ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Empleados sin fichar hoy (para alertas de manager)
     */
    public function getEmpleadosSinFicharHoy(): array {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nombre, u.apellidos, u.email, d.nombre AS departamento_nombre
             FROM usuarios u
             LEFT JOIN departamentos d ON d.id = u.id_departamento
             WHERE u.activo = 1 AND u.rol = 'empleado'
             AND u.id NOT IN (
                 SELECT DISTINCT id_usuario FROM fichajes
                 WHERE DATE(timestamp) = CURDATE()
             )
             ORDER BY u.apellidos"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
