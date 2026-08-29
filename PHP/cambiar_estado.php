<?php

session_start();
include "conexionBD.php";

$cedula = $_SESSION['Cedula'] ?? null;

if (!$cedula) {
    echo json_encode(["exito" => false, "mensaje" => "Sesión no iniciada."]);
    exit;
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$nuevoStatus = $_POST['status'] ?? '';


$estadosPermitidos = ['Activa', 'Pausada'];

if ($id === false || !in_array($nuevoStatus, $estadosPermitidos, true)) {
    echo json_encode(["exito" => false, "mensaje" => "Datos inválidos."]);
    exit;
}


$consulta = $conexion->prepare(
    "SELECT p.status FROM publicaciones p JOIN emprendimiento e ON e.ID = p.Id_emprendimiento WHERE p.id = ? AND e.cedula = ?"
);
$consulta->execute([$id, $cedula]);
$publicacion = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$publicacion) {
    echo json_encode(["exito" => false, "mensaje" => "La publicación no existe o no te pertenece."]);
    exit;
}

if ($publicacion['status'] === 'Bloqueada') {
    echo json_encode(["exito" => false, "mensaje" => "Esta publicación fue bloqueada por un administrador y no podés reactivarla."]);
    exit;
}

$update = $conexion->prepare("UPDATE publicaciones SET status = ? WHERE id = ?");
$update->execute([$nuevoStatus, $id]);

echo json_encode(["exito" => true]);

?>