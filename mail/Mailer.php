<?php
// ============================================================
// mail/Mailer.php - Sistema de envío de emails
// Usa mail() nativo de PHP. Para producción recomendamos PHPMailer/SMTP
// ============================================================

class Mailer {

    private string $fromEmail;
    private string $fromName;

    public function __construct() {
        $this->fromEmail = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : 'noreply@empresa.com';
        $this->fromName  = defined('MAIL_FROM_NAME')  ? MAIL_FROM_NAME  : 'TimeControl';
    }

    /**
     * Enviar alerta de retraso al empleado y a RRHH
     */
    public function enviarAlertaRetraso(array $usuario, int $minutos): bool {
        $asunto = "[TimeControl] Registro de retraso - {$usuario['nombre']} {$usuario['apellidos']}";
        $cuerpo = $this->template('retraso', [
            'nombre'  => $usuario['nombre'] . ' ' . $usuario['apellidos'],
            'minutos' => $minutos,
            'hora'    => date('H:i'),
            'fecha'   => date('d/m/Y'),
        ]);

        $enviado = $this->send($usuario['email'], $asunto, $cuerpo);
        $this->logEmail($usuario['id'] ?? null, $usuario['email'], $asunto, 'retraso', $enviado ? 'enviado' : 'error');
        return $enviado;
    }

    /**
     * Enviar alerta de olvido de fichaje
     */
    public function enviarAlertaOlvido(array $usuario): bool {
        $asunto = "[TimeControl] Olvido de fichaje de salida - {$usuario['nombre']} {$usuario['apellidos']}";
        $cuerpo = $this->template('olvido', [
            'nombre' => $usuario['nombre'] . ' ' . $usuario['apellidos'],
            'fecha'  => date('d/m/Y', strtotime('yesterday')),
        ]);

        $enviado = $this->send($usuario['email'], $asunto, $cuerpo);
        $this->logEmail($usuario['id'] ?? null, $usuario['email'], $asunto, 'olvido', $enviado ? 'enviado' : 'error');
        return $enviado;
    }

    /**
     * Enviar informe semanal a manager
     */
    public function enviarInformeSemanal(array $manager, array $datos): bool {
        $asunto = "[TimeControl] Informe semanal - " . date('W/Y');
        $cuerpo = $this->template('informe', $datos);

        $enviado = $this->send($manager['email'], $asunto, $cuerpo);
        $this->logEmail($manager['id'] ?? null, $manager['email'], $asunto, 'informe', $enviado ? 'enviado' : 'error');
        return $enviado;
    }

    /**
     * Función central de envío
     */
    private function send(string $to, string $subject, string $body): bool {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "X-Mailer: TimeControl/1.0\r\n";

        // En entorno de desarrollo, loguear en lugar de enviar
        if (defined('APP_ENV') && APP_ENV === 'dev') {
            error_log("[MAIL] To: {$to} | Subject: {$subject}");
            return true;
        }

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Plantillas de email en HTML
     */
    private function template(string $tipo, array $vars): string {
        $appName = APP_NAME ?? 'TimeControl';

        $estiloBase = '
            font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;
            background: #f5f5f5; padding: 20px;
        ';
        $estiloCard = '
            background: white; border-radius: 8px; padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ';
        $estiloHeader = 'background: #1e293b; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;';

        switch ($tipo) {
            case 'retraso':
                $color   = '#ef4444';
                $icono   = '⏰';
                $titulo  = 'Registro de Retraso';
                $cuerpo  = "
                    <p>Hola, <strong>{$vars['nombre']}</strong></p>
                    <p>Se ha registrado una entrada tardía en el sistema:</p>
                    <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                        <tr><td style='padding:8px;background:#f8f8f8;font-weight:bold;'>Fecha:</td><td style='padding:8px;'>{$vars['fecha']}</td></tr>
                        <tr><td style='padding:8px;background:#f8f8f8;font-weight:bold;'>Hora de entrada:</td><td style='padding:8px;'>{$vars['hora']}</td></tr>
                        <tr><td style='padding:8px;background:#f8f8f8;font-weight:bold;'>Minutos de retraso:</td><td style='padding:8px;color:{$color};font-weight:bold;'>{$vars['minutos']} minutos</td></tr>
                    </table>
                    <p>Si tienes alguna justificación, por favor contacta con tu responsable.</p>
                ";
                break;

            case 'olvido':
                $color   = '#f59e0b';
                $icono   = '📋';
                $titulo  = 'Olvido de Fichaje de Salida';
                $cuerpo  = "
                    <p>Hola, <strong>{$vars['nombre']}</strong></p>
                    <p>No se ha registrado tu fichaje de <strong>salida</strong> del día <strong>{$vars['fecha']}</strong>.</p>
                    <p>Por favor, contacta con tu responsable o con el departamento de RRHH para regularizar esta incidencia.</p>
                    <p style='background:#fef3c7;padding:12px;border-radius:6px;border-left:4px solid #f59e0b;'>
                        <strong>Importante:</strong> Recuerda fichar siempre la salida para un correcto control horario.
                    </p>
                ";
                break;

            case 'informe':
            default:
                $color   = '#3b82f6';
                $icono   = '📊';
                $titulo  = 'Informe Semanal';
                $cuerpo  = "<p>Adjunto encontrarás el informe semanal del equipo.</p>";
                break;
        }

        return "
        <div style='{$estiloBase}'>
            <div style='{$estiloCard}'>
                <div style='{$estiloHeader}'>
                    <h2 style='margin:0;'>{$icono} {$appName}</h2>
                    <p style='margin:5px 0 0;opacity:0.8;'>{$titulo}</p>
                </div>
                <div style='padding:20px 0;'>
                    {$cuerpo}
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                    <p style='color:#9ca3af;font-size:12px;text-align:center;'>
                        Este es un mensaje automático de {$appName}. No respondas a este email.
                    </p>
                </div>
            </div>
        </div>";
    }

    /**
     * Registrar email en la base de datos
     */
    private function logEmail(?int $idUsuario, string $destinatario, string $asunto, string $tipo, string $estado): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO email_log (id_usuario, destinatario, asunto, tipo, estado) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$idUsuario, $destinatario, $asunto, $tipo, $estado]);
        } catch (Exception $e) {
            error_log("Error al registrar email log: " . $e->getMessage());
        }
    }
}
