<?php
include "conexionBD.php";

session_start();
$id=filter_var($_GET["idp"], FILTER_VALIDATE_INT);
$ci=$_SESSION['Cedula'];

if ($id === false) {
    echo json_encode(["exito" => false, "mensaje" => "ID inválido."]);
    exit;
}

$sen= $conexion->prepare ("INSERT INTO carrito (ci,id)VALUES (?, ?)");
$sen->execute([$ci,$id]);


if($sen->rowCount()>0){
echo json_encode(["exito"=>true]);

}else{
     echo json_encode(["exito"=>false]);

}

 ?>