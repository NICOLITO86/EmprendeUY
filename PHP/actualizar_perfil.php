<?php
// POST -> guarda los datos personales del usuario con sesión iniciada.
// La cédula nunca se modifica y siempre sale de la sesión (no del formulario).
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/codigos.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/helpers_sesion.php';

header('Content-Type: application/json; charset=utf-8');
include "conexionBD.php";

requireRole(['cliente', 'emprendedor', 'administrador', 'usuario'], false);
csrf_validar();

const PERFIL_EDAD_MINIMA = 16; // misma regla que registro.php

function fallo(string $mensaje): void
{
    echo json_encode(['exito' => false, 'mensaje' => $mensaje]);
    exit;
}

$cedula = (int) $_SESSION['Cedula'];

$nombre   = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$correo   = trim($_POST['correo'] ?? '');
$fechaStr = trim($_POST['Fecha_Nacimiento'] ?? '');
$generoIn = strtolower(trim($_POST['genero'] ?? ''));
$telefono = trim($_POST['Num_Telefono'] ?? '');
$sobreMi  = trim($_POST['sobre_mi'] ?? '');

if ($nombre === '' || mb_strlen($nombre) > 60)     fallo('El nombre es obligatorio (máximo 60 caracteres).');
if ($apellido === '' || mb_strlen($apellido) > 60) fallo('El apellido es obligatorio (máximo 60 caracteres).');

// 50 es el largo máximo de Correo en las tablas cliente/emprendedor/administrador.
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 50) {
    fallo('Ingresá un correo electrónico válido (máximo 50 caracteres).');
}

$fecha = DateTime::createFromFormat('!Y-m-d', $fechaStr);
$errFecha = DateTime::getLastErrors();
if (!$fecha || ($errFecha && ($errFecha['warning_count'] > 0 || $errFecha['error_count'] > 0))) {
    fallo('La fecha de nacimiento no es válida.');
}
$hoy = new DateTime('today');
if ($fecha > $hoy) fallo('La fecha de nacimiento no puede ser futura.');
$edad = $hoy->diff($fecha)->y;
if ($edad < PERFIL_EDAD_MINIMA) fallo('Debés tener al menos ' . PERFIL_EDAD_MINIMA . ' años.');

$generos = ['masculino' => 'Masculino', 'femenino' => 'Femenino', 'otro' => 'Otro'];
if (!isset($generos[$generoIn])) fallo('Elegí un género válido.');
$genero = $generos[$generoIn];

if (!preg_match('/^\d{9}$/', $telefono)) fallo('El teléfono debe tener exactamente 9 dígitos.');

if (mb_strlen($sobreMi) > 300) fallo('"Sobre mí" admite hasta 300 caracteres.');

// Cada rol tiene su propia tabla con copia de nombre, apellido, correo y género.
$tablasPorRol = ['cliente' => 'cliente', 'emprendedor' => 'emprendedor', 'administrador' => 'administrador'];

try {
    $stmt = $conexion->prepare("SELECT Correo, Rol FROM usuario WHERE Cedula = ? AND Activo = 1 LIMIT 1");
    $stmt->execute([$cedula]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$actual) fallo('No se encontró tu cuenta.');

    $correoCambio = strcasecmp($actual['Correo'], $correo) !== 0;

    if ($correoCambio) {
        $stmt = $conexion->prepare("SELECT 1 FROM usuario WHERE Correo = ? AND Cedula <> ? LIMIT 1");
        $stmt->execute([$correo, $cedula]);
        if ($stmt->fetch()) fallo('Ese correo ya está registrado en otra cuenta.');
    }

    $conexion->beginTransaction();

    // Si cambió el correo, queda sin verificar hasta que ingrese el código que se le envía.
    $stmt = $conexion->prepare(
        "UPDATE usuario
         SET Nombre = ?, Apellido = ?, Correo = ?, Fecha_Nacimiento = ?, Edad = ?, Genero = ?,
             Num_Telefono = ?, Sobre_Mi = ?, CorreoVerificado = IF(?, 0, CorreoVerificado)
         WHERE Cedula = ?"
    );
    $stmt->execute([
        $nombre, $apellido, $correo, $fecha->format('Y-m-d'), $edad, $genero,
        $telefono, $sobreMi === '' ? null : $sobreMi, $correoCambio ? 1 : 0, $cedula,
    ]);

    if (isset($tablasPorRol[$actual['Rol']])) {
        $tabla = $tablasPorRol[$actual['Rol']]; // viene de una lista fija, no del usuario
        $stmt = $conexion->prepare("UPDATE `$tabla` SET Nombre = ?, Apellido = ?, Correo = ?, Genero = ? WHERE Cedula = ?");
        $stmt->execute([$nombre, $apellido, $correo, $genero, $cedula]);
    }

    $conexion->commit();
} catch (PDOException $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    error_log('Error en actualizar_perfil.php: ' . $e->getMessage());
    fallo('No se pudieron guardar los cambios. Intentá de nuevo.');
}

if (!$correoCambio) {
    $_SESSION['Nombre'] = $nombre;
    echo json_encode(['exito' => true, 'mensaje' => 'Datos actualizados.', 'nombre' => $nombre, 'apellido' => $apellido]);
    exit;
}

// Correo nuevo: se cierra la sesión y se pasa por la verificación de correo (igual que en el registro).
$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
session_regenerate_id(true);
unset($_SESSION['Cedula'], $_SESSION['Nombre'], $_SESSION['Rol']);
$_SESSION['verificacion_cedula'] = $cedula;

try {
    $permiso = puedeEmitirCodigo($conexion, $cedula, TIPO_VERIFICACION);
    if ($permiso['ok']) {
        $codigo = emitirCodigo($conexion, $cedula, TIPO_VERIFICACION, $ip);
        enviarCorreoVerificacion($correo, $nombre, $codigo, CODIGO_VIGENCIA_MIN);
    }
} catch (Throwable $e) {
    error_log('Error enviando código (actualizar_perfil.php): ' . $e->getMessage());
}

echo json_encode([
    'exito'        => true,
    'verificacion' => true,
    'mensaje'      => 'Cambiaste tu correo. Te enviamos un código a ' . enmascararCorreo($correo) . ' para verificarlo.',
    'redirect'     => '../HTML/verificar_correo.html',
]);
