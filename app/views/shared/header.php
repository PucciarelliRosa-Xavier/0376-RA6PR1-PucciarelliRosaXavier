<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'TimeControl') ?> — TimeControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <span class="nav-logo">⏱</span>
        <span class="nav-title">TimeControl</span>
    </div>
    <div class="nav-links">
        <?php $rol = $_SESSION['user_rol'] ?? ''; ?>

        <?php if ($rol === 'empleado'): ?>
            <a href="?action=dashboard" class="nav-link <?= ($action ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="?action=historial" class="nav-link <?= ($action ?? '') === 'historial' ? 'active' : '' ?>">Historial</a>
            <a href="?action=imputar" class="nav-link <?= ($action ?? '') === 'imputar' ? 'active' : '' ?>">Imputar</a>
            <a href="?action=mis_imputaciones" class="nav-link">Mis Horas</a>

        <?php elseif (in_array($rol, ['jefe', 'jefe_departamento'])): ?>
            <a href="?action=dashboard" class="nav-link <?= ($action ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="?action=manager_empleados" class="nav-link">Empleados</a>
            <a href="?action=manager_incidencias" class="nav-link">Incidencias</a>
            <a href="?action=informes" class="nav-link">Informes</a>
            <a href="?action=admin_proyectos" class="nav-link">Proyectos</a>

        <?php elseif ($rol === 'admin'): ?>
            <a href="?action=dashboard" class="nav-link <?= ($action ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="?action=admin_usuarios" class="nav-link">Usuarios</a>
            <a href="?action=admin_proyectos" class="nav-link">Proyectos</a>
            <a href="?action=admin_horarios" class="nav-link">Horarios</a>
            <a href="?action=manager_incidencias" class="nav-link">Incidencias</a>
            <a href="?action=informes" class="nav-link">Informes</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <span class="user-badge"><?= strtoupper(substr($_SESSION['user_nombre'] ?? 'U', 0, 1)) ?></span>
        <span class="user-name"><?= htmlspecialchars($_SESSION['user_nombre'] ?? '') ?></span>
        <span class="user-rol"><?= htmlspecialchars($rol) ?></span>
        <a href="?action=logout" class="btn-logout">Salir</a>
    </div>
</nav>

<main class="main-content">
