<?php
/**
 * TimeControl - EmailHelper
 * Sistema de envío de emails usando SMTP nativo (sin librerías externas)
 * Para producción se recomienda usar PHPMailer o Symfony Mailer
 */

class EmailHelper {

    /**
     * Envía un email y registra el resultado en email_log
     */
    public static function enviar(
        string $destinatario_email,
        string $destinatario_nombre,
        string $asunto,
        string $html_body,
        ?int $id_usuario = null,
        string $tipo = 'general'
    ): bool {
        $db = Database::getInstance();

        try {
            // Cabeceras del email
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
            $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
            $headers .= "X-Mailer: TimeControl/1.0\r\n";

            $asunto_encoded = '=?UTF-8?B?' . base64_encode($asunto) . '?=';

            $ok = mail($destinatario_email, $asunto_encoded, $html_body, $headers);

            $db->execute(
                "INSERT INTO email_log (id_usuario, destinatario, asunto, tipo, estado) VALUES (?,?,?,?,?)",
                [$id_usuario, $destinatario_email, $asunto, $tipo, $ok ? 'enviado' : 'error']
            );

            return $ok;

        } catch (Exception $e) {
            $db->execute(
                "INSERT INTO email_log (id_usuario, destinatario, asunto, tipo, estado, error_msg) VALUES (?,?,?,?,?,?)",
                [$id_usuario, $destinatario_email, $asunto, $tipo, 'error', $e->getMessage()]
            );
            error_log("EmailHelper error: " . $e->getMessage());
            return false;
        }
    }

    // ── Plantillas HTML ───────────────────────────────────────

    public static function plantillaRetraso(array $user, int $minutos, string $hora_fichaje): string {
        $nombre = htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']);
        $fecha  = date('d/m/Y');
        return self::wrapper("Retraso registrado", "
            <p>Hola <strong>$nombre</strong>,</p>
            <p>Se ha registrado un <span class='badge badge-warning'>retraso</span> en tu fichaje de hoy.</p>
            <table class='info-table'>
                <tr><td>📅 Fecha</td><td>$fecha</td></tr>
                <tr><td>🕐 Hora fichaje</td><td>$hora_fichaje</td></tr>
                <tr><td>⏱ Retraso</td><td><strong>$minutos minutos</strong></td></tr>
                <tr><td>👤 Empleado</td><td>$nombre</td></tr>
            </table>
            <p>Si crees que hay un error, por favor contacta con tu supervisor o el departamento de RRHH.</p>
        ");
    }

    public static function plantillaOlvido(array $user, string $tipo): string {
        $nombre = htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']);
        $fecha  = date('d/m/Y');
        $msg    = $tipo === 'salida'
            ? 'No hemos registrado tu <strong>fichaje de salida</strong> de ayer.'
            : 'No hemos registrado tu <strong>fichaje de entrada</strong> de hoy.';

        return self::wrapper("Fichaje olvidado", "
            <p>Hola <strong>$nombre</strong>,</p>
            <p>$msg</p>
            <table class='info-table'>
                <tr><td>📅 Fecha</td><td>$fecha</td></tr>
                <tr><td>👤 Empleado</td><td>$nombre</td></tr>
                <tr><td>🏢 Departamento</td><td>" . htmlspecialchars(ucfirst($user['departamento'])) . "</td></tr>
            </table>
            <p>Por favor, accede a <strong>TimeControl</strong> para regularizar tu situación o contacta con RRHH.</p>
            <p><a href='" . APP_URL . "' class='btn'>Acceder a TimeControl</a></p>
        ");
    }

    public static function plantillaSalidaAnticipada(array $user, int $minutos): string {
        $nombre = htmlspecialchars($user['nombre'] . ' ' . $user['apellidos']);
        return self::wrapper("Salida anticipada registrada", "
            <p>Hola <strong>$nombre</strong>,</p>
            <p>Se ha registrado una <span class='badge badge-info'>salida anticipada</span> hoy.</p>
            <table class='info-table'>
                <tr><td>📅 Fecha</td><td>" . date('d/m/Y') . "</td></tr>
                <tr><td>⏱ Minutos antes</td><td><strong>$minutos minutos</strong></td></tr>
            </table>
            <p>Si necesitas regularizar esta incidencia, contacta con RRHH.</p>
        ");
    }

    public static function plantillaResumenDiario(array $stats): string {
        $fecha = date('d/m/Y');
        $rows  = '';
        foreach ($stats['empleados'] as $emp) {
            $n = htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']);
            $rows .= "<tr><td>$n</td><td>{$emp['departamento']}</td><td>{$emp['horas']}h</td></tr>";
        }
        return self::wrapper("Resumen diario - $fecha", "
            <p>Aquí tienes el resumen de actividad del día <strong>$fecha</strong>:</p>
            <table class='info-table'>
                <tr><td>👥 Total empleados</td><td>{$stats['total_empleados']}</td></tr>
                <tr><td>✅ Han fichado</td><td>{$stats['han_fichado']}</td></tr>
                <tr><td>❌ Sin fichar</td><td>{$stats['sin_fichar']}</td></tr>
                <tr><td>⚠️ Incidencias</td><td>{$stats['incidencias']}</td></tr>
            </table>
            <h3>Detalle por empleado</h3>
            <table class='info-table'>
                <thead><tr><th>Empleado</th><th>Dpto.</th><th>Horas</th></tr></thead>
                <tbody>$rows</tbody>
            </table>
        ");
    }

    /**
     * Wrapper HTML común para todos los emails
     */
    private static function wrapper(string $titulo, string $contenido): string {
        $app = APP_NAME;
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>$titulo</title>
<style>
  body{margin:0;padding:0;background:#f0f2f5;font-family:'Segoe UI',Arial,sans-serif;color:#1e293b}
  .wrapper{max-width:600px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .header{background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:28px 32px;color:#fff}
  .header h1{margin:0;font-size:22px;font-weight:700}
  .header p{margin:4px 0 0;font-size:13px;opacity:.75}
  .body{padding:32px}
  .body p{font-size:15px;line-height:1.6;margin:0 0 16px}
  .info-table{width:100%;border-collapse:collapse;margin:16px 0}
  .info-table td,.info-table th{padding:10px 14px;border-bottom:1px solid #e2e8f0;font-size:14px}
  .info-table thead tr{background:#f8fafc}
  .info-table td:first-child{color:#64748b;font-weight:500;width:45%}
  .badge{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600}
  .badge-warning{background:#fef3c7;color:#92400e}
  .badge-info{background:#dbeafe;color:#1e40af}
  .btn{display:inline-block;margin-top:8px;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px}
  .footer{background:#f8fafc;padding:16px 32px;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>⏱ $app</h1>
    <p>Sistema de Control Horario y Gestión de Proyectos</p>
  </div>
  <div class="body">
    <h2 style="margin:0 0 20px;font-size:18px;color:#1e293b">$titulo</h2>
    $contenido
  </div>
  <div class="footer">
    <p>Este mensaje fue generado automáticamente por $app &bull; $year</p>
    <p>Por favor, no respondas a este correo. Para consultas, contacta con RRHH.</p>
  </div>
</div>
</body>
</html>
HTML;
    }
}
