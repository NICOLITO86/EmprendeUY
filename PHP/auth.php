<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function requireLogin(): void {
    if (!isset($_SESSION['Cedula'])) {
        http_response_code(401);
        if (esPeticionAjax()) {
            echo json_encode(["exito" => false, "mensaje" => "Sesión no iniciada."]);
        } else {
            header("Location: ../HTML/iniciar_sesion.html");
        }
        exit;
    }
}


function requireRole(array $rolesPermitidos): void {
    requireLogin();

    $rolActual = $_SESSION['Rol'] ?? null;

    if (!in_array($rolActual, $rolesPermitidos, true)) {
        http_response_code(403);
        if (esPeticionAjax()) {
            echo json_encode(["exito" => false, "mensaje" => "No tenés permiso para realizar esta acción."]);
        } else {
            header("Location: ../HTML/acceso-denegado.html");
        }
        exit;
    }
}


function esPeticionAjax(): bool {
    return (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (
        !empty($_SERVER['CONTENT_TYPE']) &&
        stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
    ) || (
        !empty($_SERVER['HTTP_ACCEPT']) &&
        stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    ) || php_sapi_name() !== 'cli' && !empty($_POST);
} 