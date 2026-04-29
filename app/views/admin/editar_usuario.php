<?php
$pageTitle = 'Editar Usuario';
$action = 'admin_editar_usuario';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Editar Usuario</h1>
        <p class="page-subtitle">Modificar datos de <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></p>
    </div>
    <a href="?action=admin_usuarios" class="btn btn-outline">← Volver</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="?action=admin_guardar_usuario" method="POST" class="form-vertical">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-input" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-input" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nueva contraseña <span class="text-muted">(dejar vacío para no cambiar)</span></label>
                <input type="password" name="password" class="form-input" minlength="8">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rol *</label>
                    <select name="rol" class="form-input" required>
                        <?php foreach (['empleado','jefe','jefe_departamento','admin'] as $r): ?>
                            <option value="<?= $r ?>" <?= $usuario['rol'] === $r ? 'selected' : '' ?>>
                                <?= str_replace('_', ' ', ucfirst($r)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Departamento</label>
                    <select name="id_departamento" class="form-input">
                        <option value="">Sin departamento</option>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $usuario['id_departamento'] == $d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Horario</label>
                    <select name="id_horario" class="form-input">
                        <option value="">Sin horario</option>
                        <?php foreach ($horarios as $h): ?>
                            <option value="<?= $h['id'] ?>" <?= $usuario['id_horario'] == $h['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h['nombre']) ?> (<?= substr($h['hora_entrada'],0,5) ?> - <?= substr($h['hora_salida'],0,5) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="activo" class="form-input">
                        <option value="1" <?= $usuario['activo'] ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= !$usuario['activo'] ? 'selected' : '' ?>>Dado de baja</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="?action=admin_usuarios" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
