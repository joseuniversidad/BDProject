<?php
session_start();
include '../conexion_db/conexionOracle.php';

// 🔹 Verificar sesión
if (!isset($_SESSION['est_id'])) {
    header("Location: ../principal_views/login.php");
    exit;
}

$id_est = $_SESSION['est_id'];
$nombre_est = $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'];

$montos = [
    "Ingenieria en Sistemas" => 450.00,
    "Ingenieria Industrial" => 400.00,
    "Ingenieria Quimica" => 420.00,
    "Licenciatura en Administracion" => 380.00,
    "Licenciatura en Trabajo Social" => 350.00
];

$monto_pagar = 0.00;
$facultad_nombre = "";
$mensaje = "";

// 🔹 Registrar pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar'])) {

    $facultad_nombre = $_POST['facultad'];
    $mes = $_POST['mes'];
    $monto_pagar = $montos[$facultad_nombre] ?? 300.00;
    $tipo = "Pago $mes";
    $metodo = $_POST['metodo'];
    $referencia = 'REF' . rand(10000, 99999);

    // Verificar si ya pagó este mes
    $sqlCheck = "SELECT COUNT(*) AS PAGADO FROM PAGOS WHERE ID_ESTUDIANTE = :id_est AND TIPO = :tipo";
    $stmtCheck = oci_parse($conn, $sqlCheck);
    oci_bind_by_name($stmtCheck, ":id_est", $id_est);
    oci_bind_by_name($stmtCheck, ":tipo", $tipo);
    oci_execute($stmtCheck);
    $rowCheck = oci_fetch_assoc($stmtCheck);
    oci_free_statement($stmtCheck);

    if ($rowCheck['PAGADO'] > 0) {
        $mensaje = "❌ Ya realizaste el pago del mes $mes.";
    } else {
        $sqlInsert = "
            INSERT INTO PAGOS (ID_PAGO, ID_ESTUDIANTE, TIPO, MONTO, FECHA_PAGO, METODO, REFERENCIA)
            VALUES (PAGOS_SEQ.NEXTVAL, :id_est, :tipo, :monto, SYSDATE, :metodo, :referencia)
        ";
        $stmtInsert = oci_parse($conn, $sqlInsert);
        oci_bind_by_name($stmtInsert, ":id_est", $id_est);
        oci_bind_by_name($stmtInsert, ":tipo", $tipo);
        oci_bind_by_name($stmtInsert, ":monto", $monto_pagar);
        oci_bind_by_name($stmtInsert, ":metodo", $metodo);
        oci_bind_by_name($stmtInsert, ":referencia", $referencia);

        if (oci_execute($stmtInsert)) {
            $mensaje = "✅ Pago del mes $mes realizado con éxito. Referencia: $referencia";
        } else {
            $e = oci_error($stmtInsert);
            $mensaje = "❌ Error al registrar el pago: " . htmlentities($e['message']);
        }
        oci_free_statement($stmtInsert);
    }
}

// 🔹 Obtener todos los pagos del estudiante
$sqlPagos = "SELECT TIPO, FECHA_PAGO, MONTO, REFERENCIA FROM PAGOS WHERE ID_ESTUDIANTE = :id_est";
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

// Meses del año
$meses = [
    "Enero","Febrero","Marzo","Abril","Mayo","Junio",
    "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación y Estado de Solvencia</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <link rel="stylesheet" href="../css/panel.css">
</head>
<body class="bg-gray-100">

<?php include '../dashboard/navbar.php'; ?>

<div class="max-w-xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">💳 Pago Mensual</h2>

    <form method="POST">
        <!-- Facultad -->
        <label class="block mb-2 font-semibold">Selecciona tu facultad:</label>
        <select name="facultad" class="border p-2 w-full rounded mb-4" required>
            <option value="">-- Selecciona una facultad --</option>
            <?php foreach ($montos as $fac => $monto): ?>
                <option value="<?php echo $fac; ?>" <?php echo ($fac == $facultad_nombre ? "selected" : ""); ?>>
                    <?php echo "$fac (Q " . number_format($monto,2) . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Mes -->
        <label class="block mb-2 font-semibold">Selecciona el mes a pagar:</label>
        <select name="mes" class="border p-2 w-full rounded mb-4" required>
            <option value="">-- Selecciona un mes --</option>
            <?php foreach ($meses as $m): ?>
                <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Método de pago -->
        <label class="block mb-2 font-semibold">Método de pago:</label>
        <select name="metodo" class="border p-2 w-full rounded mb-4" required>
            <option value="Tarjeta">Tarjeta de crédito</option>
            <option value="Transferencia">Transferencia bancaria</option>
            <option value="Efectivo">Efectivo</option>
        </select>

        <button type="submit" name="pagar" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Pagar
        </button>
    </form>

    <?php if ($mensaje): ?>
        <script>
            Swal.fire({
                icon: '<?php echo strpos($mensaje, "✅") !== false ? "success" : "error"; ?>',
                title: '<?php echo addslashes($mensaje); ?>',
                confirmButtonText: "Aceptar",
                background: "#f9fafb"
            });
        </script>
    <?php endif; ?>
</div>

<!-- Estado de Solvencia -->
<div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-xl">
    <h2 class="text-3xl font-extrabold mb-6 text-gray-800 text-center">💰 Estado de Solvencia</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-green-400 to-blue-500 text-white">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-semibold uppercase tracking-wider">Mes</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold uppercase tracking-wider">Estado</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold uppercase tracking-wider">Monto</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold uppercase tracking-wider">Referencia</th>
                    <th class="py-3 px-6 text-center text-sm font-semibold uppercase tracking-wider">Fecha de Pago</th>
                </tr>
            </thead>
            <tbody class="bg-gray-50 divide-y divide-gray-200">
                <?php foreach ($meses as $mes): 
                    $solvente = isset($pagos[$mes]);
                    $estado = $solvente ? "✅ Solvente" : "❌ Pendiente";
                    $monto = $pagos[$mes]['monto'] ?? "-";
                    $ref = $pagos[$mes]['referencia'] ?? "-";
                    $fecha = isset($pagos[$mes]['fecha']) ? date("d-m-Y", strtotime($pagos[$mes]['fecha'])) : "-";
                ?>
                <tr class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="py-3 px-6 font-medium text-gray-700"><?php echo $mes; ?></td>
                    <td class="py-3 px-6 text-center">
                        <span class="<?php echo $solvente ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'; ?>">
                            <?php echo $estado; ?>
                        </span>
                    </td>
                    <td class="py-3 px-6 text-center text-gray-700"><?php echo $monto; ?></td>
                    <td class="py-3 px-6 text-center text-gray-700"><?php echo $ref; ?></td>
                    <td class="py-3 px-6 text-center text-gray-700"><?php echo $fecha; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
