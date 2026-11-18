
<?php

class PokeApiService
{
    private $urlBase = "https://pokeapi.co/api/v2/item/";

    public function buscarItem($nomeApi)
    {
        $nomeApi = strtolower(trim($nomeApi));

        if (empty($nomeApi)) {
            throw new InvalidArgumentException("O nome do item não pode estar vazio.");
        }

        $url = $this->urlBase . rawurlencode($nomeApi) . "/";

        $contexto = stream_context_create([
            "http" => [
                "timeout" => 10
            ]
        ]);

        $resposta = @file_get_contents($url, false, $contexto);

        if ($resposta === false) {
            throw new RuntimeException("Não foi possível acessar a PokéAPI.");
        }

        $dados = json_decode($resposta, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
            throw new RuntimeException("Não foi possível interpretar os dados da API.");
        }

        return $dados;
    }
}
