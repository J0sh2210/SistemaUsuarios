<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/rol_admin.php";

header("Content-Type: application/json");

$sql = "SELECT 
    u.IdUsuario,
    u.PrimerNombre,
    u.SegundoNombre,
    u.PrimerApellido,
    u.SegundoApellido,
    c.NombreUsuario,
    c.Correo,
    r.Descripcion AS Rol
FROM Usuario u
JOIN Credencial c ON u.IdCredencial = c.IdCredencial
JOIN Rol r ON u.IdRol = r.IdRol";

$stmt = $conexion -> prepare($sql);
$stmt -> execute();
$usuarios = $stmt -> fetchAll(PDO::FETCH_ASSOC);

echo json_encode($usuarios);