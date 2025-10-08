<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validar sesión
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $id_curso = isset($_POST['id_curso']) ? $_POST['id_curso'] : null;
    $fecha_vencimiento = isset($_POST['fecha_vencimiento']) ? trim($_POST['fecha_vencimiento']) : '';
    $ponderacion = isset($_POST['ponderacion']) ? floatval($_POST['ponderacion']) : 0;
    $id_prof = $_SESSION['prof_id'];

    if (!$titulo || !$id_curso || !$fecha_vencimiento) {
        echo "<script>alert('Todos los campos son obligatorios'); window.history.back();</script>";
        exit;
    }

    // 1️⃣ Insertar tarea
    $sql = "
        INSERT INTO TAREAS (ID_TAREA, ID_ESTUDIANTE, ID_PROF, ID_CURSO, TITULO, FECHA_PUBLICADO, FECHA_VENCE, PONDERACION)
        SELECT SEQ_TAREA.NEXTVAL, e.ID_ESTUDIANTE, :prof, :curso, :titulo, SYSDATE, TO_DATE(:vence, 'YYYY-MM-DD'), :pond
        FROM ESTUDIANTES e
        WHERE e.FACULTAD = (SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof)
    ";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":prof", $id_prof);
    oci_bind_by_name($stmt, ":curso", $id_curso);
    oci_bind_by_name($stmt, ":titulo", $titulo);
    oci_bind_by_name($stmt, ":vence", $fecha_vencimiento);
    oci_bind_by_name($stmt, ":pond", $ponderacion);

    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {

        // 2️⃣ Traer emails
        $query = "SELECT EMAIL FROM ESTUDIANTES WHERE FACULTAD = (SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof)";
        $st = oci_parse($conn, $query);
        oci_bind_by_name($st, ":prof", $id_prof);
        oci_execute($st);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'JrCayetano77@gmail.com';
        $mail->Password = 'vuqjuilnyhwtgdfs'; // Mejor usar variable de entorno
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('JrCayetano77@gmail.com', 'Sistema Universitario');
        $mail->isHTML(true);
        $mail->Subject = "Nueva tarea publicada: $titulo";
        $mail->Body = "Se ha asignado una nueva tarea: <b>$titulo</b><br>Fecha límite: $fecha_vencimiento";

        while ($row = oci_fetch_assoc($st)) {
            try {
                $mail->clearAddresses();
                $mail->addAddress($row['EMAIL']);
                $mail->send();
            } catch (Exception $e) {
                error_log("Error enviando correo a {$row['EMAIL']}: {$mail->ErrorInfo}");
            }
        }

        echo "<script>alert('Tarea publicada y notificaciones enviadas'); window.location.href='crear_tarea.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>alert('Error al publicar tarea: " . $e['message'] . "');</script>";
    }

    oci_free_statement($stmt);
    oci_free_statement($st);
}
oci_close($conn);
?>
