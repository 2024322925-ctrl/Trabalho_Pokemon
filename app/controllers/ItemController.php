
<?php

require_once __DIR__ . "/../dao/ItemDAO.php";
require_once __DIR__ . "/../services/PokeApiService.php";

class ItemController
{
    private $itemDAO;
    private $pokeApi;

    public function __construct()
    {
        $this->itemDAO = new ItemDAO();
        $this->pokeApi = new PokeApiService();
    }

    public function listarItensDisponiveis()
    {
        // Busca os itens cadastrados no banco
        $itens = $this->itemDAO->listarItensDisponiveis();

        // Complementa as informações utilizando a PokéAPI
        foreach ($itens as &$item) {

            $nomeApi = strtolower(
                str_replace(" ", "-", trim($item["nome"]))
            );

            try {

                $dadosApi = $this->pokeApi->buscarItem($nomeApi);

                // Atualiza a imagem com a imagem da PokéAPI
                if (!empty($dadosApi["sprites"]["default"])) {
                    $item["imagem_item"] = $dadosApi["sprites"]["default"];
                }

            } catch (Throwable $e) {

                // Mantém a imagem cadastrada no banco caso a API falhe
                error_log(
                    "Erro ao consultar item na PokéAPI: " .
                    $item["nome"] . " - " . $e->getMessage()
                );
            }
        }

        unset($item);

        return $itens;
    }
}
?>
