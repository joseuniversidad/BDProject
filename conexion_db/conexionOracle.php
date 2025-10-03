<?php
$user ='USUARIO_CAYETANO';
$pass ="Cayetano7209$";
$db   ="localhost/XE"; 

$conn = oci_connect($user, $pass, $db, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    echo "Conexión fallida: " . $e['message'];
} else {
   
}
?>
