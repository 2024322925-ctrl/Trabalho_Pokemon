
<?php

class PokemonAPI
{
    private $urlBase = "https://pokeapi.co/api/v2/pokemon/";
    private $cacheAtaques = [];

    // Faz uma requisição e transforma a resposta em array
    private function buscarDadosApi($url)
    {
        $resposta = file_get_contents($url);

        if ($resposta === false) {
            throw new Exception("Não foi possível acessar a PokéAPI: " . $url);
        }

        $dados = json_decode($resposta, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Não foi possível interpretar os dados da API.");
        }

        return $dados;
    }

    public function buscarPokemon($nomeOuId)
    {
        $url = $this->urlBase . strtolower($nomeOuId);

        $dados = $this->buscarDadosApi($url);

        // ID original do Pokémon na PokéAPI
        $idPokemon = $dados["id"];

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

            // Imagem animada de frente
            "imagem" => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/showdown/" . $idPokemon . ".gif",

            // Imagem animada de costas
            "imagem_costas" => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/showdown/back/" . $idPokemon . ".gif",

            "tipos" => [],
            "ataques" => []
        ];

        // Busca os atributos do Pokémon
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

        // Busca os tipos do Pokémon
        foreach ($dados["types"] as $tipo) {
            $pokemon["tipos"][] = $tipo["type"]["name"];
        }

        // Busca os quatro ataques do Pokémon
        $pokemon["ataques"] = $this->buscarAtaquesPokemon(
            $dados["moves"],
            $pokemon["tipos"],
            $dados["name"]
        );

