<?php

require_once __DIR__ . "/../models/Usuario.php";
require_once __DIR__ . "/../../config/database.php";

class UsuarioDAO
{
    private $con;

    public function __construct(){
        
        $database = new Database();
        $this->con = $database->conectar();
    }

    public function cadastrar(Usuario $usuario){

        $sql = "INSERT INTO usuario
                (nome, sobrenome, email, senha, perfil)
                VALUES
                (:nome, :sobrenome, :email, :senha, :perfil)";

        $stmt = $this->con->prepare($sql);

        $stmt->bindValue(":nome", $usuario->getNome());
        $stmt->bindValue(":sobrenome", $usuario->getSobrenome());
        $stmt->bindValue(":email", $usuario->getEmail());
        $stmt->bindValue(":senha", $usuario->getSenha());
        $stmt->bindValue(":perfil", $usuario->getPerfil());

        return $stmt->execute();
    }

    public function buscarPorEmail($email){

        $sql = "SELECT * FROM usuario WHERE email = :email";

        $stmt = $this->con->prepare($sql);
        $stmt->bindValue(":email", $email);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            $usuario = new Usuario();

            $usuario->setId_usuario($dados["id_usuario"]);
            $usuario->setNome($dados["nome"]);
            $usuario->setSobrenome($dados["sobrenome"]);
            $usuario->setEmail($dados["email"]);
            $usuario->setSenha($dados["senha"]);
            $usuario->setPerfil($dados["perfil"]);

            return $usuario;
        }

        return null;
    }
}