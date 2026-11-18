
<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login/index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../../public/css/styleLoja.css">

    <title>Poké Mart | MONSTROBOLSO</title>
</head>

<body>

    <a href="../home/index.php" class="botao-voltar">
        Voltar ao início
    </a>

    <main class="loja">

        <header class="cabecalho-loja">
            <h1>POKÉ MART</h1>

            <div class="carteira-jogador">
                <span>Meus Pokédollars:</span>
                <strong id="saldo-jogador">Carregando...</strong>
            </div>
        </header>

        <section class="conteudo-loja">

            <div id="mensagem-loja"></div>

            <section class="painel-itens">

                <h2>Itens disponíveis</h2>

                <div id="lista-itens" class="lista-itens">
                    <p>Carregando itens...</p>
                </div>

            </section>

        </section>

    </main>

    <script src="../../../public/js/loja.js"></script>

</body>
</html>
