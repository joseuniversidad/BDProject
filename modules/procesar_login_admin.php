<?php
session_start();
include '../conexion_db/conexionOracle.php';
require '../vendor/autoload.php'; 

if (isset($_POST['login_admin'])) {
    $carnet = trim($_POST['carnet']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM ADMINISTRADOR WHERE CARNET = :carnet";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":carnet", $carnet);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);

    if ($row && password_verify($password, $row['PASSWORD'])) {
        $_SESSION['admin_id'] = $row['ID_ADMIN'];
        $_SESSION['admin_nombre'] = $row['NOMBRE'] . ' ' . $row['APELLIDO'];
        header("Location: ../personal_views/panel_admin.php");
        exit;
    } else {
        echo "<script>
        alert('Credenciales inválidas.');
        window.location.href = '../principal_views/login_admin.php';
        </script>";
    }
}
oci_free_statement($stmt);
oci_close($conn);
