<?php include '../conexion_db/conexionOracle.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
        font-family: 'Arial', sans-serif;
        font-size: 20px;
        color: #333;
        font-weight: 600;
    }
</style>

<body>
    <div class="contenedor-formularios">
        <h2>Inicio de Sesión</h2>
        <img src="../Imagenes/logo.png" alt="Logo" class="logo">

        <form id="formLogin" action="../modules/procesar_login_admin.php" method="POST">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="carnet" placeholder="Carnet" required pattern="\d+" autocomplete="new-carnet">
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required autocomplete="new-password">
            </div>

            <div class="input-group">
                <label for="rol">Iniciar como:</label>
                <select name="rol" required>
                    <option value="admin">Administrador</option>
                    <option value="profesor">Profesor</option>
                </select>
            </div>

            <button type="submit" name="login">Ingresar</button>
        </form>
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