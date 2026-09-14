<?php 
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(1);


include "conexionBD.php";


try {
$nombre=filter_var($_POST['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$apellido=filter_var($_POST['apellido'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$correo=filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
$Fecha_Nacimiento=$_POST['Fecha_Nacimiento'];
$cedula=filter_var($_POST['cedula'], FILTER_VALIDATE_INT);
$Num_Telefono=filter_var($_POST['Num_Telefono'], FILTER_SANITIZE_NUMBER_INT);
$genero=filter_var($_POST['genero'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// El registro público solo puede crear cuentas de 'cliente' o 'emprendedor'.
// Los administradores se crean directamente en la base de datos, nunca desde este formulario.
$rolesPermitidos = ['cliente', 'emprendedor'];
$rolSolicitado = $_POST['Rol'] ?? '';
$rol = in_array($rolSolicitado, $rolesPermitidos, true) ? $rolSolicitado : 'cliente';

$pass=$_POST['contraseña'];  
$nacimiento = new DateTime($Fecha_Nacimiento);
$hoy= new DateTime();
$edad = $hoy->diff($nacimiento)->y;


if ($correo === false || $cedula === false) {
    echo json_encode(["exito" => false, "mensaje" => "Correo o cédula inválidos"]);
    exit;
}

$pass=password_hash($pass, PASSWORD_DEFAULT); 

$sen= $conexion->prepare("INSERT INTO usuario(Nombre,Apellido,correo,Fecha_Nacimiento,Edad,Cedula,Num_Telefono,Domicilio,Calle,Manzana,Solar,Genero,Contraseña,Rol)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$sen->execute([$nombre,$apellido,$correo,$Fecha_Nacimiento,$edad,$cedula,$Num_Telefono,'','','','',$genero,$pass,$rol]);


echo json_encode(["exito"=>true]);

}catch(PDOException $e){
    error_log("Error en registro.php: " . $e->getMessage());
    echo json_encode(["exito"=>false, "mensaje"=>"No se pudo completar el registro. Intente nuevamente."]);
    exit;
}

if($rol === "emprendedor"){
    $sen= $conexion->prepare("INSERT INTO emprendedor(Nombre,Apellido,Correo,Cedula,Genero)VALUES(?,?,?,?,?)");
    $sen->execute([$nombre,$apellido,$correo,$cedula,$genero,]);


}else{
    $sen= $conexion->prepare("INSERT INTO cliente(Cedula,Nombre,Apellido,Correo,Genero)VALUES(?,?,?,?,?)");
    $sen->execute([$cedula,$nombre,$apellido,$correo,$genero]);

}

?>