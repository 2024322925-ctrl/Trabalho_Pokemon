
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
        "mensagem" => "Nenhuma batalha encontrada."
    ]);
    exit;
}

// Verifica o método da requisição
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Requisição inválida."
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

// Recupera o Pokémon ativo na batalha
$idPokemon = (int) $_SESSION["batalha"]["id_pokemon_ativo"];

// Valida se o ataque pertence ao Pokémon ativo
$ataque = $batalhaController->validarAtaque(
    $idPokemon,
    $idAtaque
);

if (!$ataque) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Esse Pokémon não possui esse ataque."
    ]);
    exit;
}

// Retorna os dados do ataque validado
echo json_encode([
    "sucesso" => true,
    "mensagem" => "Ataque recebido.",
    "ataque" => $ataque
]);

