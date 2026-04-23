<?php
$page_title = 'Panel de Supervisión';
$extra_js   = 'boss';
require_once __DIR__ . '/../shared/header.php';

$depto_labels = [
    'rrhh'          => 'RRHH',
    'direccion'     => 'Dirección',
    'contabilidad'  => 'Contabilidad',
    'desarrollo'    => 'Desarrollo',
    'diseno'        => 'Diseño',
];
?>

<div class="boss-dashboard">

    <!-- ── KPIs ── -->
    <div class="kpi-row">
        <div class="kpi-card kpi-dentro">
            <div class="kpi-icon">◉</div>
            <div class="kpi-num" id="kpiDentro"><?= $dentro_count ?></div>
            <div class="kpi-label">En oficina</div>
        </div>
        <div class="kpi-card kpi-fuera">
            <div class="kpi-icon">◎</div>
            <div class="kpi-num" id="kpiFuera"><?= $fuera_count ?></div>
            <div class="kpi-label">Salieron</div>
        </div>
        <div class="kpi-card kpi-ausente">
            <div class="kpi-icon">○</div>
            <div class="kpi-num" id="kpiAusente"><?= $sin_fichar ?></div>
            <div class="kpi-label">Sin fichar</div>
        </div>
        <div class="kpi-card kpi-alerta">
            <div class="kpi-icon">⚠</div>
            <div class="kpi-num" id="kpiAlerta"><?= $retrasos_hoy ?></div>
            <div class="kpi-label">Retrasos hoy</div>
        </div>
        <div class="kpi-card kpi-total">
            <div class="kpi-icon">◈</div>
            <div class="kpi-num"><?= count($empleados) ?></div>
            <div class="kpi-label">Total empleados</div>
        </div>
    </div>

    <div class="boss-grid">

        <!-- ── Empleados en tiempo real ── -->
        <section class="card card--wide">
            <div class="card-header">
                <span class="card-icon">◉</span>
                <h2 class="card-title">Estado de empleados — hoy</h2>
                <div class="card-header-actions">
                    <input type="text" class="search-input" id="searchEmpleado" placeholder="Buscar empleado...">
                    <select class="form-input form-input--sm" id="filterDepto">
                        <option value="">Todos los deptos.</option>
                        <?php foreach ($depto_labels as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-ghost btn-sm" id="refreshBtn" title="Actualizar">⟳</button>
                </div>
            </div>
            <div class="card-body no-pad">
                <table class="data-table" id="empleadosTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Departamento</th>
                            <th>Horario</th>
                            <th>Último fichaje</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($empleados as $emp):
                        $dentro = $emp['ultimo_tipo'] === 'entrada';
                        $fuera  = $emp['ultimo_tipo'] === 'salida';
                        $hora_str = $emp['ultimo_fichaje']
                            ? date('H:i', strtotime($emp['ultimo_fichaje']))
                            : '—';
                    ?>
                    <tr data-depto="<?= $emp['departamento'] ?>" data-nombre="<?= strtolower($emp['nombre'] . ' ' . $emp['apellidos']) ?>">
                        <td>
                            <div class="empleado-cell">
                                <div class="empleado-avatar"><?= mb_strtoupper(mb_substr($emp['nombre'], 0, 1)) ?></div>
                                <div>
                                    <div class="empleado-nombre"><?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?></div>
                                    <div class="empleado-email text-muted"><?= htmlspecialchars($emp['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="depto-chip depto-<?= $emp['departamento'] ?>"><?= $depto_labels[$emp['departamento']] ?? ucfirst($emp['departamento']) ?></span></td>
                        <td>
                            <?php if ($emp['hora_inicio']): ?>
                            <span class="mono"><?= substr($emp['hora_inicio'],0,5) ?> - <?= substr($emp['hora_fin'],0,5) ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['ultimo_fichaje']): ?>
                            <span class="time-badge time-badge--<?= $dentro ? 'in' : 'out' ?>"><?= $hora_str ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($dentro): ?>
                            <span class="status-pill status-pill--green">● Dentro</span>
                            <?php elseif ($fuera): ?>
                            <span class="status-pill status-pill--gray">○ Salió</span>
                            <?php else: ?>
                            <span class="status-pill status-pill--red">○ Sin fichar</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ── Incidencias de hoy ── -->
        <section class="card">
            <div class="card-header">
                <span class="card-icon">⚠</span>
                <h2 class="card-title">Incidencias de hoy</h2>
                <a href="<?= APP_URL ?>/index.php?action=incidencias" class="card-action">Ver todas →</a>
            </div>
            <div class="card-body no-pad">
                <?php if (empty($incidencias_hoy)): ?>
                <div class="empty-state">
                    <p>✓ Sin incidencias hoy</p>
                </div>
                <?php else: ?>
                <div class="incidencias-list">
                    <?php foreach ($incidencias_hoy as $inc): ?>
                    <div class="inc-item inc-<?= $inc['tipo'] ?>">
                        <div class="inc-avatar"><?= mb_strtoupper(mb_substr($inc['nombre'], 0, 1)) ?></div>
                        <div class="inc-data">
                            <div class="inc-nombre"><?= htmlspecialchars($inc['nombre'] . ' ' . $inc['apellidos']) ?></div>
                            <div class="inc-tipo-label"><?= str_replace('_', ' ', ucfirst($inc['tipo'])) ?></div>
                        </div>
                        <button class="btn-icon" onclick="resolverIncidencia(<?= $inc['id'] ?>)" title="Marcar como revisada">✓</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── Proyectos activos ── -->
        <section class="card">
            <div class="card-header">
                <span class="card-icon">◆</span>
                <h2 class="card-title">Proyectos — horas este mes</h2>
            </div>
            <div class="card-body">
                <?php foreach ($proyectos_resumen as $pr): ?>
                <div class="proyecto-stat-row">
                    <div class="proyecto-info">
                        <span class="proyecto-dot" style="background:<?= htmlspecialchars($pr['color']) ?>"></span>
                        <div>
                            <div class="proyecto-stat-nombre"><?= htmlspecialchars($pr['nombre']) ?></div>
                            <div class="proyecto-stat-meta text-muted"><?= $pr['num_empleados'] ?> empleados</div>
                        </div>
                    </div>
                    <div class="proyecto-horas-mes">
                        <strong><?= number_format($pr['horas_mes'], 0) ?>h</strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div><!-- /boss-grid -->

    <!-- ── Acceso rápido a informes ── -->
    <div class="quick-reports">
        <a href="<?= APP_URL ?>/index.php?action=informes&tipo=diario" class="quick-report-btn">
            <span>◈</span> Informe diario
        </a>
        <a href="<?= APP_URL ?>/index.php?action=informes&tipo=semanal" class="quick-report-btn">
            <span>◈</span> Informe semanal
        </a>
        <a href="<?= APP_URL ?>/index.php?action=informes&tipo=mensual" class="quick-report-btn">
            <span>◈</span> Informe mensual
        </a>
        <a href="<?= APP_URL ?>/index.php?action=incidencias" class="quick-report-btn quick-report-btn--warn">
            <span>⚠</span> Todas las incidencias
        </a>
    </div>

</div><!-- /boss-dashboard -->

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
