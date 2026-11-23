
<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

// Verifica se o usuário está autenticado
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Usuário não autenticado."
    ]);
    exit;
}

// Verifica se existe uma batalha na sessão
if (!isset($_SESSION["batalha"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Nenhuma batalha está em andamento."
    ]);
    exit;
}

// Verifica o método da requisição
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método de requisição inválido."
    ]);
    exit;
}

require_once __DIR__ . "/../../../controllers/BatalhaController.php";

$batalhaController = new BatalhaController();

// Recupera o ID do ataque enviado
$idAtaque = filter_input(INPUT_POST, "id_ataque", FILTER_VALIDATE_INT);

if (!$idAtaque || $idAtaque <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Ataque inválido."
    ]);
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
    ]);
    exit;
}

// Localiza o Pokémon ativo do jogador
$pokemonAtacante = null;

foreach ($pokemonsSelecionados as $pokemon) {
    if ((int) $pokemon["id_pokemon"] === (int) $idPokemonAtivo) {
        $pokemonAtacante = $pokemon;
        break;
    }
}

// Verifica se o Pokémon ativo foi encontrado
if (!$pokemonAtacante) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Pokémon ativo não encontrado."
    ]);
    exit;
}

// Recupera o adversário atual
$pokemonAdversario = $pokemonsFase[$indiceAdversario];

// Verifica se algum dos Pokémon já foi derrotado
$hpAtacante = (int) ($pokemonAtacante["hp_atual"] ?? 0);

$hpAdversario = (int) (
    $pokemonAdversario["hp_atual_exibicao"]
    ?? $pokemonAdversario["hp_atual"]
    ?? $pokemonAdversario["hp_maximo"]
    ?? $pokemonAdversario["hp"]
    ?? 0
);

if ($hpAtacante <= 0 || $hpAdversario <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não é possível atacar neste momento."
    ]);
    exit;
}

// Valida se o ataque pertence ao Pokémon ativo
$ataque = $batalhaController->validarAtaque(
    $idPokemonAtivo,
    $idAtaque
);

if (!$ataque) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Esse Pokémon não possui esse ataque."
    ]);
    exit;
}

// Por enquanto, somente ataques físicos e especiais causam dano
if (!in_array($ataque["categoria"], ["physical", "special"], true)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Os efeitos desse ataque ainda serão implementados."
    ]);
    exit;
}

// Calcula a efetividade do ataque contra os tipos do adversário
$efetividade = $batalhaController->calcularEfetividadeTipos(
    $ataque["id_tipo"],
    $pokemonAdversario["tipos"] ?? []
);

// Identifica a mensagem de efetividade
if ($efetividade == 0) {
    $mensagemEfetividade = "Não teve efeito!";
} elseif ($efetividade > 1) {
    $mensagemEfetividade = "Foi super efetivo!";
} elseif ($efetividade < 1) {
    $mensagemEfetividade = "Não foi muito efetivo...";
} else {
    $mensagemEfetividade = "";
}

// Calcula o dano
$dano = $batalhaController->calcularDano(
    $pokemonAtacante,
    $pokemonAdversario,
    $ataque
);

// Atualiza o HP do adversário
$hpMaximo = (int) (
    $pokemonAdversario["hp_maximo"]
    ?? $pokemonAdversario["hp"]
);

$hpAtual = max(0, $hpAdversario - $dano);

$porcentagemHp = $hpMaximo > 0
    ? ($hpAtual / $hpMaximo) * 100
    : 0;

// Atualiza os dados do adversário na sessão
$pokemonAdversario["hp_atual"] = $hpAtual;
$pokemonAdversario["hp_atual_exibicao"] = $hpAtual;
$pokemonAdversario["porcentagem_hp"] = $porcentagemHp;

// Salva o adversário atualizado na lista da fase
$_SESSION["batalha"]["pokemons_fase"][$indiceAdversario] = $pokemonAdversario;

// Mantém a cópia do adversário atualizada
$_SESSION["batalha"]["pokemon_adversario"] = $pokemonAdversario;

// Retorna os dados para o JavaScript
echo json_encode([
    "sucesso" => true,
    "mensagem" => $pokemonAtacante["nome"] . " usou " . $ataque["nome"] . " e causou " . $dano . " de dano!",
    "mensagem_efetividade" => $mensagemEfetividade,
    "efetividade" => $efetividade,
    "ataque" => $ataque,
    "dano" => $dano,
    "hp_atual" => $hpAtual,
    "hp_maximo" => $hpMaximo,
    "porcentagem_hp" => $porcentagemHp
], JSON_UNESCAPED_UNICODE);
