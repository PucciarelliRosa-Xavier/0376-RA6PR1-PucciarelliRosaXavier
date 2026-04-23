/**
 * TimeControl — informes.js
 * Generación dinámica de informes y exportación CSV
 */

'use strict';

let tipoActual    = 'diario';
let datosInforme  = null;

document.addEventListener('DOMContentLoaded', () => {

    // ── Tipo de informe ───────────────────────────────────────
    document.querySelectorAll('.btn-toggle[data-tipo]').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-toggle[data-tipo]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            tipoActual = this.dataset.tipo;
        });
    });

    // ── Generar informe ───────────────────────────────────────
    document.getElementById('btnGenerar')?.addEventListener('click', generarInforme);

    // ── Exportar CSV ──────────────────────────────────────────
    document.getElementById('btnExportar')?.addEventListener('click', exportarCSV);

    // ── Tabs ──────────────────────────────────────────────────
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabId = 'tab-' + this.dataset.tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            this.classList.add('active');
            document.getElementById(tabId)?.classList.remove('hidden');
        });
    });

    // Si hay tipo en URL, generar automáticamente
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tipo')) {
        tipoActual = urlParams.get('tipo');
        const btn = document.querySelector(`.btn-toggle[data-tipo="${tipoActual}"]`);
        if (btn) {
            document.querySelectorAll('.btn-toggle[data-tipo]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        generarInforme();
    }
});

async function generarInforme() {
    const fecha   = document.getElementById('filterFecha')?.value    || '';
    const usuario = document.getElementById('filterUsuario')?.value  || '';
    const proyecto= document.getElementById('filterProyecto')?.value || '';
    const btn     = document.getElementById('btnGenerar');

    btn.disabled = true;
    btn.textContent = '⟳ Generando...';

    try {
        const url = `${APP_URL}/index.php?action=informe_datos&tipo=${tipoActual}&fecha=${fecha}&usuario=${usuario}&proyecto=${proyecto}`;
        const data = await apiGet(url);

        if (data.ok) {
            datosInforme = data;
            renderInforme(data);
            document.getElementById('informeResultado').classList.remove('hidden');
            document.getElementById('informeVacio').classList.add('hidden');
        } else {
            showToast('Error al generar informe', 'error');
        }
    } catch (e) {
        showToast('Error de conexión', 'error');
        console.error(e);
    } finally {
        btn.disabled = false;
        btn.textContent = '◈ Generar informe';
    }
}

function renderInforme(data) {
    // ── KPIs ──────────────────────────────────────────────────
    const kpis = document.getElementById('informeKpis');
    kpis.innerHTML = `
        <div class="stat-card">
            <div class="stat-num">${data.totales.horas.toFixed(1)}h</div>
            <div class="stat-label">Horas imputadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">${data.totales.empleados}</div>
            <div class="stat-label">Empleados</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">${data.totales.proyectos}</div>
            <div class="stat-label">Proyectos</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">${data.fichajes.length}</div>
            <div class="stat-label">Registros asistencia</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">${data.incidencias.length}</div>
            <div class="stat-label">Incidencias</div>
        </div>
    `;

    // ── Banner período ────────────────────────────────────────
    const tipos = { diario: 'Día', semanal: 'Semana', mensual: 'Mes' };
    document.getElementById('periodoBanner').textContent =
        `${tipos[data.periodo.tipo] || ''} • ${formatFecha(data.periodo.inicio)}${data.periodo.inicio !== data.periodo.fin ? ' → ' + formatFecha(data.periodo.fin) : ''}`;

    // ── Tabla imputaciones ────────────────────────────────────
    const tbody = document.getElementById('tbodyImputaciones');
    if (!data.imputaciones.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="td-loading">Sin datos en este período</td></tr>';
    } else {
        tbody.innerHTML = data.imputaciones.map(row => `
            <tr>
                <td>${esc(row.nombre + ' ' + row.apellidos)}</td>
                <td><span class="depto-chip depto-${row.departamento}">${esc(row.departamento)}</span></td>
                <td><span class="chip-dot" style="background:${esc(row.color)};display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:6px;"></span>${esc(row.proyecto)}</td>
                <td><strong>${parseFloat(row.horas).toFixed(1)}h</strong></td>
                <td>${row.registros}</td>
            </tr>
        `).join('');
    }

    // Total
    document.getElementById('tfootImputaciones').innerHTML = `
        <tr style="background:var(--bg-elevated)">
            <td colspan="3" style="padding:10px 16px;font-weight:700">TOTAL</td>
            <td style="padding:10px 16px;font-weight:700;font-family:var(--font-mono)">${data.totales.horas.toFixed(1)}h</td>
            <td></td>
        </tr>
    `;

    // ── Tabla asistencia ──────────────────────────────────────
    const tbodyA = document.getElementById('tbodyAsistencia');
    if (!data.fichajes.length) {
        tbodyA.innerHTML = '<tr><td colspan="5" class="td-loading">Sin datos en este período</td></tr>';
    } else {
        tbodyA.innerHTML = data.fichajes.map(row => `
            <tr>
                <td>${esc(row.nombre + ' ' + row.apellidos)}</td>
                <td>${formatFecha(row.fecha)}</td>
                <td>${row.primera_entrada ? '<span class="time-badge time-badge--in">' + formatHora(row.primera_entrada) + '</span>' : '—'}</td>
                <td>${row.ultima_salida   ? '<span class="time-badge time-badge--out">' + formatHora(row.ultima_salida)   + '</span>' : '—'}</td>
                <td><strong>${row.horas_presencia !== null ? row.horas_presencia.toFixed(1) + 'h' : '—'}</strong></td>
            </tr>
        `).join('');
    }

    // ── Tabla incidencias ─────────────────────────────────────
    const tbodyI = document.getElementById('tbodyIncidencias');
    if (!data.incidencias.length) {
        tbodyI.innerHTML = '<tr><td colspan="5" class="td-loading">Sin incidencias en este período ✓</td></tr>';
    } else {
        const tipoClases = { retraso: 'warning', olvido_salida: 'error', olvido_entrada: 'error', salida_anticipada: 'info' };
        tbodyI.innerHTML = data.incidencias.map(row => `
            <tr>
                <td>${esc(row.nombre + ' ' + row.apellidos)}</td>
                <td>${formatFecha(row.fecha)}</td>
                <td><span class="badge badge-${tipoClases[row.tipo] || 'info'}">${esc(row.tipo.replace(/_/g,' '))}</span></td>
                <td class="td-truncate">${esc(row.descripcion || '—')}</td>
                <td><span class="badge badge-${row.estado === 'resuelta' ? 'success' : 'warning'}">${esc(row.estado)}</span></td>
            </tr>
        `).join('');
    }
}

// ── CSV Export ────────────────────────────────────────────────
function exportarCSV() {
    if (!datosInforme) { showToast('Genera un informe primero', 'error'); return; }

    const rows = [
        ['Empleado', 'Departamento', 'Proyecto', 'Horas', 'Registros', 'Fecha inicio', 'Fecha fin'],
        ...datosInforme.imputaciones.map(r => [
            `${r.nombre} ${r.apellidos}`,
            r.departamento,
            r.proyecto,
            r.horas,
            r.registros,
            datosInforme.periodo.inicio,
            datosInforme.periodo.fin
        ])
    ];

    const csv = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `informe_${tipoActual}_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
