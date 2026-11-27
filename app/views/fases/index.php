
<?php

require_once __DIR__ . "/../../autoload.php";

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login/index.php");
    exit;
}



$faseController = new FaseController();

$id_usuario = $_SESSION["id_usuario"];

$fases = $faseController->listarFases($id_usuario);

// Posições dos botões em porcentagem da imagem
$posicoes = [
    1 => ["x" => 32.3, "y" => 95.6],
    2 => ["x" => 39.5, "y" => 72.4],
    3 => ["x" => 64.7, "y" => 63.6],
    4 => ["x" => 32.5, "y" => 49.9],
    5 => ["x" => 61.8, "y" => 38.5],
    6 => ["x" => 37.9, "y" => 27.7],
    7 => ["x" => 68.5, "y" => 19.9],
    8 => ["x" => 31.1, "y" => 13.1]
];

$maiorFaseDesbloqueada = !empty($fases)
    ? (int) $fases[0]["maior_fase_desbloqueada"]
    : 1;

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../../public/css/styleFases.css">

    <title>Fases | MONSTROBOLSO</title>
</head>

<body>

    <a href="../home/index.php" class="botao-voltar">
        <div class="icone-menu">
            <img src="../../../public/imagens/pikachu.png" alt="Pikachu">
        </div>
        Voltar ao<br>início
    </a>

    <main class="pagina-fases">

        <div class="mapa-fases">

            <img
                src="../../../public/imagens/mapa.jpg"
                alt="Mapa de seleção das fases Pokémon"
                class="imagem-mapa"
            >

            <?php foreach ($fases as $fase): ?>

                <?php
                    $idFase = (int) $fase["id_fase"];

                    if (!isset($posicoes[$idFase])) {
                        continue;
                    }

                    $desbloqueada = $idFase <= $maiorFaseDesbloqueada;
                    $concluida = (bool) $fase["concluida"];

                    $x = $posicoes[$idFase]["x"];
                    $y = $posicoes[$idFase]["y"];
                ?>

                <?php if ($desbloqueada): ?>

                    <a
                        href="selecao.php?id_fase=<?= $idFase ?>"
                        class="botao-fase <?= $concluida ? 'concluida' : 'desbloqueada' ?>"
                        style="left: <?= $x ?>%; top: <?= $y ?>%;"
                        aria-label="Fase <?= $idFase ?>: <?= htmlspecialchars($fase["nome"]) ?>"
                        title="<?= htmlspecialchars($fase["nome"]) ?>"
                    >
                        <?= $idFase ?>
                    </a>

                <?php else: ?>

                    <button
                        class="botao-fase bloqueada"
                        style="left: <?= $x ?>%; top: <?= $y ?>%;"
                        disabled
                        aria-label="Fase <?= $idFase ?> bloqueada"
                        title="Fase bloqueada"
                    >
                        <?= $idFase ?>
                    </button>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    </main>

    <script>
        window.addEventListener("load", function () {
            window.scrollTo(0, document.body.scrollHeight);
        });
    </script>

</body>
</html>
