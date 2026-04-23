<?php
$page_title = 'Historial de Fichajes';
require_once __DIR__ . '/../shared/header.php';
?>

<div class="historial-container">

    <!-- Filtro de mes -->
    <div class="filter-bar">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="filter-form">
            <input type="hidden" name="action" value="fichajes_historial">
            <div class="form-group form-group--inline">
                <label class="form-label">Mes</label>
                <input type="month" name="mes" class="form-input" value="<?= htmlspecialchars($mes) ?>" onchange="this.form.submit()">
            </div>
        </form>
        <div class="mes-total">
            Total del mes: <strong><?= FichajeModel::formatearHoras($total_horas) ?></strong>
        </div>
    </div>

    <!-- Resumen rápido -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num"><?= count($fichajes) ?></div>
            <div class="stat-label">Días registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= FichajeModel::formatearHoras($total_horas) ?></div>
            <div class="stat-label">Horas trabajadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= count($incidencias) ?></div>
            <div class="stat-label">Incidencias</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">
                <?= $total_horas > 0 ? number_format($total_horas / max(count($fichajes), 1), 1) : 0 ?>h
            </div>
            <div class="stat-label">Media diaria</div>
        </div>
    </div>

    <!-- Tabla de fichajes -->
    <div class="card">
        <div class="card-header">
            <span class="card-icon">◷</span>
            <h2 class="card-title">Detalle por día</h2>
        </div>
        <div class="card-body no-pad">
            <?php if (empty($fichajes)): ?>
            <div class="empty-state">
                <p>No hay fichajes registrados en este período.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Horas</th>
                        <th>Estado</th>
                        <th>Incidencias</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fichajes as $f):
                    $tipos_arr = $f['tipos_arr'];
                    $horas_arr = $f['horas_arr'];

                    // Buscar primera entrada y última salida
                    $primera_entrada = null;
                    $ultima_salida   = null;
                    for ($i = 0; $i < count($tipos_arr); $i++) {
                        if ($tipos_arr[$i] === 'entrada' && !$primera_entrada) $primera_entrada = $horas_arr[$i];
                        if ($tipos_arr[$i] === 'salida')                        $ultima_salida   = $horas_arr[$i];
                    }

                    // Incidencias del día
                    $incs_dia = array_filter($incidencias, fn($i) => $i['fecha'] === $f['fecha']);
                    $tiene_retraso  = count(array_filter($incs_dia, fn($i) => $i['tipo'] === 'retraso')) > 0;
                    $tiene_olvido   = count(array_filter($incs_dia, fn($i) => str_contains($i['tipo'], 'olvido'))) > 0;

                    $horas_ok = $f['horas_trabajadas'] >= 7;
                    $row_class = ($tiene_retraso || $tiene_olvido) ? 'row-warning' : ($horas_ok ? '' : 'row-alert');
                ?>
                <tr class="<?= $row_class ?>">
                    <td>
                        <div class="fecha-cell">
                            <span class="fecha-dia"><?= date('D', strtotime($f['fecha'])) ?></span>
                            <span class="fecha-num"><?= date('d/m/Y', strtotime($f['fecha'])) ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if ($primera_entrada): ?>
                        <span class="time-badge time-badge--in"><?= substr($primera_entrada, 0, 5) ?></span>
                        <?php else: ?>
                        <span class="time-badge time-badge--missing">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($ultima_salida): ?>
                        <span class="time-badge time-badge--out"><?= substr($ultima_salida, 0, 5) ?></span>
                        <?php else: ?>
                        <span class="time-badge time-badge--missing">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= FichajeModel::formatearHoras($f['horas_trabajadas']) ?></strong>
                    </td>
                    <td>
                        <?php if ($f['horas_trabajadas'] >= 7): ?>
                        <span class="badge badge-success">✓ Completo</span>
                        <?php elseif ($f['horas_trabajadas'] > 0): ?>
                        <span class="badge badge-warning">◑ Parcial</span>
                        <?php else: ?>
                        <span class="badge badge-error">✗ Sin datos</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($tiene_retraso): ?>
                        <span class="badge badge-warning">Retraso</span>
                        <?php endif; ?>
                        <?php if ($tiene_olvido): ?>
                        <span class="badge badge-error">Olvido</span>
                        <?php endif; ?>
                        <?php if (!$tiene_retraso && !$tiene_olvido): ?>
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

    <!-- Incidencias del mes -->
    <?php if (!empty($incidencias)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <span class="card-icon">⚠</span>
            <h2 class="card-title">Incidencias del mes</h2>
        </div>
        <div class="card-body no-pad">
            <table class="data-table">
                <thead>
                    <tr><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Estado</th></tr>
                </thead>
                <tbody>
                <?php foreach ($incidencias as $inc): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($inc['fecha'])) ?></td>
                    <td><span class="badge badge-<?= $inc['tipo'] === 'retraso' ? 'warning' : 'error' ?>"><?= str_replace('_', ' ', ucfirst($inc['tipo'])) ?></span></td>
                    <td><?= htmlspecialchars($inc['descripcion']) ?></td>
                    <td><span class="badge badge-<?= $inc['estado'] === 'resuelta' ? 'success' : 'info' ?>"><?= ucfirst($inc['estado']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
