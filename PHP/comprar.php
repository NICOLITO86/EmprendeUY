<?php
// Finaliza la compra del carrito del usuario logueado (cliente o emprendedor).
// - El monto lo calcula el servidor con los precios de la base, nunca el navegador.
// - Guarda un pedido con sus productos (precio y título quedan "congelados" en pedido_item)
//   y vacía el carrito, todo en una sola transacción.
// - El pago es simulado: los datos de la tarjeta no viajan ni se guardan (no hay pasarela real).
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';      // define también $conexion
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['exito' => false, 'mensaje' => 'Método no permitido.'], 405);
}

requireRole(['cliente', 'emprendedor'], false);
csrf_validar();

$ci = (int) $_SESSION['Cedula'];

try {
    $conexion->beginTransaction();

    // Solo se cobran publicaciones que siguen activas.
    $stmt = $conexion->prepare(
        "SELECT c.id, c.cantidad, p.titulo, p.precio
         FROM carrito c JOIN publicaciones p ON p.id = c.id
         WHERE c.ci = ? AND p.status = 'Activa' AND c.cantidad > 0"
    );
    $stmt->execute([$ci]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        $conexion->rollBack();
        responderJson(['exito' => false, 'mensaje' => 'Tu carrito está vacío o los productos ya no están disponibles.'], 409);
    }

    $total = 0;
    foreach ($items as $it) {
        $total += (int) $it['precio'] * (int) $it['cantidad'];
    }

    $conexion->prepare("INSERT INTO pedido (Cedula, Total) VALUES (?, ?)")->execute([$ci, $total]);
    $idPedido = (int) $conexion->lastInsertId();

    $ins = $conexion->prepare(
        "INSERT INTO pedido_item (id_pedido, id_publicacion, titulo, precio, cantidad) VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($items as $it) {
        $ins->execute([$idPedido, $it['id'], $it['titulo'], $it['precio'], $it['cantidad']]);
    }

    $conexion->prepare("DELETE FROM carrito WHERE ci = ?")->execute([$ci]);

    $conexion->commit();

    responderJson(['exito' => true, 'id_pedido' => $idPedido, 'total' => $total]);
} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log('Error en comprar.php: ' . $e->getMessage());
    responderJson(['exito' => false, 'mensaje' => 'No se pudo completar la compra. Intentá de nuevo.'], 500);
}
