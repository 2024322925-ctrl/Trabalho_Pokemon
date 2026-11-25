<?php

class EvolucaoController
{
    private $dao;

    public function __construct()
    {
        $this->dao = new EvolucaoDAO();
    }

    public function listarEvolucoes($idUsuario, $idTime)
    {
        return $this->dao->listarEvolucoes($idUsuario, $idTime);
    }

    public function evoluir($idUsuario, $idTime, $idPokemon, $entrada)
    {
        $idEvolucao = filter_var($entrada, FILTER_VALIDATE_INT);
        if ($idEvolucao === false || $idEvolucao <= 0) {
            throw new EvolucaoException("Evolução inválida.");
        }

        $this->dao->evoluir($idUsuario, $idTime, $idPokemon, $idEvolucao);
    }

    public function importarRegras()
    {
        return $this->dao->importar(require __DIR__ . "/../models/regrasEvolucao.php");
    }
}
