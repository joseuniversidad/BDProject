<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$facultad_usuario = $_SESSION['facultad'] ?? '';

$pensums = [
    "Ingenieria en Sistemas" => "../pensum/Sistemas.php",
    "Odontologia"            => "../pensum/Odontologia.php",
    "Medicina"               => "../pensum/Medicina.php",
    "Derecho"                => "../pensum/Derecho.php",
];


$url_pensum = $pensums[$facultad_usuario] ?? "../pensum/general.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Menú Estudiante</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<style>
    .name {
        text-align: center;
        font-family: Lucida Bright, serif;
        font-size: 25px;
        font-weight: 750;
        color: white;
        
        letter-spacing: 1px;
    }
</style>

<body>
    <nav class="sidebar" id="sidebar" aria-label="Menú principal">
        <div class="profile">
            <a href="../principal_views/panel.php">
                <img src="../imagenes/logo.png" alt="avatar">
            </a>
            <div class="name">
                <?php echo htmlspecialchars($_SESSION['nombre'] . " " . $_SESSION['apellidos']); ?>
            </div>
        </div>

        <a href="#"><span class="icon">🏠</span><span class="label">Tablero</span></a>
        <a href="#"><span class="icon">📚</span><span class="label">Cursos</span></a>
        <a href="#"><span class="icon">📝</span><span class="label">Tareas</span></a>

        <a href="<?php echo $url_pensum; ?>">
            <span class="icon">📖</span><span class="label">Pensum</span>
        </a>

        <a href="#"><span class="icon">💰</span><span class="label">Estado de solvencia</span></a>
        <a href="#" class="logout" id="btnLogout"><span class="icon">🔓</span><span class="label">Cerrar Sesión</span></a>
        <button id="toggleBtn" class="toggle-btn" aria-label="Colapsar sidebar">⮜</button>
    </nav>

    <script src="../js/sidebar.js"></script>
    <script src="../js/botonlogoutconfirm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>