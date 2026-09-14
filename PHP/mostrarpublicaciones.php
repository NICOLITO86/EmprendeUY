<?php

include "conexionBD.php";

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

$sql = "SELECT id, titulo, descripcion, precio, categoria FROM publicaciones WHERE status = 'Activa'";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (titulo LIKE :busqueda1 OR descripcion LIKE :busqueda2)";
    $params[':busqueda1'] = '%' . $busqueda . '%';
    $params[':busqueda2'] = '%' . $busqueda . '%';
}

if ($categoria !== '' && $categoria !== 'todas') {
    $sql .= " AND categoria = :categoria";
    $params[':categoria'] = $categoria;
}

$sql .= " ORDER BY fecha_publicacion DESC";

$res = $conexion->prepare($sql);
$res->execute($params);
$publicaciones = $res->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($publicaciones);

?>