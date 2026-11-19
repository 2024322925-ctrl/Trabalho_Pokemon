
<?php

class Fase
{
    private $idFase;
    private $nome;
    private $tipo;

    public function __construct($nome = null, $tipo = null)
    {
        $this->nome = $nome;
        $this->tipo = $tipo;
    }

    public function getIdFase()
    {
        return $this->idFase;
    }

    public function setIdFase($idFase)
    {
        $this->idFase = $idFase;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }
}

?>
