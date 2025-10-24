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

// ===============================
// 🔹 OBTENER CURSOS ASIGNADOS
// ===============================
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
while ($curso = oci_fetch_assoc($stmt_cursos)) {
    $cursos[] = $curso;
}
oci_free_statement($stmt_cursos);

// ===============================
// 🔹 OBTENER FACULTAD DEL PROFESOR
// ===============================
$sql_fac = "SELECT ID_FACULTAD FROM PROFESORES WHERE ID_PROF = :id_prof";
$stmt_fac = oci_parse($conn, $sql_fac);
oci_bind_by_name($stmt_fac, ":id_prof", $id_prof);
oci_execute($stmt_fac);
$row_fac = oci_fetch_assoc($stmt_fac);
$id_facultad = $row_fac['ID_FACULTAD'] ?? null;

// ===============================
// 🔹 GUARDAR CALIFICACIONES
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_notas'])) {

    $id_curso = $_POST['curso_calificacion'] ?? null;

    if (!$id_curso) {
        echo "<script>
            Swal.fire({icon:'error', title:'⚠️ Error', text:'Debes seleccionar un curso antes de guardar las calificaciones.'});
        </script>";
    } else {
        foreach ($_POST['nota'] as $id_est => $nota_final) {
            if ($nota_final !== '') {
                $aprobado = ($nota_final >= 60) ? 'S' : 'N';

                // Verificar si ya existe calificación para el estudiante y curso
                $sql_check = "SELECT COUNT(*) AS TOTAL 
                              FROM CALIFICACIONES_FINAL 
                              WHERE ID_ESTUDIANTE = :id_est AND ID_CURSO = :id_curso";
                $stmt_check = oci_parse($conn, $sql_check);
                oci_bind_by_name($stmt_check, ":id_est", $id_est);
                oci_bind_by_name($stmt_check, ":id_curso", $id_curso);
                oci_execute($stmt_check);
                $row = oci_fetch_assoc($stmt_check);

                if ($row['TOTAL'] > 0) {
                    // Actualizar calificación existente
                    $sql_update = "UPDATE CALIFICACIONES_FINAL 
                                   SET NOTA_FINAL = :nota, APROBADO = :aprob
                                   WHERE ID_ESTUDIANTE = :id_est AND ID_CURSO = :id_curso";
                    $stmt_update = oci_parse($conn, $sql_update);
                    oci_bind_by_name($stmt_update, ":nota", $nota_final);
                    oci_bind_by_name($stmt_update, ":aprob", $aprobado);
                    oci_bind_by_name($stmt_update, ":id_est", $id_est);
                    oci_bind_by_name($stmt_update, ":id_curso", $id_curso);
                    oci_execute($stmt_update, OCI_COMMIT_ON_SUCCESS);
                } else {
                    // Insertar nueva calificación
                    $sql_insert = "INSERT INTO CALIFICACIONES_FINAL (ID_CAL, ID_ESTUDIANTE, ID_CURSO, NOTA_FINAL, APROBADO)
                                   VALUES (CALIFICACIONES_FINAL_SEQ.NEXTVAL, :id_est, :id_curso, :nota, :aprob)";
                    $stmt_insert = oci_parse($conn, $sql_insert);
                    oci_bind_by_name($stmt_insert, ":id_est", $id_est);
                    oci_bind_by_name($stmt_insert, ":id_curso", $id_curso);
                    oci_bind_by_name($stmt_insert, ":nota", $nota_final);
                    oci_bind_by_name($stmt_insert, ":aprob", $aprobado);
                    oci_execute($stmt_insert, OCI_COMMIT_ON_SUCCESS);
                }
            }
        }

        echo "<script>
            Swal.fire({
                icon:'success',
                title:'✅ Éxito',
                text:'Las calificaciones fueron guardadas correctamente.'
            }).then(() => window.location.href='panel_profesor.php');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Profesor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

<!-- Navbar superior -->
<div class="p-6 bg-green-600 text-white flex justify-between items-center shadow-md">
    <h1 class="text-2xl font-bold">👨‍🏫 Bienvenido, <?= htmlspecialchars($nombre) ?></h1>
    <nav class="space-x-3">
        <a href="../principal_views/panel_admin.php" class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-green-100 transition">⬅️ Panel Principal</a>
        <a href="../conexion_db/logoutadmin.php" class="bg-red-500 px-4 py-2 rounded-lg hover:bg-red-600 transition">🔒 Cerrar Sesión</a>
    </nav>
</div>

<div class="p-8 space-y-12">
    <!-- ====================== CURSOS ASIGNADOS ====================== -->
    <section>
        <h2 class="text-2xl font-semibold mb-6 text-green-700">📚 Cursos Asignados</h2>

        <?php if (!empty($cursos)): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($cursos as $curso): ?>
                <div class="bg-white shadow-lg rounded-2xl p-6 hover:shadow-2xl transition duration-300 border-t-4 border-green-600">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-bold text-green-700">
                            <?= htmlspecialchars($curso['COD_CURSO']) ?> - <?= htmlspecialchars($curso['NOMBRE']) ?>
                        </h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-gray-500 italic">❌ No tienes cursos asignados actualmente.</p>
        <?php endif; ?>
    </section>

    <!-- ====================== CALIFICACIONES ====================== -->
    <section class="bg-white p-6 rounded-2xl shadow-lg border-t-4 border-green-600">
        <h2 class="text-2xl font-bold mb-6 text-green-700 text-center">🏆 Calificaciones Finales</h2>

        <form method="POST">
            <div class="mb-6 flex items-center justify-center space-x-3">
                <label class="font-semibold text-green-700">Seleccionar curso:</label>
                <select name="curso_calificacion" class="border border-green-400 rounded-lg p-2 focus:ring-2 focus:ring-green-500" required>
                    <option value="">-- Selecciona un curso --</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= $curso['ID_CURSO'] ?>"><?= htmlspecialchars($curso['COD_CURSO'] . " - " . $curso['NOMBRE']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php
            $estudiantes = [];
            if ($id_facultad) {
                $sql_est = "SELECT ID_ESTUDIANTE, NOMBRE, APELLIDOS, CARNET 
                            FROM ESTUDIANTES 
                            WHERE FACULTAD = :facultad ORDER BY NOMBRE";
                $stmt_est = oci_parse($conn, $sql_est);
                oci_bind_by_name($stmt_est, ":facultad", $id_facultad);
                oci_execute($stmt_est);
                while ($row = oci_fetch_assoc($stmt_est)) {
                    $estudiantes[] = $row;
                }
                oci_free_statement($stmt_est);
            }
            ?>

            <?php if (!empty($estudiantes)): ?>
            <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-green-600 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">CARNET</th>
                        <th class="px-4 py-2 text-left">NOMBRE</th>
                        <th class="px-4 py-2 text-center">NOTA FINAL</th>
                        <th class="px-4 py-2 text-center">APROBADO</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($estudiantes as $est): ?>
                    <tr class="hover:bg-green-50">
                        <td class="px-4 py-2"><?= htmlspecialchars($est['CARNET']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($est['NOMBRE'] . ' ' . $est['APELLIDOS']) ?></td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" step="0.01" name="nota[<?= $est['ID_ESTUDIANTE'] ?>]" 
                                class="border rounded p-1 w-20 text-center focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2 text-center">—</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6 text-center">
                <button type="submit" name="guardar_notas"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow-md">
                    💾 Guardar Calificaciones
                </button>
            </div>
            <?php else: ?>
                <p class="text-gray-500 italic text-center">⚠️ No hay estudiantes en tu facultad.</p>
            <?php endif; ?>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

<?php
oci_close($conn);
?>
