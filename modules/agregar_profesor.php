<?php
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $id_facultad = $_POST['id_facultad'];
    $email = $_POST['email'];

    function generarPassword($length = 10) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    $password_plain = generarPassword(); 
    $password_hashed = password_hash($password_plain, PASSWORD_BCRYPT);

    
    $carnet = rand(100000, 999999);

    
    $sql = "INSERT INTO PROFESORES (CARNET, NOMBRE, APELLIDOS, ID_FACULTAD, EMAIL, CONTRASENA)
            VALUES (:carnet, :nombre, :apellidos, :id_facultad, :email, :password)";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":carnet", $carnet);
    oci_bind_by_name($stmt, ":nombre", $nombre);
    oci_bind_by_name($stmt, ":apellidos", $apellidos);
    oci_bind_by_name($stmt, ":id_facultad", $id_facultad);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":password", $password_hashed);

    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'JrCayetano77@gmail.com'; 
            $mail->Password = 'vuqjuilnyhwtgdfs'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('TUCORREO@gmail.com', 'Universidad');
            $mail->addAddress($email, "$nombre $apellidos");

            $mail->isHTML(true);
            $mail->Subject = 'Registro de Profesor - Datos de Acceso';
            $mail->Body = "
                <h3>¡Registro exitoso!</h3>
                <p>Su número de carnet es: <b>$carnet</b></p>
                <p>Su contraseña es: <b>$password_plain</b></p>
                <p>Guarde estos datos de forma segura.</p>
            ";

            $mail->send();

            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Profesor agregado',
                html: 'Profesor registrado y correo enviado.<br>Carnet: <b>$carnet</b><br>Contraseña: <b>$password_plain</b>'
            }).then(() => {
                window.location.href = '../principal_views/panel_admin.php';
            });
            </script>";

        } catch (Exception $e) {
            echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Profesor agregado, pero correo no enviado',
                html: 'Error: {$mail->ErrorInfo}'
            }).then(() => {
                window.location.href = '../principal_views/panel_admin.php';
            });
            </script>";
        }

    } else {
        $e = oci_error($stmt);
        echo "<script>alert('Error al agregar profesor: " . addslashes($e['message']) . "');</script>";
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>
