<?php
session_start();
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login Estudiante</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php
    if (isset($_POST['login'])) {

        $carnet = trim($_POST['carnet']);
        $password = trim($_POST['password']);


        if (!ctype_digit($carnet)) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Carnet inválido',
                text: 'El carnet debe contener solo números.'
            }).then(() => {
                window.location.href = '../principal_views/login.php';
            });
        </script>";
            exit;
        }

        $carnet = intval($carnet);


        $sql = "SELECT 
        E.ID_ESTUDIANTE,
        E.CARNET, 
        E.PASSWORD, 
        E.NOMBRE AS NOMBRE_ESTUDIANTE, 
        E.APELLIDOS AS APELLIDOS_ESTUDIANTE, 
        F.NOMBRE AS FACULTAD_NOMBRE
    FROM ESTUDIANTES E
    LEFT JOIN FACULTADES F ON E.FACULTAD = F.ID_FACULTAD
    WHERE E.CARNET = :carnet";


        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":carnet", $carnet, -1, SQLT_INT);
        oci_execute($stmt);

        $row = oci_fetch_assoc($stmt);


        if ($row === false) {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'No encontrado',
                text: 'No se encontró ningún estudiante con este carnet.'
            }).then(() => {
                window.location.href = '../principal_views/login.php';
            });
        </script>";
            exit;
        }


        if (password_verify($password, trim($row['PASSWORD']))) {
            $_SESSION['est_id']    = $row['ID_ESTUDIANTE'];
            $_SESSION['carnet']    = $row['CARNET'];
            $_SESSION['nombre']    = trim($row['NOMBRE_ESTUDIANTE']);
            $_SESSION['apellidos'] = trim($row['APELLIDOS_ESTUDIANTE']);
            $_SESSION['facultad']  = trim($row['FACULTAD_NOMBRE']);


            header("Location: ../personal_views/panel_estudiante.php");
            exit;
        } else {
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Credenciales inválidas',
                text: 'Carnet o contraseña incorrectos.'
            }).then(() => {
                window.location.href = '../principal_views/login.php';
            });
        </script>";
            exit;
        }


        oci_free_statement($stmt);
        oci_close($conn);
    }
    ?>

</body>

</html>