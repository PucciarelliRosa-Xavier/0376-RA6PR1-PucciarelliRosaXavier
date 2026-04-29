<?php
$pageTitle = 'Gestión de Usuarios';
$action = 'admin_usuarios';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestión de Usuarios</h1>
        <p class="page-subtitle">Alta, baja y modificación de usuarios del sistema</p>
    </div>
    <a href="?action=admin_nuevo_usuario" class="btn btn-primary">+ Nuevo usuario</a>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-ok"><span class="alert-icon">✓</span><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><span class="alert-icon">✕</span><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-wide">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Departamento</th>
                    <th>Horario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr class="<?= !$u['activo'] ? 'row-inactive' : '' ?>">
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-sm"><?= strtoupper(substr($u['nombre'], 0, 1)) ?></div>
                                <span><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></span>
                            </div>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge badge-rol-<?= $u['rol'] ?>"><?= str_replace('_', ' ', $u['rol']) ?></span></td>
                        <td class="text-muted small"><?= htmlspecialchars($u['departamento_nombre'] ?? '—') ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($u['horario_nombre'] ?? '—') ?></td>
                        <td><span class="badge <?= $u['activo'] ? 'badge-ok' : 'badge-error' ?>"><?= $u['activo'] ? 'Activo' : 'Baja' ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a href="?action=admin_editar_usuario&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">Editar</a>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <a href="?action=admin_eliminar_usuario&id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Dar de baja a <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')">
                                       Baja
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
