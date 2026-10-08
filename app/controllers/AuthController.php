<?php

require_once __DIR__ . "/../models/Usuario.php";
require_once __DIR__ . "/../dao/UsuarioDAO.php";

class AuthController
{
    private $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function cadastrar($nome, $sobrenome, $email, $senha)
    {
        $usuarioExistente = $this->usuarioDAO->buscarPorEmail($email);

        if ($usuarioExistente != null) {
            return false;
        }

        $usuario = new Usuario();

        $usuario->setNome($nome);
        $usuario->setSobrenome($sobrenome);
        $usuario->setEmail($email);

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $usuario->setSenha($senhaHash);

        $usuario->setPerfil("jogador");

        return $this->usuarioDAO->cadastrar($usuario);
    }

    public function login($email, $senha)
    {
        $usuario = $this->usuarioDAO->buscarPorEmail($email);

        if ($usuario == null) {
            return null;
        }

        if (password_verify($senha, $usuario->getSenha())) {
            return $usuario;
        }

        return false;
    }

    public function sair()
    {
        session_start();
        session_unset();
        session_destroy();

        header("Location: /Trabalho_Pokemon/app/views/login/index.php");
        exit();
    }
}