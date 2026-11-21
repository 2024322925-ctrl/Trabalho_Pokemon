
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

            // Prepara os dados visuais dos Pokémon selecionados
            $pokemonsSelecionados = array_map(
                fn($pokemon) => $this->prepararDadosVisuais(
                    $pokemon,
                    $idPokemonInicial
                ),
                $pokemonsSelecionados
            );

            // Recupera o Pokémon inicial já preparado
            foreach ($pokemonsSelecionados as $pokemon) {
                if ($pokemon["pokemon_ativo"]) {
                    $pokemonInicial = $pokemon;
                    break;
                }
            }

            // Prepara os dados visuais do adversário
            $pokemonAdversario = $this->prepararDadosVisuais(
                $pokemonsFase[0],
                0,
                true
            );

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
                    : $pokemon["imagem"]
            ]);
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

    }

    ?>
