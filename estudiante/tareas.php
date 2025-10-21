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
        TO_CHAR(T.FECHA_VENCE, 'DD/MM/YYYY') AS FECHA_VENCE, 
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
<?php include '../dashboard/navbar.php'; ?>

<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Mis Tareas Asignadas</h2>

        <?php if (!$hayTareas): ?>
            <p class="text-gray-600">No tienes tareas asignadas por el momento.</p>
        <?php else: ?>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-green-600 text-white">
                        <th class="p-2">Título</th>
                        <th>Fecha Límite</th>
                        <th>Ponderación</th>
                        <th>Acción</th>
                        <th>Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                    $row = $primerFila;
                    do {
                        $calificacion = $row['CALIFICACION'] !== null ? $row['CALIFICACION'] : "—";
                    ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2"><?php echo htmlspecialchars($row['TITULO']); ?></td>
                            <td><?php echo $row['FECHA_VENCE']; ?></td>
                            <td><?php echo $row['PONDERACION']; ?>%</td>
                            <td>
                                <a href="subir_tarea.php?id_tarea=<?php echo $row['ID_TAREA']; ?>" class="text-blue-600 hover:underline">Subir</a>
                            </td>
                            <td class="text-center font-semibold"><?php echo $calificacion; ?></td>
                        </tr>
                    <?php
                        $row = oci_fetch_assoc($stmt);
                    } while ($row !== false);
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>