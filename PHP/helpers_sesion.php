<?php
// Utilidades compartidas por login, registro y verificación de correo.
// Requiere que quien lo incluya ya haya cargado session_config.php y conexionBD.php.

/** Responde JSON y corta. */
function responderJson(array $datos, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos);
    exit;
}

/**
 * Responde al navegador de inmediato y sigue ejecutando (para enviar el correo después).
 * Así el tiempo de respuesta no depende del servidor SMTP, y en recuperación de contraseña
 * no se puede distinguir por tiempo si un correo existe o no.
 * Si el servidor no soporta cerrar la conexión antes, simplemente responde cuando termina.
 */
function responderYContinuar(array $datos): void
{
    ignore_user_abort(true);
    $json = json_encode($datos);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($json));
    header('Connection: close');
    echo $json;
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
}

/**
 * Inicia sesión del usuario (regenera el id de sesión) y devuelve a qué página mandarlo según su rol.
 * Es la misma lógica que tenía login.php, centralizada para reutilizarla tras verificar el correo.
 * Puede lanzar PDOException (el emprendedor consulta si ya tiene emprendimiento).
 */
function iniciarSesionUsuario(PDO $conexion, array $usuario): string
{
    session_regenerate_id(true);

    $_SESSION['Cedula'] = $usuario['Cedula'];
    $_SESSION['Nombre'] = $usuario['Nombre'];
    $_SESSION['Rol']    = $usuario['Rol'];

    switch ($usuario['Rol']) {
        case 'administrador':
            return '../HTML/paneladm.html';

        case 'emprendedor':
            $stmt = $conexion->prepare("SELECT 1 FROM emprendimiento WHERE cedula = :cedula LIMIT 1");
            $stmt->execute(['cedula' => $usuario['Cedula']]);
            return $stmt->fetch()
                ? '../HTML/mis_publicaciones.html'
                : '../HTML/crearemprendimiento.html';

        case 'cliente':
            return '../HTML/tienda.html';

        default:
            return '../HTML/EmprendeUY.html';
    }
}

/** Reglas de contraseña del sistema (mismas que registro.php). Devuelve null si es válida o el mensaje de error. */
function errorPoliticaContrasena(string $pass): ?string
{
    if (strlen($pass) < 8) {
        return 'La contraseña debe tener al menos 8 caracteres.';
    }
    return null;
}
