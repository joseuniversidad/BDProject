<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'JrCayetano77@gmail.com'; 
    $mail->Password   = 'wyzuvfgkddtyflmb';       
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;


    $mail->setFrom('JrCayetano77@gmail.com', 'Jr Cayetano'); 
    $mail->addAddress('JrCayetano77@gmail.com', 'Jr Cayetano');
    $mail->isHTML(true);
    $mail->Subject = 'Correo de prueba PHPMailer';
    $mail->Body    = '<h2>¡Hola!</h2><p>Este es un correo de prueba usando PHPMailer en Laragon.</p>';
    $mail->AltBody = '¡Hola! Este es un correo de prueba en texto plano.';


    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    $mail->send();
    echo '<h3>✅ Correo enviado correctamente</h3>';
} catch (Exception $e) {
    echo "<h3>❌ Error al enviar correo: {$mail->ErrorInfo}</h3>";
}
