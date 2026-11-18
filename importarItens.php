
<?php

require_once __DIR__ . "/app/services/PokeApiService.php";
require_once __DIR__ . "/app/dao/ItemDAO.php";

set_time_limit(0);

$api = new PokeApiService();
$dao = new ItemDAO();

$sucessos = 0;
$erros = 0;

// IDs do banco associados aos nomes utilizados pela PokéAPI
$itens = [
    1  => "potion",
    2  => "super-potion",
    3  => "hyper-potion",
    4  => "max-potion",
    5  => "full-restore",
    6  => "antidote",
    7  => "burn-heal",
    8  => "ice-heal",
    9  => "awakening",
    10 => "paralyze-heal",
    11 => "full-heal",
    12 => "revive",
    13 => "fire-stone",
    14 => "water-stone",
    15 => "thunder-stone",
    16 => "leaf-stone",
    17 => "moon-stone"
];

echo "<h1>Importação dos Itens</h1>";
echo "<pre>";

foreach ($itens as $idItem => $nomeApi) {

    try {

        // Busca os dados do item na PokéAPI
        $dados = $api->buscarItem($nomeApi);

        // Formata o nome para exibição na loja
        $nome = ucwords(
            str_replace("-", " ", $dados["name"])
        );

        // Obtém a imagem padrão
        $imagem = $dados["sprites"]["default"] ?? null;

        // Atualiza os dados no banco
        $dao->atualizarDadosDaAPI(
            $idItem,
            $nome,
            $imagem
        );

        $sucessos++;

        echo "[$idItem] " . htmlspecialchars($nome)
            . " atualizado com sucesso!\n";

    } catch (Throwable $e) {

        $erros++;

        echo "[$idItem] Erro ao importar $nomeApi.\n";

        error_log(
            "Erro ao importar item $nomeApi: " . $e->getMessage()
        );
    }

    flush();
}

echo "\n--------------------------------\n";
echo "Importação finalizada!\n";
echo "Atualizados com sucesso: $sucessos\n";
echo "Erros: $erros\n";

echo "</pre>";

?>
