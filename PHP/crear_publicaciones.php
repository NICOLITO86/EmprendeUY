<?php

require "auth.php";
requireRole(['emprendedor']);

include "conexionBD.php";

$cedula = $_SESSION['Cedula'];

$consulta = $conexion->prepare(
    "SELECT ID
     FROM emprendimiento
     WHERE cedula = ?"
);
$consulta->execute([$cedula]);
$emprendimiento = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$emprendimiento) {
    echo json_encode(["exito" => false, "mensaje" => "No se encontró un emprendimiento para este usuario."]);
    exit;
}

$idEmprendimiento = $emprendimiento['ID'];

$titulo = filter_var($_POST['titulo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$descripcion = filter_var($_POST['descripcion'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
$categoria = filter_var($_POST['categoria'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($precio === false) {
    echo json_encode(["exito" => false, "mensaje" => "Precio inválido."]);
    exit;
}

// Validar que llegó un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["exito" => false, "mensaje" => "No se recibió ninguna imagen."]);
    exit;
}

// Validar que el archivo sea realmente una imagen (no confiar en el nombre/extensión que manda el navegador)
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

// Insertamos primero la publicación (sin la foto) para obtener el ID autogenerado
$sen = $conexion->prepare(
    "INSERT INTO publicaciones (titulo, descripcion, precio, categoria, Id_emprendimiento)
     VALUES (?, ?, ?, ?, ?)"
);
$sen->execute([$titulo, $descripcion, $precio, $categoria, $idEmprendimiento]);

if ($sen->rowCount() > 0) {
    $Uid = $conexion->lastInsertId();

    $nombreArchivo = $Uid . '.' . $extension;
    $carpetaDestino = "../Imagenes/publicaciones/";

    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
        // Guardamos solo el nombre del archivo en la base de datos, no la imagen en sí
        $update = $conexion->prepare("UPDATE publicaciones SET foto = ? WHERE id = ?");
        $update->execute([$nombreArchivo, $Uid]);

        echo json_encode(["exito" => true]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => "No se pudo guardar la imagen en el servidor."]);
    }
} else {
    echo json_encode(["exito" => false]);
}

?>