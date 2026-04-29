<?php
$pageTitle = 'Mi Historial';
$action = 'historial';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Historial de Fichajes</h1>
        <p class="page-subtitle">Consulta todos tus registros de entrada y salida</p>
    </div>
</div>

<!-- Filtros -->
<div class="card filter-card">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="historial">
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

<!-- Resumen -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?= count($resumenDiario) ?></div>
        <div class="stat-label">Días trabajados</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-value"><?= number_format($totalHoras, 1) ?>h</div>
        <div class="stat-label">Total horas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?= count(array_filter($resumenDiario, fn($r) => $r['hubo_tardanza'])) ?></div>
        <div class="stat-label">Tardanzas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-value"><?= count(array_filter($resumenDiario, fn($r) => $r['horas_trabajadas'] !== null)) ?></div>
        <div class="stat-label">Días completos</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Resumen diario -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Resumen por día</h3>
        </div>
        <div class="card-body">
            <?php if (empty($resumenDiario)): ?>
                <p class="empty-state">No hay registros en el periodo seleccionado.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Primera Entrada</th>
                            <th>Última Salida</th>
                            <th>Horas</th>
                            <th>Entradas</th>
                            <th>Salidas</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenDiario as $dia): ?>
                            <tr class="<?= $dia['hubo_tardanza'] ? 'row-warn' : '' ?>">
                                <td><?= date('D d/m/Y', strtotime($dia['fecha'])) ?></td>
                                <td class="mono"><?= $dia['primera_entrada'] ? substr($dia['primera_entrada'], 11, 5) : '—' ?></td>
                                <td class="mono"><?= $dia['ultima_salida'] ? substr($dia['ultima_salida'], 11, 5) : '—' ?></td>
                                <td class="mono bold"><?= $dia['horas_trabajadas'] !== null ? number_format($dia['horas_trabajadas'], 1) . 'h' : '—' ?></td>
                                <td class="center"><?= $dia['num_entradas'] ?></td>
                                <td class="center"><?= $dia['num_salidas'] ?></td>
                                <td>
                                    <?php if ($dia['hubo_tardanza']): ?>
                                        <span class="badge badge-warn">Tardanza</span>
                                    <?php elseif ($dia['hubo_salida_anticipada']): ?>
                                        <span class="badge badge-warn">Salida ant.</span>
                                    <?php elseif ($dia['num_salidas'] === '0' || $dia['num_salidas'] == 0): ?>
                                        <span class="badge badge-error">Sin salida</span>
                                    <?php else: ?>
                                        <span class="badge badge-ok">Correcto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detalle de fichajes -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Detalle de fichajes</h3>
        </div>
        <div class="card-body">
            <?php if (empty($fichajes)): ?>
                <p class="empty-state">Sin fichajes en el periodo.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Fecha</th><th>Hora</th><th>Tipo</th><th>Incidencia</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fichajes as $f): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($f['timestamp'])) ?></td>
                                <td class="mono"><?= substr($f['timestamp'], 11, 8) ?></td>
                                <td>
                                    <span class="badge <?= $f['tipo'] === 'entrada' ? 'badge-entrada' : 'badge-salida' ?>">
                                        <?= ucfirst($f['tipo']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($f['es_tardanza']): ?>
                                        <span class="badge badge-warn">+<?= $f['minutos_diferencia'] ?>min</span>
                                    <?php elseif ($f['es_salida_anticipada']): ?>
                                        <span class="badge badge-warn">-<?= $f['minutos_diferencia'] ?>min ant.</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
