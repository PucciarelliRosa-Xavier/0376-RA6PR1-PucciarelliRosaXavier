<?php
$page_title = 'Imputación de Proyectos';
$extra_js   = 'imputaciones';
require_once __DIR__ . '/../shared/header.php';
?>

<div class="imputaciones-container">

    <!-- Filtro mes -->
    <div class="filter-bar">
        <form method="GET" action="<?= APP_URL ?>/index.php" class="filter-form">
            <input type="hidden" name="action" value="imputaciones">
            <div class="form-group form-group--inline">
                <label class="form-label">Mes</label>
                <input type="month" name="mes" class="form-input" value="<?= htmlspecialchars($mes) ?>" onchange="this.form.submit()">
            </div>
        </form>
        <div class="mes-total">
            Total: <strong><?= number_format($total_mes, 1) ?>h</strong>
        </div>
    </div>

    <div class="two-col-layout">

        <!-- ── Formulario nueva imputación ── -->
        <div class="col-form">
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">+</span>
                    <h2 class="card-title" id="formTitle">Nueva imputación</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($proyectos)): ?>
                    <div class="alert alert-info">
                        No tienes proyectos activos asignados. Contacta con tu responsable.
                    </div>
                    <?php else: ?>
                    <form id="impForm" class="imp-form">
                        <input type="hidden" id="impId" name="id" value="0">

                        <div class="form-group">
                            <label class="form-label">Proyecto <span class="required">*</span></label>
                            <select id="impProyecto" name="id_proyecto" class="form-input" required>
                                <option value="">Selecciona un proyecto...</option>
                                <?php foreach ($proyectos as $p): ?>
                                <option value="<?= $p['id'] ?>" data-color="<?= htmlspecialchars($p['color']) ?>">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Fecha <span class="required">*</span></label>
                                <input type="date" id="impFecha" name="fecha" class="form-input"
                                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Horas <span class="required">*</span></label>
                                <input type="number" id="impHoras" name="horas" class="form-input"
                                       min="0.5" max="12" step="0.5" placeholder="1.5" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Descripción del trabajo</label>
                            <textarea id="impDesc" name="descripcion" class="form-input form-textarea"
                                      rows="3" placeholder="Describe brevemente las tareas realizadas..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="impSubmitBtn">
                                Guardar imputación
                            </button>
                            <button type="button" class="btn btn-ghost" id="impCancelBtn" style="display:none">
                                Cancelar
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resumen por proyecto -->
            <?php if (!empty($resumen)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <span class="card-icon">◈</span>
                    <h2 class="card-title">Resumen del mes</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($resumen as $r):
                        $pct = $total_mes > 0 ? ($r['total_horas'] / $total_mes * 100) : 0;
                    ?>
                    <div class="resumen-row">
                        <div class="resumen-info">
                            <span class="resumen-color" style="background:<?= htmlspecialchars($r['color']) ?>"></span>
                            <span class="resumen-nombre"><?= htmlspecialchars($r['nombre']) ?></span>
                        </div>
                        <div class="resumen-horas"><?= number_format($r['total_horas'], 1) ?>h</div>
                        <div class="resumen-bar-wrap">
                            <div class="resumen-bar" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($r['color']) ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Lista de imputaciones ── -->
        <div class="col-list">
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">◷</span>
                    <h2 class="card-title">Imputaciones del mes</h2>
                </div>
                <div class="card-body no-pad" id="imputacionesList">
                    <?php if (empty($imputaciones)): ?>
                    <div class="empty-state">
                        <p>No hay imputaciones en este período.</p>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr><th>Fecha</th><th>Proyecto</th><th>Horas</th><th>Descripción</th><th></th></tr>
                        </thead>
                        <tbody id="impTableBody">
                        <?php foreach ($imputaciones as $imp): ?>
                        <tr id="imp-row-<?= $imp['id'] ?>">
                            <td><?= date('d/m', strtotime($imp['fecha'])) ?></td>
                            <td>
                                <span class="chip-dot" style="background:<?= htmlspecialchars($imp['proyecto_color']) ?>"></span>
                                <?= htmlspecialchars($imp['proyecto_nombre']) ?>
                            </td>
                            <td><strong><?= number_format($imp['horas'], 1) ?>h</strong></td>
                            <td class="td-truncate"><?= htmlspecialchars($imp['descripcion'] ?? '') ?></td>
                            <td class="td-actions">
                                <button class="btn-icon"
                                        onclick="editarImputacion(<?= $imp['id'] ?>, <?= $imp['id_proyecto'] ?>, '<?= $imp['fecha'] ?>', <?= $imp['horas'] ?>, '<?= addslashes($imp['descripcion'] ?? '') ?>')"
                                        title="Editar">✎</button>
                                <button class="btn-icon btn-icon--danger"
                                        onclick="eliminarImputacion(<?= $imp['id'] ?>)"
                                        title="Eliminar">✕</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /two-col-layout -->
</div>

<?php
$inline_js = "const APP_URL = '" . APP_URL . "';";
require_once __DIR__ . '/../shared/footer.php';
?>
