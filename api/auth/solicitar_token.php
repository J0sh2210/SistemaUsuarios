<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../../services/token_service.php";
$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

if (!isset($datos["correo"])){
    echo json_encode([
        "success" => false,
        "message" => "El campo se encuentra vacio"
    ]);
    exit;
}
$correo = $datos["correo"];

$sql = "SELECT idCredencial, NombreUsuario, Correo  FROM Credencial WHERE Correo = ?";
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
 try {
    $tokenService = new TokenService($conexion);
    $token = $tokenService -> crearToken($usuario["idCredencial"]);

    $config = require __DIR__ . "/../../config/correo.php";

    $mailservice = new mail_service($config);
    $mailservice -> enviarLoginLink($correo, $token);

    echo json_encode([
        "success" => true,
        "message" => "Enlace de inicio de sesion generado correctamente"
    ]);
 } catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "no pudo enviarse el enlace intentelo de nuevo"
    ]);
 }

