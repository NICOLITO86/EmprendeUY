<?php 
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(1);

require_once __DIR__ . '/session_config.php';
include "conexionBD.php";


// Constante compartida con la validación del lado del cliente (JS/validaciones.js).
const EDAD_MINIMA = 16;

try {
$nombre=filter_var($_POST['nombre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$apellido=filter_var($_POST['apellido'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$correo=filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
$Fecha_Nacimiento=$_POST['Fecha_Nacimiento'];
$cedulaCruda=trim($_POST['cedula'] ?? '');
$telefonoCrudo=trim($_POST['Num_Telefono'] ?? '');
$genero=filter_var($_POST['genero'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// SEC: la cédula y el teléfono se validan de nuevo acá porque la validación
// de JS/validaciones.js es solo para la experiencia del usuario, no es
// segura por sí sola (se puede saltear con las devtools o Postman).
if (!preg_match('/^\d{8}$/', $cedulaCruda)) {
    echo json_encode(["exito" => false, "mensaje" => "La cédula debe tener exactamente 8 dígitos."]);
    exit;
}

if (!preg_match('/^\d{9}$/', $telefonoCrudo)) {
    echo json_encode(["exito" => false, "mensaje" => "El teléfono debe tener exactamente 9 dígitos."]);
    exit;
}

$cedula=filter_var($cedulaCruda, FILTER_VALIDATE_INT);
$Num_Telefono=$telefonoCrudo;

// El registro público solo puede crear cuentas de 'cliente' o 'emprendedor'.
// Los administradores se crean directamente en la base de datos, nunca desde este formulario.
$rolesPermitidos = ['cliente', 'emprendedor'];
$rolSolicitado = $_POST['Rol'] ?? '';
$rol = in_array($rolSolicitado, $rolesPermitidos, true) ? $rolSolicitado : 'cliente';

$pass=$_POST['contraseña'];  
$nacimiento = new DateTime($Fecha_Nacimiento);
$hoy= new DateTime();

if ($correo === false || $cedula === false) {
    echo json_encode(["exito" => false, "mensaje" => "Correo o cédula inválidos"]);
    exit;
}

if ($nacimiento > $hoy) {
    echo json_encode(["exito" => false, "mensaje" => "La fecha de nacimiento no puede ser futura."]);
    exit;
}

if (strlen($pass) < 8) {
    echo json_encode(["exito" => false, "mensaje" => "La contraseña debe tener al menos 8 caracteres."]);
    exit;
}

$edad = $hoy->diff($nacimiento)->y;

if ($edad < EDAD_MINIMA) {
    echo json_encode(["exito" => false, "mensaje" => "Debés tener al menos " . EDAD_MINIMA . " años para registrarte."]);
    exit;
}

$pass=password_hash($pass, PASSWORD_DEFAULT); 

$sen= $conexion->prepare("INSERT INTO usuario(Nombre,Apellido,correo,Fecha_Nacimiento,Edad,Cedula,Num_Telefono,Domicilio,Calle,Manzana,Solar,Genero,Contraseña,Rol)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$sen->execute([$nombre,$apellido,$correo,$Fecha_Nacimiento,$edad,$cedula,$Num_Telefono,'','','','',$genero,$pass,$rol]);

if($rol === "emprendedor"){
    $sen= $conexion->prepare("INSERT INTO emprendedor(Nombre,Apellido,Correo,Cedula,Genero)VALUES(?,?,?,?,?)");
    $sen->execute([$nombre,$apellido,$correo,$cedula,$genero]);
}else{
    $sen= $conexion->prepare("INSERT INTO cliente(Cedula,Nombre,Apellido,Correo,Genero)VALUES(?,?,?,?,?)");
    $sen->execute([$cedula,$nombre,$apellido,$correo,$genero]);
}

// Registro completo: dejamos al usuario logueado, igual que si hubiera
// iniciado sesión manualmente, y le indicamos a dónde corresponde mandarlo
// segun su rol (misma logica que login.php).
session_regenerate_id(true);
$_SESSION['Cedula'] = $cedula;
$_SESSION['Nombre'] = $nombre;
$_SESSION['Rol']    = $rol;

$redirect = ($rol === "emprendedor")
    ? '../HTML/crearemprendimiento.html'
    : '../HTML/tienda.html';

echo json_encode(["exito" => true, "rol" => $rol, "redirect" => $redirect]);

}catch(PDOException $e){
    error_log("Error en registro.php: " . $e->getMessage());
    echo json_encode(["exito"=>false, "mensaje"=>"No se pudo completar el registro. Intente nuevamente."]);
    exit;
}
