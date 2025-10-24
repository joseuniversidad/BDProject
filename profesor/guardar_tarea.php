<?php
session_start();
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 🔒 Verificar sesión
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
        // ✅ 1. Insertar tarea para todos los estudiantes de la facultad del profesor
        $sql = "INSERT INTO TAREAS (
                    ID_TAREA, ID_ESTUDIANTE, ID_PROF, ID_CURSO, TITULO, 
                    FECHA_PUBLICADO, FECHA_VENCE, PONDERACION
                )
                SELECT 
                    TAREAS_SEQ.NEXTVAL, e.ID_ESTUDIANTE, :prof, :curso, :titulo, 
                    SYSDATE, TO_DATE(:vence, 'YYYY-MM-DD'), :pond
                FROM ESTUDIANTES e
                WHERE e.FACULTAD = (
                    SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof
                )";

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
                  WHERE FACULTAD = (
                      SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof
                  )";
        $st = oci_parse($conn, $query);
        oci_bind_by_name($st, ":prof", $id_prof);
        oci_execute($st);

        // ✅ 3. Configurar PHPMailer (solo una instancia y agregar todos los correos)
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'JrCayetano77@gmail.com'; // Tu correo
        $mail->Password = 'vuqjuilnyhwtgdfs'; // Contraseña de aplicación Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('JrCayetano77@gmail.com', 'Sistema Universitario');
        $mail->isHTML(true);
        $mail->Subject = "Nueva tarea publicada: $titulo";
        $mail->Body = "
            <h3>📘 Se ha asignado una nueva tarea</h3>
            <p><b>Título:</b> $titulo</p>
            <p><b>Fecha límite:</b> $fecha_vencimiento</p>
            <p><b>Ponderación:</b> $ponderacion%</p>
            <p>Por favor revisa la plataforma para más detalles.</p>
        ";

        while ($row = oci_fetch_assoc($st)) {
            $mail->addBCC($row['EMAIL']); // Usar BCC para enviar a muchos sin revelar correos
        }

        // Enviar correo (si hay estudiantes)
        if ($mail->getToAddresses() || $mail->getBCCAddresses()) {
            $mail->send();
        }

        // ✅ Mostrar mensaje con SweetAlert
        echo "
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Tarea publicada',
                text: 'La tarea fue creada y las notificaciones fueron enviadas correctamente.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = '../personal_views/panel_profesor.php';
            });
        </script>
        </body>
        </html>";
        exit;

    } catch (Exception $ex) {
        // ❌ Error general
        echo "
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error al publicar tarea',
                html: 'Ocurrió un error: <br><b>" . addslashes($ex->getMessage()) . "</b>',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Intentar nuevamente'
            }).then(() => {
                window.history.back();
            });
        </script>
        </body>
        </html>";
        exit;
    }
}
?>
