<?php
$pageTitle = 'Empleados';
$action = 'manager_empleados';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestión de Empleados</h1>
        <p class="page-subtitle">Seguimiento de actividad y horas del equipo</p>
    </div>
</div>

<!-- Filtros -->
<div class="card filter-card">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="manager_empleados">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">Departamento</label>
                <select name="departamento" class="form-input">
                    <option value="">Todos</option>
                    <?php foreach ($departamentos as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($_GET['departamento'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-input">
                    <option value="">Todos</option>
                    <option value="empleado" <?= ($_GET['rol'] ?? '') === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                    <option value="jefe" <?= ($_GET['rol'] ?? '') === 'jefe' ? 'selected' : '' ?>>Jefe</option>
                    <option value="jefe_departamento" <?= ($_GET['rol'] ?? '') === 'jefe_departamento' ? 'selected' : '' ?>>Jefe Dpto.</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" class="form-input" value="<?= htmlspecialchars($desde) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" class="form-input" value="<?= htmlspecialchars($hasta) ?>">
            </div>
            <div class="form-group form-group-action">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>
</div>

<!-- Tabla de empleados -->
<div class="card card-wide">
    <div class="card-header">
        <h3 class="card-title">Empleados (<?= count($empleados) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($empleados)): ?>
            <p class="empty-state">No se encontraron empleados.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Departamento</th>
                        <th>Rol</th>
                        <th>Días trabajados</th>
                        <th>Horas totales</th>
                        <th>Tardanzas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $emp): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm"><?= strtoupper(substr($emp['nombre'], 0, 1)) ?></div>
                                    <div>
                                        <div class="user-cell-name"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></div>
                                        <div class="user-cell-email text-muted small"><?= htmlspecialchars($emp['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($emp['departamento_nombre'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-rol-<?= $emp['rol'] ?>">
                                    <?= str_replace('_', ' ', ucfirst($emp['rol'])) ?>
                                </span>
                            </td>
                            <td class="center mono"><?= $emp['dias_trabajados'] ?></td>
                            <td class="mono bold"><?= number_format($emp['total_horas'], 1) ?>h</td>
                            <td class="center">
                                <?php if ($emp['tardanzas'] > 0): ?>
                                    <span class="badge badge-warn"><?= $emp['tardanzas'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $emp['activo'] ? 'badge-ok' : 'badge-error' ?>">
                                    <?= $emp['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
