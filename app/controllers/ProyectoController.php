<?php
// ============================================================
// app/controllers/ProyectoController.php
// ============================================================

class ProyectoController {
    private Proyecto $proyectoModel;

    public function __construct() {
        $this->proyectoModel = new Proyecto();
    }

    public function index(): void {
        $proyectos = $this->proyectoModel->getAll();
        include __DIR__ . '/../views/admin/proyectos.php';
    }
}
