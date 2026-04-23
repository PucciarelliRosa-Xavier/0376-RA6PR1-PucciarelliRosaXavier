<?php
$page_title = 'Incidencias';
require_once __DIR__ . '/../shared/header.php';

$tipo_labels = [
    'retraso'           => 'Retraso',
    'olvido_salida'     => 'Olvido salida',
    'olvido_entrada'    => 'Olvido entrada',
    'salida_anticipada' => 'Salida anticipada',
    'error'             => 'Error',
];
$tipo_badge = [
    'retraso'           => 'warning',
    'olvido_salida'     => 'error',
    'olvido_entrada'    => 'error',
    'salida_anticipada' => 'info',
    'error'             => 'error',
];
?>

<div class="incidencias-container">

    <!-- Filtros -->
    <div class="card filter-card">
        <div class="card-body">
            <form method="GET" action="<?= APP_URL ?>/index.php" class="filter-form filter-grid">
                <input type="hidden" name="action" value="incidencias">
                <div class="form-group">
                    <label class="form-label">Desde</label>
                    <input type="date" name="desde" class="form-input" value="<?= htmlspecialchars($desde) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="hasta" class="form-input" value="<?= htmlspecialchars($hasta) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-input">
                        <option value="">Todos</option>
                        <?php foreach ($tipo_labels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($_GET['tipo']??'') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-input">
                        <option value="">Todos</option>
                        <option value="pendiente"  <?= ($_GET['estado']??'') === 'pendiente'  ? 'selected':'' ?>>Pendiente</option>
                        <option value="revisada"   <?= ($_GET['estado']??'') === 'revisada'   ? 'selected':'' ?>>Revisada</option>
                        <option value="resuelta"   <?= ($_GET['estado']??'') === 'resuelta'   ? 'selected':'' ?>>Resuelta</option>
                    </select>
                </div>
                <div class="form-group form-group--actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="<?= APP_URL ?>/index.php?action=incidencias" class="btn btn-ghost">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="stats-row">
        <?php
        $por_tipo = [];
        foreach ($incidencias as $inc) {
            $t = $inc['tipo'];
            $por_tipo[$t] = ($por_tipo[$t] ?? 0) + 1;
        }
        ?>
        <div class="stat-card"><div class="stat-num"><?= count($incidencias) ?></div><div class="stat-label">Total</div></div>
        <?php foreach ($tipo_labels as $k => $v): ?>
        <?php if (isset($por_tipo[$k])): ?>
        <div class="stat-card"><div class="stat-num"><?= $por_tipo[$k] ?></div><div class="stat-label"><?= $v ?></div></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-header">
            <span class="card-icon">⚠</span>
            <h2 class="card-title">Listado de incidencias</h2>
        </div>
        <div class="card-body no-pad">
            <?php if (empty($incidencias)): ?>
            <div class="empty-state"><p>✓ No hay incidencias con los filtros actuales.</p></div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>Departamento</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($incidencias as $inc): ?>
                <tr id="inc-row-<?= $inc['id'] ?>">
                    <td><?= date('d/m/Y', strtotime($inc['fecha'])) ?></td>
                    <td>
                        <div class="empleado-mini">
                            <div class="empleado-avatar empleado-avatar--sm">
                                <?= mb_strtoupper(mb_substr($inc['nombre'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($inc['nombre'] . ' ' . $inc['apellidos']) ?>
                        </div>
                    </td>
                    <td><span class="depto-chip depto-<?= $inc['departamento'] ?>"><?= htmlspecialchars(ucfirst($inc['departamento'])) ?></span></td>
                    <td>
                        <span class="badge badge-<?= $tipo_badge[$inc['tipo']] ?? 'info' ?>">
                            <?= $tipo_labels[$inc['tipo']] ?? ucfirst($inc['tipo']) ?>
                        </span>
                    </td>
                    <td class="td-truncate"><?= htmlspecialchars($inc['descripcion'] ?? '—') ?></td>
                    <td><?= $inc['email_enviado'] ? '<span class="badge badge-success">✓ Enviado</span>' : '<span class="badge badge-gray">No enviado</span>' ?></td>
                    <td>
                        <span class="badge badge-<?= $inc['estado'] === 'resuelta' ? 'success' : ($inc['estado'] === 'revisada' ? 'info' : 'warning') ?>">
                            <?= ucfirst($inc['estado']) ?>
                        </span>
                    </td>
                    <td class="td-actions">
                        <?php if ($inc['estado'] === 'pendiente'): ?>
                        <button class="btn btn-sm btn-ghost" onclick="resolverInc(<?= $inc['id'] ?>, 'revisada')">Revisar</button>
                        <button class="btn btn-sm btn-primary" onclick="resolverInc(<?= $inc['id'] ?>, 'resuelta')">Resolver</button>
                        <?php elseif ($inc['estado'] === 'revisada'): ?>
                        <button class="btn btn-sm btn-primary" onclick="resolverInc(<?= $inc['id'] ?>, 'resuelta')">Resolver</button>
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

</div><!-- /incidencias-container -->

<script>
const APP_URL = '<?= APP_URL ?>';

async function resolverInc(id, estado) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('estado', estado);

    const res  = await fetch(APP_URL + '/index.php?action=incidencia_resolver', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.ok) {
        const row = document.getElementById('inc-row-' + id);
        if (row) row.style.opacity = '0.4';
        showToast(data.msg, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(data.msg, 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
