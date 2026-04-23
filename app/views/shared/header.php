<?php
/**
 * TimeControl - Layout Header
 * Se incluye al inicio de todas las vistas
 * Variables esperadas: $page_title (string)
 */
$user_nombre = $_SESSION['user_nombre'] ?? 'Usuario';
$user_rol    = $_SESSION['user_rol']    ?? '';
$user_depto  = $_SESSION['user_depto'] ?? '';

$rol_labels = [
    'admin'              => 'Administrador',
    'empleado'           => 'Empleado',
    'jefe'               => 'Jefe',
    'jefe_departamento'  => 'Jefe de Departamento',
];
$rol_label = $rol_labels[$user_rol] ?? ucfirst($user_rol);

$nav_links = [
    'dashboard'   => ['icon' => '◈', 'label' => 'Inicio'],
];

if ($user_rol === 'empleado') {
    $nav_links['fichajes_historial'] = ['icon' => '◷', 'label' => 'Historial'];
    $nav_links['imputaciones']       = ['icon' => '◉', 'label' => 'Proyectos'];
}

if (in_array($user_rol, ['jefe','jefe_departamento','admin'])) {
    $nav_links['informes']    = ['icon' => '◈', 'label' => 'Informes'];
    $nav_links['incidencias'] = ['icon' => '⚠', 'label' => 'Incidencias'];
}

if ($user_rol === 'admin') {
    $nav_links['usuarios']  = ['icon' => '◎', 'label' => 'Usuarios'];
    $nav_links['proyectos'] = ['icon' => '◆', 'label' => 'Proyectos'];
    $nav_links['horarios']  = ['icon' => '◐', 'label' => 'Horarios'];
}

$current_action = $_GET['action'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'TimeControl') ?> — TimeControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
</head>
<body class="rol-<?= $user_rol ?>">

<div class="app-layout">
    <!-- ── Sidebar ── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">⏱</span>
            <span class="brand-name">Time<strong>Control</strong></span>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($nav_links as $route => $link): ?>
            <a href="<?= APP_URL ?>/index.php?action=<?= $route ?>"
               class="nav-link <?= $current_action === $route ? 'active' : '' ?>">
                <span class="nav-icon"><?= $link['icon'] ?></span>
                <span class="nav-label"><?= $link['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><?= mb_strtoupper(mb_substr($user_nombre, 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user_nombre) ?></div>
                    <div class="user-role"><?= $rol_label ?></div>
                </div>
            </div>
            <a href="<?= APP_URL ?>/index.php?action=logout" class="logout-btn" title="Cerrar sesión">⏏</a>
        </div>
    </aside>

    <!-- ── Main ── -->
    <main class="main-content">
        <header class="top-bar">
            <button class="menu-toggle" id="menuToggle" aria-label="Menú">☰</button>
            <h1 class="page-title"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h1>
            <div class="top-bar-right">
                <span class="live-clock" id="liveClock"></span>
                <span class="depto-badge"><?= htmlspecialchars(ucfirst($user_depto)) ?></span>
            </div>
        </header>

        <div class="content-area">
