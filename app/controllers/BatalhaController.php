
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

    // Valida se o Pokémon possui o ataque selecionado
    public function validarAtaque($idPokemon, $idAtaque)
    {
        $ataques = $this->listarAtaquesDoPokemon($idPokemon);

        foreach ($ataques as $ataque) {
            if ((int) $ataque["id_ataque"] === (int) $idAtaque) {
                return $ataque;
            }
        }

        return false;
    }


    
    public function calcularDano($atacante, $defensor, $ataque)
    {
        $nivel = (int) ($atacante["nivel"] ?? $atacante["nivel_adversario"] ?? 1);
        $poder = (int) ($ataque["poder"] ?? 0);

        // Identifica os atributos conforme a categoria
        if ($ataque["categoria"] === "physical") {
            $atributoAtaque = (int) $atacante["ataque"];
            $atributoDefesa = (int) $defensor["defesa"];

        } elseif ($ataque["categoria"] === "special") {
            $atributoAtaque = (int) $atacante["ataque_especial"];
            $atributoDefesa = (int) $defensor["defesa_especial"];

        } else {
            return 0;
        }

        if ($poder <= 0 || $atributoDefesa <= 0) {
            return 0;
        }

        // Calcula a efetividade contra os tipos do defensor
        $efetividade = $this->calcularEfetividadeTipos(
            $ataque["id_tipo"],
            $defensor["tipos"] ?? []
        );

        // Ataque sem efeito
        if ($efetividade === 0.0 || $efetividade === 0) {
            return 0;
        }

        // Fórmula base de dano
        $danoBase = (
            (
                (2 * $nivel / 5 + 2)
                * $poder
                * $atributoAtaque
                / $atributoDefesa
            ) / 50
        ) + 2;

        // Verifica STAB
        $stab = 1;

        foreach (($atacante["tipos"] ?? []) as $tipo) {
            if ((int) $tipo["id_tipo"] === (int) $ataque["id_tipo"]) {
                $stab = 1.5;
                break;
            }
        }

        // Variação aleatória de 90% a 110%
        $variacao = random_int(90, 110) / 100;

        // Calcula o dano final
        $dano = round(
            $danoBase
            * $stab
            * $efetividade
            * $variacao
        );

        return max(1, $dano);
    }



    // Atualiza o HP atual de um Pokémon do jogador
    public function atualizarHPPokemon($idTime, $idPokemon, $hpAtual)
    {
        return $this->batalhaDAO->atualizarHPPokemon(
            $idTime,
            $idPokemon,
            $hpAtual
        );
    }

    // Inicia uma nova batalha e recupera o HP dos Pokémon selecionados
    public function iniciarBatalha($idUsuario, $preparacao)
    {
        $idTime = (int) ($preparacao["id_time"] ?? 0);
        $idsSelecionados = $preparacao["pokemons"] ?? [];

        if (!is_array($idsSelecionados) || count($idsSelecionados) !== 3) {
            return false;
        }

        $timeAtivo = $this->buscarTimeAtivo($idUsuario);

        if (
            !$timeAtivo ||
            (int) $timeAtivo["id_time"] !== $idTime
        ) {
            return false;
        }

        $pokemonsTime = $this->listarPokemonsDoTime($idUsuario);

        $idsDisponiveis = array_map(
            fn($pokemon) => (int) $pokemon["id_pokemon"],
            $pokemonsTime
        );

        foreach ($idsSelecionados as $idPokemon) {
            if (!in_array((int) $idPokemon, $idsDisponiveis, true)) {
                return false;
            }
        }

        return $this->batalhaDAO->recuperarHPDosPokemonsSelecionados(
            $idTime,
            $idsSelecionados
        );
    }

    // Prepara os dados necessários para iniciar uma batalha
    public function prepararBatalha($idUsuario, $preparacao)
    {
        $idFase = (int) ($preparacao["id_fase"] ?? 0);
        $idTime = (int) ($preparacao["id_time"] ?? 0);

        $idsSelecionados = $preparacao["pokemons"] ?? [];
        $idPokemonInicial = (int) ($preparacao["pokemon_inicial"] ?? 0);

        // Verifica se a fase é válida
        if ($idFase < 1 || $idFase > 8) {
            return ["sucesso" => false];
        }

        // Verifica se foram selecionados exatamente 3 Pokémon diferentes
        if (!is_array($idsSelecionados)) {
            return ["sucesso" => false];
        }

        $idsSelecionados = array_values(
            array_unique(array_map("intval", $idsSelecionados))
        );

        if (
            count($idsSelecionados) !== 3 ||
            !in_array($idPokemonInicial, $idsSelecionados, true)
        ) {
            return ["sucesso" => false];
        }

        // Verifica se o time continua ativo
        $timeAtivo = $this->buscarTimeAtivo($idUsuario);

        if (
            !$timeAtivo ||
            (int) $timeAtivo["id_time"] !== $idTime
        ) {
            return ["sucesso" => false];
        }

        // Busca os Pokémon do time
        $pokemonsTime = $this->listarPokemonsDoTime($idUsuario);

        // Recupera somente os três Pokémon selecionados
        $pokemonsSelecionados = [];

        foreach ($idsSelecionados as $idSelecionado) {
            foreach ($pokemonsTime as $pokemon) {
                if ((int) $pokemon["id_pokemon"] === $idSelecionado) {
                    $pokemonsSelecionados[] = $pokemon;
                    break;
                }
            }
        }

        // Confere se os três Pokémon ainda pertencem ao time
        if (count($pokemonsSelecionados) !== 3) {
            return ["sucesso" => false];
        }

        // Identifica o Pokémon inicial
        $pokemonInicial = null;

        foreach ($pokemonsSelecionados as $pokemon) {
            if ((int) $pokemon["id_pokemon"] === $idPokemonInicial) {
                $pokemonInicial = $pokemon;
                break;
            }
        }

        if (!$pokemonInicial) {
            return ["sucesso" => false];
        }

        // Busca os adversários da fase
        $pokemonsFase = $this->listarPokemonsDaFase($idFase);

        if (count($pokemonsFase) !== 3) {
            return ["sucesso" => false];
        }

        // Prepara os dados visuais e os ataques dos Pokémon selecionados
        $pokemonsSelecionados = array_map(
            fn($pokemon) => $this->prepararDadosVisuais(
                $pokemon,
                $idPokemonInicial
            ),
            $pokemonsSelecionados
        );

        // Carrega os ataques e tipos dos Pokémon selecionados
        foreach ($pokemonsSelecionados as &$pokemon) {
            $pokemon["ataques"] = $this->listarAtaquesDoPokemon(
                $pokemon["id_pokemon"]
            );

            $pokemon["tipos"] = $this->listarTiposDoPokemon(
                $pokemon["id_pokemon"]
            );
        }
        unset($pokemon);

        // Recupera o Pokémon inicial já preparado
        foreach ($pokemonsSelecionados as $pokemon) {
            if ($pokemon["pokemon_ativo"]) {
                $pokemonInicial = $pokemon;
                break;
            }
        }

        // Carrega os ataques e tipos de todos os adversários
        foreach ($pokemonsFase as &$pokemon) {
            $pokemon["ataques"] = $this->listarAtaquesDoPokemon(
                $pokemon["id_pokemon"]
            );

            $pokemon["tipos"] = $this->listarTiposDoPokemon(
                $pokemon["id_pokemon"]
            );
        }
        unset($pokemon);

        // Prepara os dados visuais dos adversários
        $pokemonsFase = array_map(
            fn($pokemon) => $this->prepararDadosVisuais(
                $pokemon,
                0,
                true
            ),
            $pokemonsFase
        );

        // Recupera o primeiro adversário
        $pokemonAdversario = $pokemonsFase[0];

        return [
            "sucesso" => true,
            "id_fase" => $idFase,
            "pokemons_selecionados" => $pokemonsSelecionados,
            "pokemon_inicial" => $pokemonInicial,
            "pokemons_fase" => $pokemonsFase,
            "pokemon_adversario" => $pokemonAdversario
        ];
    }
    
    // Prepara os dados visuais de um Pokémon para a batalha
    private function prepararDadosVisuais($pokemon, $idPokemonInicial, $adversario = false)
    {
        $hpMaximo = (int) $pokemon["hp"];

        $hpAtual = $adversario
            ? $hpMaximo
            : (int) $pokemon["hp_atual"];

        $porcentagemHp = $hpMaximo > 0
            ? max(0, min(100, ($hpAtual / $hpMaximo) * 100))
            : 0;

        return array_merge($pokemon, [
            "hp_maximo" => $hpMaximo,
            "hp_atual_exibicao" => $hpAtual,
            "porcentagem_hp" => $porcentagemHp,

            "pokemon_ativo" => (int) $pokemon["id_pokemon"] === $idPokemonInicial,

            "imagem_exibicao" => !$adversario && !empty($pokemon["imagem_costas"])
                ? $pokemon["imagem_costas"]
                : $pokemon["imagem"],

            // Estágios iniciais dos atributos
            "estagios" => [
                "ataque" => 0,
                "defesa" => 0,
                "ataque_especial" => 0,
                "defesa_especial" => 0,
                "velocidade" => 0
            ]
        ]);
    }

    public function calcularNovoEstagio($estagioAtual, $alteracao)
    {
        $novoEstagio = (int) $estagioAtual + (int) $alteracao;

        return max(-3, min(3, $novoEstagio));
    }


    // Carrega os dados necessários para a tela de seleção
    public function carregarSelecao($idUsuario)
    {
        $timeAtivo = $this->buscarTimeAtivo($idUsuario);
        $pokemons = $this->listarPokemonsDoTime($idUsuario);

        return [
            "time_ativo" => $timeAtivo,
            "pokemons" => $pokemons
        ];
    }

    // Valida os Pokémon escolhidos pelo jogador
    public function validarSelecao($timeAtivo, $pokemons, $dadosFormulario)
    {
        if (!$timeAtivo) {
            return [
                "sucesso" => false,
                "erro" => "Você precisa ter um time ativo para participar da batalha."
            ];
        }

        $selecionados = $dadosFormulario["pokemons"] ?? [];

        $pokemonInicial = isset($dadosFormulario["pokemon_inicial"])
            ? (int) $dadosFormulario["pokemon_inicial"]
            : 0;

        if (!is_array($selecionados)) {
            $selecionados = [];
        }

        $selecionados = array_values(
            array_unique(array_map("intval", $selecionados))
        );

        $idsDisponiveis = array_map(
            fn($pokemon) => (int) $pokemon["id_pokemon"],
            $pokemons
        );

        if (count($selecionados) !== 3) {
            return [
                "sucesso" => false,
                "erro" => "Selecione exatamente 3 Pokémon do seu time."
            ];
        }

        foreach ($selecionados as $idPokemon) {
            if (!in_array($idPokemon, $idsDisponiveis, true)) {
                return [
                    "sucesso" => false,
                    "erro" => "Seleção inválida. Escolha Pokémon pertencentes ao seu time."
                ];
            }
        }

        if (!in_array($pokemonInicial, $selecionados, true)) {
            return [
                "sucesso" => false,
                "erro" => "Escolha qual dos três Pokémon começará a batalha."
            ];
        }

        return [
            "sucesso" => true,
            "preparacao" => [
                "pokemons" => $selecionados,
                "pokemon_inicial" => $pokemonInicial,
                "id_time" => (int) $timeAtivo["id_time"]
            ]
        ];
    }


    
    public function calcularEfetividadeTipos($idTipoAtaque, $tiposDefensor)
    {
        // Matriz de efetividade dos tipos
        // IDs correspondem à tabela tipo do banco

        $efetividade = [

            // Grass (1)
            1 => [
                1 => 0.5, 2 => 0.5, 4 => 0.5, 5 => 0.5,
                6 => 2, 7 => 0.5, 9 => 2, 13 => 2,
                14 => 0.5, 17 => 0.5
            ],

            // Poison (2)
            2 => [
                1 => 2, 2 => 0.5, 9 => 0.5, 13 => 0.5,
                14 => 0, 16 => 0.5, 10 => 2
            ],

            // Normal (3)
            3 => [
                13 => 0.5, 14 => 0.5, 16 => 0
            ],

            // Fire (4)
            4 => [
                1 => 2, 4 => 0.5, 6 => 0.5, 7 => 2,
                13 => 0.5, 14 => 2, 15 => 2, 17 => 0.5
            ],

            // Flying (5)
            5 => [
                1 => 2, 7 => 2, 11 => 2, 8 => 0.5,
                13 => 0.5, 14 => 0.5
            ],

            // Water (6)
            6 => [
                1 => 0.5, 4 => 2, 6 => 0.5, 9 => 2,
                13 => 2, 17 => 0.5
            ],

            // Bug (7)
            7 => [
                1 => 2, 2 => 0.5, 4 => 0.5, 5 => 0.5,
                11 => 0.5, 12 => 2, 14 => 0.5,
                16 => 0.5, 10 => 0.5
            ],

            // Electric (8)
            8 => [
                5 => 2, 6 => 2, 8 => 0.5, 1 => 0.5,
                9 => 0, 17 => 0.5
            ],

            // Ground (9)
            9 => [
                1 => 0.5, 4 => 2, 8 => 2, 9 => 2,
                13 => 2, 14 => 2, 5 => 0
            ],

            // Fairy (10)
            10 => [
                4 => 0.5, 2 => 0.5, 11 => 2,
                14 => 0.5, 17 => 2
            ],

            // Fighting (11)
            11 => [
                3 => 2, 5 => 0.5, 10 => 0.5, 11 => 0.5,
                12 => 0.5, 13 => 2, 16 => 0,
                14 => 2, 15 => 2
            ],

            // Psychic (12)
            12 => [
                2 => 2, 11 => 2, 12 => 0.5,
                14 => 0.5
            ],

            // Rock (13)
            13 => [
                1 => 2, 4 => 2, 5 => 2, 7 => 2,
                11 => 0.5, 9 => 0.5, 14 => 0.5
            ],

            // Steel (14)
            14 => [
                4 => 0.5, 6 => 0.5, 8 => 0.5,
                13 => 2, 15 => 2, 10 => 2,
                14 => 0.5
            ],

            // Ice (15)
            15 => [
                1 => 2, 4 => 0.5, 5 => 2, 6 => 0.5,
                9 => 2, 14 => 0.5, 15 => 0.5, 17 => 2
            ],

            // Ghost (16)
            16 => [
                3 => 0, 12 => 2, 16 => 2
            ],

            // Dragon (17)
            17 => [
                10 => 0, 14 => 0.5, 17 => 2
            ]
        ];

        $idTipoAtaque = (int) $idTipoAtaque;
        $multiplicador = 1;

        foreach ($tiposDefensor as $tipoDefensor) {
            $idTipoDefensor = (int) $tipoDefensor["id_tipo"];

            $multiplicador *=
                $efetividade[$idTipoAtaque][$idTipoDefensor] ?? 1;
        }

        return $multiplicador;
    }


}

?>
