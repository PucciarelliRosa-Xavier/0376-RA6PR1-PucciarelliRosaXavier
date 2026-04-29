<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — TimeControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
</head>
<body class="login-body">

<div class="login-container">
    <div class="login-brand">
        <div class="login-logo">⏱</div>
        <h1 class="login-title">TimeControl</h1>
        <p class="login-subtitle">Sistema de Control Horario</p>
    </div>

    <div class="login-card">
        <h2 class="login-form-title">Iniciar sesión</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span class="alert-icon">✕</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="?action=do_login" method="POST" class="login-form">
            <div class="form-group">
                <label for="email" class="form-label">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="usuario@empresa.com"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Acceder al sistema
            </button>
        </form>

        
    </div>
</div>

</body>
</html>
