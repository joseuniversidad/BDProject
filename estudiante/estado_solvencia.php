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
     <link rel="stylesheet" href="../css/panel.css">
    <style>
        /* Contenedor principal */
        .solvencia-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fefefe;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* Título */
        .solvencia-container h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 30px;
        }

        /* Grid de tarjetas */
        .saldo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        /* Tarjeta individual */
        .saldo-card {
            border-left: 6px solid;
            padding: 20px;
            border-radius: 12px;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .saldo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        /* Texto de estado */
        .estado-solvente {
            color: #16a34a;
            /* verde */
            font-weight: bold;
        }

        .estado-pendiente {
            color: #dc2626;
            /* rojo */
            font-weight: bold;
        }

        /* Títulos dentro de tarjeta */
        .saldo-card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #1f2937;
        }

        /* Detalles de pago */
        .saldo-card p {
            margin: 4px 0;
            color: #374151;
            font-size: 0.95rem;
        }
    </style>
</head>

<body>
    <?php include '../dashboard/navbar.php'; ?>
    <div class="solvencia-container">
        <h2>💰 Estado de Solvencia</h2>
        <div class="saldo-grid">
            <?php foreach ($meses as $mes):
                $solvente = isset($pagos[$mes]);
                $color_border = $solvente ? '#16a34a' : '#dc2626';
                $estado = $solvente ? '✅ Solvente' : '❌ Pendiente';
                $monto = $pagos[$mes]['monto'] ?? '-';
                $ref = $pagos[$mes]['referencia'] ?? '-';
                $fecha = isset($pagos[$mes]['fecha']) ? date("d-m-Y", strtotime($pagos[$mes]['fecha'])) : '-';
            ?>
                <div class="saldo-card" style="border-left-color: <?php echo $color_border; ?>;">
                    <h3><?php echo $mes; ?></h3>
                    <p>Estado: <span class="<?php echo $solvente ? 'estado-solvente' : 'estado-pendiente'; ?>"><?php echo $estado; ?></span></p>
                    <p>Monto: Q <?php echo $monto; ?></p>
                    <p>Referencia: <?php echo $ref; ?></p>
                    <p>Fecha: <?php echo $fecha; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>