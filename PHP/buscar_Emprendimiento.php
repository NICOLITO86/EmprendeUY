<?php


include "conexionBD.php";

$ID = filter_var($_POST['ID'], FILTER_VALIDATE_INT);

if ($ID === false) {
    echo json_encode(["encontrado" => false, "mensaje" => "ID inválido."]);
    exit;
}

$res = $conexion->prepare("SELECT ID, Nombre, Descripcion FROM emprendimiento WHERE ID = ?");
$res->execute([$ID]);

$emprendimiento = $res->fetch(PDO::FETCH_ASSOC);

if ($emprendimiento) {
    echo json_encode($emprendimiento);
} else {
    echo json_encode([
        "encontrado" => false
    ]);
}

exit;
?>