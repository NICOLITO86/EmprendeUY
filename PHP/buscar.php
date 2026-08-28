<?php 

include "conexionBD.php";
$cedula = filter_var($_POST['cedula'], FILTER_VALIDATE_INT);

if ($cedula === false) {
    echo json_encode(["encontrado" => false, "mensaje" => "Cédula inválida."]);
    exit;
}

$res= $conexion->prepare("SELECT * from usuario WHERE cedula=?");
$res->execute([$cedula]);
$pers=$res->fetch(PDO::FETCH_ASSOC);

echo json_encode($pers);

?>