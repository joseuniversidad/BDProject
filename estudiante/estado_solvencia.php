<?php
session_start();
include '../conexion_db/conexionOracle.php';

if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_est = $_SESSION['est_id'];

// 🔹 Obtener pagos del estudiante
$sqlPagos = "SELECT TIPO, FECHA_PAGO, MONTO, REFERENCIA 
             FROM PAGOS 
             WHERE ID_ESTUDIANTE = :id_est";
$stmtPagos = oci_parse($conn, $sqlPagos);
oci_bind_by_name($stmtPagos, ":id_est", $id_est);
oci_execute($stmtPagos);

$pagos = [];
while ($row = oci_fetch_assoc($stmtPagos)) {
    $mes_pago = str_replace("Pago ", "", $row['TIPO']);
    $pagos[$mes_pago] = [
        'fecha' => $row['FECHA_PAGO'],
        'monto' => $row['MONTO'],
        'referencia' => $row['REFERENCIA']
    ];
}
oci_free_statement($stmtPagos);
oci_close($conn);

$meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Solvencia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/panel.css">
</head>

<body class="bg-gray-100 min-h-screen flex">
    <?php include '../dashboard/navbar.php'; ?>

    <!-- Contenido principal -->
    <main class="flex-1 ml-64 pt-24 px-6"> 
        <!-- ml-64 deja espacio al sidebar -->

        <section class="bg-white shadow-xl rounded-2xl p-8 max-w-6xl mx-auto">
            <h2 class="text-center text-3xl font-bold text-blue-600 mb-8 flex items-center justify-center gap-2">
                💰 Estado de Solvencia
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($meses as $mes):
                    $solvente = isset($pagos[$mes]);
                    $color_border = $solvente ? 'border-green-500' : 'border-red-500';
                    $estado = $solvente ? '✅ Solvente' : '❌ Pendiente';
                    $monto = $pagos[$mes]['monto'] ?? '-';
                    $ref = $pagos[$mes]['referencia'] ?? '-';
                    $fecha = isset($pagos[$mes]['fecha']) ? date("d-m-Y", strtotime($pagos[$mes]['fecha'])) : '-';
                ?>
                    <div class="border-l-4 <?php echo $color_border; ?> bg-gray-50 rounded-xl shadow-sm p-5 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo $mes; ?></h3>
                        <p class="text-sm text-gray-700 mb-1">
                            Estado:
                            <span class="<?php echo $solvente ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'; ?>">
                                <?php echo $estado; ?>
                            </span>
                        </p>
                        <p class="text-sm text-gray-700 mb-1">Monto: <span class="font-medium">Q <?php echo $monto; ?></span></p>
                        <p class="text-sm text-gray-700 mb-1">Referencia: <span class="font-medium"><?php echo $ref; ?></span></p>
                        <p class="text-sm text-gray-700">Fecha: <span class="font-medium"><?php echo $fecha; ?></span></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/botonlogoutconfirm.js"></script>
    <script src="../js/sidebar.js"></script>
</body>
</html>
