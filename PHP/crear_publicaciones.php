<?php

require "auth.php";
requireRole(['emprendedor']);
require_once __DIR__ . '/csrf.php';
csrf_validar();

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

$categoriasPermitidas = ['Ropa', 'Hogar', 'Tecnologia', 'Carpinteria', 'Herreria', 'Higiene', 'Deportes'];

$titulo = trim(filter_var($_POST['titulo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$descripcion = trim(filter_var($_POST['descripcion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
$categoria = filter_var($_POST['categoria'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($titulo === '') {
    echo json_encode(["exito" => false, "mensaje" => "El título es obligatorio."]);
    exit;
}

if ($precio === false || $precio <= 0) {
    echo json_encode(["exito" => false, "mensaje" => "El precio debe ser un número mayor que cero."]);
    exit;
}

if (!in_array($categoria, $categoriasPermitidas, true)) {
    echo json_encode(["exito" => false, "mensaje" => "Categoría inválida."]);
    exit;
}

// Validar que llegó un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["exito" => false, "mensaje" => "No se recibió ninguna imagen."]);
    exit;
}

// Validar que el archivo sea realmente una imagen (no confiar en el nombre/extensión que manda el navegador)
const TAMANO_MAXIMO_IMAGEN = 5 * 1024 * 1024; // 5MB
if ($_FILES['foto']['size'] > TAMANO_MAXIMO_IMAGEN) {
    echo json_encode(["exito" => false, "mensaje" => "La imagen supera el tamaño máximo permitido (5MB)."]);
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