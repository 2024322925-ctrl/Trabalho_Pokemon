<?php

require_once __DIR__ . "/../dao/TimePokemonDAO.php";

class TimePokemonController
{
    private $timePokemonDAO;

    public function __construct()
    {
        $this->timePokemonDAO = new TimePokemonDAO();
    }

    public function cadastrarTime(TimePokemon $time)
    {
        return $this->timePokemonDAO->cadastrar($time);
    }

    public function buscarTimeAtivo($id_usuario)
    {
        return $this->timePokemonDAO->buscarAtivoPorUsuario($id_usuario);
    }
}
?>
