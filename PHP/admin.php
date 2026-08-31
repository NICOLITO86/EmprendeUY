<?php
require "auth.php";
requireRole(['administrador']);

include "conexionBD.php";

$accion = $_POST['accion'] ?? '';

switch ($accion) {

    case 'borrar_usuario':
        $cedula = filter_var($_POST['cedula'] ?? null, FILTER_VALIDATE_INT);
        if ($cedula === false) {
            echo json_encode(["exito" => false, "mensaje" => "Cédula inválida."]);
            break;
        }
        $sen = $conexion->prepare("DELETE FROM usuario WHERE Cedula = ?");
        $sen->execute([$cedula]);
        echo json_encode(["exito" => true]);
        break;

    case 'borrar_emprendimiento':
        $ID = filter_var($_POST['ID'] ?? null, FILTER_VALIDATE_INT);
        if ($ID === false) {
            echo json_encode(["exito" => false, "mensaje" => "ID inválido."]);
            break;
        }
        $sen = $conexion->prepare("DELETE FROM emprendimiento WHERE ID = ?");
        $sen->execute([$ID]);
        echo json_encode(["exito" => true]);
        break;

    case 'buscar_usuario':
        $cedula = filter_var($_POST['cedula'] ?? null, FILTER_VALIDATE_INT);
        if ($cedula === false) {
            echo json_encode(["encontrado" => false, "mensaje" => "Cédula inválida."]);
            break;
        }
        $res = $conexion->prepare("SELECT * FROM usuario WHERE cedula = ?");
        $res->execute([$cedula]);
        $pers = $res->fetch(PDO::FETCH_ASSOC);
        echo json_encode($pers ?: ["encontrado" => false]);
        break;

    case 'buscar_emprendimiento':
        $ID = filter_var($_POST['ID'] ?? null, FILTER_VALIDATE_INT);
        if ($ID === false) {
            echo json_encode(["encontrado" => false, "mensaje" => "ID inválido."]);
            break;
        }
        $res = $conexion->prepare("SELECT ID, Nombre, Descripcion FROM emprendimiento WHERE ID = ?");
        $res->execute([$ID]);
        $emprendimiento = $res->fetch(PDO::FETCH_ASSOC);
        echo json_encode($emprendimiento ?: ["encontrado" => false]);
        break;

    case 'mostrar_todo':
        $res = $conexion->prepare("SELECT * FROM usuario");
        $res->execute();
        $pers = $res->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pers);
        break;

    default:
        http_response_code(400);
        echo json_encode(["exito" => false, "mensaje" => "Acción no reconocida."]);
}
?>