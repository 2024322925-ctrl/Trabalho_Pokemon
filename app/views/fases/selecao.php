<?php

require_once __DIR__ . "/../../autoload.php";

session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../../login/index.php");
    exit;
}

$idUsuario = (int) $_SESSION["id_usuario"];

// Verifica se o ID da fase foi informado
$idFase = isset($_GET["id_fase"])
    ? (int) $_GET["id_fase"]
    : 0;

// Verifica se o ID da fase é válido
if ($idFase < 1 || $idFase > 8) {
    header("Location: index.php");
    exit;
}

// Carrega os Controllers


$faseController = new FaseController();
$batalhaController = new BatalhaController();

// Verifica se a fase está desbloqueada
if (!$faseController->verificarAcessoFase($idUsuario, $idFase)) {
    header("Location: index.php");
    exit;
}

// Carrega o time e os Pokémon para a seleção

// Carrega o time e os Pokémon para a seleção
$dadosSelecao = $batalhaController->carregarSelecao($idUsuario);

$timeAtivo = $dadosSelecao["time_ativo"];
$pokemons = $dadosSelecao["pokemons"];

$erro = "";

// Recupera todos os Pokémon do time ao entrar na seleção de fase
if ($timeAtivo && !empty($pokemons)) {

    $idsPokemons = [];

    foreach ($pokemons as $pokemon) {
        $idsPokemons[] = (int) $pokemon["id_pokemon"];
    }

    $recuperouEstado = $batalhaController
        ->recuperarEstadoDosPokemonsSelecionados(
            (int) $timeAtivo["id_time"],
            $idsPokemons
        );

    if ($recuperouEstado) {

        // Recarrega os dados para exibir os HPs atualizados
        $dadosSelecao = $batalhaController->carregarSelecao($idUsuario);

        $timeAtivo = $dadosSelecao["time_ativo"];
        $pokemons = $dadosSelecao["pokemons"];

    } else {

        $erro = "Não foi possível recuperar os Pokémon do time.";
    }
}


// Processa o formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $resultado = $batalhaController->validarSelecao(
        $timeAtivo,
        $pokemons,
        $_POST
    );

    if (!$resultado["sucesso"]) {
        $erro = $resultado["erro"];
    } else {

        // Guarda a preparação na sessão
        $_SESSION["preparacao_batalha"] = [
            "id_fase" => (int) $idFase,
            "id_time" => $resultado["preparacao"]["id_time"],
            "pokemons" => $resultado["preparacao"]["pokemons"],
            "pokemon_inicial" => $resultado["preparacao"]["pokemon_inicial"]
        ];

        header("Location: batalha/index.php?id_fase=" . (int) $idFase);
        exit;
    }
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

    <link rel="stylesheet" href="../../../public/css/styleSelecao.css">

    <title>Seleção de Pokémon | MONSTROBOLSO</title>
</head>

<body>

    <main class="pagina-selecao">

        <header class="cabecalho-selecao">
            <h1>FASE <?= (int) $idFase ?></h1>
            <h2>ESCOLHA SEUS POKÉMON</h2>
            <p>Selecione 3 Pokémon e escolha qual começará a batalha.</p>
        </header>

        <?php if ($erro !== ""): ?>
            <p class="mensagem-erro">
                <?= htmlspecialchars($erro) ?>
            </p>
        <?php endif; ?>

        <?php if (!$timeAtivo): ?>

            <div class="mensagem-vazia">
                <h2>Nenhum time ativo encontrado!</h2>
                <p>Você precisa ter um time ativo para participar da batalha.</p>
            </div>

        <?php elseif (count($pokemons) < 3): ?>

            <div class="mensagem-vazia">
                <h2>Pokémon insuficientes!</h2>
                <p>Você precisa ter pelo menos 3 Pokémon no seu time.</p>
            </div>

        <?php else: ?>

            <form method="POST" id="formSelecao">

                <div class="contador-selecao">
                    Pokémon selecionados:
                    <span id="quantidadeSelecionada">0</span>/3
                </div>

                <div class="lista-pokemons">

                    <?php foreach ($pokemons as $pokemon): ?>

                        <label class="cartao-pokemon">

                            <input
                                type="checkbox"
                                name="pokemons[]"
                                value="<?= (int) $pokemon["id_pokemon"] ?>"
                                class="checkbox-pokemon"
                            >

                            <div class="imagem-pokemon">
                                <?php if (!empty($pokemon["imagem"])): ?>
                                    <img
                                        src="<?= htmlspecialchars($pokemon["imagem"]) ?>"
                                        alt="<?= htmlspecialchars($pokemon["nome"]) ?>"
                                    >
                                <?php else: ?>
                                    <span>Sem imagem</span>
                                <?php endif; ?>
                            </div>

                            <div class="informacoes-pokemon">

                                <h3>
                                    <?= htmlspecialchars($pokemon["nome"]) ?>
                                </h3>

                                <p>
                                    Nível: <?= (int) $pokemon["nivel"] ?>
                                </p>

                                <p>
                                    HP:
                                    <?= (int) $pokemon["hp_atual"] ?>
                                    /
                                    <?= (int) $pokemon["hp"] ?>
                                </p>

                                <div class="escolha-inicial">
                                    <input
                                        type="radio"
                                        name="pokemon_inicial"
                                        value="<?= (int) $pokemon["id_pokemon"] ?>"
                                        class="radio-inicial"
                                        disabled
                                    >
                                    <span>Começar a batalha</span>
                                </div>

                            </div>

                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="rodape-selecao">

                    <a href="index.php" class="botao-voltar">
                        Voltar ao mapa
                    </a>

                    <button type="submit" class="botao-confirmar">
                        Confirmar e Continuar
                    </button>

                </div>

            </form>

        <?php endif; ?>

    </main>

    <script>
        const checkboxes = document.querySelectorAll(".checkbox-pokemon");
        const radios = document.querySelectorAll(".radio-inicial");
        const contador = document.getElementById("quantidadeSelecionada");
        const formulario = document.getElementById("formSelecao");

        function atualizarSelecao() {

            const selecionados = document.querySelectorAll(
                ".checkbox-pokemon:checked"
            );

            if (contador) {
                contador.textContent = selecionados.length;
            }

            checkboxes.forEach((checkbox) => {

                const cartao = checkbox.closest(".cartao-pokemon");
                const radio = cartao.querySelector(".radio-inicial");

                cartao.classList.toggle("selecionado", checkbox.checked);

                if (!checkbox.checked) {
                    radio.checked = false;
                    radio.disabled = true;
                } else {
                    radio.disabled = false;
                }

            });
        }

        checkboxes.forEach((checkbox) => {

            checkbox.addEventListener("change", function () {

                const quantidade = document.querySelectorAll(
                    ".checkbox-pokemon:checked"
                ).length;

                if (quantidade > 3) {
                    this.checked = false;
                    alert("Você pode selecionar somente 3 Pokémon.");
                }

                atualizarSelecao();
            });

        });

        formulario?.addEventListener("submit", function (evento) {

            const selecionados = document.querySelectorAll(
                ".checkbox-pokemon:checked"
            );

            const inicial = document.querySelector(
                ".radio-inicial:checked"
            );

            if (selecionados.length !== 3) {
                evento.preventDefault();
                alert("Selecione exatamente 3 Pokémon.");
                return;
            }

            if (!inicial) {
                evento.preventDefault();
                alert("Escolha qual Pokémon começará a batalha.");
            }

        });

        atualizarSelecao();
    </script>

</body>
</html>

