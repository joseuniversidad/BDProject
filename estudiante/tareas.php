<?php
session_start();
include '../conexion_db/conexionOracle.php';

if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_est = $_SESSION['est_id'];

$sql = "
    SELECT 
        T.ID_TAREA, 
        T.TITULO, 
        TO_CHAR(T.FECHA_VENCE, 'YYYY-MM-DD') AS FECHA_VENCE, 
        T.PONDERACION,
        ET.CALIFICACION
    FROM TAREAS T
    LEFT JOIN ENTREGAS_TAREAS ET
        ON T.ID_TAREA = ET.ID_TAREA AND ET.ID_ESTUDIANTE = :id
    WHERE T.ID_ESTUDIANTE = :id
    ORDER BY T.FECHA_VENCE
";

$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $id_est);
oci_execute($stmt);

// Verificar si hay tareas
$hayTareas = false;
$primerFila = oci_fetch_assoc($stmt);
if ($primerFila !== false) {
    $hayTareas = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/panel.css">
</head>

<body class="bg-gray-100">
    <?php include '../dashboard/navbar.php'; ?>

    <div class="max-w-5xl mx-auto mt-12 bg-white p-8 rounded-2xl shadow-lg">
        <h2 class="text-3xl font-bold text-center mb-6 text-blue-700">📚 Mis Tareas Asignadas</h2>

        <?php if (!$hayTareas): ?>
            <p class="text-gray-600 text-center text-lg">No tienes tareas asignadas por el momento.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse rounded-lg overflow-hidden shadow-sm">
                    <thead>
                        <tr class="bg-blue-600 text-white text-left">
                            <th class="p-3">Título</th>
                            <th class="p-3">Fecha Límite</th>
                            <th class="p-3">Ponderación</th>
                            <th class="p-3">Acción</th>
                            <th class="p-3 text-center">Calificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $row = $primerFila;
                        do {
                            $fecha_vence = $row['FECHA_VENCE'];
                            $fecha_actual = date('Y-m-d');
                            $fecha_formateada = date('d/m/Y', strtotime($fecha_vence));
                            $color_fecha = ($fecha_vence < $fecha_actual) 
                                ? 'text-red-600 font-bold' 
                                : 'text-green-600 font-semibold';

                            $calificacion = $row['CALIFICACION'] !== null ? $row['CALIFICACION'] : "—";
                        ?>
                            <tr class="border-b hover:bg-gray-50 transition duration-200">
                                <td class="p-3"><?php echo htmlspecialchars($row['TITULO']); ?></td>
                                <td class="p-3 <?php echo $color_fecha; ?>">
                                    <?php echo $fecha_formateada; ?>
                                </td>
                                <td class="p-3"><?php echo $row['PONDERACION']; ?>%</td>
                                <td class="p-3">
                                    <a href="subir_tarea.php?id_tarea=<?php echo $row['ID_TAREA']; ?>" 
                                       class="text-blue-600 font-medium hover:underline">
                                       Subir
                                    </a>
                                </td>
                                <td class="p-3 text-center font-semibold">
                                    <?php echo $calificacion; ?>
                                </td>
                            </tr>
                        <?php
                            $row = oci_fetch_assoc($stmt);
                        } while ($row !== false);
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="../js/sidebar.js"></script>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
