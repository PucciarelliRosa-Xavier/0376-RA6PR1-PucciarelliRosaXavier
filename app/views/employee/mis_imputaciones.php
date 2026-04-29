<?php
$pageTitle = 'Mis Horas Imputadas';
$action = 'mis_imputaciones';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Mis Horas Imputadas</h1>
        <p class="page-subtitle">Histórico de horas registradas por proyecto</p>
    </div>
</div>

<!-- Filtros -->
<div class="card filter-card">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="mis_imputaciones">
        <div class="filter-row">
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

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-value"><?= number_format($totalHoras, 1) ?>h</div>
        <div class="stat-label">Total imputadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🗂️</div>
        <div class="stat-value"><?= count($resumenProy) ?></div>
        <div class="stat-label">Proyectos distintos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?= count($imputaciones) ?></div>
        <div class="stat-label">Registros</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Resumen por proyecto -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen por proyecto</h3>
        </div>
        <div class="card-body">
            <?php if (empty($resumenProy)): ?>
                <p class="empty-state">Sin datos en el periodo.</p>
            <?php else: ?>
                <?php foreach ($resumenProy as $rp): ?>
                    <div class="resumen-proy-item">
                        <div class="resumen-proy-info">
                            <span class="resumen-proy-nombre"><?= htmlspecialchars($rp['proyecto_nombre']) ?></span>
                            <span class="resumen-proy-reg"><?= $rp['num_imputaciones'] ?> registros</span>
                        </div>
                        <div class="resumen-proy-barra-wrap">
                            <?php $pct = $totalHoras > 0 ? ($rp['total_horas'] / $totalHoras * 100) : 0; ?>
                            <div class="resumen-proy-barra">
                                <div class="resumen-proy-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="resumen-proy-horas"><?= number_format($rp['total_horas'], 1) ?>h</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalle de imputaciones -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Detalle de imputaciones</h3>
        </div>
        <div class="card-body">
            <?php if (empty($imputaciones)): ?>
                <p class="empty-state">No hay imputaciones en el periodo seleccionado.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Proyecto</th>
                            <th>Horas</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imputaciones as $imp): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($imp['fecha'])) ?></td>
                                <td><?= htmlspecialchars($imp['proyecto_nombre']) ?></td>
                                <td class="mono bold"><?= number_format($imp['horas'], 1) ?>h</td>
                                <td class="text-muted small"><?= htmlspecialchars($imp['descripcion']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-total">
                            <td colspan="2"><strong>TOTAL</strong></td>
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
