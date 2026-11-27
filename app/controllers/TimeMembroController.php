<?php

class TimeMembroController
{
    private TimeMembroDAO $timeMembroDAO;

    public function __construct()
    {
        $this->timeMembroDAO = new TimeMembroDAO();
    }

    public function adicionarPokemon(TimePokemon $time, Pokemon $pokemon): void
    {
        $this->timeMembroDAO->adicionar($time, $pokemon);
    }
}