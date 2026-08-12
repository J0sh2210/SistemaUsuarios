<?php
require __DIR__ . "/../../config/conexion.php";
$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

if (!$datos) {
    echo json_encode(["success" => false, "message" => "JSON invalido"]);
    exit;
}

if (!isset($datos["correo"], $datos["contrasena"])){
    echo json_encode([
        "success" => false,
        "message" => "alguno de los campos se encuentra vacio"
    ]);
    exit;
}
$correo = $datos["correo"];
$contrasenaIngresada = $datos["contrasena"];
$sql = "SELECT u.IdUsuario , u.PrimerNombre, u.PrimerApellido, u.IdRol, c.correo, c.contrasena,c.IdCredencial  FROM Credencial c 
JOIN Usuario u ON u.IdCredencial = c.IdCredencial
WHERE Correo = ?";


$stmt = $conexion ->prepare($sql);

$stmt -> execute([$correo]);
$usuario = $stmt -> fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode([
        "success" =>false,
        "message" => "Credenciales incorrectas"
    ]);
    exit;
}
if (!password_verify($contrasenaIngresada, $usuario["contrasena"])){
        echo json_encode([
        "success" =>false,
        "message" => "Credenciales incorrectas"
    ]);
    exit;
}

session_start();
$_SESSION['idUsuario']    = (int)$usuario['IdUsuario'];
$_SESSION['IdCredencial'] = (int)$usuario['IdCredencial'];
$_SESSION['nombre']       = $usuario['PrimerNombre'];
$_SESSION['apellido']     = $usuario['PrimerApellido'];
$_SESSION['rol']          = (int)$usuario['IdRol'];
$_SESSION['correo']       = $usuario['correo'];
$_SESSION['autenticado']  = true;

echo json_encode([
    "success" =>true,
    "message" => "Sesion iniciada correctamente"
]);


