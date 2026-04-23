<?php
$page_title = 'Gestión de Proyectos';
$extra_js   = 'proyectos';
require_once __DIR__ . '/../shared/header.php';

$estado_labels = ['activo' => 'Activo', 'pausado' => 'Pausado', 'completado' => 'Completado'];
?>

<div class="admin-section">
    <div class="toolbar">
        <h2>Proyectos (<?= count($proyectos) ?>)</h2>
        <button class="btn btn-primary" id="btnNuevoProyecto">+ Nuevo proyecto</button>
    </div>

    <div class="proyectos-grid">
        <?php foreach ($proyectos as $p): ?>
        <div class="proyecto-card" style="--project-color:<?= htmlspecialchars($p['color']) ?>">
            <div class="proyecto-card-header">
                <div class="proyecto-color-bar"></div>
                <div class="proyecto-card-actions">
                    <button class="btn-icon" onclick='editarProyecto(<?= json_encode($p) ?>)' title="Editar">✎</button>
                    <button class="btn-icon btn-icon--danger" onclick="archivarProyecto(<?= $p['id'] ?>)" title="Archivar">✕</button>
                </div>
            </div>
            <div class="proyecto-card-body">
                <h3 class="proyecto-card-nombre"><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="proyecto-card-desc"><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>
                <div class="proyecto-card-stats">
                    <span>👥 <?= $p['num_empleados'] ?> empleados</span>
                    <span>⏱ <?= number_format($p['horas_totales'], 0) ?>h totales</span>
                </div>
            </div>
            <div class="proyecto-card-footer">
                <span class="badge badge-estado badge-<?= $p['estado'] ?>"><?= $estado_labels[$p['estado']] ?></span>
                <?php if ($p['fecha_inicio']): ?>
                <span class="text-muted"><?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal proyecto -->
<div class="modal-overlay hidden" id="modalProyecto">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalProyectoTitle">Nuevo proyecto</h3>
            <button class="modal-close" id="closeModalProyecto">✕</button>
        </div>
        <div class="modal-body">
            <form id="proyectoForm">
                <input type="hidden" id="pid" name="id" value="0">
                <div class="form-group">
                    <label class="form-label">Nombre del proyecto <span class="required">*</span></label>
                    <input type="text" id="pNombre" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea id="pDesc" name="descripcion" class="form-input form-textarea" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select id="pEstado" name="estado" class="form-input">
                            <option value="activo">Activo</option>
                            <option value="pausado">Pausado</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color</label>
                        <input type="color" id="pColor" name="color" class="form-input form-input--color" value="#4F6EF7">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" id="pFechaInicio" name="fecha_inicio" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha fin prevista</label>
                        <input type="date" id="pFechaFin" name="fecha_fin" class="form-input">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalProyecto').classList.add('hidden')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
