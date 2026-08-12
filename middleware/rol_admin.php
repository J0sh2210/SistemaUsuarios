<?php
require __DIR__ . "/auth.php";

if ($_SESSION['rol'] !== 1) {  
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Acceso denegado"]);
    exit;
}