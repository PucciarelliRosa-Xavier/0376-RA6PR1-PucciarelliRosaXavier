<?php
$pageTitle = 'Imputar Horas';
$action = 'imputar';
include __DIR__ . '/../shared/header.php';
$errorImp = $_SESSION['imputacion_error'] ?? null;
unset($_SESSION['imputacion_error']);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Imputar Horas</h1>
        <p class="page-subtitle">Registra las horas trabajadas por proyecto</p>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-ok"><span class="alert-icon">✓</span><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>
<?php if ($errorImp): ?>
    <div class="alert alert-error"><span class="alert-icon">✕</span><?= htmlspecialchars($errorImp) ?></div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nueva imputación</h3>
        </div>
        <div class="card-body">
            <?php if (empty($proyectos)): ?>
                <p class="empty-state">No tienes proyectos asignados. Contacta con tu responsable.</p>
            <?php else: ?>
                <form action="?action=guardar_imputacion" method="POST" class="form-vertical">
                    <div class="form-group">
                        <label class="form-label">Proyecto *</label>
                        <select name="id_proyecto" class="form-input" required>
                            <option value="">Selecciona un proyecto...</option>
                            <?php foreach ($proyectos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha" class="form-input" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Horas trabajadas *</label>
                        <input type="number" name="horas" class="form-input" min="0.5" max="24" step="0.5" placeholder="ej: 4.5" required>
                        <span class="form-hint">Introduce las horas en incrementos de 0.5 (media hora)</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción del trabajo *</label>
                        <textarea name="descripcion" class="form-input form-textarea" rows="4"
                            placeholder="Describe las tareas realizadas..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar imputación</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mis proyectos</h3>
            <a href="?action=mis_imputaciones" class="card-link">Ver todas →</a>
        </div>
        <div class="card-body">
            <?php if (empty($proyectos)): ?>
                <p class="empty-state">Sin proyectos asignados.</p>
            <?php else: ?>
                <?php foreach ($proyectos as $p): ?>
                    <div class="project-item">
                        <div class="project-dot estado-<?= $p['estado'] ?>"></div>
                        <div>
                            <div class="project-name"><?= htmlspecialchars($p['nombre']) ?></div>
                            <div class="project-hours">
                                <span class="badge badge-estado-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
