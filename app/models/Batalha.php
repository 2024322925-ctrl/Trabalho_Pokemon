
<?php

class Batalha
{
    private $idBatalha;
    private $idUsuario;
    private $resultado;
    private $recompensa;
    private $xpRecebido;
    private $data;
    private $fase;

    public function __construct(
        $idUsuario = null,
        $resultado = null,
        $recompensa = 0,
        $fase = null,
        $xpRecebido = 0
    ) {
        $this->idUsuario = $idUsuario;
        $this->resultado = $resultado;
        $this->recompensa = $recompensa;
        $this->fase = $fase;
        $this->xpRecebido = $xpRecebido;
    }

    public function getIdBatalha()
    {
        return $this->idBatalha;
    }

    public function setIdBatalha($idBatalha)
    {
        $this->idBatalha = $idBatalha;
    }

    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function getResultado()
    {
        return $this->resultado;
    }

    public function setResultado($resultado)
    {
        $this->resultado = $resultado;
    }

    public function getRecompensa()
    {
        return $this->recompensa;
    }

    public function setRecompensa($recompensa)
    {
        $this->recompensa = $recompensa;
    }

    public function getXpRecebido()
    {
        return $this->xpRecebido;
    }

    public function setXpRecebido($xpRecebido)
    {
        $this->xpRecebido = $xpRecebido;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getFase()
    {
        return $this->fase;
    }

    public function setFase($fase)
    {
        $this->fase = $fase;
    }
}

?>
