<?php
$pageTitle = 'Horarios';
$action = 'admin_horarios';
include __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestión de Horarios</h1>
        <p class="page-subtitle">Define los horarios laborales y asígnalos a empleados</p>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-ok"><span class="alert-icon">✓</span><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nuevo / Editar Horario</h3>
        </div>
        <div class="card-body">
            <form action="?action=admin_guardar_horario" method="POST" class="form-vertical" id="form-horario">
                <input type="hidden" name="id" id="hor-id" value="">
                <div class="form-group">
                    <label class="form-label">Nombre del horario *</label>
                    <input type="text" name="nombre" id="hor-nombre" class="form-input"
                        placeholder="ej: Jornada Completa Mañana" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Hora de entrada *</label>
                        <input type="time" name="hora_entrada" id="hor-entrada" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de salida *</label>
                        <input type="time" name="hora_salida" id="hor-salida" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tolerancia (minutos)</label>
                    <input type="number" name="tolerancia_minutos" id="hor-tol" class="form-input" value="15" min="0" max="60">
                    <span class="form-hint">Margen antes de marcar una tardanza</span>
                </div>
                <button type="submit" class="btn btn-primary">Guardar horario</button>
            </form>
        </div>
    </div>

    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Horarios configurados</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Nombre</th><th>Entrada</th><th>Salida</th><th>Tolerancia</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['nombre']) ?></td>
                            <td class="mono"><?= substr($h['hora_entrada'], 0, 5) ?></td>
                            <td class="mono"><?= substr($h['hora_salida'], 0, 5) ?></td>
                            <td><?= $h['tolerancia_minutos'] ?> min</td>
                            <td>
                                <button class="btn btn-sm btn-outline"
                                    onclick="editarHorario(<?= htmlspecialchars(json_encode($h)) ?>)">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editarHorario(h) {
    document.getElementById('hor-id').value = h.id;
    document.getElementById('hor-nombre').value = h.nombre;
    document.getElementById('hor-entrada').value = h.hora_entrada.substring(0,5);
    document.getElementById('hor-salida').value = h.hora_salida.substring(0,5);
    document.getElementById('hor-tol').value = h.tolerancia_minutos;
    document.getElementById('form-horario').scrollIntoView({behavior:'smooth'});
}
</script>

<?php include __DIR__ . '/../shared/footer.php'; ?>
