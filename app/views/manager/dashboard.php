<?php
$pageTitle = 'Panel de Mando';
$action = 'dashboard';
include __DIR__ . '/../shared/header.php';

// Agrupar fichajes de hoy por usuario
$fichajesMap = [];
foreach ($fichajesHoy as $f) {
    $uid = $f['id_usuario'];
    if (!isset($fichajesMap[$uid])) {
        $fichajesMap[$uid] = [
            'nombre'     => $f['nombre'] . ' ' . $f['apellidos'],
            'departamento' => $f['departamento_nombre'],
            'entrada'    => null,
            'salida'     => null,
            'tardanza'   => false,
        ];
    }
    if ($f['tipo'] === 'entrada' && !$fichajesMap[$uid]['entrada']) {
        $fichajesMap[$uid]['entrada']  = substr($f['timestamp'], 11, 5);
        $fichajesMap[$uid]['tardanza'] = (bool)$f['es_tardanza'];
    }
    if ($f['tipo'] === 'salida') {
        $fichajesMap[$uid]['salida'] = substr($f['timestamp'], 11, 5);
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Panel de Mando</h1>
        <p class="page-subtitle"><?= date('l, d \d\e F \d\e Y') ?> — Vista en tiempo real</p>
    </div>
    <div class="page-meta">
        <span class="meta-badge meta-badge-live">● En vivo</span>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?= count($fichajesMap) ?></div>
        <div class="stat-label">Han fichado hoy</div>
    </div>
    <div class="stat-card stat-card-red">
        <div class="stat-icon">🔕</div>
        <div class="stat-value"><?= count($sinFichar) ?></div>
        <div class="stat-label">Sin fichar hoy</div>
    </div>
    <div class="stat-card stat-card-yellow">
        <div class="stat-icon">⏰</div>
        <div class="stat-value"><?= count($tardanzasRecientes) ?></div>
        <div class="stat-label">Tardanzas hoy</div>
    </div>
    <div class="stat-card stat-card-orange">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?= $incidenciasPend ?></div>
        <div class="stat-label">Incidencias pendientes</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Estado fichaje en tiempo real -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Estado actual del equipo — hoy</h3>
            <a href="?action=manager_empleados" class="card-link">Ver detalle →</a>
        </div>
        <div class="card-body">
            <?php if (empty($fichajesMap)): ?>
                <p class="empty-state">Nadie ha fichado todavía hoy.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Departamento</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fichajesMap as $uid => $emp): ?>
                            <tr>
                                <td><?= htmlspecialchars($emp['nombre']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($emp['departamento'] ?? '—') ?></td>
                                <td class="mono"><?= $emp['entrada'] ?? '—' ?></td>
                                <td class="mono"><?= $emp['salida'] ?? '—' ?></td>
                                <td>
                                    <?php if ($emp['tardanza']): ?>
                                        <span class="badge badge-warn">Tardanza</span>
                                    <?php elseif ($emp['salida']): ?>
                                        <span class="badge badge-neutral">Fuera</span>
                                    <?php else: ?>
                                        <span class="badge badge-ok">Dentro ●</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sin fichar -->
    <?php if (!empty($sinFichar)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card-title-red">⚠ Sin fichar hoy</h3>
        </div>
        <div class="card-body">
            <?php foreach ($sinFichar as $emp): ?>
                <div class="alert-item">
                    <div class="alert-avatar"><?= strtoupper(substr($emp['nombre'], 0, 1)) ?></div>
                    <div>
                        <div class="alert-nombre"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></div>
                        <div class="alert-depto text-muted small"><?= htmlspecialchars($emp['departamento_nombre'] ?? '—') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tardanzas de hoy -->
    <?php if (!empty($tardanzasRecientes)): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card-title-yellow">⏰ Tardanzas de hoy</h3>
            <a href="?action=manager_incidencias" class="card-link">Ver todas →</a>
        </div>
        <div class="card-body">
            <?php foreach ($tardanzasRecientes as $t): ?>
                <div class="alert-item">
                    <div class="alert-avatar avatar-yellow"><?= strtoupper(substr($t['nombre'], 0, 1)) ?></div>
                    <div>
                        <div class="alert-nombre"><?= htmlspecialchars($t['nombre'] . ' ' . $t['apellidos']) ?></div>
                        <div class="text-muted small">
                            Entrada: <span class="mono"><?= $t['hora'] ?></span>
                            — <span class="badge badge-warn">+<?= $t['minutos_diferencia'] ?>min</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Accesos rápidos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Accesos rápidos</h3>
        </div>
        <div class="card-body">
            <div class="quick-links">
                <a href="?action=manager_empleados" class="quick-link-btn">👥 Ver empleados</a>
                <a href="?action=manager_incidencias" class="quick-link-btn">⚠️ Gestionar incidencias</a>
                <a href="?action=informes" class="quick-link-btn">📊 Generar informe</a>
                <a href="?action=admin_proyectos" class="quick-link-btn">🗂️ Ver proyectos</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
