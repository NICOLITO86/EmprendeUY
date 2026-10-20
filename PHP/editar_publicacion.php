<?php

require "auth.php";
requireRole(['emprendedor']);
require_once __DIR__ . '/csrf.php';
csrf_validar();

require_once __DIR__ . '/conexionBD.php';

header('Content-Type: application/json; charset=utf-8');

function responder(bool $exito, string $mensaje = ''): void
{
    echo json_encode(["exito" => $exito, "mensaje" => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, "Método no permitido.");
}

$cedula = $_SESSION['Cedula'];

$categoriasPermitidas = ['Indumentaria', 'Higiene', 'Higiene personal', 'Hogar', 'Higiene para el hogar', 'Cocina', 'Alimentos', 'Tecnologia', 'Deportes', 'Botánica', 'Carpinteria', 'Herreria'];

// Mismo tratamiento de texto que crear_publicaciones.php, para que lo guardado sea consistente
$id          = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$titulo      = trim(filter_var($_POST['titulo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$descripcion = trim(filter_var($_POST['descripcion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$precio      = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$categoria   = filter_var($_POST['categoria'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($id === false || $id === null) {
    responder(false, "Publicación inválida.");
}

if ($titulo === '') {
    responder(false, "El título es obligatorio.");
}

if (mb_strlen($titulo) > 100) {
    responder(false, "El título no puede superar los 100 caracteres.");
}

if ($precio === false || $precio === null) {
    responder(false, "El precio debe ser un número entero mayor que cero.");
}

if (!in_array($categoria, $categoriasPermitidas, true)) {
    responder(false, "Categoría inválida.");
}

try {

    // La publicación tiene que existir Y ser de este emprendedor (se comprueba contra la sesión, no contra lo que mande el navegador)
    $consulta = $conexion->prepare(
        "SELECT p.status, p.foto
         FROM publicaciones p
         JOIN emprendimiento e ON e.ID = p.Id_emprendimiento
         WHERE p.id = ? AND e.cedula = ?"
    );
    $consulta->execute([$id, $cedula]);
    $publicacion = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$publicacion) {
        responder(false, "La publicación no existe o no te pertenece.");
    }

    // Igual que cambiar_estado.php: una publicación bloqueada por un admin no se toca
    if ($publicacion['status'] === 'Bloqueada') {
        responder(false, "Esta publicación fue bloqueada por un administrador y no se puede editar.");
    }

    // La imagen es opcional: si no se sube una nueva, se conserva la actual
    $nuevaFoto = null;
    $hayFoto = isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($hayFoto) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            responder(false, "No se pudo recibir la imagen.");
        }

        $tamanoMaximo = 5 * 1024 * 1024; // 5MB
        if ($_FILES['foto']['size'] > $tamanoMaximo) {
            responder(false, "La imagen supera el tamaño máximo permitido (5MB).");
        }

        $infoImagen = getimagesize($_FILES['foto']['tmp_name']);
        if ($infoImagen === false) {
            responder(false, "El archivo subido no es una imagen válida.");
        }

        $extensionesPermitidas = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if (!isset($extensionesPermitidas[$infoImagen['mime']])) {
            responder(false, "Formato de imagen no permitido.");
        }

        $nuevaFoto = $id . '.' . $extensionesPermitidas[$infoImagen['mime']];
        $carpetaDestino = "../Imagenes/publicaciones/";

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $carpetaDestino . $nuevaFoto)) {
            responder(false, "No se pudo guardar la imagen en el servidor.");
        }
    }

    if ($nuevaFoto !== null) {
        $update = $conexion->prepare(
            "UPDATE publicaciones
             SET titulo = ?, descripcion = ?, precio = ?, categoria = ?, foto = ?
             WHERE id = ?"
        );
        $update->execute([$titulo, $descripcion, $precio, $categoria, $nuevaFoto, $id]);

        // Si la imagen vieja tenía otra extensión (ej. .jpg y ahora .png), la borramos para no dejar basura
        if (!empty($publicacion['foto']) && basename($publicacion['foto']) !== $nuevaFoto) {
            $rutaVieja = "../Imagenes/publicaciones/" . basename($publicacion['foto']);
            if (is_file($rutaVieja)) {
                unlink($rutaVieja);
            }
        }
    } else {
        $update = $conexion->prepare(
            "UPDATE publicaciones
             SET titulo = ?, descripcion = ?, precio = ?, categoria = ?
             WHERE id = ?"
        );
        $update->execute([$titulo, $descripcion, $precio, $categoria, $id]);
    }

    responder(true);

} catch (PDOException $e) {
    error_log("Error al editar publicación: " . $e->getMessage());
    http_response_code(500);
    responder(false, "Error interno al guardar los cambios.");
}

?>
