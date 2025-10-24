<?php
session_start();
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_curso = isset($_GET['id_curso']) ? $_GET['id_curso'] : '';
$nombre_curso = isset($_GET['nombre']) ? $_GET['nombre'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nueva Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">📘 Crear Nueva Tarea</h2>
            <a href="../personal_views/panel_profesor.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                ⬅️ Regresar
            </a>
        </div>

        <form action="guardar_tarea.php" method="POST">
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
</body>
</html>
