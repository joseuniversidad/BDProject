<?php
include '../conexion_db/conexionOracle.php';

$sql = "SELECT SEMESTRE, COD_CURSO, NOMBRE, CREDITOS
        FROM CURSOS
        WHERE ID_FACULTAD = 1
        ORDER BY SEMESTRE, ID_CURSO";
$stid = oci_parse($conn, $sql);
oci_execute($stid);

$cursos_por_semestre = [];
while ($row = oci_fetch_assoc($stid)) {
    $sem = $row['SEMESTRE'];
    $cursos_por_semestre[$sem][] = $row;
}
oci_free_statement($stid);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cursos por Semestre</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/panel.css">
    <link rel="stylesheet" href="../css/sistemas.css">
</head>

<body>

    <?php include '../dashboard/navbar.php'; ?>
    <div class="accordion-container">
        <h1 class="mb-4 text-center">Cursos por Semestre</h1>
        <div class="accordion" id="accordionSemestres">
            <?php foreach ($cursos_por_semestre as $semestre => $cursos): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $semestre ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $semestre ?>" aria-expanded="false" aria-controls="collapse<?= $semestre ?>">
                            Semestre <?= $semestre ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $semestre ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $semestre ?>" data-bs-parent="#accordionSemestres">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <?php foreach ($cursos as $curso): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="card card-curso h-100 border-primary">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($curso['NOMBRE']) ?></h5>
                                                <p class="card-text"><strong>Código:</strong> <?= htmlspecialchars($curso['COD_CURSO']) ?></p>
                                                <span class="badge badge-creditos"><?= htmlspecialchars($curso['CREDITOS']) ?> créditos</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/botonlogoutconfirm.js"></script>

</body>


</html>