<?php
$pageTitle = 'Mi Dashboard';
$action = 'dashboard';
include __DIR__ . '/../shared/header.php';

// Calcular horas hoy
$horasHoy = 0;
if (!empty($fichajesHoy)) {
    $entradas = array_filter($fichajesHoy, fn($f) => $f['tipo'] === 'entrada');
    $salidas  = array_filter($fichajesHoy, fn($f) => $f['tipo'] === 'salida');
    if (!empty($entradas) && !empty($salidas)) {
        $primerEnt = new DateTime(end($entradas)['timestamp']);
        $ultSal    = new DateTime(reset($salidas)['timestamp']);
        $horasHoy  = round(abs($ultSal->getTimestamp() - $primerEnt->getTimestamp()) / 3600, 2);
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?> 👋</h1>
        <p class="page-subtitle"><?= date('l, d \d\e F \d\e Y') ?> — <?= htmlspecialchars($usuario['departamento_nombre'] ?? 'Sin departamento') ?></p>
    </div>
    <div class="page-meta">
        <?php if ($usuario['hora_entrada']): ?>
            <span class="meta-badge">
                Horario: <?= substr($usuario['hora_entrada'], 0, 5) ?> – <?= substr($usuario['hora_salida'], 0, 5) ?>
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- BOTÓN DE FICHAJE -->
<div class="fichar-section">
    <div class="fichar-card <?= $estadoActual === 'dentro' ? 'estado-dentro' : 'estado-fuera' ?>">
        <div class="fichar-estado">
            <div class="fichar-indicador" id="fichar-indicador">
                <?= $estadoActual === 'dentro' ? '🟢' : '🔴' ?>
            </div>
            <div>
                <p class="fichar-estado-texto" id="fichar-estado-texto">
                    <?php if ($estadoActual === 'dentro'): ?>
                        Estás <strong>dentro</strong> — fichaje de entrada registrado
                    <?php elseif ($estadoActual === 'fuera'): ?>
                        Estás <strong>fuera</strong> — puedes registrar tu entrada
                    <?php else: ?>
                        Sin fichaje hoy
                    <?php endif; ?>
                </p>
                <p class="fichar-hora" id="fichar-hora-display"><?= date('H:i:s') ?></p>
            </div>
        </div>

        <button
            id="btn-fichar"
            class="btn-fichar <?= $estadoActual === 'dentro' ? 'btn-salida' : 'btn-entrada' ?>"
            data-estado="<?= $estadoActual ?>"
        >
            <?= $estadoActual === 'dentro' ? '↑ Registrar Salida' : '↓ Registrar Entrada' ?>
        </button>
    </div>

    <div id="fichar-mensaje" class="fichar-alert" style="display:none;"></div>
</div>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-value"><?= number_format($horasHoy, 1) ?>h</div>
        <div class="stat-label">Horas hoy</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?= count($resumenSemana) ?></div>
        <div class="stat-label">Días esta semana</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🗂️</div>
        <div class="stat-value"><?= count($proyectos) ?></div>
        <div class="stat-label">Proyectos activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-value"><?= number_format(array_sum(array_column($imputacionesHoy, 'horas')), 1) ?>h</div>
        <div class="stat-label">Imputadas hoy</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Fichajes de hoy -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Fichajes de hoy</h3>
            <a href="?action=historial" class="card-link">Ver historial →</a>
        </div>
        <div class="card-body">
            <?php if (empty($fichajesHoy)): ?>
                <p class="empty-state">No hay fichajes registrados hoy.</p>
            <?php else: ?>
                <div class="fichaje-list">
                    <?php foreach (array_reverse($fichajesHoy) as $f): ?>
                        <div class="fichaje-item fichaje-<?= $f['tipo'] ?>">
                            <span class="fichaje-tipo-badge"><?= $f['tipo'] === 'entrada' ? '↓ Entrada' : '↑ Salida' ?></span>
                            <span class="fichaje-hora"><?= substr($f['timestamp'], 11, 5) ?></span>
                            <?php if ($f['es_tardanza']): ?>
                                <span class="badge badge-warn">+<?= $f['minutos_diferencia'] ?>min tarde</span>
                            <?php endif; ?>
                            <?php if ($f['es_salida_anticipada']): ?>
                                <span class="badge badge-warn">-<?= $f['minutos_diferencia'] ?>min anticipado</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Proyectos asignados -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mis proyectos</h3>
            <a href="?action=imputar" class="card-link">Imputar horas →</a>
        </div>
        <div class="card-body">
            <?php if (empty($proyectos)): ?>
                <p class="empty-state">No tienes proyectos asignados.</p>
            <?php else: ?>
                <?php foreach ($proyectos as $p): ?>
                    <div class="project-item">
                        <div class="project-dot"></div>
                        <div>
                            <div class="project-name"><?= htmlspecialchars($p['nombre']) ?></div>
                            <?php
                                $hProy = 0;
                                foreach ($resumenProyectos as $rp) {
                                    if ($rp['proyecto_nombre'] === $p['nombre']) $hProy = $rp['total_horas'];
                                }
                            ?>
                            <div class="project-hours"><?= number_format($hProy, 1) ?>h este mes</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen semanal -->
    <div class="card card-wide">
        <div class="card-header">
            <h3 class="card-title">Últimos 7 días</h3>
        </div>
        <div class="card-body">
            <?php if (empty($resumenSemana)): ?>
                <p class="empty-state">Sin datos de la semana.</p>
            <?php else: ?>
                <div class="week-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Horas</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumenSemana as $dia): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($dia['fecha'])) ?></td>
                                    <td class="mono"><?= $dia['primera_entrada'] ? substr($dia['primera_entrada'], 11, 5) : '—' ?></td>
                                    <td class="mono"><?= $dia['ultima_salida'] ? substr($dia['ultima_salida'], 11, 5) : '—' ?></td>
                                    <td class="mono bold"><?= $dia['horas_trabajadas'] !== null ? number_format($dia['horas_trabajadas'], 1) . 'h' : '—' ?></td>
                                    <td>
                                        <?php if ($dia['hubo_tardanza']): ?>
                                            <span class="badge badge-warn">Tardanza</span>
                                        <?php elseif ($dia['horas_trabajadas'] !== null): ?>
                                            <span class="badge badge-ok">OK</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral">Sin salida</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../shared/footer.php'; ?>
