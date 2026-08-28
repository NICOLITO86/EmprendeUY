<?php
include "conexionBD.php";
$cedula = $_POST ['cedula'];
$sen= $conexion->prepare(" DELETE from usuario WHERE Cedula = ?");
$sen->execute([$cedula]);

echo  json_encode(["exito"=>true]);

?>