<?php include '../conexion_db/conexionOracle.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="../css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .contenedor-formularios {
            width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            text-align: center;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
        .volver {
            display: inline-block;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="contenedor-formularios">
    <h2>Recuperar Contraseña</h2>
    <form id="formRecuperar" action="../modules/procesar_recuperar.php" method="POST" >
        <input type="email" name="correo" placeholder="Correo institucional" required
               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$"
               title="Ingrese un correo electrónico válido" autocomplete="new-email">
        
        <input type="password" name="nueva_password" placeholder="Nueva contraseña" required
               pattern="(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}"
               title="Debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo especial"autocomplete="new-password" >

        <input type="password" name="confirmar_password" placeholder="Confirmar contraseña" required autocomplete="new-password" > 

        <button type="submit">Actualizar Contraseña</button>
    </form>
    <a href="login.php" class="volver">← Volver al inicio</a>
</div>

<script>
document.getElementById("formRecuperar").addEventListener("submit", function(event){
    const nueva = document.querySelector('input[name="nueva_password"]').value;
    const confirmar = document.querySelector('input[name="confirmar_password"]').value;
    
    if(nueva !== confirmar){
        event.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Las contraseñas no coinciden',
            text: 'Asegúrate de escribir la misma contraseña en ambos campos.'
        });
    }
});
</script>

</body>
</html>
