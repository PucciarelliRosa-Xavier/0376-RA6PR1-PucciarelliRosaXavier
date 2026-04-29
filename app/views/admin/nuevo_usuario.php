<?php
$pageTitle = 'Nuevo Usuario';
$action = 'admin_nuevo_usuario';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Nuevo Usuario</h1>
        <p class="page-subtitle">Crear una nueva cuenta de acceso al sistema</p>
    </div>
    <a href="?action=admin_usuarios" class="btn btn-outline">← Volver</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="?action=admin_guardar_usuario" method="POST" class="form-vertical">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña *</label>
                <input type="password" name="password" class="form-input" required minlength="8">
                <span class="form-hint">Mínimo 8 caracteres</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="rol" class="form-input" required>
                        <option value="empleado">Empleado</option>
                        <option value="jefe">Jefe</option>
                        <option value="jefe_departamento">Jefe de Departamento</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Departamento</label>
                    <select name="id_departamento" class="form-input">
                        <option value="">Sin departamento</option>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Horario asignado</label>
                <select name="id_horario" class="form-input">
                    <option value="">Sin horario</option>
                    <?php foreach ($horarios as $h): ?>
                        <option value="<?= $h['id'] ?>">
                            <?= htmlspecialchars($h['nombre']) ?> (<?= substr($h['hora_entrada'],0,5) ?> - <?= substr($h['hora_salida'],0,5) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="?action=admin_usuarios" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear usuario</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
