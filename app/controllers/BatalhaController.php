<?php



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


    public function finalizarBatalha(
        $idUsuario,
        $idTime,
        $idFase,
        $resultado,
        array $idsPokemons,
        $chaveBatalha
    ) {
        return $this->batalhaDAO->finalizarBatalha(
            $idUsuario,
            $idTime,
            $idFase,
            $resultado,
            $idsPokemons,
            $chaveBatalha
        );
    }


    public function calcularDano($atacante, $defensor, $ataque, $efetividade = null)
    {
        $nivel = (int) ($atacante["nivel"] ?? $atacante["nivel_adversario"] ?? 1);
        $poder = (int) ($ataque["poder"] ?? 0);

        // Facade: dobra o poder se o atacante estiver com uma condição de status
        $nomeAtaque = strtolower(trim($ataque["nome"] ?? ""));

        // Facade: dobra o poder se o atacante estiver com uma condição de status
        if (
            $nomeAtaque === "facade" &&
            in_array(
                $atacante["status_atual"] ?? null,
                ["queimado", "envenenado", "paralisado"],
                true
            )
        ) {
            $poder *= 2;
        }

        // Venoshock: dobra o poder se o defensor estiver envenenado
        if (
            $nomeAtaque === "venoshock" &&
            ($defensor["status_atual"] ?? null) === "envenenado"
        ) {
            $poder *= 2;
        }



        // Identifica os atributos conforme a categoria
        if ($ataque["categoria"] === "physical") {
            $atributoAtaque = (int) $atacante["ataque"];
            $atributoDefesa = (int) $defensor["defesa"];

            $estagioAtaque = $atacante["estagios"]["ataque"] ?? 0;
            $estagioDefesa = $defensor["estagios"]["defesa"] ?? 0;
        } elseif ($ataque["categoria"] === "special") {
            $atributoAtaque = (int) $atacante["ataque_especial"];
            $atributoDefesa = (int) $defensor["defesa_especial"];

            $estagioAtaque = $atacante["estagios"]["ataque_especial"] ?? 0;
            $estagioDefesa = $defensor["estagios"]["defesa_especial"] ?? 0;
        } else {
            return 0;
        }

        if ($poder <= 0 || $atributoDefesa <= 0) {
            return 0;
        }

        // Aplica os multiplicadores dos estágios
        $atributoAtaque *= $this->calcularMultiplicadorEstagio($estagioAtaque);
        $atributoDefesa *= $this->calcularMultiplicadorEstagio($estagioDefesa);

        // Calcula a efetividade somente se ela não foi informada
        if ($efetividade === null) {
            $efetividade = $this->calcularEfetividadeTipos($ataque, $defensor);
        }

        // Ataque sem efeito
        if ($efetividade == 0) {
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
        $multiplicadorQueimadura = 1;

        if (
            $ataque["categoria"] === "physical" &&
            ($atacante["status_atual"] ?? null) === "queimado"
        ) {
            $multiplicadorQueimadura = 0.5;
        }

        // Calcula o dano final
        $dano = round(
            $danoBase
                * $stab
                * $efetividade
                * $variacao
                * $multiplicadorQueimadura
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

    public function atualizarEstadoPokemon(
        $idTime,
        $idPokemon,
        $hpAtual,
        $statusAtual
    ) {
        return $this->batalhaDAO->atualizarEstadoPokemon(
            $idTime,
            $idPokemon,
            $hpAtual,
            $statusAtual
        );
    }

    // Recupera o estado dos Pokémon selecionados ao finalizar a batalha
    public function recuperarEstadoDosPokemonsSelecionados($idTime, array $idsPokemons)
    {
        return $this->batalhaDAO->recuperarEstadoDosPokemonsSelecionados(
            $idTime,
            $idsPokemons
        );
    }


    // Concede XP aos Pokémon selecionados
    public function concederExperienciaPokemons($idTime, array $idsPokemons, $xpRecebido)
    {
        return $this->batalhaDAO->concederExperienciaPokemons(
            $idTime,
            $idsPokemons,
            $xpRecebido
        );
    }


    public function buscarRecompensasFase($idFase)
    {
        return $this->batalhaDAO->buscarRecompensasFase($idFase);
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
            "hp_atual" => $hpAtual,
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
                "velocidade" => 0,
                "precisao" => 0,
                "evasao" => 0
            ]
        ]);
    }

    public function calcularNovoEstagio($estagioAtual, $alteracao)
    {
        $novoEstagio = (int) $estagioAtual + (int) $alteracao;

        return max(-3, min(3, $novoEstagio));
    }

    // Calcula o multiplicador de um estágio de atributo
    public function calcularMultiplicadorEstagio($estagio)
    {
        $multiplicadores = [
            -3 => 0.4,
            -2 => 0.5,
            -1 => 2 / 3,
            0 => 1,
            1 => 1.5,
            2 => 2,
            3 => 2.5
        ];

        $estagio = max(-3, min(3, (int) $estagio));

        return $multiplicadores[$estagio];
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



    private function calcularEfetividadeTipos(array $ataque, array $defensor): float
    {
        $tipoAtaque = TipoPokemon::porNome($ataque["tipo"]);
        return Pokemon::doBanco($defensor)->multiplicadorRecebidoDe($tipoAtaque);
    }

    // Aplica alterações nos estágios dos atributos de um Pokémon
    // Aplica alterações nos estágios dos atributos de um Pokémon
    public function aplicarAlteracoesAtributos($pokemon, $alteracoes)
    {
        $atributos = [
            "attack" => "ataque",
            "defense" => "defesa",
            "special_attack" => "ataque_especial",
            "special_defense" => "defesa_especial",
            "speed" => "velocidade",
            "accuracy" => "precisao",
            "evasion" => "evasao"
        ];

        $nomesAtributos = [
            "ataque" => "Ataque",
            "defesa" => "Defesa",
            "ataque_especial" => "Ataque Especial",
            "defesa_especial" => "Defesa Especial",
            "velocidade" => "Velocidade",
            "precisao" => "Precisão",
            "evasao" => "Evasão"
        ];

        if (!isset($pokemon["estagios"])) {
            $pokemon["estagios"] = [
                "ataque" => 0,
                "defesa" => 0,
                "ataque_especial" => 0,
                "defesa_especial" => 0,
                "velocidade" => 0,
                "precisao" => 0,
                "evasao" => 0
            ];
        }

        $mensagens = [];

        foreach ($alteracoes as $alteracao) {
            $atributoBanco = $alteracao["atributo"] ?? null;

            // Padroniza os nomes recebidos do banco
            $atributoBanco = str_replace("-", "_", (string) $atributoBanco);
            $valorAlteracao = (int) ($alteracao["alteracao"] ?? 0);

            if (
                !isset($atributos[$atributoBanco]) ||
                $valorAlteracao === 0
            ) {
                continue;
            }

            $atributo = $atributos[$atributoBanco];

            $estagioAtual = $pokemon["estagios"][$atributo] ?? 0;

            $novoEstagio = $this->calcularNovoEstagio(
                $estagioAtual,
                $valorAlteracao
            );

            $pokemon["estagios"][$atributo] = $novoEstagio;

            if ($novoEstagio === $estagioAtual) {
                $mensagens[] = $nomesAtributos[$atributo]
                    . " não pode mais "
                    . ($valorAlteracao > 0 ? "aumentar!" : "diminuir!");
            } else {
                $mensagens[] = $nomesAtributos[$atributo]
                    . ($valorAlteracao > 0 ? " aumentou!" : " diminuiu!");
            }
        }

        return [
            "pokemon" => $pokemon,
            "mensagens" => $mensagens
        ];
    }

    public function selecionarAtaqueAleatorio($pokemonAdversario)
    {
        $ataques = $pokemonAdversario["ataques"] ?? [];

        if (empty($ataques)) {
            return false;
        }

        $indice = array_rand($ataques);

        return $ataques[$indice];
    }

    // Calcula a velocidade considerando os estágios do Pokémon
    public function calcularVelocidadeEfetiva($pokemon)
    {
        $velocidade = (int) ($pokemon["velocidade"] ?? 0);

        $estagioVelocidade = $pokemon["estagios"]["velocidade"] ?? 0;

        return $velocidade * $this->calcularMultiplicadorEstagio(
            $estagioVelocidade
        );
    }


    // Define quem realizará o primeiro ataque
    public function definirOrdemTurnos($pokemonJogador, $pokemonAdversario)
    {
        $velocidadeJogador = $this->calcularVelocidadeEfetiva(
            $pokemonJogador
        );

        $velocidadeAdversario = $this->calcularVelocidadeEfetiva(
            $pokemonAdversario
        );

        // O Pokémon mais rápido começa
        if ($velocidadeJogador > $velocidadeAdversario) {
            return [
                "primeiro" => "jogador",
                "segundo" => "adversario"
            ];
        }

        if ($velocidadeAdversario > $velocidadeJogador) {
            return [
                "primeiro" => "adversario",
                "segundo" => "jogador"
            ];
        }

        // Em caso de empate, sorteia quem começa
        $primeiro = random_int(0, 1) === 0
            ? "jogador"
            : "adversario";

        return [
            "primeiro" => $primeiro,
            "segundo" => $primeiro === "jogador"
                ? "adversario"
                : "jogador"
        ];
    }

    // Verifica se um Pokémon consegue agir


    public function verificarCondicaoDeAcao(&$pokemon)
    {
        $status = $pokemon["status_atual"] ?? null;

        // Verifica hesitação
        if (!empty($pokemon["hesitou"])) {
            unset($pokemon["hesitou"]);

            return [
                "pode_agir" => false,
                "mensagem" => $pokemon["nome"]
                    . " hesitou e não conseguiu atacar!"
            ];
        }

        // Verifica se está preso pelo Bind
        if (isset($pokemon["turnos_presos"])) {
            if ($pokemon["turnos_presos"] > 0) {
                $pokemon["turnos_presos"]--;

                return [
                    "pode_agir" => false,
                    "mensagem" => $pokemon["nome"]
                        . " está preso pelo Bind e não pode atacar!"
                ];
            }

            unset($pokemon["turnos_presos"]);
        }

        // Verifica sono
        if ($status === "dormindo") {
            $turnosRestantes = (int) ($pokemon["turnos_sono"] ?? 1);

            if ($turnosRestantes > 0) {
                $pokemon["turnos_sono"] = $turnosRestantes - 1;

                if ($pokemon["turnos_sono"] <= 0) {
                    $pokemon["status_atual"] = null;
                    unset($pokemon["turnos_sono"]);
                } else {
                    return [
                        "pode_agir" => false,
                        "mensagem" => $pokemon["nome"]
                            . " está dormindo e não pode atacar!"
                    ];
                }
            } else {
                $pokemon["status_atual"] = null;
                unset($pokemon["turnos_sono"]);
            }
        }

        // Verifica paralisia
        if (($pokemon["status_atual"] ?? null) === "paralisado") {
            $naoPodeAgir = random_int(1, 100) <= 25;

            if ($naoPodeAgir) {
                return [
                    "pode_agir" => false,
                    "mensagem" => $pokemon["nome"]
                        . " está paralisado e não conseguiu se mover!"
                ];
            }
        }

        // Verifica se precisa recarregar após usar Hyper Beam
        if (!empty($pokemon["precisa_recarregar"])) {
            unset($pokemon["precisa_recarregar"]);

            return [
                "pode_agir" => false,
                "mensagem" => $pokemon["nome"]
                    . " precisa recarregar após usar Hyper Beam!"
            ];
        }

        return [
            "pode_agir" => true,
            "mensagem" => null
        ];
    }


    // Executa um ataque e aplica seus efeitos

    public function executarAtaque(
        &$atacante,
        &$defensor,
        $ataque,
        $alvoAindaVaiAgir = true
    ) {
        $mensagensEfeito = [];
        $mensagem = "";
        $mensagemEfetividade = "";
        $dano = 0;
        $efetividade = 1;

        // Verifica se o Pokémon já estava carregando um ataque
        $liberandoRazorWind = false;

        if (
            isset($atacante["ataque_carregado"]) &&
            is_array($atacante["ataque_carregado"])
        ) {
            // Recupera o ataque carregado no turno anterior
            $ataque = $atacante["ataque_carregado"];

            unset($atacante["ataque_carregado"]);

            $liberandoRazorWind = true;
        }

        // Padroniza o nome do ataque
        $nomeAtaque = strtolower(trim($ataque["nome"] ?? ""));
        $nomeAtaque = str_replace([" ", "_"], "-", $nomeAtaque);

        // Primeiro turno do Razor Wind: começa a carregar
        if (
            $nomeAtaque === "razor-wind" &&
            !$liberandoRazorWind
        ) {
            $atacante["ataque_carregado"] = $ataque;

            return [
                "atacante" => $atacante,
                "defensor" => $defensor,
                "mensagem" => $atacante["nome"]
                    . " começou a carregar o Razor Wind!",
                "mensagens_efeito" => [],
                "mensagem_efetividade" => "",
                "dano" => 0,
                "efetividade" => 1
            ];
        }

        // Decodifica os efeitos adicionais do ataque
        $dadosEfeito = [];

        if (!empty($ataque["dados_efeito"])) {
            $dadosEfeito = json_decode($ataque["dados_efeito"], true) ?? [];
        }

        // Verifica a precisão do ataque
        $precisao = (float) ($ataque["precisao"] ?? 100);

        // Considera os estágios de precisão e evasão
        $estagioPrecisao = $atacante["estagios"]["precisao"] ?? 0;
        $estagioEvasao = $defensor["estagios"]["evasao"] ?? 0;

        $diferencaEstagios = max(
            -6,
            min(6, $estagioPrecisao - $estagioEvasao)
        );

        // Calcula o multiplicador de precisão
        if ($diferencaEstagios >= 0) {
            $multiplicadorPrecisao = (3 + $diferencaEstagios) / 3;
        } else {
            $multiplicadorPrecisao = 3 / (3 - $diferencaEstagios);
        }

        $precisaoFinal = min(
            100,
            $precisao * $multiplicadorPrecisao
        );

        // Verifica se o ataque acertou
        if (random_int(1, 10000) > ($precisaoFinal * 100)) {
            $mensagem = $atacante["nome"]
                . " usou "
                . $ataque["nome"]
                . ", mas errou o ataque!";

            return [
                "atacante" => $atacante,
                "defensor" => $defensor,
                "mensagem" => $mensagem,
                "mensagens_efeito" => [],
                "mensagem_efetividade" => "",
                "dano" => 0,
                "efetividade" => 1
            ];
        }

        $categoria = $ataque["categoria"] ?? null;
        $poder = (int) ($ataque["poder"] ?? 0);



        // Aplica alterações nos estágios dos atributos
        $alteracoes = $dadosEfeito["alteracoes_atributos"] ?? [];

        if (!empty($alteracoes)) {

            // Identifica o alvo das alterações
            if (isset($dadosEfeito["alvo"])) {
                $alvoAlteracoes = $dadosEfeito["alvo"];
            } else {
                $alteracoesPositivas = count(array_filter(
                    $alteracoes,
                    fn($alteracao) => (int) ($alteracao["alteracao"] ?? 0) > 0
                ));

                $alteracoesNegativas = count(array_filter(
                    $alteracoes,
                    fn($alteracao) => (int) ($alteracao["alteracao"] ?? 0) < 0
                ));

                if ($alteracoesPositivas > 0 && $alteracoesNegativas === 0) {
                    $alvoAlteracoes = "atacante";
                } elseif ($alteracoesNegativas > 0 && $alteracoesPositivas === 0) {
                    $alvoAlteracoes = "defensor";
                } else {
                    $alvoAlteracoes = "atacante";
                }
            }

            if ($alvoAlteracoes === "defensor") {
                $resultadoAlteracoes = $this->aplicarAlteracoesAtributos(
                    $defensor,
                    $alteracoes
                );

                $defensor = $resultadoAlteracoes["pokemon"];
            } else {
                $resultadoAlteracoes = $this->aplicarAlteracoesAtributos(
                    $atacante,
                    $alteracoes
                );

                $atacante = $resultadoAlteracoes["pokemon"];
            }

            $mensagensEfeito = array_merge(
                $mensagensEfeito,
                $resultadoAlteracoes["mensagens"]
            );
        }

        // Identifica o status causado pelo ataque
        $statusAtaque = $dadosEfeito["status"]
            ?? $ataque["efeito_status"]
            ?? null;

        $statusPermitidos = [
            "queimado",
            "envenenado",
            "paralisado",
            "dormindo",
            "congelado"
        ];

        // Aplica o efeito de status
        if (
            $statusAtaque !== null &&
            in_array($statusAtaque, $statusPermitidos, true) &&
            (int) ($defensor["hp_atual"] ?? 0) > 0
        ) {
            $statusAtual = $defensor["status_atual"] ?? null;

            if (
                $statusAtual === null ||
                $statusAtual === "" ||
                $statusAtual === "normal"
            ) {
                $chanceStatus = (float) (
                    $dadosEfeito["chance_status"]
                    ?? $ataque["chance_efeito"]
                    ?? 0
                );

                $efeitoAplicado = (
                    $chanceStatus <= 0 ||
                    random_int(1, 10000) <= ($chanceStatus * 100)
                );

                if ($efeitoAplicado) {
                    $defensor["status_atual"] = $statusAtaque;

                    if ($statusAtaque === "dormindo") {
                        $defensor["turnos_sono"] = random_int(1, 3);
                    }

                    $nomesStatus = [
                        "queimado" => "queimadura",
                        "envenenado" => "envenenamento",
                        "paralisado" => "paralisia",
                        "dormindo" => "sono",
                        "congelado" => "congelamento"
                    ];

                    $mensagensEfeito[] = $defensor["nome"]
                        . " foi afetado por "
                        . $nomesStatus[$statusAtaque]
                        . "!";
                }
            }
        }

        // Ataques sem dano
        if ($poder <= 0 || $categoria === "status") {
            $mensagem = $atacante["nome"]
                . " usou "
                . $ataque["nome"]
                . "!";

            return [
                "atacante" => $atacante,
                "defensor" => $defensor,
                "mensagem" => $mensagem,
                "mensagens_efeito" => $mensagensEfeito,
                "mensagem_efetividade" => "",
                "dano" => 0,
                "efetividade" => 1
            ];
        }

        // Verifica se o defensor já foi derrotado
        if ((int) ($defensor["hp_atual"] ?? 0) <= 0) {
            return [
                "atacante" => $atacante,
                "defensor" => $defensor,
                "mensagem" => "",
                "mensagens_efeito" => $mensagensEfeito,
                "mensagem_efetividade" => "",
                "dano" => 0,
                "efetividade" => 1
            ];
        }

        // Calcula a efetividade do tipo
        $efetividade = $this->calcularEfetividadeTipos($ataque, $defensor);

        // Calcula o dano
        $dano = $this->calcularDano(
            $atacante,
            $defensor,
            $ataque,
            $efetividade
        );

        // Aplica o dano
        $hpAnterior = (int) $defensor["hp_atual"];

        $defensor["hp_atual"] = max(
            0,
            $hpAnterior - $dano
        );

        $defensor["hp_atual_exibicao"] = $defensor["hp_atual"];

        // Calcula o dano realmente causado
        $danoReal = $hpAnterior - $defensor["hp_atual"];

        // Padroniza o nome do ataque
        $nomeAtaque = strtolower(trim($ataque["nome"] ?? ""));
        $nomeAtaque = str_replace([" ", "_"], "-", $nomeAtaque);

        // Hyper Beam: precisa recarregar após acertar
        if (
            $nomeAtaque === "hyper-beam" &&
            $danoReal > 0
        ) {
            $atacante["precisa_recarregar"] = true;

            $mensagensEfeito[] = $atacante["nome"]
                . " precisará recarregar após o Hyper Beam!";
        }

        // Bind: prende o adversário por 2 a 5 turnos
        if (
            $nomeAtaque === "bind" &&
            $danoReal > 0 &&
            $defensor["hp_atual"] > 0 &&
            !isset($defensor["turnos_presos"])
        ) {
            $defensor["turnos_presos"] = random_int(2, 5);

            $mensagensEfeito[] = $defensor["nome"]
                . " ficou preso pelo Bind!";
        }

        // Verifica se o ataque possui efeito de drenagem
        $percentualDrenagem = 0;

        if (in_array($nomeAtaque, ["absorb", "mega-drain"], true)) {
            $percentualDrenagem = 0.50;
        } elseif ($nomeAtaque === "draining-kiss") {
            $percentualDrenagem = 0.75;
        }

        // Recupera parte do dano causado
        if ($percentualDrenagem > 0 && $danoReal > 0) {

            $hpMaximoAtacante = (int) (
                $atacante["hp_maximo"] ?? $atacante["hp"] ?? 0
            );

            $hpAtualAtacante = (int) $atacante["hp_atual"];

            $cura = max(
                1,
                (int) floor($danoReal * $percentualDrenagem)
            );

            $hpNovo = min(
                $hpMaximoAtacante,
                $hpAtualAtacante + $cura
            );

            $hpRecuperado = $hpNovo - $hpAtualAtacante;

            $atacante["hp_atual"] = $hpNovo;
            $atacante["hp_atual_exibicao"] = $hpNovo;

            $atacante["porcentagem_hp"] = $hpMaximoAtacante > 0
                ? ($hpNovo / $hpMaximoAtacante) * 100
                : 0;

            if ($hpRecuperado > 0) {
                $mensagensEfeito[] = $atacante["nome"]
                    . " recuperou "
                    . $hpRecuperado
                    . " HP!";
            }
        }

        // Verifica se o ataque causa dano de recuo
        if ($nomeAtaque === "take-down" && $danoReal > 0) {

            // Calcula 25% do dano realmente causado
            $danoRecuo = max(
                1,
                (int) floor($danoReal * 0.25)
            );

            // Aplica o dano ao próprio atacante
            $hpAtualAtacante = (int) $atacante["hp_atual"];

            $atacante["hp_atual"] = max(
                0,
                $hpAtualAtacante - $danoRecuo
            );

            $atacante["hp_atual_exibicao"] = $atacante["hp_atual"];

            $hpMaximoAtacante = (int) (
                $atacante["hp_maximo"] ?? $atacante["hp"] ?? 0
            );

            $atacante["porcentagem_hp"] = $hpMaximoAtacante > 0
                ? ($atacante["hp_atual"] / $hpMaximoAtacante) * 100
                : 0;

            $mensagensEfeito[] = $atacante["nome"]
                . " sofreu "
                . $danoRecuo
                . " de dano pelo recuo!";

            // Verifica se o próprio atacante foi derrotado
            if ($atacante["hp_atual"] <= 0) {
                $mensagensEfeito[] = $atacante["nome"]
                    . " foi derrotado pelo recuo!";
            }
        }

        $mensagem = $atacante["nome"]
            . " usou "
            . $ataque["nome"]
            . " e causou "
            . $dano
            . " de dano!";

        // Mensagem de efetividade
        if ($efetividade > 1) {
            $mensagemEfetividade = "É super eficaz!";
        } elseif ($efetividade > 0 && $efetividade < 1) {
            $mensagemEfetividade = "Não é muito eficaz...";
        } elseif ($efetividade == 0) {
            $mensagemEfetividade = "Não teve efeito!";
        }

        // Verifica se o ataque pode causar hesitação
        $chanceHesitacao = (float) (
            $dadosEfeito["chance_hesitacao"] ?? 0
        );

        if (
            $defensor["hp_atual"] > 0 &&
            $alvoAindaVaiAgir &&
            $chanceHesitacao > 0 &&
            random_int(1, 10000) <= ($chanceHesitacao * 100)
        ) {
            $defensor["hesitou"] = true;

            $mensagensEfeito[] = $defensor["nome"]
                . " ficou hesitante!";
        }

        // Verifica se o defensor foi derrotado
        if ($defensor["hp_atual"] <= 0) {
            $mensagensEfeito[] = $defensor["nome"] . " foi derrotado!";
        }

        return [
            "atacante" => $atacante,
            "defensor" => $defensor,
            "mensagem" => $mensagem,
            "mensagens_efeito" => $mensagensEfeito,
            "mensagem_efetividade" => $mensagemEfetividade,
            "dano" => $dano,
            "efetividade" => $efetividade
        ];
    }
}
