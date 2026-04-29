#!/usr/bin/env php
<?php
/**
 * TimeControl — Script CRON
 * Detecta olvidos de fichaje y envía alertas por email
 *
 * Configurar en crontab:
 *   # Ejecutar cada mañana a las 09:15 para detectar olvidos del día anterior
 *   15 9 * * 1-5 php /var/www/timecontrol/cron/detectar_olvidos.php
 *
 *   # Ejecutar cada 5 minutos en horario laboral para tardanzas (opcional, el sistema las detecta en tiempo real)
 *   */5 9-10 * * 1-5 php /var/www/timecontrol/cron/detectar_olvidos.php --tardanzas
 */

// Cargar configuración
define('CLI_MODE', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Fichaje.php';
require_once __DIR__ . '/../app/models/Incidencia.php';
require_once __DIR__ . '/../mail/Mailer.php';

$log = function(string $msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
};

$log('=== TimeControl CRON iniciado ===');

$fichajeModel    = new Fichaje();
$incidenciaModel = new Incidencia();
$mailer          = new Mailer();

// ---- DETECTAR OLVIDOS DE FICHAJE DEL DÍA ANTERIOR ----
$log('Detectando olvidos de fichaje de ayer...');
$olvidos = $fichajeModel->getOlvidosFichajeAyer();

$contOlvidos = 0;
foreach ($olvidos as $usuario) {
    $fecha = date('Y-m-d', strtotime('yesterday'));

    // Evitar duplicar incidencias
    $db = Database::getConnection();
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM incidencias WHERE id_usuario = ? AND tipo = 'olvido_fichaje' AND fecha = ?"
    );
    $stmt->execute([$usuario['id'], $fecha]);
    if ((int)$stmt->fetchColumn() > 0) continue;

    // Crear incidencia
    $incidenciaModel->crear([
        'id_usuario'    => $usuario['id'],
        'tipo'          => 'olvido_fichaje',
        'descripcion'   => "No se registró fichaje de salida el día {$fecha}.",
        'fecha'         => $fecha,
        'email_enviado' => 1,
    ]);

    // Enviar email
    $enviado = $mailer->enviarAlertaOlvido($usuario);
    $log("Olvido detectado: {$usuario['nombre']} {$usuario['apellidos']} ({$usuario['email']}) — email " . ($enviado ? 'enviado' : 'error'));
    $contOlvidos++;
}
$log("Olvidos procesados: {$contOlvidos}");

// ---- RESUMEN DE INCIDENCIAS PENDIENTES (log) ----
$db = Database::getConnection();
$pendientes = (int)$db->query("SELECT COUNT(*) FROM incidencias WHERE estado = 'pendiente'")->fetchColumn();
$log("Incidencias pendientes en el sistema: {$pendientes}");

$log('=== CRON finalizado ===');
exit(0);
