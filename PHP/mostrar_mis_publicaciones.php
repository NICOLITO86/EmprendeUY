<?php

require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
include "conexionBD.php";

requireRole(['emprendedor'], false);

$cedula = $_SESSION['Cedula'];


$consulta = $conexion->prepare("SELECT ID FROM emprendimiento WHERE cedula = ?");
$consulta->execute([$cedula]);
$emprendimiento = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$emprendimiento) {
    echo json_encode(["exito" => false, "mensaje" => "No se encontró un emprendimiento para este usuario."]);
    exit;
}

$idEmprendimiento = $emprendimiento['ID'];


$res = $conexion->prepare(
    "SELECT id, titulo, descripcion, precio, categoria, status AS estado FROM publicaciones WHERE Id_emprendimiento = ? ORDER BY fecha_publicacion DESC"
);
$res->execute([$idEmprendimiento]);
$publicaciones = $res->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["exito" => true, "publicaciones" => $publicaciones]);

?>