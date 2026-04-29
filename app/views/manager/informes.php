<?php
$pageTitle = 'Informes';
$action = 'informes';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Informes de Horas</h1>
        <p class="page-subtitle">Análisis de horas trabajadas e imputadas por empleado y proyecto</p>
    </div>
</div>

<!-- Filtros -->
<div class="card filter-card">
    <form method="GET" action="" class="filter-form" id="form-informe">
        <input type="hidden" name="action" value="informes">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">Periodo</label>
                <select name="tipo" class="form-input" id="select-tipo">
                    <option value="diario"  <?= ($tipo ?? '') === 'diario' ? 'selected' : '' ?>>Diario</option>
                    <option value="semanal" <?= ($tipo ?? '') === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                    <option value="mensual" <?= ($tipo ?? 'mensual') === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                    <option value="custom"  <?= ($tipo ?? '') === 'custom' ? 'selected' : '' ?>>Personalizado</option>
                </select>
            </div>
            <div class="form-group" id="grp-desde">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" class="form-input" value="<?= htmlspecialchars($desde) ?>">
            </div>
            <div class="form-group" id="grp-hasta">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" class="form-input" value="<?= htmlspecialchars($hasta) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Empleado</label>
                <select name="id_usuario" class="form-input">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($_GET['id_usuario'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Proyecto</label>
                <select name="id_proyecto" class="form-input">
                    <option value="">Todos</option>
                    <?php foreach ($proyectos as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($_GET['id_proyecto'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group-action">
                <button type="submit" class="btn btn-primary">Generar</button>
            </div>
        </div>
    </form>
</div>

<!-- Resumen -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-value"><?= number_format($totalHoras, 1) ?>h</div>
        <div class="stat-label">Total horas imputadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?= count($imputaciones) ?></div>
        <div class="stat-label">Registros</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?= count(array_unique(array_column($imputaciones, 'id_usuario'))) ?></div>
        <div class="stat-label">Empleados</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🗂️</div>
        <div class="stat-value"><?= count(array_unique(array_column($imputaciones, 'id_proyecto'))) ?></div>
        <div class="stat-label">Proyectos</div>
    </div>
</div>

<?php
// Agrupar por proyecto para el gráfico
$porProyecto = [];
foreach ($imputaciones as $imp) {
    $p = $imp['proyecto_nombre'];
    $porProyecto[$p] = ($porProyecto[$p] ?? 0) + $imp['horas'];
}
arsort($porProyecto);

// Agrupar por empleado
$porEmpleado = [];
foreach ($imputaciones as $imp) {
    $e = $imp['empleado_nombre'];
    $porEmpleado[$e] = ($porEmpleado[$e] ?? 0) + $imp['horas'];
}
arsort($porEmpleado);
?>

<div class="dashboard-grid">
    <!-- Gráfico por proyecto -->
    <?php if (!empty($porProyecto)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Horas por proyecto</h3>
        </div>
        <div class="card-body">
            <?php $maxH = max($porProyecto); ?>
            <?php foreach ($porProyecto as $proy => $horas): ?>
                <div class="bar-item">
                    <div class="bar-label"><?= htmlspecialchars($proy) ?></div>
                    <div class="bar-wrap">
                        <div class="bar-fill" style="width:<?= $maxH > 0 ? ($horas/$maxH*100) : 0 ?>%"></div>
                    </div>
                    <div class="bar-value mono"><?= number_format($horas, 1) ?>h</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Gráfico por empleado -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Horas por empleado</h3>
        </div>
        <div class="card-body">
            <?php $maxH2 = max($porEmpleado); ?>
            <?php foreach ($porEmpleado as $emp => $horas): ?>
                <div class="bar-item">
                    <div class="bar-label"><?= htmlspecialchars($emp) ?></div>
                    <div class="bar-wrap">
                        <div class="bar-fill bar-fill-alt" style="width:<?= $maxH2 > 0 ? ($horas/$maxH2*100) : 0 ?>%"></div>
                    </div>
                    <div class="bar-value mono"><?= number_format($horas, 1) ?>h</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla detallada -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Detalle de imputaciones</h3>
            <span class="card-meta"><?= date('d/m/Y', strtotime($desde)) ?> — <?= date('d/m/Y', strtotime($hasta)) ?></span>
        </div>
        <div class="card-body">
            <?php if (empty($imputaciones)): ?>
                <p class="empty-state">No hay imputaciones en el periodo seleccionado.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empleado</th>
                            <th>Departamento</th>
                            <th>Proyecto</th>
                            <th>Horas</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imputaciones as $imp): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($imp['fecha'])) ?></td>
                                <td><?= htmlspecialchars($imp['empleado_nombre']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($imp['departamento_nombre'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($imp['proyecto_nombre']) ?></td>
                                <td class="mono bold"><?= number_format($imp['horas'], 1) ?>h</td>
                                <td class="text-muted small"><?= htmlspecialchars($imp['descripcion'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-total">
                            <td colspan="4"><strong>TOTAL</strong></td>
                            <td class="mono bold"><?= number_format($totalHoras, 1) ?>h</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
