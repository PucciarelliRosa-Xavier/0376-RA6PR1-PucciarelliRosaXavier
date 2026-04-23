/**
 * TimeControl — proyectos.js
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('btnNuevoProyecto')?.addEventListener('click', () => {
        document.getElementById('proyectoForm').reset();
        document.getElementById('pid').value = 0;
        document.getElementById('pColor').value = '#4F6EF7';
        document.getElementById('modalProyectoTitle').textContent = 'Nuevo proyecto';
        document.getElementById('modalProyecto').classList.remove('hidden');
    });

    document.getElementById('closeModalProyecto')?.addEventListener('click', () => {
        document.getElementById('modalProyecto').classList.add('hidden');
    });

    document.getElementById('proyectoForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('[type=submit]');
        btn.disabled = true;

        try {
            const data = await fetch(`${APP_URL}/index.php?action=proyecto_guardar`, {
                method: 'POST', body: new FormData(e.target)
            }).then(r => r.json());

            if (data.ok) {
                showToast('✓ ' + data.msg, 'success');
                document.getElementById('modalProyecto').classList.add('hidden');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.msg, 'error');
            }
        } catch {
            showToast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
        }
    });
});

function editarProyecto(p) {
    document.getElementById('pid').value          = p.id;
    document.getElementById('pNombre').value      = p.nombre;
    document.getElementById('pDesc').value        = p.descripcion || '';
    document.getElementById('pEstado').value      = p.estado;
    document.getElementById('pColor').value       = p.color;
    document.getElementById('pFechaInicio').value = p.fecha_inicio || '';
    document.getElementById('pFechaFin').value    = p.fecha_fin    || '';
    document.getElementById('modalProyectoTitle').textContent = 'Editar proyecto';
    document.getElementById('modalProyecto').classList.remove('hidden');
}

async function archivarProyecto(id) {
    if (!confirmar('¿Marcar este proyecto como completado?')) return;
    try {
        const data = await apiPost(`${APP_URL}/index.php?action=proyecto_eliminar`, { id });
        if (data.ok) { showToast(data.msg, 'success'); setTimeout(() => location.reload(), 700); }
        else showToast(data.msg, 'error');
    } catch { showToast('Error de conexión', 'error'); }
}


/* ============================================================
   horarios.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btnNuevoHorario')?.addEventListener('click', () => {
        document.getElementById('horarioForm').reset();
        document.getElementById('hid').value = 0;
        document.getElementById('hTolerancia').value = 10;
        document.getElementById('modalHorarioTitle').textContent = 'Nuevo horario';
        document.getElementById('modalHorario').classList.remove('hidden');
    });

    document.getElementById('closeModalHorario')?.addEventListener('click', () => {
        document.getElementById('modalHorario').classList.add('hidden');
    });

    document.getElementById('horarioForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('[type=submit]');
        btn.disabled = true;

        try {
            const data = await fetch(`${APP_URL}/index.php?action=horario_guardar`, {
                method: 'POST', body: new FormData(e.target)
            }).then(r => r.json());

            if (data.ok) {
                showToast('✓ ' + data.msg, 'success');
                document.getElementById('modalHorario').classList.add('hidden');
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.msg, 'error');
            }
        } catch {
            showToast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
        }
    });
});

function editarHorario(h) {
    document.getElementById('hid').value         = h.id;
    document.getElementById('hNombre').value     = h.nombre;
    document.getElementById('hInicio').value     = h.hora_inicio.substring(0, 5);
    document.getElementById('hFin').value        = h.hora_fin.substring(0, 5);
    document.getElementById('hTolerancia').value = h.tolerancia;
    document.getElementById('modalHorarioTitle').textContent = 'Editar horario';
    document.getElementById('modalHorario').classList.remove('hidden');
}
