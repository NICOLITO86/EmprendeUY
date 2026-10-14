<?php
// Reenvía el código de verificación de correo a la cuenta pendiente (la que está en sesión).
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

$cedula = $_SESSION['verificacion_cedula'] ?? null;
if ($cedula === null) {
    responderJson(['success' => false, 'mensaje' => 'No hay una verificación pendiente. Iniciá sesión para continuar.',
                   'redirect' => '../HTML/iniciar_sesion.html'], 401);
}

try {
    $stmt = $conexion->prepare("SELECT Nombre, Correo, CorreoVerificado FROM usuario WHERE Cedula = :c AND Activo = 1 LIMIT 1");
    $stmt->execute(['c' => $cedula]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u || (int) $u['CorreoVerificado'] === 1) {
        unset($_SESSION['verificacion_cedula']);
        responderJson(['success' => false, 'mensaje' => 'No hay nada para verificar. Iniciá sesión.',
                       'redirect' => '../HTML/iniciar_sesion.html']);
    }

    $permiso = puedeEmitirCodigo($conexion, (int) $cedula, TIPO_VERIFICACION);
    if (!$permiso['ok']) {
        responderJson(['success' => false, 'mensaje' => $permiso['mensaje'], 'espera' => $permiso['espera']], 429);
    }

    $codigo = emitirCodigo($conexion, (int) $cedula, TIPO_VERIFICACION, $_SERVER['REMOTE_ADDR'] ?? 'desconocida');

    if (!enviarCorreoVerificacion($u['Correo'], $u['Nombre'], $codigo, CODIGO_VIGENCIA_MIN)) {
        $resp = ['success' => false, 'mensaje' => 'No pudimos enviar el correo. Intentá de nuevo en unos minutos.'];
        if (esPeticionLocal()) {
            // Solo en localhost: muestra el motivo técnico para poder arreglar la configuración.
            $resp['detalle'] = ultimoErrorCorreo();
        }
        responderJson($resp, 502);
    }

    responderJson(['success' => true, 'mensaje' => 'Te enviamos un código nuevo.',
                   'correo' => enmascararCorreo($u['Correo']), 'espera' => CODIGO_REENVIO_SEG]);
} catch (PDOException $e) {
    error_log('Error en reenviar_codigo.php: ' . $e->getMessage());
    responderJson(['success' => false, 'mensaje' => 'Error interno del servidor.'], 500);
}
