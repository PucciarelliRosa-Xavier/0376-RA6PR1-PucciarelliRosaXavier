<?php
$page_title = 'Gestión de Horarios';
$extra_js   = 'horarios';
require_once __DIR__ . '/../shared/header.php';
?>

<div class="admin-section">
    <div class="toolbar">
        <h2>Horarios definidos</h2>
        <button class="btn btn-primary" id="btnNuevoHorario">+ Nuevo horario</button>
    </div>

    <div class="horarios-grid">
        <?php foreach ($horarios as $h): ?>
        <div class="horario-card">
            <div class="horario-card-header">
                <div class="horario-icon">◷</div>
                <div class="horario-card-actions">
                    <button class="btn-icon" onclick='editarHorario(<?= json_encode($h) ?>)' title="Editar">✎</button>
                </div>
            </div>
            <div class="horario-card-body">
                <h3><?= htmlspecialchars($h['nombre']) ?></h3>
                <div class="horario-times">
                    <div class="horario-time">
                        <span class="time-label">Entrada</span>
                        <span class="time-val"><?= substr($h['hora_inicio'], 0, 5) ?></span>
                    </div>
                    <div class="horario-arrow">→</div>
                    <div class="horario-time">
                        <span class="time-label">Salida</span>
                        <span class="time-val"><?= substr($h['hora_fin'], 0, 5) ?></span>
                    </div>
                </div>
                <p class="tolerancia-info">Tolerancia: <?= $h['tolerancia'] ?> min</p>
            </div>
            <div class="horario-card-footer">
                <span><?= $h['num_usuarios'] ?> empleados asignados</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal horario -->
<div class="modal-overlay hidden" id="modalHorario">
    <div class="modal modal--sm">
        <div class="modal-header">
            <h3 id="modalHorarioTitle">Nuevo horario</h3>
            <button class="modal-close" id="closeModalHorario">✕</button>
        </div>
        <div class="modal-body">
            <form id="horarioForm">
                <input type="hidden" id="hid" name="id" value="0">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" id="hNombre" name="nombre" class="form-input" placeholder="Ej: Jornada Completa 9-18" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Hora entrada <span class="required">*</span></label>
                        <input type="time" id="hInicio" name="hora_inicio" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora salida <span class="required">*</span></label>
                        <input type="time" id="hFin" name="hora_fin" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tolerancia (minutos)</label>
                    <input type="number" id="hTolerancia" name="tolerancia" class="form-input" value="10" min="0" max="60">
                    <span class="form-hint">Minutos de margen antes de registrar retraso</span>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalHorario').classList.add('hidden')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
