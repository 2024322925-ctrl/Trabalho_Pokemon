<?php

final class TimeMembro implements JsonSerializable
{
    private Pokemon $pokemon;
    private int $posicao;

    public function __construct(Pokemon $pokemon, int $posicao)
    {
        $this->pokemon = $pokemon;
        $this->posicao = $posicao;
    }

    public function getPokemon(): Pokemon
    {
        return $this->pokemon;
    }

    public function getPosicao(): int
    {
        return $this->posicao;
    }

    public function jsonSerialize(): array
    {
        return $this->pokemon->dados() + ["posicao" => $this->posicao];
    }
}