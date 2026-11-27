<?php

require_once __DIR__ . "/../../app/autoload.php";


session_start();

header("Content-Type: application/json; charset=UTF-8");



function responder($dados, $status = 200)
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION["id_usuario"])) {
    responder([
        "sucesso" => false,
        "mensagem" => "Usuário não autenticado."
    ], 401);
}

// A compra só pode ser realizada por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responder([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ], 405);
}

// Recebe os dados enviados pelo JavaScript
$dados = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
    responder([
        "sucesso" => false,
        "mensagem" => "Dados inválidos."
    ], 400);
}

$itens = $dados["itens"] ?? null;

if (!is_array($itens) || empty($itens)) {
    responder([
        "sucesso" => false,
        "mensagem" => "Nenhum item foi informado."
    ], 400);
}

try {

    $compraController = new CompraController();

    $id_compra = $compraController->realizarCompra(
        (int) $_SESSION["id_usuario"],
        $itens
    );

    responder([
        "sucesso" => true,
        "mensagem" => "Compra realizada com sucesso!",
        "id_compra" => $id_compra
    ]);
} catch (Throwable $e) {

    error_log("Erro ao realizar compra: " . $e->getMessage());

    responder([
        "sucesso" => false,
        "mensagem" => "Não foi possível concluir a compra."
    ], 500);
}
