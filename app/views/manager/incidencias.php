<?php
$pageTitle = 'Incidencias';
$action = 'manager_incidencias';
include __DIR__ . '/../shared/header.php';

$tiposLabel = [
    'retraso'          => '⏰ Retraso',
    'olvido_fichaje'   => '📋 Olvido fichaje',
    'salida_anticipada'=> '↑ Salida anticipada',
    'error_fichaje'    => '⚠️ Error fichaje',
];
$estadosLabel = [
    'pendiente' => 'Pendiente',
    'revisada'  => 'Revisada',
    'resuelta'  => 'Resuelta',
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestión de Incidencias</h1>
        <p class="page-subtitle">Revisa y gestiona las incidencias del equipo</p>
    </div>
</div>

<!-- Filtros -->
<div class="card filter-card">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="action" value="manager_incidencias">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-input">
                    <option value="">Todos</option>
                    <?php foreach ($estadosLabel as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($_GET['estado'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-input">
                    <option value="">Todos</option>
                    <?php foreach ($tiposLabel as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($_GET['tipo'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" class="form-input" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>">
            </div>
            <div class="form-group form-group-action">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>
</div>

<div class="card card-wide">
    <div class="card-header">
        <h3 class="card-title">Incidencias (<?= count($incidencias) ?>)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($incidencias)): ?>
            <p class="empty-state">No hay incidencias con los filtros aplicados. ✓</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Email</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencias as $inc): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm"><?= strtoupper(substr($inc['empleado_nombre'], 0, 1)) ?></div>
                                    <div>
                                        <div class="user-cell-name"><?= htmlspecialchars($inc['empleado_nombre']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($inc['departamento_nombre'] ?? '—') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-tipo-<?= $inc['tipo'] ?>">
                                    <?= $tiposLabel[$inc['tipo']] ?? $inc['tipo'] ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($inc['fecha'])) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($inc['descripcion'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-estado-inc-<?= $inc['estado'] ?>">
                                    <?= $estadosLabel[$inc['estado']] ?? $inc['estado'] ?>
                                </span>
                            </td>
                            <td class="center">
                                <?= $inc['email_enviado'] ? '<span class="badge badge-ok">✓</span>' : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td>
                                <?php if ($inc['estado'] === 'pendiente'): ?>
                                    <button class="btn btn-sm btn-outline"
                                        onclick="abrirModalResolver(<?= $inc['id'] ?>, '<?= htmlspecialchars(addslashes($inc['empleado_nombre'])) ?>')">
                                        Resolver
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">Resuelta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal resolver incidencia -->
<div id="modal-resolver" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Resolver Incidencia</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <div class="modal-body">
            <p id="modal-desc">Resolviendo incidencia de <strong id="modal-nombre"></strong></p>
            <form action="?action=resolver_incidencia" method="POST">
                <input type="hidden" name="id" id="modal-id">
                <div class="form-group">
                    <label class="form-label">Nota de resolución</label>
                    <textarea name="nota" class="form-input form-textarea" rows="3"
                        placeholder="Añade una nota explicativa (opcional)..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Marcar como resuelta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalResolver(id, nombre) {
    document.getElementById('modal-id').value = id;
    document.getElementById('modal-nombre').textContent = nombre;
    document.getElementById('modal-resolver').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modal-resolver').style.display = 'none';
}
document.getElementById('modal-resolver').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
