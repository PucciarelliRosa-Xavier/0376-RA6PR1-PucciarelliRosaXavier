<?php
// ============================================================
// app/controllers/ImputacionController.php
// ============================================================

class ImputacionController {
    private Imputacion $imputacionModel;
    private Proyecto $proyectoModel;

    public function __construct() {
        $this->imputacionModel = new Imputacion();
        $this->proyectoModel   = new Proyecto();
    }

    public function index(): void {
        $idUsuario = $_SESSION['user_id'];
        $proyectos = $this->proyectoModel->getByUsuario($idUsuario);
        $mensaje   = $_SESSION['imputacion_ok'] ?? null;
        unset($_SESSION['imputacion_ok']);
        include __DIR__ . '/../views/employee/imputacion.php';
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?action=imputar');
            exit;
        }
        $idUsuario  = $_SESSION['user_id'];
        $idProyecto = (int)($_POST['id_proyecto'] ?? 0);
        $horas      = (float)($_POST['horas'] ?? 0);
        $fecha      = $_POST['fecha'] ?? date('Y-m-d');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $errores = [];
        if ($idProyecto <= 0) $errores[] = 'Selecciona un proyecto válido.';
        if ($horas <= 0 || $horas > 24) $errores[] = 'Las horas deben ser entre 0.5 y 24.';
        if (empty($descripcion)) $errores[] = 'La descripción es obligatoria.';

        if (empty($errores)) {
            $this->imputacionModel->crear([
                'id_usuario' => $idUsuario, 'id_proyecto' => $idProyecto,
                'horas' => $horas, 'fecha' => $fecha, 'descripcion' => $descripcion,
            ]);
            $_SESSION['imputacion_ok'] = 'Imputación registrada correctamente.';
            header('Location: ?action=imputar');
        } else {
            $_SESSION['imputacion_error'] = implode(' ', $errores);
            header('Location: ?action=imputar');
        }
        exit;
    }

    public function misImputaciones(): void {
        $idUsuario = $_SESSION['user_id'];
        $desde     = $_GET['desde'] ?? date('Y-m-01');
        $hasta     = $_GET['hasta'] ?? date('Y-m-d');

        $imputaciones   = $this->imputacionModel->getByUsuario($idUsuario, $desde, $hasta);
        $resumenProy    = $this->imputacionModel->getResumenByProyecto($idUsuario, $desde, $hasta);
        $totalHoras     = array_sum(array_column($imputaciones, 'horas'));

        include __DIR__ . '/../views/employee/mis_imputaciones.php';
    }
}
