<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login/index.php");
    exit;
}

$nome = $_SESSION["nome"];
$sobrenome = $_SESSION["sobrenome"];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/styleHome.css">
    <title>Pokémon Home</title>
</head>
<body>
    <main class="home">
    <img src="../../../public/imagens/titulo2.png" alt="Pokémon" class="titulo">

    <section class="menu-home">
        <div class="container-menu">

        <div class="coluna-menu">

            <a href="../team/index.php" class="botao-menu">
                <div class="icone-menu">
                    <img src="../../../public/imagens/pokemons-Photoroom.png" alt="icone">
                </div>
                <span>TEAM<br>BUILDER</span>
            </a>

            <a href="../batalha/index.php" class="botao-menu">
                <div class="icone-menu">
                    <img src="../../../public/imagens/pokebolas-Photoroom.png" alt="icone">
                </div>
                <span>BATALHA</span>
            </a>

            <a href="../loja/index.php" class="botao-menu">
                <div class="icone-menu">
                    <img src="../../../public/imagens/loja-Photoroom.png" alt="icone">
                </div>
                <span>LOJA</span>
            </a>

            <a href="../login/index.php?acao=sair" class="botao-menu">
                <div class="icone-menu">
                    <img src="../../../public/imagens/sair-Photoroom.png" alt="icone">
                </div>
                <span>SAIR</span>
            </a>

        </div>

        <div class="perfil">

            <h2>PERFIL DO JOGADOR</h2>

            <div class="dados-jogador">

                <div class="avatar">
                    <img src="../../../public/imagens/ash.png" alt="Ash">
                </div>

                <div class="informacoes">
                    <h3>
                        <?= htmlspecialchars($nome . " " . $sobrenome) ?>
                    </h3>
                    <p>TREINADOR POKÉMON</p>
                </div>

            </div>

            <div class="time">
                <div class="pokemon">
                    <i class="fa-solid fa-bolt"></i>
                </div>

                <div class="pokemon">
                    <i class="fa-solid fa-leaf"></i>
                </div>

                <div class="pokemon">
                    <i class="fa-solid fa-fire"></i>
                </div>

                <div class="pokemon">
                    <i class="fa-solid fa-droplet"></i>
                </div>
            </div>

        </div>
        </div>
    </section>
    </main>
</body>
</html>