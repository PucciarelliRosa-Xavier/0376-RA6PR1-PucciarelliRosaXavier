<?php
$page_title = 'Informes';
$extra_js   = 'informes';
require_once __DIR__ . '/../shared/header.php';
?>

<div class="informes-container">

    <!-- ── Filtros ── -->
    <div class="card filter-card">
        <div class="card-body">
            <div class="filter-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de informe</label>
                    <div class="btn-group" id="tipoGroup">
                        <button class="btn btn-toggle active" data-tipo="diario">Diario</button>
                        <button class="btn btn-toggle" data-tipo="semanal">Semanal</button>
                        <button class="btn btn-toggle" data-tipo="mensual">Mensual</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de referencia</label>
                    <input type="date" id="filterFecha" class="form-input" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Empleado</label>
                    <select id="filterUsuario" class="form-input">
                        <option value="">Todos los empleados</option>
                        <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['apellidos'] . ', ' . $u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Proyecto</label>
                    <select id="filterProyecto" class="form-input">
                        <option value="">Todos los proyectos</option>
                        <?php foreach ($proyectos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--actions">
                    <button class="btn btn-primary" id="btnGenerar">
                        <span>◈</span> Generar informe
                    </button>
                    <button class="btn btn-ghost" id="btnExportar">
                        <span>↓</span> Exportar CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Resultado (se rellena por JS) ── -->
    <div id="informeResultado" class="hidden">

        <!-- KPIs del período -->
        <div class="stats-row" id="informeKpis"></div>

        <!-- Período -->
        <div class="periodo-banner" id="periodoBanner"></div>

        <!-- Tabs -->
        <div class="tabs-bar">
            <button class="tab-btn active" data-tab="imputaciones">Imputaciones</button>
            <button class="tab-btn" data-tab="asistencia">Asistencia</button>
            <button class="tab-btn" data-tab="incidencias">Incidencias</button>
        </div>

        <!-- Tab: Imputaciones -->
        <div class="tab-panel active" id="tab-imputaciones">
            <div class="card">
                <div class="card-body no-pad">
                    <table class="data-table" id="tablaImputaciones">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Departamento</th>
                                <th>Proyecto</th>
                                <th>Horas</th>
                                <th>Registros</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyImputaciones">
                            <tr><td colspan="5" class="td-loading">Cargando...</td></tr>
                        </tbody>
                        <tfoot id="tfootImputaciones"></tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Asistencia -->
        <div class="tab-panel hidden" id="tab-asistencia">
            <div class="card">
                <div class="card-body no-pad">
                    <table class="data-table" id="tablaAsistencia">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Fecha</th>
                                <th>1ª Entrada</th>
                                <th>Última Salida</th>
                                <th>Horas presencia</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAsistencia">
                            <tr><td colspan="5" class="td-loading">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab: Incidencias -->
        <div class="tab-panel hidden" id="tab-incidencias">
            <div class="card">
                <div class="card-body no-pad">
                    <table class="data-table" id="tablaIncidencias">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyIncidencias">
                            <tr><td colspan="5" class="td-loading">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /informeResultado -->

    <!-- Estado vacío -->
    <div class="empty-state empty-state--lg" id="informeVacio">
        <div class="empty-icon">◈</div>
        <h3>Genera un informe</h3>
        <p>Selecciona el tipo, rango de fechas y filtros opcionales,<br>luego pulsa "Generar informe".</p>
    </div>

</div><!-- /informes-container -->

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
