<?php

include "conexionBD.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit;
}

$res = $conexion->prepare("SELECT Foto FROM emprendimiento WHERE ID = ?");
$res->execute([$id]);
$emprendimiento = $res->fetch(PDO::FETCH_ASSOC);

if ($emprendimiento && $emprendimiento['Foto']) {
    $ruta = "../Imagenes/emprendimientos/" . basename($emprendimiento['Foto']);

    if (file_exists($ruta)) {
        header("Content-Type: " . mime_content_type($ruta));
        readfile($ruta);
        exit;
    }
}

http_response_code(404);

?>
