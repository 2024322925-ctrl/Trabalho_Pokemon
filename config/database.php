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
            "mysql:host=$this->host;dbname=$this->dbname",
            $this->usuario,
            $this->senha
        );

        return $con;
    }
}
?>