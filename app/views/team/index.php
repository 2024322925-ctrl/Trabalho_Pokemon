<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login/index.php");
    exit;
}

require_once __DIR__ . "/../../controllers/TimePokemonController.php";
require_once __DIR__ . "/../../models/TimePokemon.php";

$timeController = new TimePokemonController();

$id_usuario = $_SESSION["id_usuario"];

// Procura um time ativo para o usuário
$time = $timeController->buscarTimeAtivo($id_usuario);

// Se não existir, cria um novo
if ($time == null) {

    $novoTime = new TimePokemon();

    $novoTime->setIdUsuario($id_usuario);
    $novoTime->setNome("Meu time");
    $novoTime->setAtivo(true);

    $id_time = $timeController->cadastrarTime($novoTime);

} else {

    $id_time = $time->getIdTime();

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

    <link rel="stylesheet" href="../../../public/css/styleTeam.css">

    <title>Team Builder | MONSTROBOLSO</title>
</head>
<body>

    <main class="team-builder">

        <header class="cabecalho-team">
            <h1>TEAM BUILDER</h1>
        </header>

        <section class="conteudo-team">

            <section class="painel-time">
                <h2>Meu time</h2>

                <div id="slots-time" class="slots-time">
                    <div class="slot-pokemon" data-slot="0"></div>
                    <div class="slot-pokemon" data-slot="1"></div>
                    <div class="slot-pokemon" data-slot="2"></div>
                    <div class="slot-pokemon" data-slot="3"></div>
                    <div class="slot-pokemon" data-slot="4"></div>
                    <div class="slot-pokemon" data-slot="5"></div>
                </div>
            </section>
            <aside class="painel-detalhes">
                <h2>Detalhes</h2>

                <div id="detalhes-pokemon">
                    <p>Selecione um Pokémon para ver seus detalhes.</p>
                </div>
            </aside>

            <section class="painel-selecao">
                <h2>Escolha seus Pokémons</h2>

                <p class="contador-pokemon">
                    Pokémon disponíveis:
                    <span id="quantidade-pokemons">Carregando...</span>
                </p>

                <input
                    type="search"
                    id="busca-pokemon"
                    placeholder="Buscar Pokémon pelo nome..."
                    autocomplete="off"
                >

                <div id="lista-pokemons" class="lista-pokemons">
                    <p>Carregando Pokémon...</p>
                </div>
            </section>

        </section>

    </main>


   <script>
    const idTime = <?= json_encode((int) $id_time) ?>;
    </script>

    <script src="../../../public/js/script.js"></script>

</body>
</html>
