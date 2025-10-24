<?php
session_start();
include '../conexion_db/conexionOracle.php';

// 🔒 Verificar sesión
if (!isset($_SESSION['prof_id'])) {
    header("Location: ../principal_views/login_admin.php");
    exit;
}

$id_prof = trim($_SESSION['prof_id']);
$nombre_prof = $_SESSION['prof_nombre'];
$id_curso = $_GET['id_curso'] ?? null;
$nombre_curso = $_GET['nombre_curso'] ?? 'Curso desconocido';

if (!$id_curso) {
    die("Error: No se especificó un curso.");
}

// 🔹 Obtener tareas sin duplicados
$sql_tareas = "
    SELECT DISTINCT TITULO, ID_TAREA, FECHA_PUBLICADO, FECHA_VENCE, PONDERACION
    FROM TAREAS
    WHERE ID_CURSO = :id_curso AND ID_PROF = :id_prof
    ORDER BY FECHA_PUBLICADO DESC
";
$stmt_tareas = oci_parse($conn, $sql_tareas);
oci_bind_by_name($stmt_tareas, ":id_curso", $id_curso);
oci_bind_by_name($stmt_tareas, ":id_prof", $id_prof);
oci_execute($stmt_tareas);

$tareas = [];
$titulos_vistos = [];
while ($t = oci_fetch_assoc($stmt_tareas)) {
    $titulo_normalizado = trim(strtolower($t['TITULO']));
    if (!in_array($titulo_normalizado, $titulos_vistos)) {
        $tareas[] = $t;
        $titulos_vistos[] = $titulo_normalizado;
    }
}

// 🔹 Obtener estudiantes
$sql_estudiantes = "
    SELECT DISTINCT e.ID_ESTUDIANTE, e.NOMBRE, e.APELLIDOS
    FROM ESTUDIANTES e
    JOIN PAGOS p ON p.ID_ESTUDIANTE = e.ID_ESTUDIANTE
    ORDER BY e.NOMBRE
";
$stmt_est = oci_parse($conn, $sql_estudiantes);
oci_execute($stmt_est);

$estudiantes = [];
while ($est = oci_fetch_assoc($stmt_est)) {
    $estudiantes[] = $est;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tareas - <?= htmlspecialchars($nombre_curso) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen">

<!-- Barra superior -->
<div class="p-6 bg-green-600 text-white flex justify-between items-center shadow-md">
    <h1 class="text-2xl font-bold">📘 <?= htmlspecialchars($nombre_curso) ?></h1>
    <span>Profesor: <?= htmlspecialchars($nombre_prof) ?></span>
</div>

<!-- Contenido principal -->
<div class="p-8">
<?php if (!empty($tareas)): ?>
    <?php foreach ($tareas as $tarea): ?>
        <div class="bg-white shadow-md rounded-xl p-6 mb-8 border-l-4 border-green-600 transition hover:shadow-lg">
            <h2 class="text-2xl font-bold text-green-700 mb-2">
                <?= htmlspecialchars($tarea['TITULO']) ?>
            </h2>
            <p class="text-gray-600">📅 Publicado: <?= htmlspecialchars($tarea['FECHA_PUBLICADO']) ?></p>
            <p class="text-gray-600 mb-4">⏰ Vence: <?= htmlspecialchars($tarea['FECHA_VENCE']) ?></p>
            <p class="text-gray-700 font-semibold mb-4">🧮 Ponderación: <?= htmlspecialchars($tarea['PONDERACION']) ?>%</p>

            <?php if (!empty($estudiantes)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-green-100 text-green-800 text-center font-semibold">
                                <th class="py-2 px-4 border">Estudiante</th>
                                <th class="py-2 px-4 border">Estado</th>
                                <th class="py-2 px-4 border">Archivo</th>
                                <th class="py-2 px-4 border">Fecha Entrega</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php foreach ($estudiantes as $est): ?>
                                <?php
                                // 🔍 Obtener la última entrega
                                $sql_entrega = "
                                    SELECT FECHA_ENTREGA, RUTA_ARCHIVO
                                    FROM (
                                        SELECT FECHA_ENTREGA, RUTA_ARCHIVO
                                        FROM ENTREGAS_TAREAS
                                        WHERE ID_TAREA = :id_tarea AND ID_ESTUDIANTE = :id_est
                                        ORDER BY FECHA_ENTREGA DESC
                                    ) WHERE ROWNUM = 1
                                ";
                                $stmt_ent = oci_parse($conn, $sql_entrega);
                                oci_bind_by_name($stmt_ent, ":id_tarea", $tarea['ID_TAREA']);
                                oci_bind_by_name($stmt_ent, ":id_est", $est['ID_ESTUDIANTE']);
                                oci_execute($stmt_ent);
                                $entrega = oci_fetch_assoc($stmt_ent);
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border"><?= htmlspecialchars($est['NOMBRE'] . ' ' . $est['APELLIDOS']) ?></td>
                                    <td class="py-2 px-4 border font-semibold">
                                        <?php if ($entrega): ?>
                                            <span class="text-green-600">✅ Entregado</span>
                                        <?php else: ?>
                                            <span class="text-red-500">❌ No entregado</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-2 px-4 border">
                                        <?php if ($entrega && $entrega['RUTA_ARCHIVO']): ?>
                                            <?php $ruta_archivo = "../uploads/" . htmlspecialchars($entrega['RUTA_ARCHIVO']); ?>
                                            <button 
                                                onclick="openFileModal('<?= $ruta_archivo ?>')" 
                                                class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                                👁️ Ver archivo
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-2 px-4 border">
                                        <?= $entrega ? htmlspecialchars($entrega['FECHA_ENTREGA']) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-red-500 mt-2 italic">⚠ No hay alumnos con pago registrado.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-gray-500 italic text-center mt-10 text-lg">❌ No hay tareas registradas para este curso.</p>
<?php endif; ?>
</div>

<!-- 🪟 Modal grande para mostrar el archivo -->
<div id="fileModal" class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 h-5/6 p-4 relative">
        <button onclick="closeFileModal()" class="absolute top-3 right-4 text-2xl font-bold text-gray-600 hover:text-black">✖</button>
        <iframe id="fileFrame" src="" class="w-full h-full rounded-lg border"></iframe>
    </div>
</div>

<script>
function openFileModal(ruta) {
    document.getElementById('fileFrame').src = ruta;
    document.getElementById('fileModal').classList.remove('hidden');
}
function closeFileModal() {
    document.getElementById('fileModal').classList.add('hidden');
    document.getElementById('fileFrame').src = '';
}
</script>

</body>
</html>

<?php oci_close($conn); ?>
