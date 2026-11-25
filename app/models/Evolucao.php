<?php

final class Evolucao implements JsonSerializable
{
    private $dados;

    public function __construct(array $dados)
    {
        $this->dados = [
            "id_evolucao" => (int) $dados["id_evolucao"],
            "id_pokemon" => (int) $dados["id_pokemon"],
            "item" => $dados["item"],
            "destino" => $dados["destino"],
            "quantidade" => (int) $dados["quantidade"],
            "destino_no_time" => (int) $dados["destino_no_time"]
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->dados;
    }
}
