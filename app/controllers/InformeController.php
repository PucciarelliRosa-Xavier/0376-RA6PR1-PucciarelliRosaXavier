<?php
// ============================================================
// app/controllers/InformeController.php
// ============================================================

class InformeController {
    private Imputacion $imputacionModel;
    private Fichaje $fichajeModel;
    private Usuario $usuarioModel;
    private Proyecto $proyectoModel;

    public function __construct() {
        $this->imputacionModel = new Imputacion();
        $this->fichajeModel    = new Fichaje();
        $this->usuarioModel    = new Usuario();
        $this->proyectoModel   = new Proyecto();
    }

    public function index(): void {
        $usuarios   = $this->usuarioModel->getAll(['activo' => 1]);
        $proyectos  = $this->proyectoModel->getAll();
        $tipo       = $_GET['tipo'] ?? 'mensual';
        $idUsuario  = (int)($_GET['id_usuario'] ?? 0);
        $idProyecto = (int)($_GET['id_proyecto'] ?? 0);

        [$desde, $hasta] = $this->calcularRango($tipo);
        if (!empty($_GET['desde'])) $desde = $_GET['desde'];
        if (!empty($_GET['hasta'])) $hasta = $_GET['hasta'];

        $filtros = ['desde' => $desde, 'hasta' => $hasta];
        if ($idUsuario > 0) $filtros['id_usuario'] = $idUsuario;
        if ($idProyecto > 0) $filtros['id_proyecto'] = $idProyecto;

        $imputaciones  = $this->imputacionModel->getInformeGeneral($filtros);
        $totalHoras    = array_sum(array_column($imputaciones, 'horas'));

        include __DIR__ . '/../views/manager/informes.php';
    }

    public function getInformeJson(): void {
        header('Content-Type: application/json');
        $tipo  = $_GET['tipo'] ?? 'mensual';
        [$desde, $hasta] = $this->calcularRango($tipo);

        $filtros = ['desde' => $desde, 'hasta' => $hasta];
        if (!empty($_GET['id_usuario'])) $filtros['id_usuario'] = (int)$_GET['id_usuario'];

        $imputaciones = $this->imputacionModel->getInformeGeneral($filtros);

        // Agrupar por proyecto para gráfico
        $porProyecto = [];
        foreach ($imputaciones as $imp) {
            $proy = $imp['proyecto_nombre'];
            $porProyecto[$proy] = ($porProyecto[$proy] ?? 0) + $imp['horas'];
        }

        echo json_encode([
            'desde'        => $desde,
            'hasta'        => $hasta,
            'total_horas'  => array_sum(array_column($imputaciones, 'horas')),
            'por_proyecto' => $porProyecto,
            'detalle'      => $imputaciones,
        ]);
        exit;
    }

    private function calcularRango(string $tipo): array {
        return match($tipo) {
            'diario'   => [date('Y-m-d'), date('Y-m-d')],
            'semanal'  => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))],
            'mensual'  => [date('Y-m-01'), date('Y-m-t')],
            default    => [date('Y-m-01'), date('Y-m-t')],
        };
    }
}
