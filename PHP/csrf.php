<?php
// Protección CSRF simple basada en un token atado a la sesión.
require_once __DIR__ . '/session_config.php';

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Corta la ejecución con un 403 si el token enviado no coincide con el de la sesión.
function csrf_validar(): void
{
    $enviado = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (empty($_SESSION['csrf_token']) || $enviado === '' || !hash_equals($_SESSION['csrf_token'], $enviado)) {
        http_response_code(403);
        header('Content-Type: application/json');
        exit(json_encode([
            'success' => false,
            'exito'   => false,
            'mensaje' => 'Token de seguridad inválido o expirado. Recargá la página e intentá de nuevo.',
        ]));
    }
}
