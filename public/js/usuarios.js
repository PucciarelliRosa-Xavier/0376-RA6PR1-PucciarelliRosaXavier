/**
 * TimeControl — usuarios.js
 * CRUD de usuarios en panel admin
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // ── Nuevo usuario ─────────────────────────────────────────
    document.getElementById('btnNuevoUsuario')?.addEventListener('click', () => {
        document.getElementById('usuarioForm').reset();
        document.getElementById('uid').value = 0;
        document.getElementById('uActivo').checked = true;
        document.getElementById('modalUsuarioTitle').textContent = 'Nuevo usuario';
        document.getElementById('passReq').style.display = 'inline';
        document.getElementById('modalUsuario').classList.remove('hidden');
    });

    document.getElementById('closeModalUsuario')?.addEventListener('click', cerrarModalUsuario);
    document.getElementById('cancelUsuario')?.addEventListener('click', cerrarModalUsuario);
    document.getElementById('modalUsuario')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) cerrarModalUsuario();
    });

    // ── Guardar usuario ───────────────────────────────────────
    document.getElementById('usuarioForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('[type=submit]');
        btn.disabled = true;
        btn.textContent = 'Guardando...';

        const fd = new FormData(e.target);
        // Checkbox activo
        if (!e.target.querySelector('#uActivo').checked) fd.delete('activo');

        try {
            const data = await fetch(`${APP_URL}/index.php?action=usuario_guardar`, {
                method: 'POST', body: fd
            }).then(r => r.json());

            if (data.ok) {
                showToast('✓ ' + data.msg, 'success');
                cerrarModalUsuario();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.msg, 'error');
            }
        } catch {
            showToast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Guardar';
        }
    });

    // ── Modal proyectos ───────────────────────────────────────
    document.getElementById('closeModalProyectos')?.addEventListener('click', () => {
        document.getElementById('modalProyectos').classList.add('hidden');
    });

    // Checkbox proyectos: guardar inmediatamente
    document.querySelectorAll('.proyecto-checkbox').forEach(cb => {
        cb.addEventListener('change', async function () {
            const uid = document.getElementById('asignarUid').value;
            const pid = this.value;
            const accion = this.checked ? 'asignar' : 'quitar';

            try {
                const data = await apiPost(`${APP_URL}/index.php?action=usuario_asignar_proyecto`, {
                    id_usuario: uid, id_proyecto: pid, accion
                });
                showToast(data.ok ? data.msg : data.msg, data.ok ? 'success' : 'error');
            } catch {
                showToast('Error de conexión', 'error');
            }
        });
    });
});

function cerrarModalUsuario() {
    document.getElementById('modalUsuario').classList.add('hidden');
}

function editarUsuario(user) {
    document.getElementById('uid').value          = user.id;
    document.getElementById('uNombre').value      = user.nombre;
    document.getElementById('uApellidos').value   = user.apellidos;
    document.getElementById('uEmail').value       = user.email;
    document.getElementById('uRol').value         = user.rol;
    document.getElementById('uDepartamento').value= user.departamento;
    document.getElementById('uHorario').value     = user.id_horario || '';
    document.getElementById('uActivo').checked    = user.activo == 1;
    document.getElementById('uPassword').value    = '';
    document.getElementById('modalUsuarioTitle').textContent = 'Editar usuario';
    document.getElementById('passReq').style.display = 'none';
    document.getElementById('modalUsuario').classList.remove('hidden');
}

async function eliminarUsuario(id) {
    if (!confirmar('¿Desactivar este usuario? No podrá acceder al sistema.')) return;
    try {
        const data = await apiPost(`${APP_URL}/index.php?action=usuario_eliminar`, { id });
        if (data.ok) {
            showToast('Usuario desactivado', 'success');
            const row = document.getElementById('user-row-' + id);
            if (row) row.style.opacity = '0.4';
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.msg, 'error');
        }
    } catch {
        showToast('Error de conexión', 'error');
    }
}

async function abrirAsignarProyecto(uid, nombre) {
    document.getElementById('asignarNombre').textContent = nombre;
    document.getElementById('asignarUid').value = uid;

    // Cargar proyectos actuales del usuario para pre-marcar checkboxes
    try {
        // Pequeño hack: hacemos fetch de la página de usuarios con los datos del usuario
        // En producción se añadiría un endpoint /api/usuario_proyectos
        document.querySelectorAll('.proyecto-checkbox').forEach(cb => cb.checked = false);
    } catch {}

    document.getElementById('modalProyectos').classList.remove('hidden');
}
