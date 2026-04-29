<?php
$pageTitle = 'Panel Administración';
$action = 'dashboard';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Panel de Administración</h1>
        <p class="page-subtitle">Gestión completa del sistema TimeControl</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card-blue">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?= $totalUsuarios ?></div>
        <div class="stat-label">Usuarios activos</div>
    </div>
    <div class="stat-card stat-card-green">
        <div class="stat-icon">🗂️</div>
        <div class="stat-value"><?= $totalProyectos ?></div>
        <div class="stat-label">Proyectos activos</div>
    </div>
    <div class="stat-card stat-card-orange">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?= $incidenciasPend ?></div>
        <div class="stat-label">Incidencias pendientes</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Gestión rápida -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestión de Usuarios</h3>
        </div>
        <div class="card-body">
            <div class="quick-links">
                <a href="?action=admin_nuevo_usuario" class="quick-link-btn quick-link-primary">+ Nuevo usuario</a>
                <a href="?action=admin_usuarios" class="quick-link-btn">Ver todos los usuarios</a>
                <a href="?action=admin_horarios" class="quick-link-btn">Gestionar horarios</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Gestión de Proyectos</h3>
        </div>
        <div class="card-body">
            <div class="quick-links">
                <a href="?action=admin_proyectos" class="quick-link-btn quick-link-primary">Ver proyectos</a>
                <a href="?action=admin_asignar_proyecto" class="quick-link-btn">Asignar empleados</a>
                <a href="?action=informes" class="quick-link-btn">Ver informes</a>
                <a href="?action=manager_incidencias" class="quick-link-btn">Gestionar incidencias</a>
            </div>
        </div>
    </div>

    <!-- Lista de usuarios -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Usuarios del sistema</h3>
            <a href="?action=admin_usuarios" class="card-link">Gestionar →</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Departamento</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($empleados, 0, 10) as $emp): ?>
                        <tr>
                            <td><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($emp['email']) ?></td>
                            <td><span class="badge badge-rol-<?= $emp['rol'] ?>"><?= str_replace('_', ' ', $emp['rol']) ?></span></td>
                            <td class="text-muted small"><?= htmlspecialchars($emp['departamento_nombre'] ?? '—') ?></td>
                            <td><span class="badge <?= $emp['activo'] ? 'badge-ok' : 'badge-error' ?>"><?= $emp['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Proyectos -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Proyectos</h3>
            <a href="?action=admin_proyectos" class="card-link">Gestionar →</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Proyecto</th><th>Estado</th><th>Responsable</th><th>Inicio</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><span class="badge badge-estado-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                            <td class="text-muted small"><?= htmlspecialchars($p['responsable_nombre'] ?? '—') ?></td>
                            <td class="text-muted small"><?= $p['fecha_inicio'] ? date('d/m/Y', strtotime($p['fecha_inicio'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
