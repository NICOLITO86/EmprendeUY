<?php
// Paso 2 de recuperación de contraseña: correo + código + contraseña nueva.
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
session_write_close();

$correo    = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
$codigo    = trim($_POST['codigo'] ?? '');
$nueva     = $_POST['nueva'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';

if ($correo === false) {
    responderJson(['success' => false, 'mensaje' => 'Ingresá un correo válido.']);
}
if (!preg_match('/^\d{' . CODIGO_LONGITUD . '}$/', $codigo)) {
    responderJson(['success' => false, 'mensaje' => 'El código debe tener ' . CODIGO_LONGITUD . ' dígitos.']);
}
// La política de contraseña se valida ANTES de gastar un intento del código.
if ($err = errorPoliticaContrasena($nueva)) {
    responderJson(['success' => false, 'mensaje' => $err]);
}
if ($nueva !== $confirmar) {
    responderJson(['success' => false, 'mensaje' => 'Las contraseñas no coinciden.']);
}

try {
    $stmt = $conexion->prepare("SELECT Cedula, Nombre, Correo FROM usuario WHERE Correo = :c AND Activo = 1 LIMIT 1");
    $stmt->execute(['c' => $correo]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    $mensajeGenerico = 'Código incorrecto o vencido. Revisá el correo o pedí uno nuevo.';

    if (!$u) {
        responderJson(['success' => false, 'mensaje' => $mensajeGenerico]);
    }

    $resultado = validarCodigo($conexion, (int) $u['Cedula'], TIPO_RECUPERACION, $codigo);
    if (!$resultado['ok']) {
        $msg = $resultado['motivo'] === 'expirado' ? $mensajeGenerico : mensajeCodigoFallido($resultado);
        responderJson(['success' => false, 'mensaje' => $msg, 'motivo' => $resultado['motivo']]);
    }

    $hash = password_hash($nueva, PASSWORD_DEFAULT);

    $conexion->beginTransaction();
    // Recibir el código prueba que controla el correo: también queda verificado.
    $conexion->prepare("UPDATE usuario SET `Contraseña` = ?, CorreoVerificado = 1 WHERE Cedula = ?")
             ->execute([$hash, $u['Cedula']]);
    // Si estaba bloqueado por intentos fallidos, se levanta el bloqueo.
    $conexion->prepare("DELETE FROM bloqueo_temporal WHERE Cedula = ?")->execute([$u['Cedula']]);
    // Cualquier otro código pendiente de esta cuenta deja de servir.
    $conexion->prepare("UPDATE codigo_verificacion SET Usado = 1 WHERE Cedula = ? AND Usado = 0")->execute([$u['Cedula']]);
    $conexion->commit();

    responderYContinuar([
        'success'  => true,
        'mensaje'  => 'Contraseña actualizada. Ya podés iniciar sesión.',
        'redirect' => '../HTML/iniciar_sesion.html'
    ]);

    enviarAvisoCambioClave($u['Correo'], $u['Nombre']);
} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log('Error en recuperar_confirmar.php: ' . $e->getMessage());
    if (!headers_sent()) {
        responderJson(['success' => false, 'mensaje' => 'Error interno del servidor.'], 500);
    }
}
