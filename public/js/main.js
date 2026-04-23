/**
 * TimeControl — main.js
 * Utilidades globales: reloj, sidebar, toasts, helpers
 */

'use strict';

/* ── Live Clock ──────────────────────────────────────────────── */
(function startClock() {
    const el = document.getElementById('liveClock');
    if (!el) return;
    const tick = () => {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('es-ES', {
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    };
    tick();
    setInterval(tick, 1000);
})();

/* ── Mobile Sidebar ──────────────────────────────────────────── */
(function initSidebar() {
    const toggle  = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar) return;

    const open  = () => { sidebar.classList.add('open'); overlay.classList.add('visible'); };
    const close = () => { sidebar.classList.remove('open'); overlay.classList.remove('visible'); };

    toggle.addEventListener('click', () => sidebar.classList.contains('open') ? close() : open());
    overlay.addEventListener('click', close);
})();

/* ── Toast Notifications ─────────────────────────────────────── */
function showToast(message, type = 'info', duration = 3500) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;

    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    toast.innerHTML = `<span>${icons[type] || 'ℹ'}</span><span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('fade-out');
        toast.addEventListener('animationend', () => toast.remove());
    }, duration);
}

/* ── AJAX Helper ─────────────────────────────────────────────── */
async function apiPost(url, data = {}) {
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);

    const res = await fetch(url, { method: 'POST', body: fd });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

async function apiGet(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

/* ── Format helpers ──────────────────────────────────────────── */
function formatHoras(horas) {
    const h = Math.floor(horas);
    const m = Math.round((horas - h) * 60);
    return `${h}h ${m}m`;
}

function formatFecha(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatHora(iso) {
    if (!iso) return '—';
    const d = new Date(iso.replace(' ', 'T'));
    return d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

/* ── Confirm Dialog ──────────────────────────────────────────── */
function confirmar(mensaje) {
    return window.confirm(mensaje);
}
