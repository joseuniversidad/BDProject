<?php
session_start();
include '../conexion_db/conexionOracle.php';

// 🔒 Verificar sesión
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login_admin.php");
    exit;
}

$nombre = $_SESSION['prof_nombre'];
$id_prof = trim($_SESSION['prof_id']);

$sql_cursos = "
    SELECT C.ID_CURSO, C.COD_CURSO, C.NOMBRE
    FROM CURSOS C
    JOIN PROFESOR_CURSO PC ON C.ID_CURSO = PC.ID_CURSO
    WHERE TRIM(PC.ID_PROF) = TRIM(:id_prof)
";
$stmt_cursos = oci_parse($conn, $sql_cursos);
oci_bind_by_name($stmt_cursos, ":id_prof", $id_prof);
oci_execute($stmt_cursos);

$cursos = [];
while ($row = oci_fetch_assoc($stmt_cursos)) {
    $cursos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Profesor</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navbar -->
    <div class="p-6 bg-green-600 text-white flex justify-between items-center shadow">
        <h1 class="text-2xl font-bold">Bienvenido, <?= htmlspecialchars($nombre) ?></h1>
        <nav class="space-x-3">
            <a href="../conexion_db/logoutadmin.php" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600">🔒 Cerrar Sesión</a>
        </nav>
    </div>

    <div class="p-8">
        <h2 class="text-xl font-semibold mb-6">📚 Cursos Asignados</h2>

        <?php if (!empty($cursos)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($cursos as $curso): ?>
            <div 
                class="cursor-pointer bg-white rounded-2xl shadow-lg p-6 hover:shadow-2xl hover:scale-105 transform transition duration-300"
                onclick="crearTarea('<?= $curso['ID_CURSO'] ?>','<?= htmlspecialchars($curso['NOMBRE']) ?>')">
                <h3 class="text-lg font-bold text-green-700 mb-2">
                    <?= htmlspecialchars($curso['COD_CURSO']) ?>
                </h3>
                <p class="text-gray-700 text-base"><?= htmlspecialchars($curso['NOMBRE']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-gray-500 italic">❌ No tienes cursos asignados actualmente.</p>
        <?php endif; ?>
    </div>

    <script>
        function crearTarea(idCurso, nombreCurso) {
            Swal.fire({
                title: '¿Crear tarea para ' + nombreCurso + '?',
                text: 'Se abrirá el formulario para crear la tarea de este curso.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear tarea',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../profesor/crear_tarea.php?id_curso=' + idCurso + '&nombre=' + encodeURIComponent(nombreCurso);
                }
            });
        }
    </script>
</body>
</html>

<?php
oci_close($conn);
?>
