
<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../app/controllers/TimePokemonController.php";
require_once __DIR__ . "/../../app/controllers/TimeMembroController.php";
require_once __DIR__ . "/../../app/models/TimeMembro.php";

function responder($dados) {
    echo json_encode($dados);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION["id_usuario"])) {
    responder([
        "sucesso" => false,
        "mensagem" => "Usuário não autenticado."
    ]);
}

$usuarioId = $_SESSION["id_usuario"];

$timeController = new TimePokemonController();
$membroController = new TimeMembroController();

// Busca o time ativo do usuário
$time = $timeController->buscarTimeAtivo($usuarioId);

if ($time == null) {
    responder([
        "sucesso" => false,
        "mensagem" => "Nenhum time encontrado."
    ]);
}

$id_time = $time->getIdTime();

// GET: listar Pokémon do time
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $pokemons = $membroController->listarPokemonsDoTime($id_time);

    responder([
        "sucesso" => true,
        "pokemons" => $pokemons
    ]);
}

// POST: adicionar ou remover Pokémon
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {
        responder([
            "sucesso" => false,
            "mensagem" => "Dados inválidos."
        ]);
    }

    $acao = $dados["acao"] ?? "";
    $id_pokemon = $dados["id_pokemon"] ?? 0;

    if (!is_numeric($id_pokemon) || (int) $id_pokemon <= 0) {
        responder([
            "sucesso" => false,
            "mensagem" => "Pokémon inválido."
        ]);
    }

    $id_pokemon = (int) $id_pokemon;

    // Adicionar Pokémon
    if ($acao == "adicionar") {

        $pokemons = $membroController->listarPokemonsDoTime($id_time);

        if (count($pokemons) >= 6) {
            responder([
                "sucesso" => false,
                "mensagem" => "Seu time já está completo."
            ]);
        }

        foreach ($pokemons as $pokemon) {
            if ($pokemon["id_pokemon"] == $id_pokemon) {
                responder([
                    "sucesso" => false,
                    "mensagem" => "Esse Pokémon já está no seu time."
                ]);
            }
        }

        $posicao = 1;

        foreach ($pokemons as $pokemon) {
            $posicao = max($posicao, (int) $pokemon["posicao"] + 1);
        }

        $membro = new TimeMembro();
        $membro->setIdTime($id_time);
        $membro->setIdPokemon($id_pokemon);
        $membro->setPosicao($posicao);

        $resultado = $membroController->adicionarPokemon($membro);

        responder([
            "sucesso" => $resultado,
            "mensagem" => $resultado
                ? "Pokémon adicionado!"
                : "Não foi possível adicionar o Pokémon."
        ]);
    }

    // Remover Pokémon
    if ($acao == "remover") {

        $resultado = $membroController->removerPokemon(
            $id_time,
            $id_pokemon
        );

        responder([
            "sucesso" => $resultado,
            "mensagem" => $resultado
                ? "Pokémon removido!"
                : "Não foi possível remover o Pokémon."
        ]);
    }
}

responder([
    "sucesso" => false,
    "mensagem" => "Método não permitido."
]);

?>
