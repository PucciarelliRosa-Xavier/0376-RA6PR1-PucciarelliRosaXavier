/**
 * TimeControl — fichaje.js
 * Lógica del widget de fichaje (entrada/salida en tiempo real)
 */

'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const btnFichar    = document.getElementById('btnFichar');
    const btnIcon      = document.getElementById('btnFicharIcon');
    const btnText      = document.getElementById('btnFicharText');
    const estadoLabel  = document.getElementById('estadoLabel');
    const fichajeHora  = document.getElementById('fichajeHora');
    const horasHoy     = document.getElementById('horasHoy');
    const statusDot    = document.getElementById('statusDot');
    const pulseRing    = document.getElementById('pulseRing');
    const fichajeCard  = document.getElementById('fichajeCard');
    const timeline     = document.getElementById('fichajeTimeline');

    if (!btnFichar) return;

    // Cargar estado actual
    await cargarEstado();

    // Actualizar horas cada minuto si está dentro
    setInterval(async () => {
        const data = await apiGet(`${APP_URL}/index.php?action=api_estado`);
        if (data.dentro) {
            horasHoy.textContent = formatHoras(data.horas_hoy || 0);
        }
    }, 60000);

    // Click en fichar
    btnFichar.addEventListener('click', async () => {
        btnFichar.disabled = true;
        btnIcon.textContent = '⟳';
        btnText.textContent = 'Procesando...';

        try {
            const data = await apiPost(`${APP_URL}/index.php?action=fichar`);

            if (data.ok) {
                actualizarUI(data.tipo === 'entrada', data.timestamp, data.horas_hoy);

                if (data.incidencia) {
                    const msgs = {
                        retraso:           `⚠ Retraso registrado: ${data.incidencia.minutos} minutos tarde`,
                        salida_anticipada: `⚠ Salida anticipada: ${data.incidencia.minutos} min antes`
                    };
                    showToast(msgs[data.incidencia.tipo] || 'Incidencia registrada', 'error', 5000);
                } else {
                    const msg = data.tipo === 'entrada'
                        ? `✓ Entrada registrada a las ${data.timestamp}`
                        : `✓ Salida registrada a las ${data.timestamp}`;
                    showToast(msg, 'success');
                }

                // Recargar timeline
                setTimeout(cargarEstado, 500);
            } else {
                showToast(data.msg || 'Error al fichar', 'error');
                btnFichar.disabled = false;
            }
        } catch (e) {
            showToast('Error de conexión. Inténtalo de nuevo.', 'error');
            console.error(e);
            btnFichar.disabled = false;
        }
    });

    async function cargarEstado() {
        try {
            const data = await apiGet(`${APP_URL}/index.php?action=api_estado`);
            actualizarUI(data.dentro, data.ultimo?.timestamp, data.horas_hoy);
            renderTimeline(data.fichajes || []);
        } catch (e) {
            console.error('Error cargando estado:', e);
            estadoLabel.textContent = 'Error de conexión';
            btnFichar.disabled = false;
        }
    }

    function actualizarUI(dentro, timestamp, horas) {
        // Estado visual
        fichajeCard.classList.toggle('dentro', dentro);
        fichajeCard.classList.toggle('fuera', !dentro);
        statusDot.classList.toggle('dentro', dentro);

        estadoLabel.textContent = dentro ? '● Dentro' : '○ Fuera';
        estadoLabel.style.color = dentro ? 'var(--accent-2)' : 'var(--text-muted)';

        if (timestamp) {
            const d = new Date(timestamp.replace(' ', 'T'));
            fichajeHora.textContent = d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        } else {
            fichajeHora.textContent = '--:--';
        }

        horasHoy.textContent = formatHoras(horas || 0);

        // Botón
        btnFichar.disabled = false;
        if (dentro) {
            btnIcon.textContent = '⏹';
            btnText.textContent = 'Fichar Salida';
        } else {
            btnIcon.textContent = '⏺';
            btnText.textContent = 'Fichar Entrada';
        }
    }

    function renderTimeline(fichajes) {
        if (!timeline || !fichajes.length) {
            if (timeline) timeline.innerHTML = '<span style="color:var(--text-muted);font-size:12px">No hay fichajes hoy</span>';
            return;
        }
        timeline.innerHTML = fichajes.map(f => {
            const hora = formatHora(f.timestamp);
            const tipo = f.tipo === 'entrada' ? 'in' : 'out';
            const label = f.tipo === 'entrada' ? 'Entrada' : 'Salida';
            return `
                <div class="timeline-item">
                    <div class="timeline-dot-${tipo}"></div>
                    <span class="timeline-label">${label}</span>
                    <span class="timeline-time">${hora}</span>
                </div>`;
        }).join('<span style="color:var(--border-strong);font-size:16px">→</span>');
    }

    // ── Formulario imputación rápida ──────────────────────────
    const impForm = document.getElementById('imputarRapidoForm');
    if (impForm) {
        impForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(impForm);
            const btn = impForm.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            try {
                const data = await fetch(`${APP_URL}/index.php?action=imputacion_guardar`, {
                    method: 'POST',
                    body: fd
                }).then(r => r.json());

                if (data.ok) {
                    showToast('✓ ' + data.msg, 'success');
                    impForm.reset();
                    impForm.querySelector('input[name=fecha]').value = new Date().toISOString().split('T')[0];
                } else {
                    showToast(data.msg, 'error');
                }
            } catch {
                showToast('Error de conexión', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Guardar imputación';
            }
        });
    }
});
