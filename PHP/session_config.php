<?php
// Arranque centralizado y seguro de la sesión.
// Se debe usar `require_once __DIR__ . '/session_config.php';` en vez de `session_start();`
// para que todos los endpoints compartan la misma configuración de cookies.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // el JS del navegador no puede leer la cookie de sesión
        'samesite' => 'Lax',  // mitiga CSRF básico
        // 'secure' => true,  // descomentar cuando el sitio se sirva por HTTPS
    ]);
    session_start();
}
