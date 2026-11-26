
<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

// Verifica autenticação
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Usuário não autenticado."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se existe uma batalha
if (!isset($_SESSION["batalha"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Nenhuma batalha está em andamento."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica o método
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método de requisição inválido."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$batalha = $_SESSION["batalha"];

// Só permite a troca quando ela estiver pendente
if (empty($batalha["aguardando_troca"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não é possível trocar de Pokémon neste momento."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se a batalha já terminou
if (!empty($batalha["resultado"])) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "A batalha já foi encerrada."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Recupera o ID escolhido
$idPokemon = filter_input(
    INPUT_POST,
    "id_pokemon",
    FILTER_VALIDATE_INT
);

if (!$idPokemon || $idPokemon <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Pokémon inválido."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Procura o Pokémon escolhido na equipe
$pokemonsSelecionados = $batalha["pokemons_selecionados"] ?? [];

if (!is_array($pokemonsSelecionados) || empty($pokemonsSelecionados)) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível localizar sua equipe."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$indiceEscolhido = null;
$pokemonEscolhido = null;

foreach ($pokemonsSelecionados as $indice => $pokemon) {

    if ((int) $pokemon["id_pokemon"] === (int) $idPokemon) {
        $indiceEscolhido = $indice;
        $pokemonEscolhido = $pokemon;
        break;
    }
}

// Verifica se o Pokémon pertence à equipe
if ($pokemonEscolhido === null) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Esse Pokémon não pertence à sua equipe."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se o Pokémon está vivo
$hpAtual = (int) (
    $pokemonEscolhido["hp_atual"]
    ?? $pokemonEscolhido["hp_atual_exibicao"]
    ?? 0
);

if ($hpAtual <= 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Esse Pokémon está derrotado."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Atualiza o estado de Pokémon ativo na equipe
foreach ($pokemonsSelecionados as $indice => &$pokemon) {
    $pokemon["pokemon_ativo"] = (
        (int) $pokemon["id_pokemon"] === (int) $idPokemon
    );
}
unset($pokemon);

$pokemonEscolhido = $pokemonsSelecionados[$indiceEscolhido];

// Atualiza o Pokémon ativo na batalha
$_SESSION["batalha"]["pokemons_selecionados"] = $pokemonsSelecionados;
$_SESSION["batalha"]["id_pokemon_ativo"] = (int) $idPokemon;
$_SESSION["batalha"]["pokemon_inicial"] = $pokemonEscolhido;

// Libera a batalha novamente
$_SESSION["batalha"]["aguardando_troca"] = false;

// Retorna sucesso
echo json_encode([
    "sucesso" => true,
    "mensagem" => $pokemonEscolhido["nome"]
        . " entrou na batalha!"
], JSON_UNESCAPED_UNICODE);
