<?php
session_start();
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login General</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php
    if (isset($_POST['login'])) {
        $carnet = trim($_POST['carnet']);
        $password = trim($_POST['password']);
        $rol = $_POST['rol'];

        // =======================
        // LOGIN ADMINISTRADOR
        // =======================
        if ($rol === 'admin') {
            $sql = "SELECT * FROM ADMINISTRADOR WHERE CARNET = :carnet";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":carnet", $carnet);
            oci_execute($stmt);
            $row = oci_fetch_assoc($stmt);

            if ($row && password_verify($password, $row['PASSWORD'])) {
                $_SESSION['admin_id'] = $row['ID_ADMIN'];
                $_SESSION['admin_nombre'] = $row['NOMBRE'] . ' ' . $row['APELLIDO'];

                echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Bienvenido Administrador',
                    text: 'Inicio de sesión exitoso'
                }).then(() => {
                    window.location.href = '../personal_views/panel_admin.php';
                });
            </script>";
            } else {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Credenciales inválidas',
                    text: 'Carnet o contraseña incorrectos.'
                }).then(() => {
                    window.location.href = '../principal_views/login_admin.php';
                });
            </script>";
            }

            oci_free_statement($stmt);
        }

        // =======================
        // LOGIN PROFESOR
        // =======================
        elseif ($rol === 'profesor') {
            $sql = "SELECT * FROM PROFESORES WHERE CARNET = :carnet";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":carnet", $carnet);
            oci_execute($stmt);
            $row = oci_fetch_assoc($stmt);

            if ($row) {
                $passwordHash = $row['CONTRASENA'];

                // Verificar si es hash o texto plano (por compatibilidad)
                if (password_verify($password, $passwordHash) || $password === $passwordHash) {
                    $_SESSION['prof_id'] = $row['ID_PROF'];
                    $_SESSION['prof_nombre'] = $row['NOMBRE'] . ' ' . $row['APELLIDOS'];
                    $_SESSION['id_facultad'] = $row['ID_FACULTAD'];

                    echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Bienvenido Profesor',
                        text: 'Inicio de sesión exitoso'
                    }).then(() => {
                        window.location.href = '../personal_views/panel_profesor.php';
                    });
                </script>";
                } else {
                    echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Contraseña incorrecta',
                        text: 'Por favor verifica tus credenciales.'
                    }).then(() => {
                        window.location.href = '../principal_views/login_admin.php';
                    });
                </script>";
                }
            } else {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Carnet no encontrado',
                    text: 'No existe un profesor con ese carnet.'
                }).then(() => {
                    window.location.href = '../principal_views/login_admin.php';
                });
            </script>";
            }

            oci_free_statement($stmt);
        }
    }

    oci_close($conn);
    ?>

</body>

</html>