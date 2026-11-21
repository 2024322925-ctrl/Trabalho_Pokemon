
<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../../../login/index.php");
    exit;
}

if (!isset($_SESSION["preparacao_batalha"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../../../controllers/BatalhaController.php";

$batalhaController = new BatalhaController();

$dadosBatalha = $batalhaController->prepararBatalha(
    (int) $_SESSION["id_usuario"],
    $_SESSION["preparacao_batalha"]
);

if (!$dadosBatalha["sucesso"]) {

    $idFase = (int) ($_SESSION["preparacao_batalha"]["id_fase"] ?? 0);

    unset($_SESSION["preparacao_batalha"]);

    if ($idFase >= 1 && $idFase <= 8) {
        header("Location: ../selecao.php?id_fase=" . $idFase);
    } else {
        header("Location: ../index.php");
    }

    exit;
}

$pokemonInicial = $dadosBatalha["pokemon_inicial"];
$pokemonAdversario = $dadosBatalha["pokemon_adversario"];
$pokemonsSelecionados = $dadosBatalha["pokemons_selecionados"];

// MAPEAMENTO DAS POSIÇÕES DOS POKÉMON NA ARENA
$posicoesPokemon = [
    "jogador" => ["x" => 28.0, "y" => 55.0],
    "adversario" => ["x" => 56.0, "y" => 18]
];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Batalha - MONSTROBOLSO</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../../../public/css/styleBatalha.css">
</head>

<body>

    <main class="container-batalha">

        <!-- PARTE SUPERIOR: ARENA -->
        <section class="arena-batalha">

            <!-- STATUS DO JOGADOR -->
            <div class="painel-status status-jogador">

                <div class="informacoes-status">
                    <h2><?= htmlspecialchars($pokemonInicial["nome"], ENT_QUOTES, "UTF-8") ?></h2>
                    <span>Nv. <?= (int) $pokemonInicial["nivel"] ?></span>
                </div>

                <div class="barra-hp">
                    <div class="preenchimento-hp"
                        style="width: <?= $pokemonInicial["porcentagem_hp"] ?>%;">
                    </div>
                </div>

                <p class="texto-hp">
                    HP: <?= $pokemonInicial["hp_atual_exibicao"] ?> /
                    <?= $pokemonInicial["hp_maximo"] ?>
                </p>

            </div>

            <!-- EQUIPE DO JOGADOR -->
            <section class="equipe-batalha">

                <h2>SEU TIME</h2>

                <div class="lista-equipe">

                    <?php foreach ($pokemonsSelecionados as $pokemon): ?>

                        <div class="cartao-equipe <?= $pokemon["pokemon_ativo"] ? 'pokemon-ativo' : '' ?>">

                            <img
                                src="<?= htmlspecialchars($pokemon["imagem"], ENT_QUOTES, "UTF-8") ?>"
                                alt="<?= htmlspecialchars($pokemon["nome"], ENT_QUOTES, "UTF-8") ?>"
                            >

                            <div class="dados-equipe">

                                <h3><?= htmlspecialchars($pokemon["nome"], ENT_QUOTES, "UTF-8") ?></h3>

                                <p>Nv. <?= (int) $pokemon["nivel"] ?></p>

                                <div class="barra-hp">
                                    <div class="preenchimento-hp"
                                        style="width: <?= $pokemon["porcentagem_hp"] ?>%;">
                                    </div>
                                </div>

                                <p class="texto-hp">
                                    <?= $pokemon["hp_atual_exibicao"] ?> /
                                    <?= $pokemon["hp_maximo"] ?> HP
                                </p>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

            <!-- POKÉMON DO JOGADOR -->
            <div
                class="pokemon-campo pokemon-jogador"
                style="
                    left: <?= $posicoesPokemon["jogador"]["x"] ?>%;
                    top: <?= $posicoesPokemon["jogador"]["y"] ?>%;
                "
            >

                <div class="plataforma-pokemon"></div>

                <img
                    src="<?= htmlspecialchars($pokemonInicial["imagem_exibicao"], ENT_QUOTES, "UTF-8") ?>"
                    alt="<?= htmlspecialchars($pokemonInicial["nome"], ENT_QUOTES, "UTF-8") ?>"
                >

            </div>

            <!-- POKÉMON ADVERSÁRIO -->
            <div
                class="pokemon-campo pokemon-adversario"
                style="
                    left: <?= $posicoesPokemon["adversario"]["x"] ?>%;
                    top: <?= $posicoesPokemon["adversario"]["y"] ?>%;
                "
            >

                <div class="plataforma-pokemon"></div>

                <img
                    src="<?= htmlspecialchars($pokemonAdversario["imagem_exibicao"], ENT_QUOTES, "UTF-8") ?>"
                    alt="<?= htmlspecialchars($pokemonAdversario["nome"], ENT_QUOTES, "UTF-8") ?>"
                >

            </div>

            <!-- STATUS DO ADVERSÁRIO -->
            <div class="painel-status status-adversario">

                <div class="informacoes-status">
                    <h2><?= htmlspecialchars($pokemonAdversario["nome"], ENT_QUOTES, "UTF-8") ?></h2>
                    <span>Nv. <?= (int) $pokemonAdversario["nivel_adversario"] ?></span>
                </div>

                <div class="barra-hp">
                    <div class="preenchimento-hp"
                        style="width: <?= $pokemonAdversario["porcentagem_hp"] ?>%;">
                    </div>
                </div>

                <p class="texto-hp">
                    HP: <?= $pokemonAdversario["hp_atual_exibicao"] ?> /
                    <?= $pokemonAdversario["hp_maximo"] ?>
                </p>

            </div>

        </section>

        <!-- PARTE INFERIOR -->
        <section class="painel-acoes">

            <div class="caixa-mensagem">

                <p id="mensagem-batalha">
                    O que <?= htmlspecialchars($pokemonInicial["nome"], ENT_QUOTES, "UTF-8") ?> fará?
                </p>

            </div>

            <div class="acoes-batalha">

                <button type="button" id="botao-atacar">
                    ATACAR
                </button>

                <button type="button" id="botao-trocar">
                    TROCAR POKÉMON
                </button>

                <button type="button" id="botao-itens">
                    ITENS
                </button>

                <button type="button" id="botao-fugir">
                    FUGIR
                </button>

            </div>

        </section>

    </main>

</body>

</html>
