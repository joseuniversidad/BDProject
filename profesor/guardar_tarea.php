<?php
session_start();
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar sesión del profesor
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $id_curso = $_POST['id_curso'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $ponderacion = $_POST['ponderacion'];
    $id_prof = $_SESSION['prof_id'];

    try {
        // ✅ 1. Insertar tarea para todos los estudiantes de la misma facultad del profesor
        $sql = "INSERT INTO TAREAS (ID_TAREA, ID_ESTUDIANTE, ID_PROF, ID_CURSO, TITULO, FECHA_PUBLICADO, FECHA_VENCE, PONDERACION)
                SELECT TAREAS_SEQ.NEXTVAL, e.ID_ESTUDIANTE, :prof, :curso, :titulo, SYSDATE, TO_DATE(:vence, 'YYYY-MM-DD'), :pond
                FROM ESTUDIANTES e
                WHERE e.FACULTAD = (SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof)";

        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":prof", $id_prof);
        oci_bind_by_name($stmt, ":curso", $id_curso);
        oci_bind_by_name($stmt, ":titulo", $titulo);
        oci_bind_by_name($stmt, ":vence", $fecha_vencimiento);
        oci_bind_by_name($stmt, ":pond", $ponderacion);

        if (!oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stmt);
            throw new Exception($e['message']);
        }

        // ✅ 2. Obtener los correos de los estudiantes de esa facultad
        $query = "SELECT EMAIL FROM ESTUDIANTES 
                  WHERE FACULTAD = (SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof)";
        $st = oci_parse($conn, $query);
        oci_bind_by_name($st, ":prof", $id_prof);
        oci_execute($st);

        // ✅ 3. Configurar PHPMailer
        while ($row = oci_fetch_assoc($st)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'JrCayetano77@gmail.com';  // tu correo
                $mail->Password = 'vuqjuilnyhwtgdfs';       // tu contraseña de aplicación Gmail
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom('JrCayetano77@gmail.com', 'Sistema Universitario');
                $mail->addAddress($row['EMAIL']);
                $mail->isHTML(true);
                $mail->Subject = "Nueva tarea publicada: $titulo";
                $mail->Body = "
                    <h3>Se ha asignado una nueva tarea</h3>
                    <p><b>Título:</b> $titulo</p>
                    <p><b>Fecha límite:</b> $fecha_vencimiento</p>
                    <p><b>Ponderación:</b> $ponderacion%</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                // No detenemos el flujo si un correo falla
                error_log("❌ Error enviando correo a {$row['EMAIL']}: {$mail->ErrorInfo}");
            }
        }

        echo "<script>alert('✅ Tarea publicada y notificaciones enviadas correctamente'); window.location.href='crear_tarea.php';</script>";
    } catch (Exception $ex) {
        echo "<script>alert('❌ Error al publicar tarea: " . addslashes($ex->getMessage()) . "'); window.history.back();</script>";
    }
}
