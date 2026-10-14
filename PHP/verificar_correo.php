<?php
// GET  -> estado de la verificación pendiente (a qué correo se envió el código).
// POST -> valida el código; si es correcto marca el correo como verificado e inicia sesión.
require_once __DIR__ . '/session_config.php';
require 'conexionBD.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/codigos.php';
require_once __DIR__ . '/helpers_sesion.php';

$cedula = $_SESSION['verificacion_cedula'] ?? null;

if ($cedula === null) {
    responderJson([
        'success'  => false,
        'pendiente' => false,
        'mensaje'  => 'No hay una verificación pendiente. Iniciá sesión para continuar.',
        'redirect' => '../HTML/iniciar_sesion.html'
    ], 401);
}

$stmt = $conexion->prepare("SELECT * FROM usuario WHERE Cedula = :c AND Activo = 1 LIMIT 1");
$stmt->execute(['c' => $cedula]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    unset($_SESSION['verificacion_cedula']);
    responderJson(['success' => false, 'pendiente' => false, 'mensaje' => 'Cuenta no encontrada.',
                   'redirect' => '../HTML/iniciar_sesion.html'], 404);
}

if ((int) $usuario['CorreoVerificado'] === 1) {
    unset($_SESSION['verificacion_cedula']);
    responderJson(['success' => false, 'pendiente' => false,
                   'mensaje' => 'Tu correo ya está verificado. Iniciá sesión.',
                   'redirect' => '../HTML/iniciar_sesion.html']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    responderJson(['success' => true, 'pendiente' => true, 'correo' => enmascararCorreo($usuario['Correo'])]);
}

csrf_validar();

$codigo = trim($_POST['codigo'] ?? '');
if (!preg_match('/^\d{' . CODIGO_LONGITUD . '}$/', $codigo)) {
    responderJson(['success' => false, 'mensaje' => 'El código debe tener ' . CODIGO_LONGITUD . ' dígitos.']);
}

try {
    $resultado = validarCodigo($conexion, (int) $cedula, TIPO_VERIFICACION, $codigo);

    if (!$resultado['ok']) {
        responderJson(['success' => false, 'mensaje' => mensajeCodigoFallido($resultado),
                       'motivo' => $resultado['motivo']]);
    }

    $conexion->prepare("UPDATE usuario SET CorreoVerificado = 1 WHERE Cedula = ?")->execute([$cedula]);
    unset($_SESSION['verificacion_cedula']);

    $redirect = iniciarSesionUsuario($conexion, $usuario);
    responderJson(['success' => true, 'mensaje' => '¡Correo verificado!', 'rol' => $usuario['Rol'], 'redirect' => $redirect]);
} catch (PDOException $e) {
    error_log('Error en verificar_correo.php: ' . $e->getMessage());
    responderJson(['success' => false, 'mensaje' => 'Error interno del servidor.'], 500);
}
