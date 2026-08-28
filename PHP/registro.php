<?php 
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);


include "conexionBD.php";


try {
$nombre=filter_var($_POST['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$apellido=filter_var($_POST['apellido'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$correo=filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
$Fecha_Nacimiento=$_POST['Fecha_Nacimiento'];
$cedula=filter_var($_POST['cedula'], FILTER_VALIDATE_INT);
$Num_Telefono=filter_var($_POST['Num_Telefono'], FILTER_SANITIZE_NUMBER_INT);
$genero=filter_var($_POST['genero'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$rol = filter_var($_POST['Rol'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$pass=$_POST['contraseña'];  

if ($correo === false || $cedula === false) {
    echo json_encode(["exito" => false, "mensaje" => "Correo o cédula inválidos"]);
    exit;
}

$pass=password_hash($pass, PASSWORD_DEFAULT); 

$sen= $conexion->prepare("INSERT INTO usuario(Nombre,Apellido,correo,Fecha_Nacimiento,Cedula,Num_Telefono,Genero,Contraseña,Rol)VALUES(?,?,?,?,?,?,?,?,?)");
$sen->execute([$nombre,$apellido,$correo,$Fecha_Nacimiento,$cedula,$Num_Telefono,$genero,$pass,$rol]);


echo json_encode(["exito"=>true]);

}catch(PDOException $e){
    echo json_encode(["exito"=>false]);
}

if($rol === "emprendedor"){
    $sen= $conexion->prepare("INSERT INTO emprendedor(Nombre,Apellido,Correo,Cedula,Genero)VALUES(?,?,?,?,?)");
    $sen->execute([$nombre,$apellido,$correo,$cedula,$genero,]);


}else{
    $sen= $conexion->prepare("INSERT INTO cliente(Cedula,Nombre,Apellido,Correo,Genero)VALUES(?,?,?,?,?)");
    $sen->execute([$cedula,$nombre,$apellido,$correo,$genero]);

}

?>