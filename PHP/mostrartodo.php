<?php 

include "conexionBD.php";

$res= $conexion->prepare("SELECT * from usuario");
$res->execute();
$pers=$res->fetchALL(PDO::FETCH_ASSOC);

echo json_encode($pers);

?>