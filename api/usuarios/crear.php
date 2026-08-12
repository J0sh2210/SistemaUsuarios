<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/rol_admin.php";

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

if (!isset($datos["PrimerNombre"], $datos["SegundoNombre"], $datos["PrimerApellido"],
    $datos["SegundoApellido"], $datos["IdRol"], $datos["NombreUsuario"], $datos["Correo"],
    $datos["contrasena"]
)){
    echo json_encode([
        "success" => false,
        "message" => "Faltan campos obligatorios"
    ]);
    exit;
}

$PrimerNombre = $datos["PrimerNombre"];
$PrimerApellido=$datos["PrimerApellido"];
$SegundoNombre =  $datos["SegundoNombre"];
$SegundoApellido = $datos["SegundoApellido"];
$Idrol =  $datos["IdRol"];
$NombreUsuario = $datos["NombreUsuario"];
$Correo = $datos["Correo"];
$contrasenaplana =$datos["contrasena"];
$contrasenaHash = password_hash($contrasenaplana, PASSWORD_DEFAULT);

try {
    $conexion ->beginTransaction();
    $sql = "INSERT INTO Credencial (NombreUsuario, correo ,contrasena) VALUES (?,?,?)";
    $stmt = $conexion -> prepare($sql);
    $resultado = $stmt -> execute([$NombreUsuario, $Correo, $contrasenaHash]);
    $idcredencial = $conexion ->lastInsertId();


    $sql = "INSERT INTO Usuario (PrimerNombre, SegundoNombre, PrimerApellido, SegundoApellido
    , IdRol, IdCredencial) VALUES (?,?,?, ?, ? , ?)";

    $stmt = $conexion ->prepare($sql);
    $resultado = $stmt -> execute([$PrimerNombre, $SegundoNombre, $PrimerApellido, $SegundoApellido, $Idrol, $idcredencial]);


    
        $conexion -> commit();

        echo json_encode([
        "success" =>$resultado,
        "message" => "Usuario creado correctamente"
    ]);
} catch (Exception $e){
    $conexion ->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "no se pudo crear el usuario, vuelvelo a intentar mas tarde"
    ]);
}