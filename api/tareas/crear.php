<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/rol_admin.php";

session_start();

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

if (!isset($datos["IdUsuario"], $datos["Titulo"]
)){
    echo json_encode([
        "success" => false,
        "message" => "Faltan campos obligatorios"
    ]);
    exit;
}

$IdUsuario =$datos["IdUsuario"];
$IdCreador = $_SESSION["IdUsuario"];
$Titulo =$datos["Titulo"];
$Descripcion =$datos["Descripcion"];
$FechaFin =$datos["FechaFin"];


$sql = "INSERT INTO Tarea (IdUsuario, IdCreador, Titulo, Descripcion, FechaFin) VALUES (?,?,?,?,?)";
$stmt = $conexion -> prepare($sql);
$resultado = $stmt -> execute([$IdUsuario,$IdCreador, $Titulo, $Descripcion, $FechaFin]);
$idcredencial = $conexion ->lastInsertId();

if(!$resultado) {
    echo json_encode([
        "success" => false,
        "message" => "No se pudo crear la tarea, intentelo de nuevo"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Tarea creada correctamente"
]);
