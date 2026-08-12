<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/auth.php";

session_start();

header("Content-Type: application/json");
if ($_SESSION["rol"] === 1) {
    $sql = "select 
            t.IdTarea,
            t.Titulo,
            t.Descripcion,
            t.Estado,
            t.FechaCreacion,
            t.FechaFin,
            CONCAT(u.PrimerNombre , ' ' , u.PrimerApellido) AS Usuario 
            from Tarea AS t 
            JOIN Usuario u ON u.IdUsuario = t.IdUsuario";
    $stmt = $conexion -> prepare($sql);
    $stmt -> execute();
    $tareas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tareas);

}
else {
    $IdUsuario = $_SESSION["idUsuario"];
    $sql = "select 
            t.IdTarea,
            t.Titulo,
            t.Descripcion,
            t.Estado,
            t.FechaCreacion,
            t.FechaFin,
            CONCAT(u.PrimerNombre , ' ' , u.PrimerApellido) AS Usuario 
            from Tarea AS t 
            JOIN Usuario u ON u.IdUsuario = t.IdUsuario
            WHERE IdUsuario = ?";
    $stmt = $conexion -> prepare($sql);
    $stmt -> execute([$IdUsuario]);
    $tareas = $stmt -> fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tareas);
}