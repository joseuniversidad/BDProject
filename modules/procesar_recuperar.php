<?php
error_reporting(0);
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $nueva = trim($_POST['nueva_password']);
    $confirmar = trim($_POST['confirmar_password']);


    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Correo inválido',
                text: 'Por favor ingresa un correo electrónico válido.'
            }).then(()=>{ window.location.href='../principal_views/recuperar_password.php'; });
        </script>";
        exit;
    }

    if ($nueva !== $confirmar) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Contraseñas distintas',
                text: 'Ambas contraseñas deben coincidir.'
            }).then(()=>{ window.location.href='../principal_views/recuperar_password.php'; });
        </script>";
        exit;
    }


    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $nueva)) {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Contraseña débil',
                text: 'Debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo.'
            }).then(()=>{ window.location.href='../principal_views/recuperar_password.php'; });
        </script>";
        exit;
    }


    $sql = "SELECT ID_ESTUDIANTE FROM ESTUDIANTES WHERE EMAIL = :correo";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":correo", $correo);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if (!$row) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Correo no encontrado',
                text: 'No existe un estudiante con ese correo registrado.'
            }).then(()=>{ window.location.href='../principal_views/recuperar_password.php'; });
        </script>";
        exit;
    }


    $hashed_password = password_hash($nueva, PASSWORD_DEFAULT);
    $update_sql = "UPDATE ESTUDIANTES SET PASSWORD = :password WHERE EMAIL = :correo";
    $update_stmt = oci_parse($conn, $update_sql);
    oci_bind_by_name($update_stmt, ":password", $hashed_password);
    oci_bind_by_name($update_stmt, ":correo", $correo);
    $r = oci_execute($update_stmt, OCI_COMMIT_ON_SUCCESS);

    if ($r) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Contraseña actualizada',
                text: 'Tu contraseña fue cambiada exitosamente.'
            }).then(()=>{ window.location.href='../principal_views/login.php'; });
        </script>";
    } else {
        $e = oci_error($update_stmt);
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar',
                text: 'No se pudo cambiar la contraseña. Detalle: ".addslashes($e['message'])."'
            }).then(()=>{ window.location.href='../principal_views/recuperar_password.php'; });
        </script>";
    }

    oci_free_statement($stmt);
    oci_free_statement($update_stmt);
    oci_close($conn);
}
?>

</body>
</html>
