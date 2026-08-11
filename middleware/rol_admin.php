<?php
require __DIR__ . "/auth.php";
 session_start();

 
if ($_SESSION['rol'] !== 1) {  
    http_response_code(403);
    echo "Acceso denegado";
    exit;
}