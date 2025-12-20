<?php
namespace application;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    public static function send(string $to, string $subject, string $body, string $toName = '', string $from = 'noreply@tusitio.com'): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.tuservidor.com';         // Por ejemplo: smtp.gmail.com
            $mail->SMTPAuth = true;
            $mail->Username = 'tucorreo@tusitio.com';     // Tu usuario SMTP
            $mail->Password = 'tu_clave';                // Tu contraseña SMTP
            $mail->SMTPSecure = 'tls';                   // 'tls' o 'ssl'
            $mail->Port = 587;                           // 587 para TLS, 465 para SSL

            // Remitente y destinatario
            $mail->setFrom($from, 'Soporte');
            $mail->addAddress($to, $toName);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }
}
