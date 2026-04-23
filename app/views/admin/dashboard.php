<?php
$page_title = 'Panel de Administración';
require_once __DIR__ . '/../shared/header.php';

$depto_labels = [
    'rrhh'         => 'RRHH',
    'direccion'    => 'Dirección',
    'contabilidad' => 'Contabilidad',
    'desarrollo'   => 'Desarrollo',
    'diseno'       => 'Diseño',
];
?>

<div class="admin-dashboard">

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon">◎</div>
            <div class="kpi-num"><?= $stats['total_empleados'] ?></div>
            <div class="kpi-label">Empleados activos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon">◆</div>
            <div class="kpi-num"><?= $stats['proyectos_activos'] ?></div>
            <div class="kpi-label">Proyectos activos</div>
        </div>
        <div class="kpi-card kpi-alerta">
            <div class="kpi-icon">⚠</div>
            <div class="kpi-num"><?= $stats['incidencias_pendientes'] ?></div>
            <div class="kpi-label">Incidencias pendientes</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon">◷</div>
            <div class="kpi-num"><?= $stats['fichajes_hoy'] ?></div>
            <div class="kpi-label">Fichajes hoy</div>
        </div>
    </div>

    <!-- Accesos rápidos de admin -->
    <div class="admin-shortcuts">
        <a href="<?= APP_URL ?>/index.php?action=usuarios" class="shortcut-card">
            <span class="shortcut-icon">◎</span>
            <span class="shortcut-label">Gestionar usuarios</span>
            <span class="shortcut-arrow">→</span>
        </a>
        <a href="<?= APP_URL ?>/index.php?action=proyectos" class="shortcut-card">
            <span class="shortcut-icon">◆</span>
            <span class="shortcut-label">Gestionar proyectos</span>
            <span class="shortcut-arrow">→</span>
        </a>
        <a href="<?= APP_URL ?>/index.php?action=horarios" class="shortcut-card">
            <span class="shortcut-icon">◐</span>
            <span class="shortcut-label">Definir horarios</span>
            <span class="shortcut-arrow">→</span>
        </a>
        <a href="<?= APP_URL ?>/index.php?action=informes" class="shortcut-card">
            <span class="shortcut-icon">◈</span>
            <span class="shortcut-label">Ver informes</span>
            <span class="shortcut-arrow">→</span>
        </a>
        <a href="<?= APP_URL ?>/index.php?action=incidencias" class="shortcut-card">
            <span class="shortcut-icon">⚠</span>
            <span class="shortcut-label">Incidencias</span>
            <span class="shortcut-arrow">→</span>
        </a>
    </div>

    <div class="admin-grid">

        <!-- Incidencias recientes -->
        <section class="card">
            <div class="card-header">
                <span class="card-icon">⚠</span>
                <h2 class="card-title">Incidencias pendientes</h2>
                <a href="<?= APP_URL ?>/index.php?action=incidencias" class="card-action">Ver todas →</a>
            </div>
            <div class="card-body no-pad">
                <?php if (empty($incidencias_recientes)): ?>
                <div class="empty-state"><p>✓ Sin incidencias pendientes</p></div>
                <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Empleado</th><th>Dpto.</th><th>Tipo</th><th>Fecha</th></tr></thead>
                    <tbody>
                    <?php foreach ($incidencias_recientes as $inc): ?>
                    <tr>
                        <td><?= htmlspecialchars($inc['nombre'] . ' ' . $inc['apellidos']) ?></td>
                        <td><span class="depto-chip depto-<?= $inc['departamento'] ?>"><?= $depto_labels[$inc['departamento']] ?? ucfirst($inc['departamento']) ?></span></td>
                        <td><span class="badge badge-warning"><?= str_replace('_', ' ', ucfirst($inc['tipo'])) ?></span></td>
                        <td><?= date('d/m', strtotime($inc['fecha'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sin fichar hoy -->
        <section class="card">
            <div class="card-header">
                <span class="card-icon">○</span>
                <h2 class="card-title">Sin fichar hoy (<?= count($sin_fichar_hoy) ?>)</h2>
            </div>
            <div class="card-body no-pad">
                <?php if (empty($sin_fichar_hoy)): ?>
                <div class="empty-state"><p>✓ Todos han fichado hoy</p></div>
                <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Empleado</th><th>Departamento</th><th>Hora entrada</th></tr></thead>
                    <tbody>
                    <?php foreach ($sin_fichar_hoy as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></td>
                        <td><span class="depto-chip depto-<?= $u['departamento'] ?>"><?= $depto_labels[$u['departamento']] ?? ucfirst($u['departamento']) ?></span></td>
                        <td><?= substr($u['hora_inicio'], 0, 5) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </section>

    </div><!-- /admin-grid -->

</div><!-- /admin-dashboard -->

<?php require_once __DIR__ . '/../shared/footer.php'; ?>
