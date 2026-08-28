<?php
// Endpoint genérico para subir una imagen suelta a una carpeta.
// Recibe un campo "foto" por POST y devuelve el nombre de archivo generado.

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["exito" => false, "mensaje" => "No se recibió ninguna imagen."]);
    exit;
}

$infoImagen = getimagesize($_FILES['foto']['tmp_name']);
if ($infoImagen === false) {
    echo json_encode(["exito" => false, "mensaje" => "El archivo subido no es una imagen válida."]);
    exit;
}

$extensionesPermitidas = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$mime = $infoImagen['mime'];
if (!isset($extensionesPermitidas[$mime])) {
    echo json_encode(["exito" => false, "mensaje" => "Formato de imagen no permitido."]);
    exit;
}
$extension = $extensionesPermitidas[$mime];

$nombreArchivo = uniqid('img_', true) . '.' . $extension;
$carpetaDestino = "../Imagenes/subidas/";

if (!is_dir($carpetaDestino)) {
    mkdir($carpetaDestino, 0755, true);
}

if (move_uploaded_file($_FILES['foto']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
    echo json_encode(["exito" => true, "archivo" => $nombreArchivo]);
} else {
    echo json_encode(["exito" => false, "mensaje" => "No se pudo guardar la imagen en el servidor."]);
}

?>
