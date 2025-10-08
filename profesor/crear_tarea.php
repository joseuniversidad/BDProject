<?php
session_start();
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded shadow-lg">
    <h2 class="text-2xl font-bold text-center mb-6">Asignar Nueva Tarea</h2>
    
    <form action="guardar_tarea.php" method="POST">
        <label class="block mb-2 font-semibold">Título de la tarea:</label>
        <input type="text" name="titulo" required class="w-full border p-2 rounded mb-4">

        <label class="block mb-2 font-semibold">ID del Curso:</label>
        <input type="number" name="id_curso" required class="w-full border p-2 rounded mb-4">

        <label class="block mb-2 font-semibold">Fecha de entrega:</label>
        <input type="date" name="fecha_vencimiento" required class="w-full border p-2 rounded mb-4">

        <label class="block mb-2 font-semibold">Ponderación (%):</label>
        <input type="number" step="0.01" name="ponderacion" required class="w-full border p-2 rounded mb-4">

        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            Publicar Tarea
        </button>
    </form>
</div>

</body>
</html>
