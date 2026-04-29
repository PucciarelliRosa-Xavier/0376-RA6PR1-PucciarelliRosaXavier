/**
 * TimeControl — JavaScript principal
 * Gestiona: fichaje AJAX, reloj en tiempo real, alertas, filtros
 */

'use strict';

/* ============================================================
   RELOJ EN TIEMPO REAL
   ============================================================ */
function iniciarReloj() {
    const el = document.getElementById('fichar-hora-display');
    if (!el) return;

    function actualizar() {
        const ahora = new Date();
        const h = String(ahora.getHours()).padStart(2, '0');
        const m = String(ahora.getMinutes()).padStart(2, '0');
        const s = String(ahora.getSeconds()).padStart(2, '0');
        el.textContent = `${h}:${m}:${s}`;
    }
    actualizar();
    setInterval(actualizar, 1000);
}

/* ============================================================
   FICHAJE AJAX
   ============================================================ */
function iniciarFichaje() {
    const btnFichar = document.getElementById('btn-fichar');
    if (!btnFichar) return;

    btnFichar.addEventListener('click', async function () {
        if (this.disabled) return;

        this.disabled = true;
        const textoOriginal = this.textContent;
        this.textContent = 'Registrando...';

        try {
            const resp = await fetch('?action=fichar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf=1'
            });

            if (!resp.ok) throw new Error('Error de red');
            const data = await resp.json();

            if (data.ok) {
                mostrarAlertaFichaje(data);
                actualizarEstadoFichaje(data);
            } else {
                mostrarMensajeFichaje('Error: ' + (data.mensaje || 'Error desconocido'), 'err');
                this.disabled = false;
                this.textContent = textoOriginal;
            }
        } catch (err) {
            mostrarMensajeFichaje('Error de conexión. Inténtalo de nuevo.', 'err');
            this.disabled = false;
            this.textContent = textoOriginal;
        }
    });
}

function actualizarEstadoFichaje(data) {
    const btn         = document.getElementById('btn-fichar');
    const estadoTexto = document.getElementById('fichar-estado-texto');
    const indicador   = document.getElementById('fichar-indicador');
    const fichajeCard = btn?.closest('.fichar-card');

    if (!btn) return;

    if (data.tipo === 'entrada') {
        btn.textContent = '↑ Registrar Salida';
        btn.className = 'btn-fichar btn-salida';
        btn.dataset.estado = 'dentro';
        if (estadoTexto) estadoTexto.innerHTML = 'Estás <strong>dentro</strong> — entrada registrada a las ' + data.hora;
        if (indicador)   indicador.textContent = '🟢';
        if (fichajeCard) { fichajeCard.classList.remove('estado-fuera'); fichajeCard.classList.add('estado-dentro'); }
    } else {
        btn.textContent = '↓ Registrar Entrada';
        btn.className = 'btn-fichar btn-entrada';
        btn.dataset.estado = 'fuera';
        if (estadoTexto) estadoTexto.innerHTML = 'Estás <strong>fuera</strong> — salida registrada a las ' + data.hora;
        if (indicador)   indicador.textContent = '🔴';
        if (fichajeCard) { fichajeCard.classList.remove('estado-dentro'); fichajeCard.classList.add('estado-fuera'); }
    }

    btn.disabled = false;
}

function mostrarAlertaFichaje(data) {
    let clase   = 'ok';
    let mensaje = data.mensaje;

    if (data.incidencia === 'retraso') {
        clase   = 'warn';
        mensaje = `⏰ ${data.mensaje} — Se ha detectado un retraso. Se ha notificado a tu responsable.`;
    } else if (data.incidencia === 'salida_anticipada') {
        clase   = 'warn';
        mensaje = `⚠️ ${data.mensaje} — Salida registrada antes de la hora habitual.`;
    }

    mostrarMensajeFichaje(mensaje, clase);
}

function mostrarMensajeFichaje(mensaje, tipo) {
    const el = document.getElementById('fichar-mensaje');
    if (!el) return;

    el.textContent = mensaje;
    el.className = `fichar-alert ${tipo}`;
    el.style.display = 'block';

    // Auto-ocultar tras 6 segundos
    clearTimeout(el._timeout);
    el._timeout = setTimeout(() => { el.style.display = 'none'; }, 6000);
}

/* ============================================================
   ALERTAS GLOBALES — Auto-ocultar
   ============================================================ */
function iniciarAlertas() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });
}

/* ============================================================
   FILTROS — Actualizar fechas al cambiar periodo en informes
   ============================================================ */
function iniciarFiltrosPeriodo() {
    const sel = document.getElementById('select-tipo');
    if (!sel) return;

    const grpDesde = document.getElementById('grp-desde');
    const grpHasta = document.getElementById('grp-hasta');

    function actualizar() {
        if (sel.value === 'custom') {
            if (grpDesde) grpDesde.style.display = '';
            if (grpHasta) grpHasta.style.display = '';
        } else {
            if (grpDesde) grpDesde.style.display = 'none';
            if (grpHasta) grpHasta.style.display = 'none';
        }
    }

    sel.addEventListener('change', actualizar);
    actualizar(); // Estado inicial
}

/* ============================================================
   CONFIRMACIONES DE ACCIONES DESTRUCTIVAS
   ============================================================ */
function iniciarConfirmaciones() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
}

/* ============================================================
   TOOLTIP SIMPLE
   ============================================================ */
function iniciarTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function () {
            const tip = document.createElement('div');
            tip.className = 'tooltip-popup';
            tip.textContent = this.dataset.tooltip;
            tip.style.cssText = `
                position:absolute;background:#1e293b;color:#e2e8f0;
                padding:4px 10px;border-radius:4px;font-size:.75rem;
                pointer-events:none;z-index:999;border:1px solid #334155;
                white-space:nowrap;
            `;
            document.body.appendChild(tip);

            const rect = this.getBoundingClientRect();
            tip.style.top  = (rect.bottom + window.scrollY + 6) + 'px';
            tip.style.left = (rect.left + window.scrollX) + 'px';
            this._tooltip = tip;
        });
        el.addEventListener('mouseleave', function () {
            if (this._tooltip) { this._tooltip.remove(); this._tooltip = null; }
        });
    });
}

/* ============================================================
   VALIDACIÓN DE FORMULARIOS
   ============================================================ */
function iniciarValidaciones() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;
            this.querySelectorAll('[required]').forEach(field => {
                field.classList.remove('input-error');
                if (!field.value.trim()) {
                    field.classList.add('input-error');
                    valid = false;
                }
            });
            if (!valid) {
                e.preventDefault();
                const primera = this.querySelector('.input-error');
                if (primera) primera.focus();
            }
        });
    });
}

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    iniciarReloj();
    iniciarFichaje();
    iniciarAlertas();
    iniciarFiltrosPeriodo();
    iniciarConfirmaciones();
    iniciarTooltips();
    iniciarValidaciones();
});
