<?php
session_start();
include '../conexion_db/conexionOracle.php'; // Conexión a Oracle
require '../vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php

// -------------------
// FUNCION PARA GENERAR CONTRASEÑA ALEATORIA
// -------------------
function generarPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

if (isset($_POST['registrar'])) {
    // -------------------
    // RECIBIR DATOS DEL FORMULARIO
    // -------------------
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $fecha_nac = $_POST['fecha_nac']; // formato 'YYYY-MM-DD'
    $direccion = trim($_POST['direccion']);
    $email = trim($_POST['email']);

    // -------------------
    // FACULTADES
    // -------------------
    $facultades = [
        1 => "Ingeniería en Sistemas",
        2 => "Ingeniería Química",
        3 => "Ingeniería Industrial",
        4 => "Licenciatura en Administración",
        5 => "Licenciatura en Trabajo Social",
        6 => "Enfermería",
        7 => "Odontología",
        8 => "Criminología",
        9 => "Psicología",
        10 => "Arquitectura"
    ];

    // ---------- CAMBIO: FACULTAD como número para BD
    $facultad = intval($_POST['facultad']); // número para la BD
    $nombre_facultad = $facultades[$facultad] ?? null; // solo para mostrar

    if (!$nombre_facultad) {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Facultad inválida',
            text: 'Debe seleccionar una facultad válida.'
        });
        </script>";
        exit;
    }

    // -------------------
    // VALIDACIÓN DE EDAD
    // -------------------
    $edad = (int)((time() - strtotime($fecha_nac)) / (365.25 * 24 * 60 * 60));
    if ($edad < 17) {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Edad insuficiente',
            text: 'Debe tener al menos 17 años para registrarse.'
        }).then(() => {
            window.location.href = '../principal_views/registro_estudiante.php';
        });
        </script>";
        exit;
    }

    // -------------------
    // VALIDACIÓN DE EMAIL
    // -------------------
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Email inválido',
            text: 'El email no tiene un formato válido.'
        }).then(() => {
            window.location.href = '../principal_views/registro_estudiante.php';
        });
        </script>";
        exit;
    }

    // -------------------
    // VERIFICAR SI EL EMAIL YA EXISTE
    // -------------------
    $check_sql = "SELECT COUNT(*) AS CANT FROM ESTUDIANTES WHERE EMAIL = :email";
    $check_stmt = oci_parse($conn, $check_sql);
    oci_bind_by_name($check_stmt, ':email', $email);
    oci_execute($check_stmt);
    $row = oci_fetch_assoc($check_stmt);

    if ($row['CANT'] > 0) {
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Email ya registrado',
            text: 'El email ya está registrado.'
        }).then(() => {
            window.location.href = '../principal_views/registro_estudiante.php';
        });
        </script>";
        exit;
    }

    // -------------------
    // GENERAR CARNET Y CONTRASEÑA
    // -------------------
    $carnet = intval(rand(100000, 999999)); // número
    $password = generarPassword(10);
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // -------------------
    // INSERTAR ESTUDIANTE
    // -------------------
    $insert_sql = "INSERT INTO ESTUDIANTES 
    (CARNET, NOMBRE, APELLIDOS, FECHA_NAC, DIRECCION, EMAIL, PASSWORD, FACULTAD, FECHA_REGISTRO, ESTADO_VERIFICADO)
    VALUES (:carnet, :nombre, :apellidos, TO_DATE(:fecha_nac, 'YYYY-MM-DD'), :direccion, :email, :password, :facultad, SYSDATE, 'S')";

    $insert_stmt = oci_parse($conn, $insert_sql);

    // Bind de texto
    oci_bind_by_name($insert_stmt, ':nombre', $nombre);
    oci_bind_by_name($insert_stmt, ':apellidos', $apellidos);
    oci_bind_by_name($insert_stmt, ':fecha_nac', $fecha_nac);
    oci_bind_by_name($insert_stmt, ':direccion', $direccion);
    oci_bind_by_name($insert_stmt, ':email', $email);
    oci_bind_by_name($insert_stmt, ':password', $password_hashed);

    // ---------- CAMBIO: Bind de números explícito para Oracle
    oci_bind_by_name($insert_stmt, ':carnet', $carnet, -1, SQLT_INT);
    oci_bind_by_name($insert_stmt, ':facultad', $facultad, -1, SQLT_INT);

    // Ejecutar
    if (oci_execute($insert_stmt, OCI_COMMIT_ON_SUCCESS)) {
        // -------------------
        // ENVIAR CORREO CON PHPMailer
        // -------------------
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
            $mail->Subject = 'Registro de Estudiante - Datos de Acceso';
            $mail->Body = "
                <h3>¡Registro exitoso!</h3>
                <p>Su número de carnet es: <b>$carnet</b></p>
                <p>Su contraseña es: <b>$password</b></p>
                <p>Facultad: <b>$nombre_facultad</b></p>
                <p>Guarde estos datos de forma segura.</p>
            ";
            $mail->send();

            echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Registro exitoso!',
                html: 'Estudiante registrado y correo enviado.<br>Su carnet: <b>$carnet</b><br>Contraseña: <b>$password</b><br>Facultad: <b>$nombre_facultad</b>'
            }).then(() => {
                window.location.href = '../principal_views/login.php';
            });
            </script>";

        } catch (Exception $e) {
            echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Registro con aviso',
                html: 'Estudiante registrado pero no se pudo enviar el correo.<br>Error: {$mail->ErrorInfo}'
            }).then(() => {
                window.location.href = '../principal_views/registro_estudiante.php';
            });
            </script>";
        }

    } else {
        $e = oci_error($insert_stmt);
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error al registrar',
            text: '{$e['message']}'
        }).then(() => {
            window.location.href = '../principal_views/registro_estudiante.php';
        });
        </script>";
    }

    oci_free_statement($insert_stmt);
}

oci_close($conn);
?>
