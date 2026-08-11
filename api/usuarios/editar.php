<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/auth.php";

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
$IdUsuario   = $datos["IdUsuario"] ?? $_SESSION["idUsuario"];
$IdCredencial = $datos["IdCredencial"] ?? null;

if ($IdUsuario !== $_SESSION["idUsuario"] && $_SESSION["rol"] !== 1) {
    http_response_code(403);
    exit;
}

header("Content-Type: application/json");
$PrimerNombre = $datos["PrimerNombre"];
$PrimerApellido=$datos["PrimerApellido"];
$SegundoNombre =  $datos["SegundoNombre"];
$SegundoApellido = $datos["SegundoApellido"];
$Correo = $datos["Correo"];
try {
    $conexion -> beginTransaction();
    $sql = "UPDATE Usuario 
        SET PrimerNombre = ?, SegundoNombre = ?, PrimerApellido = ?, SegundoApellido = ?
        WHERE IdUsuario = ?";
    $stmt = $conexion -> prepare($sql);
    $stmt -> execute([$PrimerNombre, $SegundoNombre, $PrimerApellido, $SegundoApellido, $IdUsuario]);
    if ($_SESSION["rol"] === 1) {
    $sql = "UPDATE Credencial SET Correo = ? WHERE IdCredencial = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$Correo, $IdCredencial]);
    }
$conexion -> commit();
echo json_encode([
    "success" => true,
    "message" => "Usuario editado correctamente"
]);
}
 catch (Exception $e) {
    $conexion -> rollBack();
    echo json_encode([
        "success" => false,
        "message" => "no se pudo editar el usuario, vuelvelo a intentar mas tarde"
    ]);   
 }
