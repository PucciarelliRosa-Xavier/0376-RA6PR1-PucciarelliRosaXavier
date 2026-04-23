<?php
// 404.php
http_response_code(404);
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>404 — TimeControl</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:opsz,wght@9..40,400;9..40,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
</head><body>
<div class="error-page">
    <div class="error-code">404</div>
    <div class="error-msg">Página no encontrada</div>
    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary" style="margin-top:20px">← Volver al inicio</a>
</div>
</body></html>
