<?php
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
include "conexionBD.php";

header('Content-Type: application/json');

// Solo cliente y emprendedor usan el carrito (el administrador no compra).
requireRole(['cliente', 'emprendedor'], false);

csrf_validar();

$ci = $_SESSION['Cedula'];

// Vacía todo el carrito del usuario logueado (no un solo producto).
$consulta = $conexion->prepare("DELETE FROM carrito WHERE ci = ?");
$consulta->execute([$ci]);

echo json_encode(["exito" => true]);
?>