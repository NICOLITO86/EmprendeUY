<?php
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/csrf.php';
include "conexionBD.php";

header('Content-Type: application/json');

if (empty($_SESSION['Cedula'])) {
    http_response_code(401);
    echo json_encode(["exito" => false, "mensaje" => "Debe iniciar sesión."]);
    exit;
}

csrf_validar();

$id = filter_var($_POST["idp"] ?? null, FILTER_VALIDATE_INT);
$ci = $_SESSION['Cedula'];

if ($id === false) {
    echo json_encode(["exito" => false, "mensaje" => "ID inválido."]);
    exit;
}

// Solo borra la fila si pertenece al usuario logueado (ci) y al producto indicado (id).
$sen = $conexion->prepare("DELETE FROM carrito WHERE ci = ? AND id = ?");
$sen->execute([$ci, $id]);

if ($sen->rowCount() === 0) {
    echo json_encode(["exito" => false, "mensaje" => "El producto no estaba en el carrito."]);
    exit;
}

echo json_encode(["exito" => true]);

 ?>