        return $pokemon;
    }

    private function buscarAtaqueDetalhado($url)
    {
        // Evita buscar novamente um ataque já consultado
        if (!isset($this->cacheAtaques[$url])) {
            $this->cacheAtaques[$url] = $this->buscarDadosApi($url);
        }

        return $this->cacheAtaques[$url];
    }

    
    private function buscarAtaquesPokemon($movimentos, $tipos, $nomePokemon)
    {
        $tipoPrincipal = $tipos[0] ?? null;
        $tipoSecundario = $tipos[1] ?? $tipoPrincipal;
        $ehDitto = in_array("ditto", $tipos, true);

        $ataqueStatus = null;
        $ataqueNormal = null;
        $ataqueSecundario = null;
        $ataquesPrincipais = [];
        $movimentosOfensivos = [];
        
        // Verifica se o Ditto possui o movimento Transform
        $ataqueTransform = null;

        if ($nomePokemon === "ditto") {
            foreach ($movimentos as $movimento) {
                if ($movimento["move"]["name"] === "transform") {
                    $detalhes = $this->buscarAtaqueDetalhado(
                        $movimento["move"]["url"]
                    );

                    $ataqueTransform = $this->formatarAtaque($detalhes, 1);
                    break;
                }
            }
        }

        foreach ($movimentos as $movimento) {
            $detalhes = $this->buscarAtaqueDetalhado($movimento["move"]["url"]);

            $tipo = $detalhes["type"]["name"];
            $categoria = $detalhes["damage_class"]["name"];
            $poder = $detalhes["power"] ?? 0;

            $efeitos = $this->extrairEfeitos($detalhes);

            // Guarda movimentos que causam dano para usar como fallback
            if (
                ($poder > 0 && in_array($categoria, ["physical", "special"], true)) ||
                ($categoria === "status" && $efeitos["possui_efeito"])
            ) {
                $movimentosOfensivos[] = $this->formatarAtaque($detalhes, 0);
            }

            // 1. Ataque de status compatível com o tipo do Pokémon
            if (
                $ataqueStatus === null &&
                $categoria === "status" &&
                in_array($tipo, $tipos, true) &&
                $efeitos["possui_efeito"]
            ) {
                $ataqueStatus = $this->formatarAtaque($detalhes, 1);
            }

            // 2. Ataque físico do tipo Normal
            if (
                $ataqueNormal === null &&
                $tipo === "normal" &&
                $categoria === "physical" &&
                $poder > 0
            ) {
                $ataqueNormal = $this->formatarAtaque($detalhes, 2);
            }

            // 3. Ataques especiais do tipo principal
            if ($tipo === $tipoPrincipal && $categoria === "special" && $poder > 0) {
                $ataquesPrincipais[] = $this->formatarAtaque($detalhes, 3);
            }

            // 4. Ataque especial do segundo tipo
            if (
                count($tipos) > 1 &&
                $ataqueSecundario === null &&
                $tipo === $tipoSecundario &&
                $categoria === "special" &&
                $poder > 0
            ) {
                $ataqueSecundario = $this->formatarAtaque($detalhes, 4);
            }
        }

        $ataques = [];
        // Transform ocupa obrigatoriamente a primeira posição do Ditto
        if ($ataqueTransform !== null) {
            $ataques[1] = $ataqueTransform;
        }

        // Adiciona os ataques encontrados nas posições prioritárias
        if ($ataqueStatus !== null) {
            $ataques[1] = $ataqueStatus;
        }

        if ($ataqueNormal !== null) {
            $ataques[2] = $ataqueNormal;
        }

        if (isset($ataquesPrincipais[0])) {
            $ataques[3] = $ataquesPrincipais[0];
            $ataques[3]["posicao"] = 3;
        }

        if ($ataqueSecundario !== null) {
            $ataques[4] = $ataqueSecundario;
        } elseif (isset($ataquesPrincipais[1])) {
            $ataques[4] = $ataquesPrincipais[1];
            $ataques[4]["posicao"] = 4;
        }

        // Registra os nomes já utilizados para evitar repetição
        $nomesUsados = [];

        foreach ($ataques as $ataque) {
            $nomesUsados[] = $ataque["nome"];
        }

        if ($ataqueTransform !== null) {
            $nomesUsados[] = $ataqueTransform["nome"];
        }

        // Procura um movimento ofensivo para preencher uma posição vazia
        $adicionarFallback = function ($posicao, $categorias = []) use (
            &$ataques,
            &$nomesUsados,
            $movimentosOfensivos
        ) {
            foreach ($movimentosOfensivos as $movimento) {
                if (in_array($movimento["nome"], $nomesUsados, true)) {
                    continue;
                }

                if (
                    !empty($categorias) &&
                    !in_array($movimento["categoria"], $categorias, true)
                ) {
                    continue;
                }

                $movimento["posicao"] = $posicao;

                $ataques[$posicao] = $movimento;
                $nomesUsados[] = $movimento["nome"];

                return true;
            }

            return false;
        };

        // Fallback da posição 2: tenta outro ataque físico
        if (!isset($ataques[2])) {
            if (!$adicionarFallback(2, ["physical"])) {
                $adicionarFallback(2);
            }
        }

        // Fallback da posição 3: tenta outro ataque especial
        if (!isset($ataques[3])) {
            if (!$adicionarFallback(3, ["special"])) {
                $adicionarFallback(3);
            }
        }

        // Fallback da posição 4: tenta outro ataque especial
        if (!isset($ataques[4])) {
            if (!$adicionarFallback(4, ["special"])) {
                $adicionarFallback(4);
            }
        }

        // Fallback da posição 1: tenta qualquer ataque ofensivo
        if (!isset($ataques[1])) {
            $adicionarFallback(1);
        }

        // Última tentativa: preenche qualquer posição que ainda esteja vazia
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($ataques[$i])) {
                $adicionarFallback($i);
            }
        }

        ksort($ataques);

        return array_values($ataques);
    }


    // Extrai os efeitos reais fornecidos pela PokéAPI
    private function extrairEfeitos($dados)
    {
        $meta = $dados["meta"] ?? [];

        // Condições de status
        $ailment = $meta["ailment"]["name"] ?? "none";

        $traducaoStatus = [
            "burn" => "queimado",
            "poison" => "envenenado",
            "paralysis" => "paralisado",
            "sleep" => "dormindo",
            "freeze" => "congelado"
        ];

        $status = $traducaoStatus[$ailment] ?? null;

        // Alterações de atributos
        $alteracoes = [];

        foreach ($dados["stat_changes"] ?? [] as $alteracao) {
            $alteracoes[] = [
                "atributo" => $alteracao["stat"]["name"],
                "alteracao" => $alteracao["change"]
            ];
        }

        // Chance de hesitação
        $chanceHesitacao = $meta["flinch_chance"] ?? 0;

        // Chance do efeito secundário
        $chanceEfeito = $dados["effect_chance"] ?? 0;

        // Chance específica de aplicar condição de status
        $chanceStatus = $meta["ailment_chance"] ?? 0;

        // Verifica se o movimento possui algum efeito
        $possuiEfeito = (
            $status !== null ||
            !empty($alteracoes) ||
            $chanceHesitacao > 0
        );

        return [
            "possui_efeito" => $possuiEfeito,
            "status" => $status,
            "chance_status" => $chanceStatus,
            "alteracoes_atributos" => $alteracoes,
            "chance_efeito" => $chanceEfeito,
            "chance_hesitacao" => $chanceHesitacao
        ];
    }

    private function formatarAtaque($dados, $posicao)
    {
        // Extrai os efeitos do ataque
        $efeitos = $this->extrairEfeitos($dados);

        return [
            "posicao" => $posicao,
            "nome" => $dados["name"],
            "tipo" => $dados["type"]["name"],
            "poder" => $dados["power"] ?? 0,
            "precisao" => $dados["accuracy"],
            "categoria" => $dados["damage_class"]["name"],

            // Mantém os campos antigos
            "efeito_status" => $efeitos["status"],
            "chance_efeito" => $efeitos["chance_status"],

            // Guarda todos os dados dos efeitos em JSON
            "dados_efeito" => $efeitos
        ];
    }
}

?>
