<?php
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login Estudiante</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre']);
        $apellidos = trim($_POST['apellidos']);
        $id_facultad = trim($_POST['id_facultad']);
        $email = trim($_POST['email']);

        // --- FUNCIONES AUXILIARES ---
        function generarPassword($length = 10)
        {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $chars[rand(0, strlen($chars) - 1)];
            }
            return $password;
        }

        function generarCarnetUnico($conn)
        {
            do {
                $carnet = rand(100000, 999999);
                $query = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM PROFESORES WHERE CARNET = :carnet");
                oci_bind_by_name($query, ":carnet", $carnet);
                oci_execute($query);
                $row = oci_fetch_assoc($query);
                $existe = $row['TOTAL'] > 0;
                oci_free_statement($query);
            } while ($existe);
            return $carnet;
        }

        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

        // --- VERIFICAR SI EL CORREO YA EXISTE ---
        $verificar = oci_parse($conn, "SELECT COUNT(*) AS TOTAL FROM PROFESORES WHERE EMAIL = :email");
        oci_bind_by_name($verificar, ":email", $email);
        oci_execute($verificar);
        $row = oci_fetch_assoc($verificar);
        if ($row['TOTAL'] > 0) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Correo ya registrado',
                text: 'El correo $email ya se encuentra en uso. Intenta con otro.'
            }).then(() => {
                window.history.back();
            });
        </script>";
            oci_free_statement($verificar);
            oci_close($conn);
            exit;
        }
        oci_free_statement($verificar);

        // --- GENERACIÓN DE DATOS ---
        $password_plain = generarPassword();
        $password_hashed = password_hash($password_plain, PASSWORD_BCRYPT);
        $carnet = generarCarnetUnico($conn);

        // --- INSERCIÓN EN LA BD ---
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
            // --- ENVÍO DE CORREO ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'JrCayetano77@gmail.com';
                $mail->Password = 'vuqjuilnyhwtgdfs';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('JrCayetano77@gmail.com', 'Universidad');
                $mail->addAddress($email, "$nombre $apellidos");

                $mail->isHTML(true);
                $mail->Subject = 'Registro de Profesor - Credenciales';
                $mail->Body = "
                <div style='font-family: Arial, sans-serif;'>
                    <h3 style='color:#007BFF;'>¡Registro exitoso!</h3>
                    <p>Estimado(a) <b>$nombre $apellidos</b>,</p>
                    <p>Su registro ha sido completado.</p>
                    <hr>
                    <p><b>Carnet:</b> $carnet</p>
                    <p><b>Contraseña:</b> $password_plain</p>
                    <hr>
                    <p>Por favor guarde estos datos de forma segura.</p>
                </div>
            ";

                $mail->send();

                echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Profesor agregado y correo enviado',
                    html: 'Carnet: <b>$carnet</b><br>Contraseña: <b>$password_plain</b>'
                }).then(() => {
                    window.location.href = '../personal_views/panel_admin.php';
                });
            </script>";
            } catch (Exception $e) {
                echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Profesor agregado',
                    text: 'Error al enviar correo: {$mail->ErrorInfo}'
                }).then(() => {
                    window.location.href = '../personal_views/panel_admin.php';
                });
            </script>";
            }
        } else {
            $e = oci_error($stmt);
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error al registrar profesor',
                html: 'Detalles: " . addslashes($e['message']) . "'
            });
        </script>";
        }

        oci_free_statement($stmt);
        oci_close($conn);
    }
