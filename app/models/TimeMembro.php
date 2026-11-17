<?php

class TimeMembro
{
    private $id_time;
    private $id_pokemon;
    private $posicao;

    public function getIdTime()
    {
        return $this->id_time;
    }

    public function setIdTime($id_time)
    {
        $this->id_time = $id_time;
    }

    public function getIdPokemon()
    {
        return $this->id_pokemon;
    }

    public function setIdPokemon($id_pokemon)
    {
        $this->id_pokemon = $id_pokemon;
    }

    public function getPosicao()
    {
        return $this->posicao;
    }

    public function setPosicao($posicao)
    {
        $this->posicao = $posicao;
    }
}

?>