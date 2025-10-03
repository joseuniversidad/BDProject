<?php
include '../conexion_db/conexionOracle.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Estudiante</title>
    <link rel="stylesheet" href="../css/registro_estudiante.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    
</head>

<body>
    <div class="contenedor-formularios">
        <div class="titulo">Registro de Estudiante</div>
        <div class="subtitulo">Complete los campos para registrarse</div>
        <img src="../Imagenes/logo.png" alt="Logo" class="logo">
        <form action="../modules/procesar_registro.php" method="POST">
            <div class="input-row">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellidos" placeholder="Apellidos" required>
            </div>
            <input type="date" name="fecha_nac" required>
            <input type="text" name="direccion" placeholder="Dirección">
            <input type="email" name="email" placeholder="Correo electrónico" required>

            <select name="facultad" required>
                <option value="">Seleccione Facultad...</option>
                <option value="1">Ingeniería en Sistemas</option>
                <option value="2">Ingeniería Química</option>
                <option value="3">Ingeniería Industrial</option>
                <option value="4">Licenciatura en Administración</option>
                <option value="5">Licenciatura en Trabajo Social</option>
                <option value="6">Enfermería</option>
                <option value="7">Odontología</option>
                <option value="8">Criminología</option>
                <option value="9">Psicología</option>
                <option value="10">Arquitectura</option>
            </select>

            <button type="submit" name="registrar">Registrar</button>
             <a href="login.php" class="enlace-login">¿Ya tienes una cuenta?</a>
        </form>
    </div>
</body>
</html>

<?php oci_close($conn); ?>
