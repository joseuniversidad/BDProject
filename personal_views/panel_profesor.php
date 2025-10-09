<?php
session_start();
include '../conexion_db/conexionOracle.php';

// Verificar sesión
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login_admin.php");
    exit;
}

$nombre = $_SESSION['prof_nombre'];
$id_prof = $_SESSION['prof_id'];

// =====================
// GUARDAR CALIFICACIÓN
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_entrega'], $_POST['calificacion'])) {
    $id_entrega = $_POST['id_entrega'];
    $calificacion = $_POST['calificacion'];

    $sql = "UPDATE ENTREGAS_TAREAS 
            SET CALIFICACION = :calificacion 
            WHERE ID_ENT = :id_entrega";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":calificacion", $calificacion);
    oci_bind_by_name($stmt, ":id_entrega", $id_entrega);
    oci_execute($stmt);
}

// =====================
// AGRUPAR TAREAS POR TÍTULO
// =====================
$sql_tareas = "
    SELECT TITULO
    FROM TAREAS
    WHERE ID_PROF = :id_prof
    GROUP BY TITULO
    ORDER BY MIN(ID_TAREA)
";
$stmt_tareas = oci_parse($conn, $sql_tareas);
oci_bind_by_name($stmt_tareas, ":id_prof", $id_prof);
oci_execute($stmt_tareas);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Profesor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800">

    <!-- Encabezado -->
    <div class="p-6 bg-green-600 text-white flex justify-between items-center">
        <h1 class="text-2xl font-bold">Bienvenido, <?php echo htmlspecialchars($nombre); ?></h1>
        <nav class="space-x-3">
            <a href="../profesor/crear_tarea.php" class="bg-white text-green-600 px-4 py-2 rounded hover:bg-green-100">➕ Asignar Tarea</a>
            <a href="../principal_views/panel_admin.php" class="bg-white text-green-600 px-4 py-2 rounded hover:bg-green-100">⬅️ Regresar al Panel Principal</a>
            <a href="../logout.php" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600">🔒 Cerrar Sesión</a>
        </nav>
    </div>

    <div class="p-8">
        <h2 class="text-xl font-semibold mb-6">📋 Tareas Publicadas</h2>

        <?php
        // Recorremos títulos de tareas únicas
        while ($tarea = oci_fetch_assoc($stmt_tareas)) {
            $titulo = htmlspecialchars($tarea['TITULO']);

            echo "<div class='bg-white rounded shadow p-4 mb-6'>";
            echo "<h3 class='text-lg font-semibold text-green-700 mb-3'>📘 $titulo</h3>";

            // Obtener todas las entregas asociadas a cualquier tarea con ese título
            $sql_entregas = "
                SELECT 
                    ET.ID_ENT,
                    S.NOMBRE,
                    S.APELLIDOS,
                    S.EMAIL,
                    ET.FECHA_ENTREGA,
                    ET.RUTA_ARCHIVO,
                    ET.CALIFICACION
                FROM ENTREGAS_TAREAS ET
                JOIN ESTUDIANTES S ON ET.ID_ESTUDIANTE = S.ID_ESTUDIANTE
                JOIN TAREAS T ON ET.ID_TAREA = T.ID_TAREA
                WHERE T.TITULO = :titulo AND T.ID_PROF = :id_prof
                ORDER BY ET.FECHA_ENTREGA DESC
            ";
            $stmt_entregas = oci_parse($conn, $sql_entregas);
            oci_bind_by_name($stmt_entregas, ":titulo", $tarea['TITULO']);
            oci_bind_by_name($stmt_entregas, ":id_prof", $id_prof);
            oci_execute($stmt_entregas);

            $hay_entregas = false;

            echo "<table class='w-full border-collapse border border-gray-300 text-sm'>";
            echo "<thead>
                    <tr class='bg-green-100 text-left'>
                        <th class='border p-2'>Estudiante</th>
                        <th class='border p-2'>Email</th>
                        <th class='border p-2'>Fecha de Entrega</th>
                        <th class='border p-2'>Archivo</th>
                        <th class='border p-2'>Calificación</th>
                        <th class='border p-2'>Acción</th>
                    </tr>
                  </thead><tbody>";

            while ($entrega = oci_fetch_assoc($stmt_entregas)) {
                $hay_entregas = true;
                $nombre_est = htmlspecialchars($entrega['NOMBRE'] . ' ' . $entrega['APELLIDOS']);
                $email = htmlspecialchars($entrega['EMAIL']);
                $fecha = htmlspecialchars($entrega['FECHA_ENTREGA']);
                $archivo = htmlspecialchars($entrega['RUTA_ARCHIVO']);
                $nota = $entrega['CALIFICACION'];

                echo "
                <tr class='hover:bg-gray-50'>
                    <td class='border p-2'>$nombre_est</td>
                    <td class='border p-2'>$email</td>
                    <td class='border p-2'>$fecha</td>
                    <td class='border p-2'>
                        <a href='../uploads/$archivo' target='_blank' class='text-blue-600 hover:underline'>📂 Ver</a>
                    </td>
                    <form method='POST'>
                        <td class='border p-2 text-center'>
                            <input type='hidden' name='id_entrega' value='{$entrega['ID_ENT']}'>
                            <input type='number' name='calificacion' step='0.01' min='0' max='100' 
                                   value='" . ($nota ?? '') . "' 
                                   class='w-16 p-1 border rounded'>
                        </td>
                        <td class='border p-2'>
                            <button type='submit' class='bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700'>
                                Guardar
                            </button>
                        </td>
                    </form>
                </tr>";
            }

            echo "</tbody></table>";

            if (!$hay_entregas) {
                echo "<p class='text-gray-500 italic mt-2'>Ningún estudiante ha entregado esta tarea aún.</p>";
            }

            echo "</div>";
        }

        oci_close($conn);
        ?>
    </div>

</body>

</html>