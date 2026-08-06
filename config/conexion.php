<?php
$host = "localhost";
$db = "sistemausuario";
$user = "root";
$password = "";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $password, [PDO::ATTR_PERSISTENT => true]
    );

    $conexion -> setAttribute(PDO::ATTR_ERRMODE,
                            PDO::ERRMODE_EXCEPTION
    );
} catch (PDOException $e) {
    die("Error: " . $e ->getMessage());
}