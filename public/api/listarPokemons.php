<?php

session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../../app/dao/PokemonDesbloqueioDAO.php";

require_once __DIR__ . "/../../app/controllers/PokemonController.php";

try {
    $controller = new PokemonController();

    $pokemons = $controller->listarTeamBuilder();

    if (isset($_SESSION["id_usuario"])) {
        $estado = (new PokemonDesbloqueioDAO())->estado($_SESSION["id_usuario"]);
        $idsExtras = array_diff($estado["desbloqueados"], array_column($pokemons, "id_pokemon"));
        if ($idsExtras) {
            foreach ($controller->listarPokemons() as $pokemon) {
                if (in_array((int) $pokemon["id_pokemon"], $idsExtras, true)) {
                    $pokemons[] = $pokemon;
                }
            }
        }
    }

    echo json_encode(
        $pokemons,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

} catch (Throwable $e) {
    error_log("Erro ao listar Pokémon: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "erro" => "Não foi possível listar os Pokémon."
    ], JSON_UNESCAPED_UNICODE);
}