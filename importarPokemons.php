<?php

require_once __DIR__ . "/app/services/PokemonAPI.php";
require_once __DIR__ . "/app/dao/PokemonDAO.php";

set_time_limit(0);

$api = new PokemonAPI();
$dao = new PokemonDAO();

$sucessos = 0;
$erros = 0;

echo "<h1>Importação dos Pokémon</h1>";
echo "<pre>";

for ($id = 1; $id <= 151; $id++) {
    try {
        // Busca os dados na PokéAPI
        $dados = $api->buscarPokemon($id);

        // Salva os dados no banco
        $dao->salvarDaAPI($dados);

        $sucessos++;

        echo "[$id/151] " . htmlspecialchars($dados["nome"])
            . " importado com sucesso!\n";

    } catch (Throwable $e) {
        $erros++;

        echo "[$id/151] Erro ao importar o Pokémon de ID $id.\n";
        error_log(
            "Erro ao importar Pokémon $id: " . $e->getMessage()
        );
    }

    // Atualiza a saída no navegador
    flush();
}

echo "\n--------------------------------\n";
echo "Importação finalizada!\n";
echo "Importados com sucesso: $sucessos\n";
echo "Erros: $erros\n";

echo "</pre>";