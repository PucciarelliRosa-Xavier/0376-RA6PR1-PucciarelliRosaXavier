#!/usr/bin/env php
<?php
/**
 * TimeControl - Cron Job: Detección de olvidos de fichaje
 *
 * Configurar en crontab:
 *   # Ejecutar cada día a las 23:00
 *   0 23 * * 1-5 /usr/bin/php /var/www/html/timecontrol/cron/detectar_olvidos.php >> /var/log/timecontrol_cron.log 2>&1
 *
 * También se puede lanzar a mediodía para detectar ausencias de mañana:
 *   0 10 * * 1-5 /usr/bin/php /var/www/html/timecontrol/cron/detectar_olvidos.php morning
 */

// Bootstrap
define('CRON_MODE', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../app/helpers/EmailHelper.php';

$db   = Database::getInstance();
$hoy  = date('Y-m-d');
$ayer = date('Y-m-d', strtotime('-1 day'));
$log  = fn(string $msg) => print("[" . date('Y-m-d H:i:s') . "] $msg\n");

$log("=== Inicio cron detectar_olvidos ===");

// ── 1. Usuarios que no han fichado hoy ────────────────────────
$sin_fichar_hoy = $db->query(
    "SELECT u.id, u.nombre, u.apellidos, u.email, u.departamento, h.hora_inicio
     FROM usuarios u
     INNER JOIN horarios h ON h.id = u.id_horario
     WHERE u.activo = 1 AND u.rol != 'admin'
     AND TIME(NOW()) > ADDTIME(h.hora_inicio, '01:00:00')
     AND u.id NOT IN (
         SELECT DISTINCT id_usuario FROM fichajes WHERE DATE(timestamp) = ?
     )",
    [$hoy]
);

foreach ($sin_fichar_hoy as $emp) {
    // Evitar duplicar incidencia del mismo día
    $existe = $db->queryOne(
        "SELECT id FROM incidencias WHERE id_usuario=? AND tipo='olvido_entrada' AND fecha=?",
        [$emp['id'], $hoy]
    );
    if ($existe) continue;

    $db->execute(
        "INSERT INTO incidencias (id_usuario,tipo,descripcion,fecha,estado) VALUES (?,?,?,?,?)",
        [
            $emp['id'],
            'olvido_entrada',
            "El empleado no ha fichado entrada el día $hoy",
            $hoy,
            'pendiente'
        ]
    );

    // Email al empleado
    EmailHelper::enviar(
        $emp['email'],
        $emp['nombre'] . ' ' . $emp['apellidos'],
        'Recuerda fichar tu entrada - ' . date('d/m/Y'),
        EmailHelper::plantillaOlvido($emp, 'entrada'),
        $emp['id'],
        'olvido_entrada'
    );

    $db->execute(
        "UPDATE incidencias SET email_enviado=1 WHERE id_usuario=? AND tipo='olvido_entrada' AND fecha=?",
        [$emp['id'], $hoy]
    );

    $log("Olvido entrada detectado: {$emp['nombre']} {$emp['apellidos']} ({$emp['email']})");
}

// ── 2. Usuarios con entrada pero sin salida de ayer ───────────
$sin_salida_ayer = $db->query(
    "SELECT u.id, u.nombre, u.apellidos, u.email, u.departamento
     FROM usuarios u
     WHERE u.activo = 1
     AND u.id IN (
         -- Tienen entrada de ayer
         SELECT DISTINCT id_usuario FROM fichajes WHERE DATE(timestamp) = ? AND tipo = 'entrada'
     )
     AND u.id NOT IN (
         -- Pero no tienen salida de ayer
         SELECT DISTINCT id_usuario FROM fichajes WHERE DATE(timestamp) = ? AND tipo = 'salida'
     )",
    [$ayer, $ayer]
);

foreach ($sin_salida_ayer as $emp) {
    $existe = $db->queryOne(
        "SELECT id FROM incidencias WHERE id_usuario=? AND tipo='olvido_salida' AND fecha=?",
        [$emp['id'], $ayer]
    );
    if ($existe) continue;

    $db->execute(
        "INSERT INTO incidencias (id_usuario,tipo,descripcion,fecha,estado) VALUES (?,?,?,?,?)",
        [
            $emp['id'],
            'olvido_salida',
            "El empleado no fichó la salida el día $ayer",
            $ayer,
            'pendiente'
        ]
    );

    EmailHelper::enviar(
        $emp['email'],
        $emp['nombre'] . ' ' . $emp['apellidos'],
        'Fichaje de salida no registrado - ' . date('d/m/Y', strtotime($ayer)),
        EmailHelper::plantillaOlvido($emp, 'salida'),
        $emp['id'],
        'olvido_salida'
    );

    $db->execute(
        "UPDATE incidencias SET email_enviado=1 WHERE id_usuario=? AND tipo='olvido_salida' AND fecha=?",
        [$emp['id'], $ayer]
    );

    $log("Olvido salida detectado: {$emp['nombre']} {$emp['apellidos']}");
}

$log("=== Fin cron. Sin fichar hoy: " . count($sin_fichar_hoy) . " | Sin salida ayer: " . count($sin_salida_ayer) . " ===\n");
