<?php
require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$host = $_ENV["DB_HOST"];
$db = $_ENV["DB_NAME"];
$user = $_ENV["DB_USER"];
$password = $_ENV["DB_PASSWORD"];

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