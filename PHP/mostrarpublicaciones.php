<?php

include "conexionBD.php";

$res = $conexion->prepare("SELECT id, titulo, descripcion, precio, categoria FROM publicaciones WHERE status = 'Activa' ORDER BY fecha_publicacion DESC");
$res->execute();
$publicaciones = $res->fetchALL(PDO::FETCH_ASSOC);

echo json_encode($publicaciones);

?>
