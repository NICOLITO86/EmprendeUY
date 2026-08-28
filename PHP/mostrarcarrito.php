<?php

session_start();
include "conexionBD.php";

if (empty($_SESSION['Cedula'])) {
    http_response_code(401);
    echo json_encode(["error" => "No hay sesion iniciada"]);
    exit;
}

$ci = $_SESSION['Cedula'];

$res = $conexion->prepare(
    "SELECT c.id_carrito, c.id, p.titulo, p.descripcion, p.precio, p.categoria FROM carrito c JOIN publicaciones p ON p.id = c.id WHERE c.ci = ? ORDER BY c.id_carrito DESC"
);
$res->execute([$ci]);
$productos = $res->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);

?>
