<?php
session_start();
include '../conexion_db/conexionOracle.php';

if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_est = $_SESSION['est_id'];

$sqlPago = "SELECT TIPO, FECHA_PAGO 
            FROM PAGOS 
            WHERE ID_ESTUDIANTE = :id_est 
            ORDER BY FECHA_PAGO DESC FETCH FIRST 1 ROWS ONLY";
$stmtPago = oci_parse($conn, $sqlPago);
oci_bind_by_name($stmtPago, ":id_est", $id_est);
oci_execute($stmtPago);
$rowPago = oci_fetch_assoc($stmtPago);
oci_free_statement($stmtPago);

$tipoPago = $rowPago ? strtolower(trim($rowPago['TIPO'])) : '';
$mes_pagado = null;

$meses = [
    'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
    'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
    'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
];

foreach ($meses as $nombre => $num) {
    if (strpos($tipoPago, $nombre) !== false) {
        $mes_pagado = $num;
        break;
    }
}

$semestre_pagado = ($mes_pagado && $mes_pagado > 6) ? 2 : 1;

$sqlFac = "SELECT FACULTAD FROM ESTUDIANTES WHERE ID_ESTUDIANTE = :id_est";
$stmtFac = oci_parse($conn, $sqlFac);
oci_bind_by_name($stmtFac, ":id_est", $id_est);
oci_execute($stmtFac);
$rowFac = oci_fetch_assoc($stmtFac);
oci_free_statement($stmtFac);

$id_facultad = $rowFac['FACULTAD'] ?? null;

$cursos = [];
if ($id_facultad) {
    $sqlCursos = "SELECT COD_CURSO, NOMBRE, CREDITOS 
                  FROM CURSOS 
                  WHERE SEMESTRE = :sem 
                  AND ID_FACULTAD = :fac";
    $stmtCursos = oci_parse($conn, $sqlCursos);
    oci_bind_by_name($stmtCursos, ":sem", $semestre_pagado, -1, SQLT_INT);
    oci_bind_by_name($stmtCursos, ":fac", $id_facultad, -1, SQLT_INT);
    oci_execute($stmtCursos);

    while ($curso = oci_fetch_assoc($stmtCursos)) {
        $cursos[] = $curso;
    }

    oci_free_statement($stmtCursos);
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursos Asignados</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link rel="stylesheet" href="../css/panel.css">
</head>

<body class="bg-gray-100 min-h-screen flex">
    <?php include '../dashboard/navbar.php'; ?>


    <main class="flex-1 ml-64 pt-24 px-6"> 
   

        <section class="bg-white shadow-xl rounded-2xl p-8 max-w-6xl mx-auto">
            <h2 class="text-center text-3xl font-bold text-blue-600 mb-8 flex items-center justify-center gap-2">
                📚 Cursos del Semestre <?php echo htmlspecialchars($semestre_pagado); ?>
            </h2>

            <?php if (empty($cursos)): ?>
                <p class="text-center text-gray-500 text-lg">
                    No hay cursos asignados para tu facultad o semestre.
                </p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($cursos as $curso): ?>
                        <div class="bg-white border border-blue-400 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-blue-600 mb-3">
                                    <?php echo htmlspecialchars($curso['NOMBRE']); ?>
                                </h3>
                                <p class="text-gray-700 mb-1">
                                    <strong>Código:</strong> <?php echo htmlspecialchars($curso['COD_CURSO']); ?>
                                </p>
                                <p class="text-gray-700 mb-3">
                                    <strong>Créditos:</strong> <?php echo htmlspecialchars($curso['CREDITOS']); ?>
                                </p>
                            </div>
                            <div class="bg-blue-600 text-white text-center py-2 rounded-b-xl font-semibold">
                                Semestre <?php echo htmlspecialchars($semestre_pagado); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/botonlogoutconfirm.js"></script>
    <script src="../js/sidebar.js"></script>
</body>
</html>
