<?php include '../conexion_db/conexionOracle.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<style>
    .logo {
        width: 180px;
        height: auto;
        border-radius: 50%;
        object-fit: cover;
    }

    .subtitulo {
        text-align: center;
        /* centra el texto debajo del logo */
        font-family: 'Arial', sans-serif;
        font-size: 20px;
        color: #333;
        font-weight: 600;
    }
</style>

<body>
    <div class="contenedor-formularios">
        <h2>Inicio de Sesión</h2>
        <h2 class="subtitulo">Bienvenidos a nuestra universidad</h2>
        <img src="../Imagenes/logo.png" alt="Logo" class="logo">
        <form id="formLogin" action="../modules/procesar_login.php" method="POST">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="carnet" placeholder="Carnet" required pattern="\d+" title="Solo números" required autocomplete="new-email">
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required autocomplete="new-password">
                <i class="fa-solid fa-eye" id="togglePassword" style="right: 14px; left: auto; cursor:pointer;"></i>
            </div>
            <button type="submit" name="login">Ingresar</button>
        </form>
        <div class="acciones">
            <a href="../modules/recuperar_contrasenia.php" class="forgot">Recuperar Contraseña</a>
            <a href="registro_estudiante.php" class="signup">Registrarse <i class="fa-solid fa-user-plus"></i></a>
        </div>
    </div>
</body>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.querySelector('input[name="password"]');

    togglePassword.addEventListener('click', () => {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        togglePassword.classList.toggle('fa-eye-slash');
    });
</script>

</html>