<?php

require __DIR__ . "/../../config/conexion.php";

$token = $_GET["token"] ?? null;

if (!$token) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "Token no proporcionado"]);
    exit;
}

header("Content-Type: application/json");

$sql = "UPDATE LoginToken 
        SET Usado = TRUE 
        WHERE Token = ? 
          AND Usado = FALSE 
          AND FechaExpiracion > NOW()";

$stmt = $conexion->prepare($sql);
$stmt->execute([$token]);

if ($stmt->rowCount() === 0) {
    echo json_encode(["success" => false, "message" => "Token inválido o expirado"]);
    exit;
}


$sql = "SELECT 
    u.IdUsuario,
    u.PrimerNombre,
    u.PrimerApellido,
    u.IdRol,
    c.Correo,
    c.IdCredencial
FROM LoginToken lt
JOIN Credencial c ON lt.IdCredencial = c.IdCredencial
JOIN Usuario u ON u.IdCredencial = c.IdCredencial
WHERE lt.Token = ?
  AND lt.FechaExpiracion > NOW()";

$stmt = $conexion -> prepare($sql);

$stmt->execute([$token]);
$usuario = $stmt -> fetch(PDO::FETCH_ASSOC);



session_start();
$_SESSION['idUsuario']    = (int)$usuario['IdUsuario'];
$_SESSION['IdCredencial'] = (int)$usuario['IdCredencial'];
$_SESSION['nombre']       = $usuario['PrimerNombre'];
$_SESSION['apellido']     = $usuario['PrimerApellido'];
$_SESSION['rol']          = (int)$usuario['IdRol'];
$_SESSION['correo']       = $usuario['correo'];
$_SESSION['autenticado']  = true;


header("Location: ../../public/dashboard.php");
exit;
