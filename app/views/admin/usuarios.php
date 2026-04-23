<?php
$page_title = 'Gestión de Usuarios';
$extra_js   = 'usuarios';
require_once __DIR__ . '/../shared/header.php';

$rol_labels = [
    'admin'             => 'Administrador',
    'empleado'          => 'Empleado',
    'jefe'              => 'Jefe',
    'jefe_departamento' => 'Jefe de Dpto.',
];
$depto_labels = [
    'rrhh'         => 'RRHH',
    'direccion'    => 'Dirección',
    'contabilidad' => 'Contabilidad',
    'desarrollo'   => 'Desarrollo',
    'diseno'       => 'Diseño',
];
?>

<div class="admin-section">

    <!-- Barra de herramientas -->
    <div class="toolbar">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="toolbar-filters">
            <input type="hidden" name="action" value="usuarios">
            <input type="text" name="search" class="form-input" placeholder="Buscar por nombre o email..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="departamento" class="form-input form-input--sm">
                <option value="">Todos los deptos.</option>
                <?php foreach ($depto_labels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($_GET['departamento']??'') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <select name="rol" class="form-input form-input--sm">
                <option value="">Todos los roles</option>
                <?php foreach ($rol_labels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($_GET['rol']??'') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-ghost">Filtrar</button>
        </form>
        <button class="btn btn-primary" id="btnNuevoUsuario">+ Nuevo usuario</button>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card">
        <div class="card-body no-pad">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Empleado</th>
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
                <tr id="user-row-<?= $u['id'] ?>">
                    <td>
                        <div class="empleado-cell">
                            <div class="empleado-avatar <?= $u['activo'] ? '' : 'avatar-inactive' ?>"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></div>
                            <div>
                                <div class="empleado-nombre"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-role badge-<?= $u['rol'] ?>"><?= $rol_labels[$u['rol']] ?? ucfirst($u['rol']) ?></span></td>
                    <td><span class="depto-chip depto-<?= $u['departamento'] ?>"><?= $depto_labels[$u['departamento']] ?? ucfirst($u['departamento']) ?></span></td>
                    <td><?= htmlspecialchars($u['horario_nombre'] ?? '—') ?></td>
                    <td><?= $u['activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-gray">Inactivo</span>' ?></td>
                    <td class="td-actions">
                        <button class="btn-icon" title="Editar"
                            onclick='editarUsuario(<?= json_encode($u) ?>)'>✎</button>
                        <button class="btn-icon" title="Asignar proyecto"
                            onclick="abrirAsignarProyecto(<?= $u['id'] ?>, '<?= addslashes($u['nombre'] . ' ' . $u['apellidos']) ?>')">◆</button>
                        <?php if ($u['activo']): ?>
                        <button class="btn-icon btn-icon--danger" title="Desactivar"
                            onclick="eliminarUsuario(<?= $u['id'] ?>)">✕</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /admin-section -->

<!-- ── Modal: Crear / Editar Usuario ── -->
<div class="modal-overlay hidden" id="modalUsuario">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalUsuarioTitle">Nuevo usuario</h3>
            <button class="modal-close" id="closeModalUsuario">✕</button>
        </div>
        <div class="modal-body">
            <form id="usuarioForm">
                <input type="hidden" id="uid" name="id" value="0">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre <span class="required">*</span></label>
                        <input type="text" id="uNombre" name="nombre" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos <span class="required">*</span></label>
                        <input type="text" id="uApellidos" name="apellidos" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" id="uEmail" name="email" class="form-input" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Rol <span class="required">*</span></label>
                        <select id="uRol" name="rol" class="form-input" required>
                            <option value="empleado">Empleado</option>
                            <option value="jefe">Jefe</option>
                            <option value="jefe_departamento">Jefe de Departamento</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento <span class="required">*</span></label>
                        <select id="uDepartamento" name="departamento" class="form-input" required>
                            <option value="desarrollo">Desarrollo</option>
                            <option value="diseno">Diseño</option>
                            <option value="contabilidad">Contabilidad</option>
                            <option value="rrhh">RRHH</option>
                            <option value="direccion">Dirección</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Horario asignado</label>
                    <select id="uHorario" name="id_horario" class="form-input">
                        <option value="">Sin horario</option>
                        <?php foreach ($horarios as $h): ?>
                        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['nombre']) ?> (<?= substr($h['hora_inicio'],0,5) ?> - <?= substr($h['hora_fin'],0,5) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña <span class="required" id="passReq">*</span></label>
                    <input type="password" id="uPassword" name="password" class="form-input" placeholder="Dejar vacío para no cambiar (edición)">
                </div>
                <div class="form-group form-group--checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" id="uActivo" name="activo" value="1" checked>
                        <span>Usuario activo</span>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-ghost" id="cancelUsuario">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal: Asignar Proyectos ── -->
<div class="modal-overlay hidden" id="modalProyectos">
    <div class="modal">
        <div class="modal-header">
            <h3>Asignar proyectos a <span id="asignarNombre"></span></h3>
            <button class="modal-close" id="closeModalProyectos">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="asignarUid" value="">
            <div class="proyectos-checkboxes">
                <?php foreach ($proyectos as $p): ?>
                <label class="checkbox-label proyecto-check" data-pid="<?= $p['id'] ?>">
                    <input type="checkbox" class="proyecto-checkbox" value="<?= $p['id'] ?>">
                    <span class="chip-dot" style="background:<?= htmlspecialchars($p['color']) ?>"></span>
                    <?= htmlspecialchars($p['nombre']) ?>
                </label>
                <?php endforeach; ?>
            </div>
            <p class="text-muted mt-2">Los cambios se guardan al marcar/desmarcar.</p>
        </div>
    </div>
</div>

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
