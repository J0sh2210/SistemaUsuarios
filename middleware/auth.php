<?php


session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: /SistemaUsuarios/public/index.html");
    exit;
}