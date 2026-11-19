
<?php

class PokeApiService
{
    private $urlBaseItem = "https://pokeapi.co/api/v2/item/";
    private $urlBaseEspecie = "https://pokeapi.co/api/v2/pokemon-species/";

    // Realiza uma requisição à PokéAPI
    private function buscarDados($url)
    {
        $contexto = stream_context_create([
            "http" => [
                "timeout" => 15
            ]
        ]);

        $resposta = @file_get_contents($url, false, $contexto);

        if ($resposta === false) {
            throw new RuntimeException(
                "Não foi possível acessar a PokéAPI."
            );
        }

        $dados = json_decode($resposta, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
            throw new RuntimeException(
                "Não foi possível interpretar os dados da API."
            );
        }

        return $dados;
    }

    // Busca itens
    public function buscarItem($nomeApi)
    {
        $nomeApi = strtolower(trim($nomeApi));

        if (empty($nomeApi)) {
            throw new InvalidArgumentException(
                "O nome do item não pode estar vazio."
            );
        }

        $url = $this->urlBaseItem . rawurlencode($nomeApi) . "/";

        return $this->buscarDados($url);
    }

    // Busca a espécie do Pokémon
    public function buscarEspecie($nomeOuId)
    {
        $nomeOuId = strtolower(trim((string) $nomeOuId));

        if ($nomeOuId === '') {
            throw new InvalidArgumentException(
                "O Pokémon não pode estar vazio."
            );
        }

        $url = $this->urlBaseEspecie . rawurlencode($nomeOuId) . "/";

        return $this->buscarDados($url);
    }

    // Busca a cadeia evolutiva
    public function buscarCadeiaEvolutiva($url)
    {
        $prefixo = "https://pokeapi.co/api/v2/evolution-chain/";

        if (
            !is_string($url) ||
            strpos($url, $prefixo) !== 0
        ) {
            throw new InvalidArgumentException(
                "URL de cadeia evolutiva inválida."
            );
        }

        return $this->buscarDados($url);
    }
}

?>
