<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Acceso — TimeControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <!-- Panel izquierdo: decorativo -->
    <div class="login-panel login-panel--left" aria-hidden="true">
        <div class="login-grid">
            <?php for ($i = 0; $i < 80; $i++): ?>
            <div class="grid-cell"></div>
            <?php endfor; ?>
        </div>
        <div class="login-tagline">
            <span class="brand-huge">⏱</span>
            <h2>Time<br><strong>Control</strong></h2>
            <p>Sistema de control horario<br>y gestión de proyectos</p>
        </div>
    </div>

    <!-- Panel derecho: formulario -->
    <div class="login-panel login-panel--right">
        <div class="login-form-container">
            <div class="login-header">
                <div class="login-logo">⏱ <span>TC</span></div>
                <h1>Acceder</h1>
                <p>Introduce tus credenciales para continuar</p>
            </div>

            <?php if (!empty($_SESSION['login_error'])): ?>
            <div class="alert alert-error" role="alert">
                <span>⚠</span> <?= htmlspecialchars($_SESSION['login_error']) ?>
            </div>
            <?php unset($_SESSION['login_error']); endif; ?>

            <form method="POST" action="<?= APP_URL ?>/index.php?action=login_process" class="login-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Email corporativo</label>
                    <div class="input-wrapper">
                        <span class="input-icon">@</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="usuario@empresa.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <span class="input-icon">◈</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" id="togglePwd" aria-label="Mostrar contraseña">
                            <span id="eyeIcon">◉</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-login" id="loginBtn">
                    <span class="btn-text">Entrar</span>
                    <span class="btn-loader hidden">⟳</span>
                </button>
            </form>

            <p class="login-hint">
                Demo: <code>admin@empresa.com</code> / <code>Admin1234!</code>
            </p>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePwd').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '◎';
    } else {
        input.type = 'password';
        icon.textContent = '◉';
    }
});

// Show loading state on submit
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').classList.add('hidden');
    btn.querySelector('.btn-loader').classList.remove('hidden');
});
</script>
</body>
</html>
