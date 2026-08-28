<?php

include "conexionBD.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit;
}

$res = $conexion->prepare("SELECT foto FROM publicaciones WHERE id = ?");
$res->execute([$id]);
$publicacion = $res->fetch(PDO::FETCH_ASSOC);

if ($publicacion && $publicacion['foto']) {
    // basename() evita que alguien mande algo tipo "../../conexionBD.php" en el nombre
    $ruta = "../Imagenes/publicaciones/" . basename($publicacion['foto']);

    if (file_exists($ruta)) {
        header("Content-Type: " . mime_content_type($ruta));
        readfile($ruta);
        exit;
    }
}

http_response_code(404);

?>
