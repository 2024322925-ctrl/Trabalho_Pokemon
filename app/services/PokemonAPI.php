<?php

class PokemonAPI
{
    private $urlBase = "https://pokeapi.co/api/v2/pokemon/";

    public function buscarPokemon($nomeOuId)
    {
        $url = $this->urlBase . strtolower($nomeOuId);

        $resposta = @file_get_contents($url);

        if ($resposta === false) {
            throw new Exception("Não foi possível acessar a PokéAPI.");
        }

        $dados = json_decode($resposta, true);

        if ($dados === null) {
            throw new Exception("Não foi possível interpretar os dados da API.");
        }

        $pokemon = [
            "nome" => $dados["name"],
            "hp" => 0,
            "hp_atual" => 0,
            "velocidade" => 0,
            "ataque" => 0,
            "defesa" => 0,
            "ataque_especial" => 0,
            "defesa_especial" => 0,
            "status_atual" => null,
            "ativo" => true,
            "imagem" => $dados["sprites"]["versions"]["generation-v"]["black-white"]["animated"]["front_default"]
            ?? $dados["sprites"]["front_default"],
            "tipos" => []
        ];

        foreach ($dados["stats"] as $stat) {
            switch ($stat["stat"]["name"]) {
                case "hp":
                    $pokemon["hp"] = $stat["base_stat"];
                    $pokemon["hp_atual"] = $stat["base_stat"];
                    break;

                case "speed":
                    $pokemon["velocidade"] = $stat["base_stat"];
                    break;

                case "attack":
                    $pokemon["ataque"] = $stat["base_stat"];
                    break;

                case "defense":
                    $pokemon["defesa"] = $stat["base_stat"];
                    break;

                case "special-attack":
                    $pokemon["ataque_especial"] = $stat["base_stat"];
                    break;

                case "special-defense":
                    $pokemon["defesa_especial"] = $stat["base_stat"];
                    break;
            }
        }

        foreach ($dados["types"] as $tipo) {
            $pokemon["tipos"][] = $tipo["type"]["name"];
        }

        return $pokemon;
    }
}