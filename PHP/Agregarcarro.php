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

// CARR-02: el producto tiene que existir y estar activo antes de meterlo al carrito.
$consulta = $conexion->prepare("SELECT id FROM publicaciones WHERE id = ? AND status = 'Activa'");
$consulta->execute([$id]);
if (!$consulta->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(["exito" => false, "mensaje" => "El producto no existe o ya no está disponible."]);
    exit;
}

// CARR-03: si ya está en el carrito se suma la cantidad en vez de duplicar la fila.
$sen = $conexion->prepare(
    "INSERT INTO carrito (ci, id, cantidad) VALUES (?, ?, 1)
     ON DUPLICATE KEY UPDATE cantidad = cantidad + 1"
);
$sen->execute([$ci, $id]);

echo json_encode(["exito" => true]);

 ?>