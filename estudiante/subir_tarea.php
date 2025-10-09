<?php
session_start();
include '../conexion_db/conexionOracle.php';

if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_est = $_SESSION['est_id'];
$id_tarea = $_GET['id_tarea'] ?? null;

if (!$id_tarea) {
    die("ID de tarea no especificado.");
}

// Manejo de subida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $file = $_FILES['archivo'];
    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $error = "Tipo de archivo no permitido.";
    } elseif ($file['error'] !== 0) {
        $error = "Error al subir el archivo.";
    } else {
        // Crear carpeta uploads si no existe
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Nombre único
        $nombreArchivo = time() . "_$id_est." . $ext;
        $ruta = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($file['tmp_name'], $ruta)) {
            // Guardar en la tabla ENTREGAS_TAREAS
            $sql = "INSERT INTO ENTREGAS_TAREAS (ID_ENT, ID_TAREA, ID_ESTUDIANTE, FECHA_ENTREGA, RUTA_ARCHIVO) 
                    VALUES (ENTREGAS_SEQ.NEXTVAL, :id_tarea, :id_est, SYSDATE, :ruta)";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":id_tarea", $id_tarea);
            oci_bind_by_name($stmt, ":id_est", $id_est);
            oci_bind_by_name($stmt, ":ruta", $nombreArchivo);

            if (oci_execute($stmt)) {
                $success = "Archivo subido correctamente.";
            } else {
                $error = "Error al registrar la entrega.";
            }
        } else {
            $error = "No se pudo mover el archivo.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Subir Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Subir Tarea</h2>

        <?php if (isset($error)): ?>
            <p class="text-red-600 mb-4"><?php echo $error; ?></p>
        <?php elseif (isset($success)): ?>
            <p class="text-green-600 mb-4"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block mb-2 font-semibold">Archivo (Word, PDF, Imagen):</label>
                <input type="file" name="archivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Subir</button>
        </form>
    </div>
</body>

</html>