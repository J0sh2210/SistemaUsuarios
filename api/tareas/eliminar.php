<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/rol_admin.php";

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

$IdTarea = $datos["IdTarea"];


    $sql = "
    DELETE FROM Tarea WHERE IdTarea = ?
    ";
    $stmt = $conexion -> prepare($sql);
    $resultado =$stmt -> execute([ $IdTarea]);
    if (!$resultado){

    echo json_encode([
        "success" => false,
        "message" => "No se pudo eliminar la tarea, intentelo mas tarde"
    ]);
    exit;

    }


    echo json_encode([
        "success" => true,
        "message" => "Tarea eliminada correctamente"
    ]);



