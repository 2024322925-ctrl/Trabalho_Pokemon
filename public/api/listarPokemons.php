<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../app/controllers/PokemonController.php";

try {
    $controller = new PokemonController();

    $pokemons = $controller->listarPokemons();

    echo json_encode(
        $pokemons,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        "erro" => "Não foi possível listar os Pokémon."
    ], JSON_UNESCAPED_UNICODE);
}