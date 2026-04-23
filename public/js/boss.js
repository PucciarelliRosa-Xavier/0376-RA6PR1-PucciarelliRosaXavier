/**
 * TimeControl — boss.js
 * Panel de supervisión
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // ── Búsqueda y filtro en tabla empleados ──────────────────
    const searchInput = document.getElementById('searchEmpleado');
    const deptoFilter = document.getElementById('filterDepto');
    const refreshBtn  = document.getElementById('refreshBtn');

    function filtrarEmpleados() {
        const q     = (searchInput?.value || '').toLowerCase();
        const depto = deptoFilter?.value || '';
        const rows  = document.querySelectorAll('#empleadosTable tbody tr');

        rows.forEach(row => {
            const nombre = row.dataset.nombre  || '';
            const rdept  = row.dataset.depto   || '';
            const matchQ = !q     || nombre.includes(q);
            const matchD = !depto || rdept === depto;
            row.style.display = (matchQ && matchD) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', filtrarEmpleados);
    deptoFilter?.addEventListener('change', filtrarEmpleados);

    // ── Refrescar página ──────────────────────────────────────
    refreshBtn?.addEventListener('click', () => location.reload());

    // ── Auto-refresh cada 2 minutos ───────────────────────────
    setInterval(() => location.reload(), 120000);
});

async function resolverIncidencia(id) {
    try {
        const data = await apiPost(`${APP_URL}/index.php?action=incidencia_resolver`, {
            id, estado: 'revisada'
        });
        if (data.ok) {
            showToast('Incidencia marcada como revisada', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.msg, 'error');
        }
    } catch {
        showToast('Error de conexión', 'error');
    }
}
