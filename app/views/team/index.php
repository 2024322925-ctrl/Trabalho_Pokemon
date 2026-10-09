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

    <title>Team Builder</title>
</head>
<body>

    <h1>TEAM BUILDER</h1>

    <p>Pokémon disponíveis: <span id="quantidade-pokemons">Carregando...</span></p>

    <script src="../../../public/js/script.js"></script>

</body>
</html>