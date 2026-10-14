<?php
// Paso 1 de recuperación de contraseña: el usuario ingresa su correo y se le envía un código.
// La respuesta es SIEMPRE la misma exista o no el correo (no revela qué correos están registrados).
require_once __DIR__ . '/session_config.php';
require 'conexionBD.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/codigos.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/helpers_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['success' => false, 'mensaje' => 'Método no permitido.'], 405);
}
csrf_validar();
session_write_close(); // liberamos la sesión: el resto puede tardar (SMTP)

$correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
if ($correo === false) {
    responderJson(['success' => false, 'mensaje' => 'Ingresá un correo válido.']);
}

$respuestaGenerica = [
    'success' => true,
    'espera'  => CODIGO_REENVIO_SEG,
    'mensaje' => 'Si el correo está registrado, te enviamos un código de ' . CODIGO_LONGITUD
               . ' dígitos que vence en ' . CODIGO_VIGENCIA_MIN . ' minutos.'
];

try {
    $stmt = $conexion->prepare("SELECT Cedula, Nombre, Correo FROM usuario WHERE Correo = :c AND Activo = 1 LIMIT 1");
    $stmt->execute(['c' => $correo]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    responderYContinuar($respuestaGenerica);

    if (!$u) {
        exit;
    }

    $permiso = puedeEmitirCodigo($conexion, (int) $u['Cedula'], TIPO_RECUPERACION);
    if (!$permiso['ok']) {
        exit; // límite de envíos: no se manda, pero la respuesta ya fue la genérica
    }

    $codigo = emitirCodigo($conexion, (int) $u['Cedula'], TIPO_RECUPERACION, $_SERVER['REMOTE_ADDR'] ?? 'desconocida');
    enviarCorreoRecuperacion($u['Correo'], $u['Nombre'], $codigo, CODIGO_VIGENCIA_MIN);
} catch (Throwable $e) {
    error_log('Error en recuperar_solicitar.php: ' . $e->getMessage());
    if (!headers_sent()) {
        responderJson($respuestaGenerica);
    }
}
