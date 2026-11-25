
<?php

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

require_once __DIR__ . "/../../app/controllers/BatalhaController.php";

$batalhaController = new BatalhaController();

// Recupera o ID do ataque enviado pelo jogador
$idAtaque = filter_input(INPUT_POST, "id_ataque", FILTER_VALIDATE_INT);

if (!$idAtaque || $idAtaque <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Ataque inválido."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Recupera os dados da batalha
$batalha = $_SESSION["batalha"];

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

// Verifica se algum Pokémon já foi derrotado
if ($hpAtacante <= 0 || $hpAdversario <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não é possível atacar neste momento."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Valida se o ataque pertence ao Pokémon ativo
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

    // Mensagem de efetividade para ambos os Pokémon
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
    &$efetividadeAdversario
) {

    // Verifica se o Pokémon pode agir diretamente na variável original
    if ($lado === "jogador") {

        $condicao = $batalhaController->verificarCondicaoDeAcao(
            $pokemonAtacante
        );

    } else {

        $condicao = $batalhaController->verificarCondicaoDeAcao(
            $pokemonAdversario
        );
    }

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

    // Executa o ataque conforme o lado
    if ($lado === "jogador") {

        $resultado = $batalhaController->executarAtaque(
            $pokemonAtacante,
            $pokemonAdversario,
            $ataque
        );

        $pokemonAtacante = $resultado["atacante"];
        $pokemonAdversario = $resultado["defensor"];

        $danoJogador = $resultado["dano"];
        $efetividadeJogador = $resultado["efetividade"];

    } else {

        $resultado = $batalhaController->executarAtaque(
            $pokemonAdversario,
            $pokemonAtacante,
            $ataque
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

    $mensagens[] = $resultado["mensagem"];

    if (!empty($resultado["mensagem_efetividade"])) {
        $mensagensEfetividade[] = $resultado["mensagem_efetividade"];
    }

    $mensagensEfeito = array_merge(
        $mensagensEfeito,
        $resultado["mensagens_efeito"] ?? []
    );
};


// Define a ordem dos ataques
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
| ATUALIZAÇÃO DA SESSÃO
|--------------------------------------------------------------------------
*/

// Atualiza os dados do Pokémon do jogador
$pokemonsSelecionados[$indiceAtacante] = $pokemonAtacante;

$_SESSION["batalha"]["pokemons_selecionados"] = $pokemonsSelecionados;

// Atualiza a cópia do Pokémon inicial, se for o ativo
if (
    isset($_SESSION["batalha"]["pokemon_inicial"]) &&
    (int) $_SESSION["batalha"]["pokemon_inicial"]["id_pokemon"] === (int) $idPokemonAtivo
) {
    $_SESSION["batalha"]["pokemon_inicial"] = $pokemonAtacante;
}

// Atualiza o adversário na lista da fase
$_SESSION["batalha"]["pokemons_fase"][$indiceAdversario] = $pokemonAdversario;

// Atualiza a cópia do adversário atual
$_SESSION["batalha"]["pokemon_adversario"] = $pokemonAdversario;


/*
|--------------------------------------------------------------------------
| PERSISTÊNCIA DO HP NO BANCO
|--------------------------------------------------------------------------
*/

// Busca o time ativo do jogador
$timeAtivo = $batalhaController->buscarTimeAtivo(
    $_SESSION["id_usuario"]
);

if (!$timeAtivo) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível localizar o time do jogador."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Salva o HP e o status atual do Pokémon do jogador
$salvouEstado = $batalhaController->atualizarEstadoPokemon(
    $timeAtivo["id_time"],
    $idPokemonAtivo,
    $pokemonAtacante["hp_atual"],
    $pokemonAtacante["status_atual"] ?? null
);

if (!$salvouEstado) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível atualizar o HP do Pokémon no banco."
    ], JSON_UNESCAPED_UNICODE);
    exit;
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
