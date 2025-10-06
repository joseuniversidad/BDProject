<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}
include '../conexion_db/conexionOracle.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<style>
    .signup {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #4CAF50, #81C784);
        color: white;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
        border-radius: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .signup i {
        font-size: 18px;
    }

    .signup:hover {
        background: linear-gradient(135deg, #388E3C, #66BB6A);
        transform: translateY(-2px);
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
    }
</style>

<body class="bg-light">

    <div class="container py-4">

        <h1 class="text-center mb-3">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></h1>
        <h2 class="text-center mb-5">Gestión Universitaria</h2>


        <section class="mb-5">
            <h3 class="mb-3">Carreras / Facultades</h3>
            <div class="row g-4">
                <?php
                $sql = "SELECT * FROM FACULTADES ORDER BY ID_FACULTAD";
                $stmt = oci_parse($conn, $sql);
                oci_execute($stmt);
                while ($fac = oci_fetch_assoc($stmt)) {
                    $facId = $fac['ID_FACULTAD'];
                    $facNombre = htmlspecialchars($fac['NOMBRE']);
                    $collapseFacId = "collapseFac{$facId}";

                    echo '<div class="col-md-6">';
                    echo '<div class="card shadow-sm border-success">';
                    echo "<div class='card-header bg-success text-white'>";

                    echo "<button class='btn btn-link text-white text-decoration-none w-100 text-start' 
                        data-bs-toggle='collapse' data-bs-target='#{$collapseFacId}' aria-expanded='false'>";
                    echo $facNombre;
                    echo "</button>";
                    echo "</div>";


                    echo "<div class='collapse' id='{$collapseFacId}'>";
                    echo '<div class="card-body">';

                    $sqlSem = "SELECT DISTINCT SEMESTRE FROM CURSOS WHERE ID_FACULTAD = :id_facultad ORDER BY SEMESTRE";
                    $stmtSem = oci_parse($conn, $sqlSem);
                    oci_bind_by_name($stmtSem, ":id_facultad", $facId);
                    oci_execute($stmtSem);

                    while ($sem = oci_fetch_assoc($stmtSem)) {
                        $semestre = $sem['SEMESTRE'];
                        $collapseSemId = "collapseFac{$facId}Sem{$semestre}";


                        echo "<div class='mb-2'>";
                        echo "<button class='btn btn-outline-success w-100 text-start' 
                                data-bs-toggle='collapse' data-bs-target='#{$collapseSemId}' aria-expanded='false'>";
                        echo "Semestre {$semestre}";
                        echo "</button>";


                        echo "<div class='collapse mt-1' id='{$collapseSemId}'>";
                        echo '<ul class="list-group list-group-flush">';

                        $sqlCursos = "SELECT NOMBRE, CREDITOS 
                                  FROM CURSOS 
                                  WHERE ID_FACULTAD = :id_fac AND SEMESTRE = :semestre 
                                  ORDER BY NOMBRE";
                        $stmtCursos = oci_parse($conn, $sqlCursos);
                        oci_bind_by_name($stmtCursos, ":id_fac", $facId);
                        oci_bind_by_name($stmtCursos, ":semestre", $semestre);
                        oci_execute($stmtCursos);

                        while ($curso = oci_fetch_assoc($stmtCursos)) {
                            echo "<li class='list-group-item'>{$curso['NOMBRE']} ({$curso['CREDITOS']} créditos)</li>";
                        }

                        echo '</ul>';
                        echo '</div>';
                        echo "</div>";
                    }

                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>

        <!-- Agregar Profesor -->
        <section class="mb-5">
            <h3 class="mb-3">Agregar Profesor</h3>
            <form action="../modules/agregar_profesor.php" method="POST" class="row g-3 bg-white p-4 shadow-sm rounded">
                <div class="col-md-6">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="apellidos" class="form-control" placeholder="Apellidos" required>
                </div>
                <div class="col-md-6">
                    <input type="number" name="id_facultad" class="form-control" placeholder="ID Facultad" required>
                </div>
                <div class="col-md-6">
                    <input type="email" name="email" class="form-control" placeholder="Correo" required>
                </div>
                <div class="col-md-6">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">Registrar Profesor</button>
                </div>
                <a href="../principal_views/registro_estudiante.php" class="signup">Registrar Estudiante <i class="fa-solid fa-user-plus"></i></a>


            </form>
        </section>

    </div>
    <!-- Botón de Logout -->
    <div class="text-center mt-5">
        <a href="../conexion_db/logoutadmin.php" class="btn btn-danger btn-lg">Cerrar Sesión</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>