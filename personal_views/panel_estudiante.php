<?php


session_start();
if (!isset($_SESSION['carnet'])) {
    header("Location: login.php");
    exit;
}


$nombre    = $_SESSION['nombre'] ?? 'Usuario';
$apellidos = $_SESSION['apellidos'] ?? '';
$facultad  = $_SESSION['facultad'] ?? 'Facultad desconocida';
$foto      = $_SESSION['foto'] ?? '../imagenes/logo.png';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/panel.css">
</head>

<body>
    <?php include '../dashboard/navbar.php'; ?>
    <div class="content">
        <div class="profile-card">
            <img src="<?php echo htmlspecialchars($foto); ?>" class="avatar" alt="Avatar">
            <h1>Bienvenido</h1>
            <h2><?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?></h2>
            <p class="facultad">Facultad: <?php echo htmlspecialchars($facultad); ?></p>

            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>

            <a href="#" class="btn-message">Mensaje</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/botonlogoutconfirm.js"></script>
    <script src="../js/sidebar.js"></script>


</body>

</html>