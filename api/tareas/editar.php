<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/rol_admin.php";

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
$IdTarea = $datos["IdTarea"];
$IdUsuario = $datos["IdUsuario"];
$Titulo = $datos["Titulo"];
$Descripcion = $datos["Descripcion"];
$Estado = $datos["Estado"];
$FechaFin = $datos["FechaFin"];

$sql = "UPDATE Tarea SET
    IdUsuario = ?, Titulo = ?, Descripcion = ?, Estado =?, FechaFin = ?
    WHERE IdTarea = ?
";

$stmt = $conexion -> prepare($sql);
$resultado = $stmt -> execute([$IdUsuario, $Titulo, $Descripcion, $Estado, $FechaFin, $IdTarea]);
if (!$resultado) {
    echo json_encode([
        "success" => false,
        "message" => "La tarea no pudo ser editada, intentelo nuevamente"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Tarea editada correctamente"
]);