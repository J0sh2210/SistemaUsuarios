<?php
class TokenService {
    private PDO $conexion;

    public function __construct (PDO $conexion){
        $this -> conexion = $conexion;
    }

    public function crearToken (int $idCredencial){

    try {
        $this ->conexion ->beginTransaction();
        $sql = "UPDATE LoginToken
                SET Usado = TRUE
                WHERE IdCredencial = ?
                AND Usado = FALSE";
        $stmt = $this-> conexion -> prepare($sql);
        $stmt -> execute([$idCredencial]);

        $token =bin2hex(random_bytes(32));
        $expiracion = date ("Y-m-d H:i:s", strtotime("+15 minutes"));

        $sql = "INSERT INTO LoginToken (
        IdCredencial,
        Token,
        FechaExpiracion
         )
        VALUES (?, ?, ?)";

        $stmt = $this-> conexion -> prepare($sql);
        $stmt -> execute([$idCredencial, $token , $expiracion]);

        $this-> conexion ->commit();

        return $token;

    } catch (Exception $e){
        $this -> conexion -> rollBack();
        throw $e;

    }
    }
}