<?php
/**
 * TimeControl — API: Proyectos de un usuario
 * Endpoint: ?action=api_usuario_proyectos&uid=X
 * Usado por el modal de asignación de proyectos
 */

// Este bloque se añade al router (index.php) como:
// case 'api_usuario_proyectos': (new UsuarioController())->apiProyectos(); break;
//
// Y este método va dentro de UsuarioController:

/*
public function apiProyectos(): void {
    header('Content-Type: application/json');
    $uid = (int)($_GET['uid'] ?? 0);
    if (!$uid) { echo json_encode(['ok'=>false]); exit; }

    $proyectos = $this->db->query(
        "SELECT id_proyecto FROM usuario_proyecto WHERE id_usuario = ?",
        [$uid]
    );
    $ids = array_column($proyectos, 'id_proyecto');
    echo json_encode(['ok' => true, 'proyectos' => $ids]);
    exit;
}
*/

// ============================================================
// INSTRUCCIONES DE INTEGRACIÓN COMPLETA
// ============================================================
//
// Para activar la precarga de proyectos en el modal de asignación:
//
// 1. Añadir el case en index.php:
//    case 'api_usuario_proyectos': (new UsuarioController())->apiProyectos(); break;
//
// 2. Añadir el método en UsuarioController.php (ver arriba)
//
// 3. Actualizar la función abrirAsignarProyecto() en usuarios.js:
/*
async function abrirAsignarProyecto(uid, nombre) {
    document.getElementById('asignarNombre').textContent = nombre;
    document.getElementById('asignarUid').value = uid;

    // Cargar proyectos actuales
    try {
        const data = await apiGet(`${APP_URL}/index.php?action=api_usuario_proyectos&uid=${uid}`);
        const asignados = data.proyectos || [];
        document.querySelectorAll('.proyecto-checkbox').forEach(cb => {
            cb.checked = asignados.includes(parseInt(cb.value));
        });
    } catch {}

    document.getElementById('modalProyectos').classList.remove('hidden');
}
*/
