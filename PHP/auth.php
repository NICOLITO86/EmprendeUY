<?php


session_start();
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