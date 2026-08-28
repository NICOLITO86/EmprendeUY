<?php
include "conexionBD.php";
$ID = $_POST ['ID'];
$sen= $conexion->prepare(" DELETE from emprendimiento WHERE ID = ?");
$sen->execute([$ID]);

echo  json_encode(["exito"=>true]);

?>