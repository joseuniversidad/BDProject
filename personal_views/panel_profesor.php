<?php
session_start();
include '../conexion_db/conexionOracle.php';

if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login_admin.php");
    exit;
}

$nombre = $_SESSION['prof_nombre'];
$id_prof = $_SESSION['prof_id'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Profesor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800">

    <div class="p-6 bg-green-600 text-white flex justify-between">
        <h1 class="text-2xl font-bold">Bienvenido, <?php echo htmlspecialchars($nombre); ?></h1>
        <nav>
            <a href="../profesor/crear_tarea.php" class="bg-white text-green-600 px-4 py-2 rounded hover:bg-green-100">
                Asignar Tarea
            </a>
            <a href="../logout.php" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600">
                Cerrar Sesión
            </a>
        </nav>
    </div>

    <div class="p-8">
        <h2 class="text-xl font-semibold mb-4">Gestión de Tareas</h2>
        <p>Aquí puedes crear nuevas tareas, asignarlas a tus estudiantes y revisar entregas.</p>
    </div>

</body>

</html>