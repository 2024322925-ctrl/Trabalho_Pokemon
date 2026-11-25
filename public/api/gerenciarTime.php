<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../app/autoload.php";
require_once __DIR__ . "/../../app/controllers/TimePokemonController.php";
require_once __DIR__ . "/../../app/controllers/TimeMembroController.php";
require_once __DIR__ . "/../../app/models/TimeMembro.php";
require_once __DIR__ . "/../../app/controllers/PokemonController.php";

require_once __DIR__ . "/../../app/dao/PokemonDesbloqueioDAO.php";

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

if (!in_array($_SERVER["REQUEST_METHOD"], ["GET", "POST"])) {
    http_response_code(405);

    responder([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
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
$desbloqueio = new PokemonDesbloqueioDAO();
$estado = $desbloqueio->estado($usuarioId);

// GET: listar Pokémon do time
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $pokemons = $membroController->listarPokemonsDoTime($id_time);

    responder([
        "sucesso" => true,
        "pokemons" => $pokemons,
        "desbloqueio" => $estado,
        "evolucoes" => (new EvolucaoController())->listarEvolucoes((int) $usuarioId, $id_time)
    ]);
}

// POST: adicionar ou remover Pokémon
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $dados = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
        responder([
            "sucesso" => false,
            "mensagem" => "Dados inválidos."
        ]);
    }

    $acao = $dados["acao"] ?? "";

    if (in_array($acao, ["escolher_iniciais", "comprar_pokemon"], true)) {
        $entrada = $acao === "escolher_iniciais" ? ($dados["ids"] ?? null) : [$dados["id_pokemon"] ?? null];
        if (!is_array($entrada) || count($entrada) < 1 || count($entrada) > 3) {
            responder(["sucesso" => false, "mensagem" => "Seleção inválida."]);
        }

        $ids = array_map(static function ($id) {
            return filter_var($id, FILTER_VALIDATE_INT);
        }, array_values($entrada));
        $pokemonController = new PokemonController();
        $catalogo = $acao === "escolher_iniciais"
            ? $pokemonController->listarTeamBuilder()
            : $pokemonController->listarLoja();
        $permitidos = array_map("intval", array_column($catalogo, "id_pokemon"));
        if (in_array(false, $ids, true) || count(array_unique($ids)) !== count($ids) ||
            array_diff($ids, $permitidos)) {
            responder(["sucesso" => false, "mensagem" => "Escolha Pokémon distintos do catálogo."]);
        }

        try {
            if ($acao === "escolher_iniciais") {
                $desbloqueio->escolher($usuarioId, $id_time, $ids);
            } else {
                $precos = array_column($catalogo, "preco_pokemon", "id_pokemon");
                $desbloqueio->comprar($usuarioId, $ids[0], $precos[$ids[0]]);
            }
            responder(["sucesso" => true]);
        } catch (DomainException $erro) {
            responder(["sucesso" => false, "mensagem" => $erro->getMessage()]);
        } catch (Throwable $erro) {
            error_log($erro->getMessage());
            http_response_code(500);
            responder(["sucesso" => false, "mensagem" => "Não foi possível concluir a ação."]);
        }
    }

    if (!$estado["escolheu_iniciais"]) {
        responder(["sucesso" => false, "mensagem" => "Confirme seus três Pokémon iniciais primeiro."]);
    }

    $id_pokemon = $dados["id_pokemon"] ?? 0;

    if (!is_numeric($id_pokemon) || (int) $id_pokemon <= 0) {
        responder([
            "sucesso" => false,
            "mensagem" => "Pokémon inválido."
        ]);
    }

    $id_pokemon = (int) $id_pokemon;

    if ($acao === "evoluir") {
        try {
            (new EvolucaoController())->evoluir((int) $usuarioId, $id_time, $id_pokemon, $dados["id_evolucao"] ?? null);
            responder(["sucesso" => true]);
        } catch (EvolucaoException $e) {
            responder(["sucesso" => false, "mensagem" => $e->getMessage()]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            responder(["sucesso" => false, "mensagem" => "Falha ao evoluir."]);
        }
    }


    // Adicionar Pokémon
    if ($acao == "adicionar") {
        if (!in_array($id_pokemon, $estado["desbloqueados"], true)) {
            responder(["sucesso" => false, "mensagem" => "Desbloqueie este Pokémon na Poké Mart."]);
        }

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

        try {
            $resultado = $desbloqueio->removerDoTime($id_time, $id_pokemon);
        } catch (DomainException $erro) {
            responder(["sucesso" => false, "mensagem" => $erro->getMessage()]);
        }

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
