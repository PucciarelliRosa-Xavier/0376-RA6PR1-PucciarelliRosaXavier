<?php
$pageTitle = 'Proyectos';
$action = 'admin_proyectos';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestión de Proyectos</h1>
        <p class="page-subtitle">Crear, editar y asignar empleados a proyectos</p>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-ok"><span class="alert-icon">✓</span><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="dashboard-grid">
    <!-- Formulario nuevo proyecto -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nuevo / Editar Proyecto</h3>
        </div>
        <div class="card-body">
            <form action="?action=admin_guardar_proyecto" method="POST" class="form-vertical" id="form-proyecto">
                <input type="hidden" name="id" id="proy-id" value="">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" id="proy-nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="proy-desc" class="form-input form-textarea" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="estado" id="proy-estado" class="form-input">
                            <option value="activo">Activo</option>
                            <option value="pausado">Pausado</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Responsable</label>
                        <select name="id_responsable" class="form-input">
                            <option value="">Sin responsable</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" name="fecha_fin" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar proyecto</button>
            </form>
        </div>
    </div>

    <!-- Lista de proyectos -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Proyectos existentes</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Nombre</th><th>Estado</th><th>Responsable</th><th>Inicio</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><span class="badge badge-estado-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                            <td class="text-muted small"><?= htmlspecialchars($p['responsable_nombre'] ?? '—') ?></td>
                            <td class="text-muted small"><?= $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : '—' ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline"
                                    onclick="editarProy(<?= htmlspecialchars(json_encode($p)) ?>)">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Asignar empleado a proyecto -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Asignar Empleado a Proyecto</h3>
        </div>
        <div class="card-body">
            <form action="?action=admin_asignar_proyecto" method="POST" class="form-vertical">
                <div class="form-group">
                    <label class="form-label">Proyecto</label>
                    <select name="id_proyecto" class="form-input" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($proyectos as $p): if ($p['estado'] !== 'activo') continue; ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Empleado</label>
                    <select name="id_usuario" class="form-input" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Asignar</button>
            </form>
        </div>
    </div>
</div>

<script>
function editarProy(p) {
    document.getElementById('proy-id').value = p.id;
    document.getElementById('proy-nombre').value = p.nombre;
    document.getElementById('proy-desc').value = p.descripcion || '';
    document.getElementById('proy-estado').value = p.estado;
    document.getElementById('form-proyecto').scrollIntoView({behavior:'smooth'});
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
