<?php

require __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class mail_service {
    private array $config;
   

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function enviarLoginLink (string $correo, string $token){

        $mail = new PHPMailer(true);
        try {
            $mail -> isSMTP();
            $mail ->  Host = $this -> config["Host"];
            $mail -> SMTPAuth = true;
            $mail -> Username = $this -> config["Username"];
            $mail -> Password = $this -> config["Password"];
            $mail -> SMTPSecure = PHPMailer :: ENCRYPTION_STARTTLS;
            $mail -> Port = $this -> config ["Port"];

            $mail -> setFrom(
                $this -> config["Username"],
                $this -> config["Name"]
            );

            $mail -> addAddress($correo);
            $mail -> isHTML(true);
            $mail -> Subject = "Correo para login";
            $url = $_ENV["APP_URL"] . "/api/auth/verificar_token_email.php?token=" . urlencode($token);
            $mail -> Body = "<h1>Hola</h1> <p>Ingresa al link para iniciar sesion: </p> 
            <a href = '$url'>Iniciar sesion </a>
            " ;

            $mail -> send();

            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }
}
