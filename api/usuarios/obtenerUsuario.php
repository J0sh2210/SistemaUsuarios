<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/auth.php";
session_start();
$json = file_get_contents("php://input");
$datos = json_decode($json,true);

if ($datos["IdUsuario"] !== null && $_SESSION["rol"] !== 1) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Sin permisos"]);
    exit;
}
$IdUsuario = $datos["IdUsuario"] ?? $_SESSION["idUsuario"];
header("Content-Type: application/json");

$sql = "
    SELECT u.IdUsuario, u.PrimerNombre, u.SegundoNombre, u.PrimerApellido, u.SegundoApellido, c.Correo, c.IdCredencial
    FROM Usuario u
    JOIN Credencial c ON c.IdCredencial = u.IdCredencial
    Where u.IdUsuario = ?
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$IdUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "usuario" => $usuario]);



