<?php 
include "conexionBD.php";

$pass=password_hash($contraseña, 'DEFAULT');

$contraseña=$_POST["password"];

if(password_verify ($contraseña , $pass)){

} 



?>