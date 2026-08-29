<?php

include "conexionBD.php";

$res = $conexion->query("SELECT DISTINCT categoria FROM publicaciones WHERE status = 'Activa' ORDER BY categoria ASC");
$categorias = $res->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($categorias);

?>