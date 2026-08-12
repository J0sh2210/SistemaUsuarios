<?php
session_start();

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/auth.php";

$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");
$IdCredencial = $_SESSION ["idCredencial"];
$contrasena_actual = $datos["Contrasena_Actual"];
$contrasena_nueva = $datos ["Contrasena_Nueva"];

$sql = "SELECT contrasena FROM Credencial WHERE IdCredencial = ?";

$stmt = $conexion -> prepare($sql);
$stmt -> execute([$IdCredencial]);
$contrasena_db = $stmt -> fetch(PDO::FETCH_ASSOC);

if(!password_verify($contrasena_actual, $contrasena_db["contrasena"])){
    echo json_encode([
        "success" => false,
        "message" => "La contrasena no es la correcta"
    ]);
    exit;
}

$contrasenah_hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);

$sql = "UPDATE Credencial SET contrasena = ? WHERE IdCredencial = ?";

$stmt = $conexion -> prepare($sql);
$stmt -> execute([$contrasenah_hash, $IdCredencial]);

echo json_encode([
    "success" => true,
    "message" => "Contrasena cambiada correctamente"
]);




