<?php
// POST (multipart) -> guarda la foto de perfil en ../Imagenes/perfiles/ y la asocia al usuario.
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json; charset=utf-8');

requireRole(['cliente', 'emprendedor', 'administrador', 'usuario'], false);
csrf_validar();

const FOTO_MAX_BYTES = 2 * 1024 * 1024; // 2 MB

function fallo(string $mensaje): void
{
    echo json_encode(['exito' => false, 'mensaje' => $mensaje]);
    exit;
}

$cedula = (int) $_SESSION['Cedula'];

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    fallo('Elegí una imagen primero.');
}
$archivo = $_FILES['foto'];

if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['error'] === UPLOAD_ERR_FORM_SIZE) {
    fallo('La imagen es muy pesada (máximo 2 MB).');
}
if ($archivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
    fallo('No se pudo subir la imagen. Intentá de nuevo.');
}
if ($archivo['size'] > FOTO_MAX_BYTES) {
    fallo('La imagen es muy pesada (máximo 2 MB).');
}

// El tipo se detecta por el contenido real del archivo, no por el nombre ni por lo que diga el navegador.
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
$extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!isset($extensiones[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
    fallo('Formato no permitido. Usá JPG, PNG o WEBP.');
}

$carpeta = __DIR__ . '/../Imagenes/perfiles';
if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
    error_log('subir_foto_perfil.php: no se pudo crear ' . $carpeta);
    fallo('No se pudo guardar la imagen en el servidor.');
}

// Nombre nuevo en cada subida (cédula + marca de tiempo) para que el navegador no muestre la foto vieja en caché.
$nombreArchivo = $cedula . '_' . time() . '.' . $extensiones[$mime];
if (!move_uploaded_file($archivo['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
    fallo('No se pudo guardar la imagen en el servidor.');
}

try {
    $stmt = $conexion->prepare("SELECT Foto_Perfil FROM usuario WHERE Cedula = ?");
    $stmt->execute([$cedula]);
    $anterior = $stmt->fetchColumn();

    $conexion->prepare("UPDATE usuario SET Foto_Perfil = ? WHERE Cedula = ?")->execute([$nombreArchivo, $cedula]);

    if ($anterior) {
        $rutaAnterior = $carpeta . '/' . basename($anterior);
        if (is_file($rutaAnterior)) @unlink($rutaAnterior);
    }
} catch (PDOException $e) {
    @unlink($carpeta . '/' . $nombreArchivo);
    error_log('Error en subir_foto_perfil.php: ' . $e->getMessage());
    fallo('No se pudo guardar la foto. Intentá de nuevo.');
}

echo json_encode([
    'exito'   => true,
    'mensaje' => 'Foto actualizada.',
    'foto'    => '../Imagenes/perfiles/' . rawurlencode($nombreArchivo),
]);
