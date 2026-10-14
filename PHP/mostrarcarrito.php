<?php

require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
include "conexionBD.php";

// Solo cliente y emprendedor usan el carrito (el administrador no compra).
requireRole(['cliente', 'emprendedor'], false);

$ci = $_SESSION['Cedula'];

$res = $conexion->prepare(
    "SELECT c.id_carrito, c.id, c.cantidad, p.titulo, p.descripcion, p.precio, p.categoria FROM carrito c JOIN publicaciones p ON p.id = c.id WHERE c.ci = ? ORDER BY c.id_carrito DESC"
);
$res->execute([$ci]);
$productos = $res->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);

?>
