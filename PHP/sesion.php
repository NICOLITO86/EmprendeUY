<?php
// ../PHP/sesion.php
// Le dice al JavaScript de la tienda (tienda.js) si hay un usuario con sesion iniciada.
// Es solo informativo: la validacion real de la compra esta en Agregarcarro.php.
require_once __DIR__ . '/session_config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/conexionBD.php';

$logueado = !empty($_SESSION['Cedula']);
if ($logueado) {
    $stmt = $conexion->prepare("SELECT Activo FROM usuario WHERE Cedula = ? LIMIT 1");
    $stmt->execute([$_SESSION['Cedula']]);
    $logueado = (int) $stmt->fetchColumn() === 1;
}

// 'rol' lo usa la tienda para sacar de ahí al administrador.
echo json_encode([
    'logueado' => $logueado,
    'rol'      => $logueado ? ($_SESSION['Rol'] ?? null) : null,
]);
