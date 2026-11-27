
<?php

require_once __DIR__ . "/../../app/autoload.php";

session_start();

header("Content-Type: application/json; charset=UTF-8");



function responder($dados, $status = 200)
{
    http_response_code($status);
    echo json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION["id_usuario"])) {
    responder([
        "sucesso" => false,
        "mensagem" => "Usuário não autenticado."
    ], 401);
}

// Essa API utiliza o método GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responder([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ], 405);
}

try {

    $id_usuario = (int) $_SESSION["id_usuario"];

    $itemController = new ItemController();
    $carteiraController = new CarteiraController();

    // Busca os itens disponíveis na loja
    $itens = $itemController->listarItensDisponiveis();

    // Busca o saldo do jogador
    $saldo = $carteiraController->buscarSaldoPorUsuario($id_usuario);

    if ($saldo === false) {
        throw new RuntimeException("Carteira não encontrada.");
    }

    responder([
        "sucesso" => true,
        "itens" => $itens,
        "saldo" => $saldo
    ]);
} catch (Throwable $e) {

    error_log("Erro ao carregar a Poké Mart: " . $e->getMessage());

    responder([
        "sucesso" => false,
        "mensagem" => "Não foi possível carregar a loja."
    ], 500);
}

?>
