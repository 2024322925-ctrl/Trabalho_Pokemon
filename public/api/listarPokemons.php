<?php

session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../app/controllers/PokemonController.php";
require_once __DIR__ . "/../../app/dao/PokemonDesbloqueioDAO.php";

try {
    $controller = new PokemonController();
    $catalogoDaLoja = ($_GET["catalogo"] ?? "") === "loja";

    if ($catalogoDaLoja) {
        $pokemons = $controller->listarLoja();
    } elseif (isset($_SESSION["id_usuario"])) {
        $estado = (new PokemonDesbloqueioDAO())->estado($_SESSION["id_usuario"]);

        $pokemons = $estado["escolheu_iniciais"]
            ? $controller->listarLoja()
            : $controller->listarTeamBuilder();

        // Inclui as formas evoluídas que a conta já desbloqueou.
        $idsExtras = array_diff(
            $estado["desbloqueados"],
            array_column($pokemons, "id_pokemon")
        );

        if ($idsExtras) {
            $idsExtras = array_fill_keys($idsExtras, true);

            foreach ($controller->listarPokemons() as $pokemon) {
                if (isset($idsExtras[$pokemon["id_pokemon"]])) {
                    $pokemons[] = $pokemon;
                }
            }
        }
    } else {
        $pokemons = $controller->listarTeamBuilder();
    }

    echo json_encode($pokemons, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $erro) {
    error_log("Erro ao listar Pokémon: " . $erro->getMessage());
    http_response_code(500);
    echo json_encode(
        ["erro" => "Não foi possível listar os Pokémon."],
        JSON_UNESCAPED_UNICODE
    );
}