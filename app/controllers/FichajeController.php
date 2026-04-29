<?php
// ============================================================
// app/controllers/FichajeController.php
// ============================================================

class FichajeController {
    private Fichaje $fichajeModel;
    private Usuario $usuarioModel;
    private Incidencia $incidenciaModel;
    private Proyecto $proyectoModel;
    private Imputacion $imputacionModel;

    public function __construct() {
        $this->fichajeModel     = new Fichaje();
        $this->usuarioModel     = new Usuario();
        $this->incidenciaModel  = new Incidencia();
        $this->proyectoModel    = new Proyecto();
        $this->imputacionModel  = new Imputacion();
    }

    /**
     * Dashboard del empleado
     */
    public function dashboard(): void {
        $idUsuario = $_SESSION['user_id'];
        $usuario   = $this->usuarioModel->findById($idUsuario);

        $estadoActual    = $this->fichajeModel->getEstadoActual($idUsuario);
        $fichajesHoy     = $this->fichajeModel->getHistorial($idUsuario, date('Y-m-d'), date('Y-m-d'));
        $proyectos       = $this->proyectoModel->getByUsuario($idUsuario);
        $resumenSemana   = $this->fichajeModel->getResumenDiario($idUsuario, date('Y-m-d', strtotime('-6 days')), date('Y-m-d'));
        $imputacionesHoy = $this->imputacionModel->getByUsuario($idUsuario, date('Y-m-d'), date('Y-m-d'));
        $resumenProyectos = $this->imputacionModel->getResumenByProyecto($idUsuario, date('Y-m-01'), date('Y-m-d'));

        include __DIR__ . '/../views/employee/dashboard.php';
    }

    /**
     * Procesar fichaje (AJAX)
     */
    public function fichar(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
            exit;
        }

        $idUsuario = $_SESSION['user_id'];
        $horario   = $_SESSION['user_horario'] ?? [];
        $estado    = $this->fichajeModel->getEstadoActual($idUsuario);

        // Determinar tipo de fichaje
        $tipo = ($estado === 'dentro') ? 'salida' : 'entrada';

        $resultado = $this->fichajeModel->registrar($idUsuario, $tipo, $horario);

        // Gestionar incidencias
        $incidencia = null;
        $mailer = new Mailer();
        $usuario = $this->usuarioModel->findById($idUsuario);

        if ($resultado['es_tardanza'] && !$this->incidenciaModel->existeHoy($idUsuario, 'retraso')) {
            $minutos = $resultado['minutos_diferencia'];
            $this->incidenciaModel->crear([
                'id_usuario'   => $idUsuario,
                'tipo'         => 'retraso',
                'descripcion'  => "Llegada con {$minutos} minutos de retraso.",
                'fecha'        => date('Y-m-d'),
                'email_enviado' => 1,
            ]);
            $mailer->enviarAlertaRetraso($usuario, $minutos);
            $incidencia = 'retraso';
        }

        if ($resultado['es_salida_anticipada'] && !$this->incidenciaModel->existeHoy($idUsuario, 'salida_anticipada')) {
            $minutos = $resultado['minutos_diferencia'];
            $this->incidenciaModel->crear([
                'id_usuario'   => $idUsuario,
                'tipo'         => 'salida_anticipada',
                'descripcion'  => "Salida {$minutos} minutos antes del horario.",
                'fecha'        => date('Y-m-d'),
                'email_enviado' => 0,
            ]);
            $incidencia = 'salida_anticipada';
        }

        echo json_encode([
            'ok'         => true,
            'tipo'       => $tipo,
            'hora'       => date('H:i:s'),
            'incidencia' => $incidencia,
            'mensaje'    => $tipo === 'entrada' ? '¡Entrada registrada correctamente!' : '¡Salida registrada correctamente!',
        ]);
        exit;
    }

    /**
     * Historial de fichajes
     */
    public function historial(): void {
        $idUsuario = $_SESSION['user_id'];
        $desde     = $_GET['desde'] ?? date('Y-m-01');
        $hasta     = $_GET['hasta'] ?? date('Y-m-d');

        $fichajes       = $this->fichajeModel->getHistorial($idUsuario, $desde, $hasta);
        $resumenDiario  = $this->fichajeModel->getResumenDiario($idUsuario, $desde, $hasta);
        $totalHoras     = array_sum(array_column(
            array_filter($resumenDiario, fn($r) => $r['horas_trabajadas'] !== null),
            'horas_trabajadas'
        ));

        include __DIR__ . '/../views/employee/historial.php';
    }
}
