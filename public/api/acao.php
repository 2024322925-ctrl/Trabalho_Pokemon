<?php

require_once __DIR__ . "/../../app/autoload.php";

session_start();

header("Content-Type: application/json; charset=UTF-8");

// Verifica se o usuário está autenticado
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Usuário não autenticado."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se existe uma batalha na sessão
if (!isset($_SESSION["batalha"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Nenhuma batalha está em andamento."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica o método da requisição
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método de requisição inválido."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}



$batalhaController = new BatalhaController();



$itemController = new ItemController();

// Verifica se o jogador solicitou fugir da batalha
if (isset($_POST["fugir"]) && $_POST["fugir"] === "1") {

    $batalha = $_SESSION["batalha"];

    // Verifica se a batalha pertence ao usuário autenticado
    if (
        (int) ($batalha["id_usuario"] ?? 0)
        !== (int) $_SESSION["id_usuario"]
    ) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Batalha inválida."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Recupera os IDs dos três Pokémon selecionados
    $idsPokemons = [];

    foreach ($batalha["pokemons_selecionados"] as $pokemon) {
        $idsPokemons[] = (int) $pokemon["id_pokemon"];
    }

    // Recupera o HP e remove os status negativos
    $recuperouEstado = $batalhaController
        ->recuperarEstadoDosPokemonsSelecionados(
            $batalha["id_time"],
            $idsPokemons
        );

    if (!$recuperouEstado) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível recuperar seus Pokémon."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Encerra a batalha sem registrar derrota ou conceder recompensas
    unset($_SESSION["batalha"]);
    unset($_SESSION["preparacao_batalha"]);

    echo json_encode([
        "sucesso" => true,
        "resultado_batalha" => "fuga",
        "mensagem" => "Você fugiu da batalha!"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Recupera os dados enviados pelo jogador
$idAtaque = filter_input(INPUT_POST, "id_ataque", FILTER_VALIDATE_INT);
$idPokemonTroca = filter_input(INPUT_POST, "id_pokemon", FILTER_VALIDATE_INT);
$idItem = filter_input(INPUT_POST, "id_item", FILTER_VALIDATE_INT);

$idPokemonReviver = filter_input(
    INPUT_POST,
    "id_pokemon_reviver",
    FILTER_VALIDATE_INT
);

// Identifica se a ação é uma troca voluntária
$trocaVoluntaria = $idPokemonTroca !== null
    && $idPokemonTroca !== false
    && $idPokemonTroca > 0;

$usarItem = $idItem !== null
    && $idItem !== false
    && $idItem > 0;

// Se não for troca nem uso de item, precisa informar um ataque válido
if (
    !$trocaVoluntaria &&
    !$usarItem &&
    (!$idAtaque || $idAtaque <= 0)
) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Ação inválida."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Recupera os dados da batalha
$batalha = $_SESSION["batalha"];

// Busca e valida o item escolhido pelo jogador
$itemUtilizado = null;

if ($usarItem) {

    $itemUtilizado = $itemController->buscarItemDeBatalha(
        (int) $_SESSION["id_usuario"],
        (int) $idItem
    );

    // Verifica se o item pertence ao inventário e pode ser usado
    if (!$itemUtilizado) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Item indisponível no inventário."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| TENTATIVA DE FINALIZAÇÃO DE UMA BATALHA JÁ ENCERRADA
|--------------------------------------------------------------------------
*/

// Se a batalha já terminou, não executa outro turno
if (
    in_array(
        $batalha["resultado"] ?? null,
        ["vitoria", "derrota"],
        true
    )
) {

    // Verifica se a finalização já foi realizada
    if (empty($_SESSION["batalha"]["finalizacao"])) {

        // Recupera os Pokémon selecionados
        $pokemonsSelecionados = $batalha["pokemons_selecionados"] ?? [];

        $idsPokemons = [];

        foreach ($pokemonsSelecionados as $pokemon) {
            $idsPokemons[] = (int) $pokemon["id_pokemon"];
        }

        /*
        |--------------------------------------------------------------------------
        | CORREÇÃO: UTILIZA O TIME DA BATALHA
        |--------------------------------------------------------------------------
        */

        // Recupera o mesmo time utilizado no início da batalha
        $idTime = (int) ($batalha["id_time"] ?? 0);

        if ($idTime <= 0) {
            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Não foi possível identificar o time utilizado na batalha."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Salva o HP e o status dos três Pokémon selecionados
        foreach ($pokemonsSelecionados as $pokemon) {

            $salvouEstado = $batalhaController->atualizarEstadoPokemon(
                $idTime,
                (int) $pokemon["id_pokemon"],
                (int) $pokemon["hp_atual"],
                $pokemon["status_atual"] ?? null
            );

            if (!$salvouEstado) {
                echo json_encode([
                    "sucesso" => false,
                    "mensagem" => "Não foi possível atualizar o estado dos Pokémon no banco."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        // Tenta finalizar a batalha novamente
        $dadosFinalizacao = $batalhaController->finalizarBatalha(
            (int) $_SESSION["id_usuario"],
            $idTime,
            (int) $batalha["id_fase"],
            $batalha["resultado"],
            $idsPokemons,
            $batalha["chave_batalha"] ?? null
        );

        if (
            !$dadosFinalizacao ||
            empty($dadosFinalizacao["sucesso"])
        ) {
            echo json_encode([
                "sucesso" => false,
                "resultado_batalha" => $batalha["resultado"],
                "mensagem" => "Não foi possível finalizar a batalha e registrar as recompensas."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Guarda os dados da finalização na sessão
        $_SESSION["batalha"]["finalizacao"] = $dadosFinalizacao;
    }

    // Recupera os dados já registrados
    $dadosFinalizacao = $_SESSION["batalha"]["finalizacao"];

    echo json_encode([
        "sucesso" => true,
        "resultado_batalha" => $batalha["resultado"],
        "recompensas" => $dadosFinalizacao,
        "mensagem" => "Batalha finalizada."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Impede ataques enquanto o jogador precisa escolher outro Pokémon
if (!empty($batalha["aguardando_troca"])) {
    echo json_encode([
        "sucesso" => false,
        "solicitar_troca" => true,
        "mensagem" => "Escolha outro Pokémon para continuar a batalha."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idPokemonAtivo = $batalha["id_pokemon_ativo"] ?? null;
$indiceAdversario = $batalha["indice_adversario"] ?? null;

$pokemonsSelecionados = $batalha["pokemons_selecionados"] ?? [];
$pokemonsFase = $batalha["pokemons_fase"] ?? [];

// Verifica se os dados necessários existem
if (
    !$idPokemonAtivo ||
    $indiceAdversario === null ||
    !isset($pokemonsFase[$indiceAdversario])
) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível identificar os Pokémon da batalha."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Localiza o Pokémon ativo do jogador
$pokemonAtacante = null;
$indiceAtacante = null;

foreach ($pokemonsSelecionados as $indice => $pokemon) {

    if ((int) $pokemon["id_pokemon"] === (int) $idPokemonAtivo) {

        $pokemonAtacante = $pokemon;
        $indiceAtacante = $indice;

        break;
    }
}

// Verifica se o Pokémon ativo foi encontrado
if ($pokemonAtacante === null) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Pokémon ativo não encontrado."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Valida a troca voluntária
if ($trocaVoluntaria) {

    // Não permite trocar pelo Pokémon que já está em campo
    if ((int) $idPokemonTroca === (int) $idPokemonAtivo) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Esse Pokémon já está em batalha."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pokemonEncontrado = false;

    foreach ($pokemonsSelecionados as $indice => $pokemon) {

        if ((int) $pokemon["id_pokemon"] === (int) $idPokemonTroca) {

            $pokemonEncontrado = true;

            // Não permite escolher um Pokémon derrotado
            if ((int) ($pokemon["hp_atual"] ?? 0) <= 0) {
                echo json_encode([
                    "sucesso" => false,
                    "mensagem" => "Esse Pokémon está derrotado."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Atualiza o Pokémon ativo
            $pokemonAtacante = $pokemon;
            $indiceAtacante = $indice;
            $idPokemonAtivo = (int) $idPokemonTroca;

            break;
        }
    }

    // Verifica se o Pokémon pertence à equipe
    if (!$pokemonEncontrado) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Esse Pokémon não pertence à sua equipe."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Recupera o adversário atual
$pokemonAdversario = $pokemonsFase[$indiceAdversario];

// Recupera os HPs
$hpAtacante = (int) (
    $pokemonAtacante["hp_atual"]
    ?? $pokemonAtacante["hp_atual_exibicao"]
    ?? 0
);

$hpAdversario = (int) (
    $pokemonAdversario["hp_atual"]
    ?? $pokemonAdversario["hp_atual_exibicao"]
    ?? $pokemonAdversario["hp_maximo"]
    ?? $pokemonAdversario["hp"]
    ?? 0
);

// Garante que os dois Pokémon tenham o HP atual definido
$pokemonAtacante["hp_atual"] = $hpAtacante;
$pokemonAtacante["hp_atual_exibicao"] = $hpAtacante;

$pokemonAdversario["hp_atual"] = $hpAdversario;
$pokemonAdversario["hp_atual_exibicao"] = $hpAdversario;

// Verifica se o jogador está solicitando a seleção de um Pokémon para reviver
if ($usarItem && (int) $idItem === 12 && !$idPokemonReviver) {

    $pokemonsParaReviver = [];

    // Procura Pokémon derrotados entre os três selecionados
    foreach ($pokemonsSelecionados as $pokemon) {

        if ((int) ($pokemon["hp_atual"] ?? 0) <= 0) {

            $pokemonsParaReviver[] = [
                "id_pokemon" => (int) $pokemon["id_pokemon"],
                "nome" => $pokemon["nome"],
                "imagem" => $pokemon["imagem"] ?? null,
                "hp_maximo" => (int) $pokemon["hp_maximo"]
            ];
        }
    }

    // Verifica se existe algum Pokémon que possa ser revivido
    if (empty($pokemonsParaReviver)) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Você não possui Pokémon derrotados para reviver."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Solicita ao jogador que escolha um Pokémon
    echo json_encode([
        "sucesso" => true,
        "solicitar_revive" => true,
        "pokemons_para_reviver" => $pokemonsParaReviver
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Valida o Pokémon escolhido para utilizar o Revive
if ($usarItem && (int) $idItem === 12 && $idPokemonReviver) {

    $pokemonEncontrado = false;

    foreach ($pokemonsSelecionados as $indice => $pokemon) {

        if ((int) $pokemon["id_pokemon"] === (int) $idPokemonReviver) {

            $pokemonEncontrado = true;

            // Verifica se o Pokémon realmente está derrotado
            if ((int) ($pokemon["hp_atual"] ?? 0) > 0) {
                echo json_encode([
                    "sucesso" => false,
                    "mensagem" => "Esse Pokémon não está derrotado."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            break;
        }
    }

    // Verifica se o Pokémon pertence aos três selecionados
    if (!$pokemonEncontrado) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Esse Pokémon não pertence à sua equipe selecionada."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Verifica se algum Pokémon já foi derrotado
if ($hpAtacante <= 0 || $hpAdversario <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não é possível atacar neste momento."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Valida o ataque somente quando a ação não for uma troca

// Aplica o efeito do item escolhido
$mensagemItem = null;

if ($usarItem) {

    $hpMaximo = (int) $pokemonAtacante["hp_maximo"];
    $statusAtual = $pokemonAtacante["status_atual"] ?? null;

    // Revive
    if ((int) $idItem === 12) {

        foreach ($pokemonsSelecionados as $indice => $pokemon) {

            if ((int) $pokemon["id_pokemon"] === (int) $idPokemonReviver) {

                $hpMaximoReviver = (int) $pokemon["hp_maximo"];
                $hpRecuperado = max(1, (int) ceil($hpMaximoReviver * 0.5));

                $pokemonsSelecionados[$indice]["hp_atual"] = $hpRecuperado;
                $pokemonsSelecionados[$indice]["hp_atual_exibicao"] = $hpRecuperado;
                $pokemonsSelecionados[$indice]["status_atual"] = null;

                unset($pokemonsSelecionados[$indice]["turnos_sono"]);

                $mensagemItem = $pokemon["nome"]
                    . " foi revivido e recuperou "
                    . $hpRecuperado
                    . " HP!";

                break;
            }
        }
    } else {

        // Itens de recuperação de HP
        switch ((int) $idItem) {

            case 1: // Potion
                $novoHP = min($hpAtacante + 20, $hpMaximo);
                break;

            case 2: // Super Potion
                $novoHP = min($hpAtacante + 50, $hpMaximo);
                break;

            case 3: // Hyper Potion
                $novoHP = min($hpAtacante + 200, $hpMaximo);
                break;

            case 4: // Max Potion
            case 5: // Full Restore
                $novoHP = $hpMaximo;
                break;

            default:
                $novoHP = $hpAtacante;
                break;
        }

        // Verifica qual status o item pode remover
        $statusParaCurar = [
            6 => "envenenado", // Antidote
            7 => "queimado",   // Burn Heal
            8 => "congelado",  // Ice Heal
            9 => "dormindo",   // Awakening
            10 => "paralisado" // Paralyze Heal
        ];

        $curarStatus = false;

        // Full Restore e Full Heal removem qualquer status negativo
        if (in_array((int) $idItem, [5, 11], true)) {

            $curarStatus = !in_array(
                $statusAtual,
                [null, "", "normal"],
                true
            );
        } elseif (isset($statusParaCurar[(int) $idItem])) {

            $curarStatus = $statusAtual === $statusParaCurar[(int) $idItem];
        }

        // Verifica se o item realmente produzirá algum efeito
        if (
            $novoHP === $hpAtacante &&
            !$curarStatus
        ) {

            echo json_encode([
                "sucesso" => false,
                "mensagem" => "Esse Pokémon não precisa desse item."
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        // Calcula quanto HP foi recuperado
        $hpRecuperado = $novoHP - $hpAtacante;

        // Atualiza o HP do Pokémon ativo
        $pokemonAtacante["hp_atual"] = $novoHP;
        $pokemonAtacante["hp_atual_exibicao"] = $novoHP;

        $hpAtacante = $novoHP;

        // Remove o status negativo quando necessário
        if ($curarStatus) {

            $pokemonAtacante["status_atual"] = null;

            unset($pokemonAtacante["turnos_sono"]);
        }

        // Monta a mensagem de utilização do item
        if ($curarStatus && $hpRecuperado > 0) {

            $mensagemItem = $pokemonAtacante["nome"]
                . " recuperou "
                . $hpRecuperado
                . " HP e teve seu status removido usando "
                . $itemUtilizado["nome"] . "!";
        } elseif ($curarStatus) {

            $mensagemItem = $pokemonAtacante["nome"]
                . " teve seu status removido usando "
                . $itemUtilizado["nome"] . "!";
        } else {

            $mensagemItem = $pokemonAtacante["nome"]
                . " recuperou "
                . $hpRecuperado
                . " HP usando "
                . $itemUtilizado["nome"] . "!";
        }
    }
}

$ataqueJogador = null;

if (!$trocaVoluntaria && !$usarItem) {

    $ataqueJogador = $batalhaController->validarAtaque(
        $idPokemonAtivo,
        $idAtaque
    );

    if (!$ataqueJogador) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Esse Pokémon não possui esse ataque."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Sorteia o ataque do adversário
$ataqueAdversario = $batalhaController->selecionarAtaqueAleatorio(
    $pokemonAdversario
);

if (!$ataqueAdversario) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "O adversário não possui ataques disponíveis."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Consome uma unidade do inventário somente se um item foi utilizado
if ($usarItem) {

    $consumiuItem = $itemController->consumirItem(
        (int) $_SESSION["id_usuario"],
        (int) $idItem
    );

    if (!$consumiuItem) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível utilizar o item."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Define quem atacará primeiro conforme a velocidade
$ordemTurnos = $batalhaController->definirOrdemTurnos(
    $pokemonAtacante,
    $pokemonAdversario
);

// Armazena as mensagens e os resultados
$mensagens = [];
$mensagensEfeito = [];
$mensagensEfetividade = [];

// Armazena os acontecimentos individuais do turno
$eventos = [];

$danoJogador = 0;
$danoAdversario = 0;

$efetividadeJogador = 1;
$efetividadeAdversario = 1;

// Controla quais Pokémon já realizaram sua ação neste turno
$jaAgiram = [
    "jogador" => false,
    "adversario" => false
];

// A troca voluntária ou o uso de item consome a ação do jogador
if ($trocaVoluntaria || $usarItem) {
    $jaAgiram["jogador"] = true;
}

// Controla a entrada de um novo adversário
$pokemonAdversarioEntrou = false;

/*
|--------------------------------------------------------------------------
| REGISTRO DOS ACONTECIMENTOS
|--------------------------------------------------------------------------
*/

$registrarEventos = function (
    $resultado,
    $pokemonJogador,
    $pokemonAdversario
) use (&$eventos) {

    $mensagensDoAtaque = [];

    // Mensagem principal do ataque
    if (!empty($resultado["mensagem"])) {
        $mensagensDoAtaque[] = $resultado["mensagem"];
    }

    // Mensagem de efetividade
    if (!empty($resultado["mensagem_efetividade"])) {
        $mensagensDoAtaque[] = $resultado["mensagem_efetividade"];
    }

    // Mensagens dos efeitos
    if (!empty($resultado["mensagens_efeito"])) {

        foreach ($resultado["mensagens_efeito"] as $mensagemEfeito) {
            $mensagensDoAtaque[] = $mensagemEfeito;
        }
    }

    // HP atual do jogador
    $hpJogador = (int) (
        $pokemonJogador["hp_atual"]
        ?? $pokemonJogador["hp_atual_exibicao"]
        ?? 0
    );

    $hpMaximoJogador = (int) $pokemonJogador["hp_maximo"];

    // HP atual do adversário
    $hpAdversario = (int) (
        $pokemonAdversario["hp_atual"]
        ?? $pokemonAdversario["hp_atual_exibicao"]
        ?? 0
    );

    $hpMaximoAdversario = (int) $pokemonAdversario["hp_maximo"];

    // Calcula as porcentagens de HP
    $porcentagemJogador = $hpMaximoJogador > 0
        ? ($hpJogador / $hpMaximoJogador) * 100
        : 0;

    $porcentagemAdversario = $hpMaximoAdversario > 0
        ? ($hpAdversario / $hpMaximoAdversario) * 100
        : 0;

    // Cria um acontecimento para cada mensagem
    foreach ($mensagensDoAtaque as $mensagem) {

        $eventos[] = [
            "mensagem" => $mensagem,

            "hp_jogador" => $hpJogador,
            "hp_maximo_jogador" => $hpMaximoJogador,
            "porcentagem_hp_jogador" => $porcentagemJogador,

            "hp_adversario" => $hpAdversario,
            "hp_maximo_adversario" => $hpMaximoAdversario,
            "porcentagem_hp_adversario" => $porcentagemAdversario
        ];
    }
};

// Registra a utilização do item como um evento da batalha
if ($mensagemItem !== null) {

    $registrarEventos(
        ["mensagem" => $mensagemItem],
        $pokemonAtacante,
        $pokemonAdversario
    );

    $mensagens[] = $mensagemItem;
}

/*
|--------------------------------------------------------------------------
| EXECUÇÃO DOS ATAQUES COM VERIFICAÇÃO DE STATUS
|--------------------------------------------------------------------------
*/

// Executa o ataque de um Pokémon, verificando primeiro se ele pode agir
$executarAtaqueDoTurno = function (
    &$pokemonAtacante,
    &$pokemonAdversario,
    $ataque,
    $lado
) use (
    $batalhaController,
    $registrarEventos,
    &$mensagens,
    &$mensagensEfeito,
    &$mensagensEfetividade,
    &$danoJogador,
    &$danoAdversario,
    &$efetividadeJogador,
    &$efetividadeAdversario,
    &$jaAgiram
) {

    // Verifica se o Pokémon pode agir
    if ($lado === "jogador") {

        $condicao = $batalhaController->verificarCondicaoDeAcao(
            $pokemonAtacante
        );
    } else {

        $condicao = $batalhaController->verificarCondicaoDeAcao(
            $pokemonAdversario
        );
    }

    // Marca que chegou a vez deste Pokémon
    $jaAgiram[$lado] = true;

    // Se não puder agir, registra a mensagem e encerra sua vez
    if (!$condicao["pode_agir"]) {

        $registrarEventos(
            ["mensagem" => $condicao["mensagem"]],
            $pokemonAtacante,
            $pokemonAdversario
        );

        $mensagens[] = $condicao["mensagem"];

        return;
    }

    // Identifica o lado do alvo
    $ladoAlvo = ($lado === "jogador")
        ? "adversario"
        : "jogador";

    // Verifica se o alvo ainda terá sua ação neste turno
    $alvoAindaVaiAgir = !$jaAgiram[$ladoAlvo];

    // Executa o ataque conforme o lado
    if ($lado === "jogador") {

        $resultado = $batalhaController->executarAtaque(
            $pokemonAtacante,
            $pokemonAdversario,
            $ataque,
            $alvoAindaVaiAgir
        );

        $pokemonAtacante = $resultado["atacante"];
        $pokemonAdversario = $resultado["defensor"];

        $danoJogador = $resultado["dano"];
        $efetividadeJogador = $resultado["efetividade"];
    } else {

        $resultado = $batalhaController->executarAtaque(
            $pokemonAdversario,
            $pokemonAtacante,
            $ataque,
            $alvoAindaVaiAgir
        );

        $pokemonAdversario = $resultado["atacante"];
        $pokemonAtacante = $resultado["defensor"];

        $danoAdversario = $resultado["dano"];
        $efetividadeAdversario = $resultado["efetividade"];
    }

    // Registra os acontecimentos do ataque
    $registrarEventos(
        $resultado,
        $pokemonAtacante,
        $pokemonAdversario
    );

    if (!empty($resultado["mensagem"])) {
        $mensagens[] = $resultado["mensagem"];
    }

    if (!empty($resultado["mensagem_efetividade"])) {
        $mensagensEfetividade[] = $resultado["mensagem_efetividade"];
    }

    $mensagensEfeito = array_merge(
        $mensagensEfeito,
        $resultado["mensagens_efeito"] ?? []
    );
};

// Executa o turno conforme a ação escolhida
if ($trocaVoluntaria || $usarItem) {

    // A troca ou o uso de item consome a ação do jogador.
    // Portanto, somente o adversário executa seu ataque.
    $executarAtaqueDoTurno(
        $pokemonAtacante,
        $pokemonAdversario,
        $ataqueAdversario,
        "adversario"
    );
} else {

    // Define a ordem dos ataques conforme a velocidade
    if ($ordemTurnos["primeiro"] === "jogador") {

        // Primeiro: jogador
        $executarAtaqueDoTurno(
            $pokemonAtacante,
            $pokemonAdversario,
            $ataqueJogador,
            "jogador"
        );

        // Segundo: adversário, caso ainda esteja vivo
        if ((int) $pokemonAdversario["hp_atual"] > 0) {

            $executarAtaqueDoTurno(
                $pokemonAtacante,
                $pokemonAdversario,
                $ataqueAdversario,
                "adversario"
            );
        }
    } else {

        // Primeiro: adversário
        $executarAtaqueDoTurno(
            $pokemonAtacante,
            $pokemonAdversario,
            $ataqueAdversario,
            "adversario"
        );

        // Segundo: jogador, caso ainda esteja vivo
        if ((int) $pokemonAtacante["hp_atual"] > 0) {

            $executarAtaqueDoTurno(
                $pokemonAtacante,
                $pokemonAdversario,
                $ataqueJogador,
                "jogador"
            );
        }
    }
}

/*
|--------------------------------------------------------------------------
| DANO RESIDUAL DE QUEIMADURA E ENVENENAMENTO
|--------------------------------------------------------------------------
*/

$aplicarDanoResidual = function (
    &$pokemonJogador,
    &$pokemonAdversario,
    $lado
) use (&$eventos, &$mensagensEfeito) {

    // Identifica qual Pokémon receberá o dano
    if ($lado === "jogador") {
        $pokemonAfetado = &$pokemonJogador;
    } else {
        $pokemonAfetado = &$pokemonAdversario;
    }

    $status = $pokemonAfetado["status_atual"] ?? null;

    // Só queimadura e envenenamento causam dano residual
    if (!in_array($status, ["queimado", "envenenado"], true)) {
        return;
    }

    $hpAtual = (int) ($pokemonAfetado["hp_atual"] ?? 0);

    // Pokémon derrotados não recebem dano residual
    if ($hpAtual <= 0) {
        return;
    }

    $hpMaximo = (int) (
        $pokemonAfetado["hp_maximo"]
        ?? $pokemonAfetado["hp"]
        ?? 0
    );

    if ($hpMaximo <= 0) {
        return;
    }

    // Calcula 6,25% do HP máximo, arredondando para cima
    $danoResidual = max(1, (int) ceil($hpMaximo * 0.0625));

    // Impede que o HP fique negativo
    $danoResidual = min($danoResidual, $hpAtual);

    $pokemonAfetado["hp_atual"] = $hpAtual - $danoResidual;
    $pokemonAfetado["hp_atual_exibicao"] = $hpAtual - $danoResidual;

    // Monta a mensagem correspondente ao status
    if ($status === "queimado") {
        $mensagem = $pokemonAfetado["nome"]
            . " sofreu dano pela queimadura!";
    } else {
        $mensagem = $pokemonAfetado["nome"]
            . " sofreu dano pelo envenenamento!";
    }

    $mensagensEfeito[] = $mensagem;

    // Recupera os HPs atualizados dos dois Pokémon
    $hpJogador = (int) $pokemonJogador["hp_atual"];
    $hpMaximoJogador = (int) $pokemonJogador["hp_maximo"];

    $hpAdversario = (int) $pokemonAdversario["hp_atual"];
    $hpMaximoAdversario = (int) $pokemonAdversario["hp_maximo"];

    // Registra o dano residual como um evento da batalha
    $eventos[] = [
        "mensagem" => $mensagem,

        "hp_jogador" => $hpJogador,
        "hp_maximo_jogador" => $hpMaximoJogador,
        "porcentagem_hp_jogador" => $hpMaximoJogador > 0
            ? ($hpJogador / $hpMaximoJogador) * 100
            : 0,

        "hp_adversario" => $hpAdversario,
        "hp_maximo_adversario" => $hpMaximoAdversario,
        "porcentagem_hp_adversario" => $hpMaximoAdversario > 0
            ? ($hpAdversario / $hpMaximoAdversario) * 100
            : 0
    ];
};

// Aplica o dano residual depois dos ataques
$aplicarDanoResidual(
    $pokemonAtacante,
    $pokemonAdversario,
    "jogador"
);

$aplicarDanoResidual(
    $pokemonAtacante,
    $pokemonAdversario,
    "adversario"
);

/*
|--------------------------------------------------------------------------
| ATUALIZAÇÃO DA SESSÃO E CONTROLE DE DERROTAS
|--------------------------------------------------------------------------
*/

// Atualiza os dados de HP do Pokémon ativo
$hpMaximoPokemon = (int) $pokemonAtacante["hp_maximo"];

$hpAtualPokemon = (int) $pokemonAtacante["hp_atual"];

$pokemonAtacante["hp_atual_exibicao"] = $hpAtualPokemon;

$pokemonAtacante["porcentagem_hp"] = $hpMaximoPokemon > 0
    ? ($hpAtualPokemon / $hpMaximoPokemon) * 100
    : 0;

// Atualiza o Pokémon do jogador na equipe
$pokemonsSelecionados[$indiceAtacante] = $pokemonAtacante;

// Atualiza o destaque visual do Pokémon ativo na equipe
foreach ($pokemonsSelecionados as &$pokemonSelecionado) {
    $pokemonSelecionado["pokemon_ativo"] =
        (int) $pokemonSelecionado["id_pokemon"] === (int) $idPokemonAtivo;
}
unset($pokemonSelecionado);

// Controla se o jogador precisa escolher outro Pokémon
$aguardandoTroca = false;
$resultadoBatalha = null;

// Verifica se o Pokémon do jogador foi derrotado
if ((int) $pokemonAtacante["hp_atual"] <= 0) {

    // Procura Pokémon restantes com HP maior que zero
    $pokemonsDisponiveis = array_filter(
        $pokemonsSelecionados,
        function ($pokemon) {
            return (int) ($pokemon["hp_atual"] ?? 0) > 0;
        }
    );

    if (count($pokemonsDisponiveis) > 0) {

        $aguardandoTroca = true;

        $eventos[] = [
            "mensagem" => "Escolha outro Pokémon para continuar!",
            "hp_jogador" => 0,
            "hp_maximo_jogador" => (int) $pokemonAtacante["hp_maximo"],
            "porcentagem_hp_jogador" => 0,

            "hp_adversario" => (int) $pokemonAdversario["hp_atual"],
            "hp_maximo_adversario" => (int) $pokemonAdversario["hp_maximo"],
            "porcentagem_hp_adversario" => (
                (int) $pokemonAdversario["hp_maximo"] > 0
                ? (
                    (int) $pokemonAdversario["hp_atual"]
                    / (int) $pokemonAdversario["hp_maximo"]
                ) * 100
                : 0
            )
        ];
    } else {

        // Todos os Pokémon do jogador foram derrotados
        $resultadoBatalha = "derrota";
    }
}

// Atualiza os dados de HP do adversário atual
$hpMaximoAdversarioAtual = (int) $pokemonAdversario["hp_maximo"];
$hpAtualAdversarioAtual = (int) $pokemonAdversario["hp_atual"];

$pokemonAdversario["hp_atual_exibicao"] = $hpAtualAdversarioAtual;

$pokemonAdversario["porcentagem_hp"] = $hpMaximoAdversarioAtual > 0
    ? ($hpAtualAdversarioAtual / $hpMaximoAdversarioAtual) * 100
    : 0;

// Salva o adversário atualizado na lista da fase
$pokemonsFase[$indiceAdversario] = $pokemonAdversario;

// Verifica se o adversário foi derrotado
if ((int) $pokemonAdversario["hp_atual"] <= 0) {

    $proximoIndiceAdversario = null;

    // Procura o próximo adversário ainda não derrotado
    for (
        $i = $indiceAdversario + 1;
        $i < count($pokemonsFase);
        $i++
    ) {

        $hpProximo = (int) (
            $pokemonsFase[$i]["hp_atual"]
            ?? $pokemonsFase[$i]["hp_maximo"]
            ?? $pokemonsFase[$i]["hp"]
            ?? 0
        );

        if ($hpProximo > 0) {
            $proximoIndiceAdversario = $i;
            break;
        }
    }

    if ($proximoIndiceAdversario !== null) {

        // Atualiza o índice do adversário
        $indiceAdversario = $proximoIndiceAdversario;

        // Recupera o próximo Pokémon
        $pokemonAdversario = $pokemonsFase[$indiceAdversario];

        $hpNovoAdversario = (int) (
            $pokemonAdversario["hp_atual"]
            ?? $pokemonAdversario["hp_maximo"]
            ?? $pokemonAdversario["hp"]
            ?? 0
        );

        $pokemonAdversario["hp_atual"] = $hpNovoAdversario;
        $pokemonAdversario["hp_atual_exibicao"] = $hpNovoAdversario;

        // Calcula a porcentagem de HP do novo adversário
        $hpMaximoNovoAdversario = (int) $pokemonAdversario["hp_maximo"];

        $pokemonAdversario["porcentagem_hp"] = $hpMaximoNovoAdversario > 0
            ? ($hpNovoAdversario / $hpMaximoNovoAdversario) * 100
            : 0;

        // Salva o novo adversário na lista da fase
        $pokemonsFase[$indiceAdversario] = $pokemonAdversario;

        // Marca que um novo adversário entrou
        $pokemonAdversarioEntrou = true;

        // Registra a entrada do novo adversário
        $eventos[] = [
            "mensagem" => $pokemonAdversario["nome"]
                . " entrou na batalha!",
            "novo_adversario" => true,

            "hp_jogador" => (int) $pokemonAtacante["hp_atual"],
            "hp_maximo_jogador" => (int) $pokemonAtacante["hp_maximo"],
            "porcentagem_hp_jogador" => (
                (int) $pokemonAtacante["hp_maximo"] > 0
                ? (
                    (int) $pokemonAtacante["hp_atual"]
                    / (int) $pokemonAtacante["hp_maximo"]
                ) * 100
                : 0
            ),

            "hp_adversario" => $hpNovoAdversario,
            "hp_maximo_adversario" => (int) $pokemonAdversario["hp_maximo"],
            "porcentagem_hp_adversario" => (
                (int) $pokemonAdversario["hp_maximo"] > 0
                ? (
                    $hpNovoAdversario
                    / (int) $pokemonAdversario["hp_maximo"]
                ) * 100
                : 0
            )
        ];
    } else {

        // Todos os adversários foram derrotados
        if ($resultadoBatalha === null) {
            $resultadoBatalha = "vitoria";
        }
    }
}

if ($resultadoBatalha === "vitoria") {
    $aguardandoTroca = false;
}

// Atualiza os dados da batalha na sessão
$_SESSION["batalha"]["pokemons_selecionados"] = $pokemonsSelecionados;

// Atualiza o Pokémon ativo da batalha
$_SESSION["batalha"]["id_pokemon_ativo"] = $idPokemonAtivo;

// Atualiza os dados do Pokémon exibido na batalha
$_SESSION["batalha"]["pokemon_inicial"] = $pokemonAtacante;

$_SESSION["batalha"]["pokemons_fase"] = $pokemonsFase;
$_SESSION["batalha"]["indice_adversario"] = $indiceAdversario;
$_SESSION["batalha"]["pokemon_adversario"] = $pokemonAdversario;

$_SESSION["batalha"]["aguardando_troca"] = $aguardandoTroca;

if ($resultadoBatalha !== null) {
    $_SESSION["batalha"]["resultado"] = $resultadoBatalha;
}

/*
|--------------------------------------------------------------------------
| PERSISTÊNCIA DO HP NO BANCO
|--------------------------------------------------------------------------
*/

// Recupera o mesmo time utilizado no início da batalha
$idTime = (int) ($batalha["id_time"] ?? 0);

if ($idTime <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível identificar o time utilizado na batalha."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Salva o HP e o status dos três Pokémon selecionados
foreach ($pokemonsSelecionados as $pokemonSelecionado) {

    $salvouEstado = $batalhaController->atualizarEstadoPokemon(
        $idTime,
        (int) $pokemonSelecionado["id_pokemon"],
        (int) $pokemonSelecionado["hp_atual"],
        $pokemonSelecionado["status_atual"] ?? null
    );

    if (!$salvouEstado) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível atualizar o estado dos Pokémon no banco."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| FINALIZAÇÃO DA BATALHA E CONCESSÃO DE RECOMPENSAS
|--------------------------------------------------------------------------
*/

$dadosFinalizacao = null;

// Somente vitórias e derrotas são registradas
if (in_array($resultadoBatalha, ["vitoria", "derrota"], true)) {

    // Recupera os IDs dos três Pokémon selecionados
    $idsPokemons = [];

    foreach ($pokemonsSelecionados as $pokemon) {
        $idsPokemons[] = (int) $pokemon["id_pokemon"];
    }

    // Recupera a chave única da batalha
    $chaveBatalha = $_SESSION["batalha"]["chave_batalha"] ?? null;

    // Registra a batalha e concede as recompensas
    $dadosFinalizacao = $batalhaController->finalizarBatalha(
        (int) $_SESSION["id_usuario"],
        $idTime,
        (int) $_SESSION["batalha"]["id_fase"],
        $resultadoBatalha,
        $idsPokemons,
        $chaveBatalha
    );

    // Verifica se a finalização foi concluída
    if (
        !$dadosFinalizacao ||
        empty($dadosFinalizacao["sucesso"])
    ) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Não foi possível finalizar a batalha e registrar as recompensas."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Guarda os dados das recompensas na sessão
    $_SESSION["batalha"]["finalizacao"] = $dadosFinalizacao;
}

/*
|--------------------------------------------------------------------------
| CÁLCULO DOS DADOS FINAIS
|--------------------------------------------------------------------------
*/

// Dados finais do jogador
$hpJogadorAtual = (int) $pokemonAtacante["hp_atual"];
$hpJogadorMaximo = (int) $pokemonAtacante["hp_maximo"];

$porcentagemHpJogador = $hpJogadorMaximo > 0
    ? ($hpJogadorAtual / $hpJogadorMaximo) * 100
    : 0;

// Dados finais do adversário
$hpAdversarioAtual = (int) $pokemonAdversario["hp_atual"];
$hpAdversarioMaximo = (int) $pokemonAdversario["hp_maximo"];

$porcentagemHpAdversario = $hpAdversarioMaximo > 0
    ? ($hpAdversarioAtual / $hpAdversarioMaximo) * 100
    : 0;

/*
|--------------------------------------------------------------------------
| RESPOSTA PARA O JAVASCRIPT
|--------------------------------------------------------------------------
*/

echo json_encode([

    "sucesso" => true,

    // Controle da troca e do encerramento da batalha
    "solicitar_troca" => $aguardandoTroca,
    "resultado_batalha" => $resultadoBatalha,

    // Dados das recompensas da batalha
    "recompensas" => $dadosFinalizacao,

    "indice_adversario" => $indiceAdversario,

    // Informa se um novo adversário entrou
    "pokemon_adversario_entrou" => $pokemonAdversarioEntrou,

    // Dados atualizados da batalha
    "pokemon_adversario" => $pokemonAdversario,
    "pokemons_selecionados" => $pokemonsSelecionados,

    // Dados do item utilizado
    "item_utilizado" => $usarItem ? [
        "id_item" => (int) $idItem,
        "nome" => $itemUtilizado["nome"],
        "quantidade_restante" => max(
            0,
            (int) $itemUtilizado["quantidade"] - 1
        )
    ] : null,

    // Acontecimentos individuais do turno
    "eventos" => $eventos,

    // Mensagens gerais
    "mensagem" => implode(" ", $mensagens),
    "mensagens_efeito" => $mensagensEfeito,
    "mensagem_efetividade" => implode(" ", $mensagensEfetividade),

    // Ordem dos ataques
    "ordem_turnos" => $ordemTurnos,

    // Ataques utilizados
    "ataque" => $ataqueJogador,
    "ataque_adversario" => $ataqueAdversario,

    // Danos causados
    "dano" => $danoJogador,
    "dano_adversario" => $danoAdversario,

    // Efetividade
    "efetividade" => $efetividadeJogador,
    "efetividade_adversario" => $efetividadeAdversario,

    // HP do adversário
    "hp_atual" => $hpAdversarioAtual,
    "hp_maximo" => $hpAdversarioMaximo,
    "porcentagem_hp" => $porcentagemHpAdversario,

    // HP do jogador
    "hp_jogador" => $hpJogadorAtual,
    "hp_maximo_jogador" => $hpJogadorMaximo,
    "porcentagem_hp_jogador" => $porcentagemHpJogador,

    // Estágios dos atributos
    "estagios_atacante" => $pokemonAtacante["estagios"],
    "estagios_adversario" => $pokemonAdversario["estagios"]

], JSON_UNESCAPED_UNICODE);
