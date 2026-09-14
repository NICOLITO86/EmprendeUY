<?php


require_once __DIR__ . '/session_config.php';
require 'conexionBD.php'; 
function requireRole(array $rolesPermitidos): void
{
    global $conexion;

    $cedula    = $_SESSION['Cedula'] ?? null;
    $rolActual = $_SESSION['Rol'] ?? null;
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $recurso   = $_SERVER['SCRIPT_NAME'] ?? 'desconocido';

    if (isset($_POST['accion'])) {
        $recurso .= ':' . $_POST['accion'];
    } elseif (isset($_GET['accion'])) {
        $recurso .= ':' . $_GET['accion'];
    }

    if ($cedula === null) {
        http_response_code(401);
        header('Content-Type: application/json');
        exit(json_encode(['success' => false, 'mensaje' => 'Debe iniciar sesión.']));
    }

    $permitido = in_array($rolActual, $rolesPermitidos, true);

    $stmt = $conexion->prepare(
        "INSERT INTO log_acceso_admin (Cedula, Recurso, Resultado, IP)
         VALUES (:cedula, :recurso, :resultado, :ip)"
    );
    $stmt->execute([
        'cedula'    => $cedula,
        'recurso'   => $recurso,
        'resultado' => $permitido ? 'permitido' : 'denegado',
        'ip'        => $ip
    ]);

    if (!$permitido) {
        http_response_code(403);
        header('Content-Type: application/json');
        exit(json_encode(['success' => false, 'mensaje' => 'Acceso denegado. No tiene permisos suficientes.']));
    }
}

function estaLogueado(): bool
{
    return isset($_SESSION['Cedula']);
}

/**
 * Protege una página HTML/PHP completa en el servidor (no solo con JS).
 * A diferencia de requireRole(), si no hay permiso redirige a una página
 * de error en vez de devolver JSON, porque esto se usa al principio de
 * una página que el navegador carga directamente (GET normal).
 */
function requirePageRole(array $rolesPermitidos): void
{
    $rolActual = $_SESSION['Rol'] ?? null;

    if ($rolActual === null || !in_array($rolActual, $rolesPermitidos, true)) {
        header('Location: ../HTML/acceso-denegado.html');
        exit;
    }
}

// Si se llama directamente a auth.php (fetch desde el JS, no via require),
// devuelve el estado de sesión en JSON.
if (basename($_SERVER['SCRIPT_NAME']) === 'auth.php') {
    header("Content-Type: application/json");

    if (!isset($_SESSION['Cedula'])) {
        http_response_code(401);
        echo json_encode(["autenticado" => false, "rol" => null]);
        exit;
    }

    echo json_encode([
        "autenticado" => true,
        "rol" => $_SESSION['Rol'],
        "usuario" => $_SESSION['Nombre'] ?? null
    ]);
    exit;
}