<?php

session_start();
include "conexionBD.php";

$nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$descripcion = filter_var($_POST['descripcion'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$cedula = $_SESSION['Cedula'];

// Validar que llegó un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["exito" => false, "mensaje" => "No se recibió ninguna imagen."]);
    exit;
}

// Validar que el archivo sea realmente una imagen
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

// Insertamos primero el emprendimiento (sin la foto) para obtener el ID autogenerado
$sen = $conexion->prepare(
    "INSERT INTO emprendimiento (nombre, descripcion, cedula) VALUES (?, ?, ?)"
);
$sen->execute([$nombre, $descripcion, $cedula]);

if ($sen->rowCount() > 0) {
    $idEmprendimiento = $conexion->lastInsertId();

    $nombreArchivo = $idEmprendimiento . '.' . $extension;
    $carpetaDestino = "../Imagenes/emprendimientos/";

    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
        $update = $conexion->prepare("UPDATE emprendimiento SET Foto = ? WHERE ID = ?");
        $update->execute([$nombreArchivo, $idEmprendimiento]);

        echo json_encode(["exito" => true]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => "No se pudo guardar la imagen en el servidor."]);
    }
} else {
    echo json_encode(["exito" => false, "mensaje" => "No se pudo crear el emprendimiento."]);
}

?>
