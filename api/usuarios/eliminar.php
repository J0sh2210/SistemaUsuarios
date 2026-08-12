<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/rol_admin.php";

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

if (!$datos) {
    echo json_encode(["success" => false, "message" => "JSON invalido"]);
    exit;
}

$IdUsuario = $datos["IdUsuario"];
$IdCredencial = $datos["IdCredencial"];

try {
    $conexion -> beginTransaction();

    $sql = "
    DELETE FROM Usuario WHERE IdUsuario = ?
    ";
    $stmt = $conexion -> prepare($sql);
    $stmt -> execute([$IdUsuario]);

        $sql = "
    DELETE FROM Credencial WHERE IdCredencial = ?
    ";
    $stmt = $conexion -> prepare($sql);
    $stmt -> execute([$IdCredencial]);


    $conexion -> commit();
    echo json_encode([
        "success" => true,
        "message" => "Usuario eliminado correctamente"
    ]);


}

catch(Exception $e){
    $conexion -> rollBack();
    echo json_encode([
        "success" => false,
        "message" => "No se pudo eliminar el usuario, intentelo mas tarde"
    ]);
}