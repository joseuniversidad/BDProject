<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // ===== Configuración SMTP =====
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'JrCayetano77@gmail.com'; // Tu Gmail completo
    $mail->Password   = 'wyzuvfgkddtyflmb';       // App Password de 16 caracteres, sin espacios
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // ===== Remitente y destinatario =====
    $mail->setFrom('JrCayetano77@gmail.com', 'Jr Cayetano'); // Debe ser tu correo Gmail
    $mail->addAddress('JrCayetano77@gmail.com', 'Jr Cayetano');

    // ===== Contenido =====
    $mail->isHTML(true);
    $mail->Subject = 'Correo de prueba PHPMailer';
    $mail->Body    = '<h2>¡Hola!</h2><p>Este es un correo de prueba usando PHPMailer en Laragon.</p>';
    $mail->AltBody = '¡Hola! Este es un correo de prueba en texto plano.';

    // ===== Depuración =====
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    $mail->send();
    echo '<h3>✅ Correo enviado correctamente</h3>';
} catch (Exception $e) {
    echo "<h3>❌ Error al enviar correo: {$mail->ErrorInfo}</h3>";
}
