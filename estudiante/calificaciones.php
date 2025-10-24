<?php
session_start();
include '../conexion_db/conexionOracle.php';


if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_estudiante = $_SESSION['est_id'] ?? null;
if (!$id_estudiante) {
    die("Error: estudiante no identificado.");
}


$sql = "
    SELECT C.COD_CURSO, C.NOMBRE AS NOMBRE_CURSO, CF.NOTA_FINAL, CF.APROBADO
    FROM CALIFICACIONES_FINAL CF
    JOIN CURSOS C ON CF.ID_CURSO = C.ID_CURSO
    WHERE CF.ID_ESTUDIANTE = :id_est
    ORDER BY C.COD_CURSO
";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id_est", $id_estudiante);
oci_execute($stmt);

$calificaciones = [];
while ($row = oci_fetch_assoc($stmt)) {
    $calificaciones[] = $row;
}
oci_free_statement($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Calificaciones Finales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/panel.css">
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center p-8">

    <div class="w-full max-w-6xl mb-6">
        <button onclick="history.back()" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            ← Regresar
        </button>
    </div>

    <h1 class="text-3xl font-bold mb-8 text-green-700 text-center">🏆 Mis Calificaciones Finales</h1>

    <?php if (!empty($calificaciones)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 w-full max-w-6xl">
            <?php foreach ($calificaciones as $cal):
                $nota = $cal['NOTA_FINAL'];
                $aprobado = $cal['APROBADO'] === 'S';
                $colorNota = ($nota !== null && $nota >= 60) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            ?>
            <div class="bg-white shadow-lg rounded-2xl p-6 flex flex-col justify-between hover:shadow-2xl transition duration-300">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($cal['COD_CURSO']) ?></h2>
                    <p class="text-gray-700 font-semibold mb-4"><?= htmlspecialchars($cal['NOMBRE_CURSO']) ?></p>

                    <p class="text-center text-lg font-semibold">
                        <span class="px-3 py-1 rounded-full <?= $colorNota ?>">
                            <?= ($nota !== null) ? htmlspecialchars($nota) : '—' ?>
                        </span>
                    </p>
                </div>

                <div class="text-center mt-4">
                    <?php if ($nota === null): ?>
                        <span class="text-gray-400 font-medium">—</span>
                    <?php else: ?>
                        <?php if ($aprobado): ?>
                            <span class="text-green-700 font-bold">✅ Aprobado</span>
                        <?php else: ?>
                            <span class="text-red-700 font-bold">❌ Reprobado</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500 italic text-lg mt-6">
            ⚠️ No tienes calificaciones registradas aún.
        </p>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

<?php oci_close($conn); ?>
