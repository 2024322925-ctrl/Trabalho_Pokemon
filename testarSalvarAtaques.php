
<?php

require_once "app/services/PokemonAPI.php";
require_once "app/dao/PokemonDAO.php";

try {

    $api = new PokemonAPI();
    $dao = new PokemonDAO();

    $pokemon = $api->buscarPokemon("ditto");

    $dao->salvarDaAPI($pokemon);

    echo "Pokémon e ataques salvos com sucesso!";

} catch (Throwable $e) {

    echo "Erro: " . $e->getMessage();

}
