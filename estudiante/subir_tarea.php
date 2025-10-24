<?php
session_start();
include '../conexion_db/conexionOracle.php';

// 🔒 Verificar sesión de estudiante
if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_est = $_SESSION['est_id'];
$id_tarea = $_GET['id_tarea'] ?? null;

if (!$id_tarea) {
    die("ID de tarea no especificado.");
}

// 🧮 Verificar número de intentos anteriores
$sql_intentos = "
    SELECT COUNT(*) AS NUM_INTENTOS
    FROM ENTREGAS_TAREAS
    WHERE ID_TAREA = :id_tarea AND ID_ESTUDIANTE = :id_est
";
$stmt_int = oci_parse($conn, $sql_intentos);
oci_bind_by_name($stmt_int, ":id_tarea", $id_tarea);
oci_bind_by_name($stmt_int, ":id_est", $id_est);
oci_execute($stmt_int);
$row_int = oci_fetch_assoc($stmt_int);
$num_intentos = $row_int['NUM_INTENTOS'] ?? 0;

// ⚠️ Límite de intentos (3 máximo)
$max_intentos = 3;

if ($num_intentos >= $max_intentos) {
    $error = "Has alcanzado el límite de 3 intentos para subir esta tarea.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {

    $file = $_FILES['archivo'];
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // ✅ Validaciones
    if (!in_array($ext, $allowed)) {
        $error = "Solo se permiten archivos PDF o imágenes (JPG, JPEG, PNG).";
    } elseif ($file['error'] !== 0) {
        $error = "Error al subir el archivo.";
    } else {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // 📦 Renombrar archivo de forma única
        $nombreArchivo = time() . "_$id_est." . $ext;
        $ruta = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($file['tmp_name'], $ruta)) {

            // 💾 Insertar registro de entrega
            $sql = "INSERT INTO ENTREGAS_TAREAS (ID_ENT, ID_TAREA, ID_ESTUDIANTE, FECHA_ENTREGA, RUTA_ARCHIVO) 
                    VALUES (ENTREGAS_SEQ.NEXTVAL, :id_tarea, :id_est, SYSDATE, :ruta)";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":id_tarea", $id_tarea);
            oci_bind_by_name($stmt, ":id_est", $id_est);
            oci_bind_by_name($stmt, ":ruta", $nombreArchivo);

            if (oci_execute($stmt)) {
                $success = "Archivo subido correctamente. Intento #" . ($num_intentos + 1) . " de $max_intentos.";
            } else {
                $error = "Error al registrar la entrega.";
            }
        } else {
            $error = "No se pudo mover el archivo al servidor.";
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">📤 Subir Tarea</h2>
            
            <!-- 🔙 Botón de Regresar -->
            <a href="../estudiante/tareas.php" 
               class="bg-gray-500 text-white px-3 py-2 rounded hover:bg-gray-600 transition duration-200">
                ⬅️ Regresar
            </a>
        </div>

        <?php if (isset($error)): ?>
            <p class="text-red-600 mb-4 font-semibold"><?= htmlspecialchars($error) ?></p>
        <?php elseif (isset($success)): ?>
            <p class="text-green-600 mb-4 font-semibold"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <p class="text-gray-700 mb-4">Intentos usados: <strong><?= $num_intentos ?></strong> / 3</p>

        <?php if ($num_intentos < $max_intentos): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block mb-2 font-semibold">Archivo (PDF o Imagen):</label>
                <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required
                       class="border p-2 w-full rounded">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
                Subir Archivo
            </button>
        </form>
        <?php else: ?>
            <p class="text-gray-500 italic text-center mt-4">Ya no puedes subir más archivos para esta tarea.</p>
        <?php endif; ?>
    </div>

</body>
</html>
