<?php
session_start();
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 🔒 Verificar sesión
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login_admin.php");
    exit;
}

$id_prof = $_SESSION['prof_id'];
$id_curso = $_GET['id_curso'] ?? '';
$nombre_curso = $_GET['nombre'] ?? '';

$mensaje = null;
$icono = null;

// 📨 Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $id_curso = $_POST['id_curso'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $ponderacion = $_POST['ponderacion'];

    try {
        // 1️⃣ Insertar tarea para todos los estudiantes de la facultad del profesor
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

        // 2️⃣ Obtener correos de los estudiantes
        $query = "SELECT EMAIL FROM ESTUDIANTES 
                  WHERE FACULTAD = (SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :prof)";
        $st = oci_parse($conn, $query);
        oci_bind_by_name($st, ":prof", $id_prof);
        oci_execute($st);

        // 3️⃣ Enviar correos
        while ($row = oci_fetch_assoc($st)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'JrCayetano77@gmail.com';
                $mail->Password = 'vuqjuilnyhwtgdfs';
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
                    <p><b>Curso:</b> $id_curso</p>
                    <p><b>Fecha límite:</b> $fecha_vencimiento</p>
                    <p><b>Ponderación:</b> $ponderacion%</p>
                ";
                $mail->send();
            } catch (Exception $e) {
                error_log("❌ Error enviando correo a {$row['EMAIL']}: {$mail->ErrorInfo}");
            }
        }

        $mensaje = "Tarea publicada y correos enviados correctamente.";
        $icono = "success";

    } catch (Exception $ex) {
        $mensaje = "Error al publicar la tarea: " . $ex->getMessage();
        $icono = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nueva Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">📘 Crear Nueva Tarea</h2>
            <a href="../personal_views/panel_profesor.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                ⬅️ Regresar
            </a>
        </div>

        <form action="" method="POST">
            <div class="mb-4">
                <label class="block font-semibold mb-1">Curso:</label>
                <input type="text" value="<?= htmlspecialchars($nombre_curso) ?>" class="w-full border rounded p-2 bg-gray-100" readonly>
                <input type="hidden" name="id_curso" value="<?= htmlspecialchars($id_curso) ?>">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Título:</label>
                <input type="text" name="titulo" class="w-full border rounded p-2" placeholder="Ejemplo: Proyecto Final" required>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Fecha de vencimiento:</label>
                <input type="date" name="fecha_vencimiento" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Ponderación (%):</label>
                <input type="number" step="0.01" name="ponderacion" class="w-full border rounded p-2" placeholder="Ej: 25" required>
            </div>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full transition">
                Publicar Tarea
            </button>
        </form>
    </div>

    <?php if ($mensaje): ?>
        <script>
        Swal.fire({
            title: '<?= $icono === "success" ? "✅ Éxito" : "❌ Error" ?>',
            text: '<?= addslashes($mensaje) ?>',
            icon: '<?= $icono ?>',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            <?php if ($icono === "success"): ?>
                window.location.href = '../personal_views/panel_profesor.php';
            <?php endif; ?>
        });
        </script>
    <?php endif; ?>
</body>
</html>
