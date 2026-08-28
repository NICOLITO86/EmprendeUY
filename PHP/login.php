<?php

session_start();
include "conexionBD.php";

    $cedula = filter_var($_POST['cedula'], FILTER_VALIDATE_INT);
    $pass = $_POST['contraseña'];

    if ($cedula === false) {
        echo json_encode(["success" => false, "mensaje" => "Cédula inválida."]);
        exit;
    }

    $sql = "SELECT * FROM usuario
            WHERE Cedula = :cedula";
        

    $stmtUsuario = $conexion->prepare($sql);

    $stmtUsuario->execute([
        ':cedula' => $cedula,
        
    ]);
    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    $sql1 = "SELECT * FROM emprendimiento
            WHERE cedula = :cedula";
    $stmtEmprendimiento = $conexion->prepare($sql1);
    $stmtEmprendimiento->execute([
        ':cedula' => $cedula
    ]);

    $emprendimiento = $stmtEmprendimiento->fetch(PDO::FETCH_ASSOC);

   if ($usuario) {

    if(password_verify($pass,$usuario['Contraseña'])){

    $_SESSION['Cedula'] = $usuario['Cedula'];
    $_SESSION['Nombre'] = $usuario['Nombre'];
    $_SESSION['Rol'] = $usuario['Rol'];
    
    switch ($usuario['Rol']) {

        case 'administrador':
            echo json_encode([
                "success" => true,
                "redirect" => "../HTML/paneladm.html"
            ]);
            break;

        case 'cliente':
            echo json_encode([
                "success" => true,
                "redirect" => "../HTML/tienda.html"
            ]);
            break;

        case 'emprendedor': if($emprendimiento){
            echo json_encode([
                "success" => true,
                "redirect" => "../HTML/creadorpublicaciones.html"
                
            ]);
            }else{
                echo json_encode([
                    "success" => true,
                    "redirect" => "../HTML/crearemprendimiento.html"
                    
                ]);
            }
            break;
    }

    
} else {

    echo json_encode([
        "success" => false,
        "mensaje" => "Cédula o contraseña incorrectas."
    ]);
}

}

?>