<?php
$page_title = 'Mi Dashboard';
$extra_js   = 'fichaje';
require_once __DIR__ . '/../shared/header.php';

$horario = $_SESSION['user_horario'] ?? [];
?>

<div class="dashboard-grid">

    <!-- ── Fichaje Principal ── -->
    <section class="card card--fichaje" id="fichajeCard">
        <div class="card-body fichaje-body">
            <div class="fichaje-status" id="fichajeStatus">
                <div class="pulse-ring" id="pulseRing"></div>
                <div class="status-dot" id="statusDot"></div>
            </div>
            <div class="fichaje-info">
                <div class="fichaje-estado-label" id="estadoLabel">Cargando...</div>
                <div class="fichaje-hora" id="fichajeHora">--:--</div>
                <div class="fichaje-horas-hoy">
                    Hoy: <strong id="horasHoy">0h 0m</strong>
                </div>
            </div>
            <button class="btn-fichar" id="btnFichar" disabled>
                <span id="btnFicharIcon">⏺</span>
                <span id="btnFicharText">...</span>
            </button>
        </div>
        <!-- Timeline del día -->
        <div class="fichaje-timeline" id="fichajeTimeline"></div>
    </section>

    <!-- ── Horario asignado ── -->
    <section class="card">
        <div class="card-header">
            <span class="card-icon">◷</span>
            <h2 class="card-title">Mi horario</h2>
        </div>
        <div class="card-body">
            <?php if ($horario && $horario['inicio']): ?>
            <div class="horario-display">
                <div class="horario-bloque">
                    <div class="horario-label">Entrada</div>
                    <div class="horario-hora"><?= substr($horario['inicio'], 0, 5) ?></div>
                </div>
                <div class="horario-sep">→</div>
                <div class="horario-bloque">
                    <div class="horario-label">Salida</div>
                    <div class="horario-hora"><?= substr($horario['fin'], 0, 5) ?></div>
                </div>
            </div>
            <p class="horario-nombre"><?= htmlspecialchars($horario['nombre'] ?? '') ?></p>
            <p class="horario-tolerancia">Tolerancia: <?= (int)($horario['tolerancia'] ?? 10) ?> minutos</p>
            <?php else: ?>
            <p class="text-muted">Sin horario asignado. Contacta con tu responsable.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Proyectos asignados ── -->
    <section class="card card--proyectos">
        <div class="card-header">
            <span class="card-icon">◆</span>
            <h2 class="card-title">Mis proyectos</h2>
            <a href="<?= APP_URL ?>/index.php?action=imputaciones" class="card-action">Ver todo →</a>
        </div>
        <div class="card-body">
            <?php if (empty($proyectos)): ?>
            <p class="text-muted">No tienes proyectos asignados actualmente.</p>
            <?php else: ?>
            <div class="proyecto-chips">
                <?php foreach ($proyectos as $p): ?>
                <div class="proyecto-chip" style="--chip-color: <?= htmlspecialchars($p['color']) ?>">
                    <span class="chip-dot"></span>
                    <?= htmlspecialchars($p['nombre']) ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Resumen semana ── -->
    <section class="card">
        <div class="card-header">
            <span class="card-icon">◉</span>
            <h2 class="card-title">Esta semana</h2>
        </div>
        <div class="card-body">
            <div class="semana-stat">
                <div class="stat-num"><?= number_format($horas_semana, 1) ?>h</div>
                <div class="stat-desc">horas imputadas</div>
            </div>
            <?php if (!empty($imputaciones_semana)): ?>
            <div class="imputaciones-mini">
                <?php
                $by_proyecto = [];
                foreach ($imputaciones_semana as $imp) {
                    $key = $imp['id_proyecto'];
                    if (!isset($by_proyecto[$key])) {
                        $by_proyecto[$key] = ['nombre' => $imp['proyecto_nombre'], 'color' => $imp['proyecto_color'], 'horas' => 0];
                    }
                    $by_proyecto[$key]['horas'] += $imp['horas'];
                }
                foreach ($by_proyecto as $bp): ?>
                <div class="imp-row">
                    <span class="imp-dot" style="background:<?= htmlspecialchars($bp['color']) ?>"></span>
                    <span class="imp-proyecto"><?= htmlspecialchars($bp['nombre']) ?></span>
                    <span class="imp-horas"><?= number_format($bp['horas'], 1) ?>h</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Imputar horas rápido ── -->
    <section class="card card--imputar">
        <div class="card-header">
            <span class="card-icon">+</span>
            <h2 class="card-title">Imputar horas</h2>
        </div>
        <div class="card-body">
            <?php if (empty($proyectos)): ?>
            <p class="text-muted">Necesitas proyectos asignados para imputar horas.</p>
            <?php else: ?>
            <form id="imputarRapidoForm" class="form-compact">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Proyecto</label>
                        <select name="id_proyecto" class="form-input" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group--sm">
                        <label class="form-label">Horas</label>
                        <input type="number" name="horas" class="form-input" min="0.5" max="12" step="0.5" placeholder="2.0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-input" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción (opcional)</label>
                    <input type="text" name="descripcion" class="form-input" placeholder="¿En qué has trabajado?">
                </div>
                <button type="submit" class="btn btn-primary">Guardar imputación</button>
            </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Incidencias ── -->
    <?php if (!empty($incidencias)): ?>
    <section class="card card--incidencias">
        <div class="card-header">
            <span class="card-icon">⚠</span>
            <h2 class="card-title">Incidencias pendientes</h2>
        </div>
        <div class="card-body">
            <?php foreach ($incidencias as $inc): ?>
            <div class="incidencia-row incidencia-<?= $inc['tipo'] ?>">
                <div class="inc-tipo"><?= str_replace('_', ' ', ucfirst($inc['tipo'])) ?></div>
                <div class="inc-desc"><?= htmlspecialchars($inc['descripcion']) ?></div>
                <div class="inc-fecha"><?= date('d/m/Y', strtotime($inc['fecha'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div><!-- /dashboard-grid -->

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
