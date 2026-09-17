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

$ci = $_SESSION['Cedula'];

// Vacía todo el carrito del usuario logueado (no un solo producto).
$consulta = $conexion->prepare("DELETE FROM carrito WHERE ci = ?");
$consulta->execute([$ci]);

echo json_encode(["exito" => true]);
?>