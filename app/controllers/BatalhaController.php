
<?php

require_once __DIR__ . "/../dao/BatalhaDAO.php";

class BatalhaController
{
    private $batalhaDAO;

    public function __construct()
    {
        $this->batalhaDAO = new BatalhaDAO();
    }

    // Busca o time ativo do usuário
    public function buscarTimeAtivo($idUsuario)
    {
        return $this->batalhaDAO->buscarTimeAtivoPorUsuario($idUsuario);
    }

    // Lista os Pokémon do time do usuário
    public function listarPokemonsDoTime($idUsuario)
    {
        return $this->batalhaDAO->listarPokemonsDoTime($idUsuario);
    }

    // Lista os adversários de uma fase
    public function listarPokemonsDaFase($idFase)
    {
        return $this->batalhaDAO->listarPokemonsDaFase($idFase);
    }

    // Lista os ataques de um Pokémon
    public function listarAtaquesDoPokemon($idPokemon)
    {
        return $this->batalhaDAO->listarAtaquesDoPokemon($idPokemon);
    }

    // Lista os tipos de um Pokémon
    public function listarTiposDoPokemon($idPokemon)
    {
        return $this->batalhaDAO->listarTiposDoPokemon($idPokemon);
    }
}

?>
