<?php

require_once __DIR__ . "/../dao/PokemonDAO.php";

class PokemonController
{
    private $pokemonDAO;

    public function __construct()
    {
        $this->pokemonDAO = new PokemonDAO();
    }

    public function listarPokemons()
    {
        return $this->pokemonDAO->listarTodos();
    }

    public function buscarPokemon($id_pokemon)
    {
        return $this->pokemonDAO->buscarPorId($id_pokemon);
    }
}
?>