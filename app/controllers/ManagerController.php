<?php
// ============================================================
// app/controllers/ManagerController.php
// ============================================================

class ManagerController {
    private Usuario $usuarioModel;
    private Fichaje $fichajeModel;
    private Incidencia $incidenciaModel;
    private Imputacion $imputacionModel;
    private Proyecto $proyectoModel;

    public function __construct() {
        $this->usuarioModel     = new Usuario();
        $this->fichajeModel     = new Fichaje();
        $this->incidenciaModel  = new Incidencia();
        $this->imputacionModel  = new Imputacion();
        $this->proyectoModel    = new Proyecto();
    }

    public function dashboard(): void {
        $fichajesHoy       = $this->fichajeModel->getFichajesHoy();
        $sinFichar         = $this->usuarioModel->getEmpleadosSinFicharHoy();
        $tardanzasRecientes = $this->fichajeModel->getTardanzas(date('Y-m-d'));
        $incidenciasPend   = $this->incidenciaModel->contarPendientes();
        $empleados         = $this->usuarioModel->getAll(['activo' => 1]);

        include __DIR__ . '/../views/manager/dashboard.php';
    }

    public function empleados(): void {
        $filtros  = [];
        if (!empty($_GET['departamento'])) $filtros['departamento'] = (int)$_GET['departamento'];
        if (!empty($_GET['rol'])) $filtros['rol'] = $_GET['rol'];

        $empleados     = $this->usuarioModel->getAll($filtros);
        $departamentos = $this->usuarioModel->getDepartamentos();
        $desde         = $_GET['desde'] ?? date('Y-m-01');
        $hasta         = $_GET['hasta'] ?? date('Y-m-d');

        // Enriquecer con datos de fichaje
        foreach ($empleados as &$emp) {
            $resumen = $this->fichajeModel->getResumenDiario($emp['id'], $desde, $hasta);
            $emp['total_horas'] = array_sum(array_column(
                array_filter($resumen, fn($r) => $r['horas_trabajadas'] !== null),
                'horas_trabajadas'
            ));
            $emp['dias_trabajados'] = count($resumen);
            $emp['tardanzas'] = count(array_filter($resumen, fn($r) => $r['hubo_tardanza']));
        }

        include __DIR__ . '/../views/manager/empleados.php';
    }

    public function incidencias(): void {
        $filtros = [];
        if (!empty($_GET['estado'])) $filtros['estado'] = $_GET['estado'];
        if (!empty($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
        if (!empty($_GET['desde'])) $filtros['desde'] = $_GET['desde'];

        $incidencias = $this->incidenciaModel->getAll($filtros);
        include __DIR__ . '/../views/manager/incidencias.php';
    }

    public function resolverIncidencia(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id   = (int)($_POST['id'] ?? 0);
            $nota = trim($_POST['nota'] ?? '');
            if ($id > 0) {
                $this->incidenciaModel->resolver($id, $_SESSION['user_id'], $nota);
            }
        }
        header('Location: ?action=manager_incidencias');
        exit;
    }
}
