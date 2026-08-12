<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/auth.php";

$json = file_get_contents("php://input");
$datos = json_decode($json, true);
header("Content-Type: application/json");

if (!$datos) {
    echo json_encode(["success" => false, "message" => "JSON invalido"]);
    exit;
}

$idTarea = $datos["IdTarea"];

$sql = "SELECT * FROM Tarea WHERE IdTarea = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$idTarea]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$tarea) {
    echo json_encode(["success" => false, "message" => "Tarea no encontrada"]);
    exit;
}


if ($_SESSION["rol"] !== 1 && $tarea["IdUsuario"] !== $_SESSION["idUsuario"]) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Sin permisos"]);
    exit;
}

echo json_encode(["success" => true, "tarea" => $tarea]);