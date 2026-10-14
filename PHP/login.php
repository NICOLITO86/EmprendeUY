<?php

require_once __DIR__ . '/session_config.php';
require 'conexionBD.php'; 
require_once __DIR__ . '/codigos.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/helpers_sesion.php';
header('Content-Type: application/json');


const MAX_INTENTOS    = 5;   
const MINUTOS_VENTANA = 15;  
const MINUTOS_BLOQUEO = 10;  

$cedula = trim($_POST['cedula'] ?? '');
$pass   = $_POST['contraseña'] ?? '';
$ip     = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

if ($cedula === '' || $pass === '') {
    echo json_encode(['success' => false, 'mensaje' => 'Cédula y contraseña son obligatorios.']);
    exit;
}

$stmt = $conexion->prepare(
    "SELECT BloqueadoHasta FROM bloqueo_temporal
     WHERE Cedula = :cedula AND BloqueadoHasta > NOW()
     ORDER BY BloqueadoHasta DESC LIMIT 1"
);
$stmt->execute(['cedula' => $cedula]);
$bloqueo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($bloqueo) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Cuenta bloqueada temporalmente por múltiples intentos fallidos. Reintente después de ' . $bloqueo['BloqueadoHasta'] . '.'
    ]);
    exit;
}


$stmt = $conexion->prepare("SELECT * FROM usuario WHERE Cedula = :cedula AND Activo = 1 LIMIT 1");
$stmt->execute(['cedula' => $cedula]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$loginExitoso = $usuario && password_verify($pass, $usuario['Contraseña']);


$stmt = $conexion->prepare(
    "INSERT INTO intento_login (Cedula, IP, Exitoso) VALUES (:cedula, :ip, :exitoso)"
);
$stmt->execute([
    'cedula'  => $cedula,
    'ip'      => $ip,
    'exitoso' => $loginExitoso ? 1 : 0
]);


if (!$loginExitoso) {
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM intento_login
         WHERE Cedula = :cedula AND Exitoso = 0
         AND FechaIntento > (NOW() - INTERVAL :minutos MINUTE)"
    );
    $stmt->bindValue('cedula', $cedula);
    $stmt->bindValue('minutos', MINUTOS_VENTANA, PDO::PARAM_INT);
    $stmt->execute();
    $fallos = (int) $stmt->fetchColumn();

    if ($fallos >= MAX_INTENTOS) {
        $stmt = $conexion->prepare(
            "INSERT INTO bloqueo_temporal (Cedula, IP, BloqueadoHasta)
             VALUES (:cedula, :ip, NOW() + INTERVAL :minutos MINUTE)"
        );
        $stmt->bindValue('cedula', $cedula);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('minutos', MINUTOS_BLOQUEO, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'success' => false,
            'mensaje' => 'Demasiados intentos fallidos. Cuenta bloqueada por ' . MINUTOS_BLOQUEO . ' minutos.'
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'mensaje' => 'Cédula o contraseña incorrectos.']);
    exit;
}


// Contraseña correcta pero correo sin verificar: no se inicia sesión.
// Se deja pendiente la verificación y se manda un código nuevo (si el límite de envíos lo permite).
if ((int) $usuario['CorreoVerificado'] !== 1) {
    session_regenerate_id(true);
    $_SESSION['verificacion_cedula'] = (int) $usuario['Cedula'];

    try {
        $permiso = puedeEmitirCodigo($conexion, (int) $usuario['Cedula'], TIPO_VERIFICACION);
        if ($permiso['ok']) {
            $codigo = emitirCodigo($conexion, (int) $usuario['Cedula'], TIPO_VERIFICACION, $ip);
            enviarCorreoVerificacion($usuario['Correo'], $usuario['Nombre'], $codigo, CODIGO_VIGENCIA_MIN);
        }
    } catch (Throwable $e) {
        error_log('Error enviando código de verificación (login.php): ' . $e->getMessage());
    }

    echo json_encode([
        'success'              => false,
        'requiere_verificacion' => true,
        'mensaje'              => 'Tenés que verificar tu correo antes de iniciar sesión. Te enviamos un código.',
        'redirect'             => '../HTML/verificar_correo.html'
    ]);
    exit;
}

try {
    $redirect = iniciarSesionUsuario($conexion, $usuario);
} catch (PDOException $e) {
    error_log('Error verificando emprendimiento: ' . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'Error interno del servidor.']);
    exit;
}

echo json_encode([
    'success'  => true,
    'rol'      => $usuario['Rol'],
    'redirect' => $redirect
]);
