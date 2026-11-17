<?php

class Database
{
    private $host = "localhost";
    private $dbname = "jogo_pokemon";
    private $usuario = "root";
    private $senha = "";

    public function conectar()
    {
        $con = new PDO(
            "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
            $this->usuario,
            $this->senha
        );

        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $con;
    }
}
?>