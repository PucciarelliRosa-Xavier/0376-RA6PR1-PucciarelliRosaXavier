/**
 * TimeControl — imputaciones.js
 */

'use strict';

let currentEditId = 0;

document.addEventListener('DOMContentLoaded', () => {
    const form       = document.getElementById('impForm');
    const cancelBtn  = document.getElementById('impCancelBtn');
    const formTitle  = document.getElementById('formTitle');
    const submitBtn  = document.getElementById('impSubmitBtn');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        const fd = new FormData(form);
        try {
            const data = await fetch(`${APP_URL}/index.php?action=imputacion_guardar`, {
                method: 'POST', body: fd
            }).then(r => r.json());

            if (data.ok) {
                showToast('✓ ' + data.msg, 'success');
                resetForm();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.msg, 'error');
            }
        } catch {
            showToast('Error de conexión', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar imputación';
        }
    });

    cancelBtn?.addEventListener('click', resetForm);

    function resetForm() {
        form.reset();
        document.getElementById('impId').value = 0;
        document.getElementById('impFecha').value = new Date().toISOString().split('T')[0];
        formTitle.textContent = 'Nueva imputación';
        cancelBtn.style.display = 'none';
        submitBtn.textContent = 'Guardar imputación';
    }
});

function editarImputacion(id, pid, fecha, horas, desc) {
    document.getElementById('impId').value = id;
    document.getElementById('impProyecto').value = pid;
    document.getElementById('impFecha').value = fecha;
    document.getElementById('impHoras').value = horas;
    document.getElementById('impDesc').value = desc;
    document.getElementById('formTitle').textContent = 'Editar imputación';
    document.getElementById('impSubmitBtn').textContent = 'Actualizar';
    document.getElementById('impCancelBtn').style.display = 'inline-flex';
    document.getElementById('impProyecto').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

async function eliminarImputacion(id) {
    if (!confirmar('¿Eliminar esta imputación?')) return;
    try {
        const data = await apiPost(`${APP_URL}/index.php?action=imputacion_eliminar`, { id });
        if (data.ok) {
            const row = document.getElementById('imp-row-' + id);
            if (row) row.remove();
            showToast('Imputación eliminada', 'success');
        } else {
            showToast(data.msg, 'error');
        }
    } catch {
        showToast('Error de conexión', 'error');
    }
}
