<?php
require "auth.php";
requireRole(['administrador']);
require_once __DIR__ . '/csrf.php';
csrf_validar();

include "conexionBD.php";

$accion = $_POST['accion'] ?? '';

switch ($accion) {

    // El administrador NO borra usuarios: los suspende (usuario.Activo = 0) y todo lo
    // relacionado se suspende con ellos. Nada se elimina de la base, así se puede reactivar.
    case 'suspender_usuario':
    case 'reactivar_usuario':
        $cedula = filter_var($_POST['cedula'] ?? null, FILTER_VALIDATE_INT);
        if ($cedula === false) {
            echo json_encode(["exito" => false, "mensaje" => "Cédula inválida."]);
            break;
        }
        $suspender = ($accion === 'suspender_usuario');

        $sen = $conexion->prepare("SELECT Rol, Activo FROM usuario WHERE Cedula = ? LIMIT 1");
        $sen->execute([$cedula]);
        $objetivo = $sen->fetch(PDO::FETCH_ASSOC);

        if (!$objetivo) {
            echo json_encode(["exito" => false, "mensaje" => "No existe un usuario con esa cédula."]);
            break;
        }
        if ($suspender && ($cedula === (int) $_SESSION['Cedula'] || $objetivo['Rol'] === 'administrador')) {
            echo json_encode(["exito" => false, "mensaje" => "No se puede suspender a un administrador."]);
            break;
        }
        if ((int) $objetivo['Activo'] === ($suspender ? 0 : 1)) {
            echo json_encode(["exito" => false, "mensaje" => $suspender
                ? "El usuario ya está suspendido." : "El usuario ya está activo."]);
            break;
        }

        try {
            $conexion->beginTransaction();

            // 1) El usuario (cliente o emprendedor): suspendido no puede iniciar sesión ni comprar.
            $conexion->prepare("UPDATE usuario SET Activo = ? WHERE Cedula = ?")
                     ->execute([$suspender ? 0 : 1, $cedula]);

            // 2) Si es emprendedor: su emprendimiento y sus publicaciones.
            if ($objetivo['Rol'] === 'emprendedor') {
                $conexion->prepare("UPDATE emprendimiento SET Activo = ? WHERE cedula = ?")
                         ->execute([$suspender ? 0 : 1, $cedula]);

                if ($suspender) {
                    // Se guarda el estado anterior para devolverlo igual al reactivar.
                    // Las que un admin ya había bloqueado a mano no se tocan.
                    $conexion->prepare(
                        "UPDATE publicaciones p
                         JOIN emprendimiento e ON e.ID = p.Id_emprendimiento
                         SET p.status_previo = p.status, p.status = 'Bloqueada'
                         WHERE e.cedula = ? AND p.status IN ('Activa', 'Pausada')"
                    )->execute([$cedula]);
                } else {
                    $conexion->prepare(
                        "UPDATE publicaciones p
                         JOIN emprendimiento e ON e.ID = p.Id_emprendimiento
                         SET p.status = p.status_previo, p.status_previo = NULL
                         WHERE e.cedula = ? AND p.status_previo IS NOT NULL"
                    )->execute([$cedula]);
                }
            }

            $conexion->commit();
            echo json_encode(["exito" => true, "mensaje" => $suspender
                ? "Usuario suspendido junto con todo lo relacionado."
                : "Usuario reactivado."]);
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log('Error en admin.php (' . $accion . '): ' . $e->getMessage());
            echo json_encode(["exito" => false, "mensaje" => "No se pudo completar la operación."]);
        }
        break;

    // Igual que con los usuarios, un emprendimiento no se borra: se suspende junto con sus publicaciones.
    case 'suspender_emprendimiento':
    case 'reactivar_emprendimiento':
        $ID = filter_var($_POST['ID'] ?? null, FILTER_VALIDATE_INT);
        if ($ID === false) {
            echo json_encode(["exito" => false, "mensaje" => "ID inválido."]);
            break;
        }
        $suspender = ($accion === 'suspender_emprendimiento');

        $sen = $conexion->prepare(
            "SELECT e.Activo, u.Activo AS dueno_activo
             FROM emprendimiento e LEFT JOIN usuario u ON u.Cedula = e.cedula
             WHERE e.ID = ? LIMIT 1"
        );
        $sen->execute([$ID]);
        $emp = $sen->fetch(PDO::FETCH_ASSOC);

        if (!$emp) {
            echo json_encode(["exito" => false, "mensaje" => "No existe un emprendimiento con ese ID."]);
            break;
        }
        if ((int) $emp['Activo'] === ($suspender ? 0 : 1)) {
            echo json_encode(["exito" => false, "mensaje" => $suspender
                ? "El emprendimiento ya está suspendido." : "El emprendimiento ya está activo."]);
            break;
        }
        if (!$suspender && $emp['dueno_activo'] !== null && (int) $emp['dueno_activo'] !== 1) {
            echo json_encode(["exito" => false, "mensaje" => "Su dueño está suspendido: reactivá primero al usuario."]);
            break;
        }

        try {
            $conexion->beginTransaction();
            $conexion->prepare("UPDATE emprendimiento SET Activo = ? WHERE ID = ?")
                     ->execute([$suspender ? 0 : 1, $ID]);

            if ($suspender) {
                $conexion->prepare(
                    "UPDATE publicaciones
                     SET status_previo = status, status = 'Bloqueada'
                     WHERE Id_emprendimiento = ? AND status IN ('Activa', 'Pausada')"
                )->execute([$ID]);
            } else {
                $conexion->prepare(
                    "UPDATE publicaciones
                     SET status = status_previo, status_previo = NULL
                     WHERE Id_emprendimiento = ? AND status_previo IS NOT NULL"
                )->execute([$ID]);
            }
            $conexion->commit();
            echo json_encode(["exito" => true, "mensaje" => $suspender
                ? "Emprendimiento suspendido junto con sus publicaciones."
                : "Emprendimiento reactivado."]);
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log('Error en admin.php (' . $accion . '): ' . $e->getMessage());
            echo json_encode(["exito" => false, "mensaje" => "No se pudo completar la operación."]);
        }
        break;

    case 'buscar_usuario':
        $cedula = filter_var($_POST['cedula'] ?? null, FILTER_VALIDATE_INT);
        if ($cedula === false) {
            echo json_encode(["encontrado" => false, "mensaje" => "Cédula inválida."]);
            break;
        }
        $res = $conexion->prepare(
            "SELECT Cedula, Nombre, Apellido, Fecha_Nacimiento, Edad, Correo,
                    Num_Telefono, Domicilio, Calle, Manzana, Solar, Genero, Rol, Activo
             FROM usuario WHERE cedula = ?"
        );
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
        $res = $conexion->prepare("SELECT ID, Nombre, Descripcion, Activo FROM emprendimiento WHERE ID = ?");
        $res->execute([$ID]);
        $emprendimiento = $res->fetch(PDO::FETCH_ASSOC);
        echo json_encode($emprendimiento ?: ["encontrado" => false]);
        break;

    case 'mostrar_todo':
        $res = $conexion->prepare(
            "SELECT Cedula, Nombre, Apellido, Fecha_Nacimiento, Edad, Correo,
                    Num_Telefono, Domicilio, Calle, Manzana, Solar, Genero, Rol, Activo
             FROM usuario"
        );
        $res->execute();
        $pers = $res->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pers);
        break;

    default:
        http_response_code(400);
        echo json_encode(["exito" => false, "mensaje" => "Acción no reconocida."]);
}
?>