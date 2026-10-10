<?php
// ../PHP/sesion.php
// Le dice al JavaScript de la tienda (tienda.js) si hay un usuario con sesion iniciada.
// Es solo informativo: la validacion real de la compra esta en Agregarcarro.php.
require_once __DIR__ . '/session_config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

echo json_encode(['logueado' => !empty($_SESSION['Cedula'])]);
