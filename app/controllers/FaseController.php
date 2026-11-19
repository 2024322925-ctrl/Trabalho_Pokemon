
<?php

require_once __DIR__ . "/../dao/FaseDAO.php";

class FaseController
{
    private $faseDAO;

    public function __construct()
    {
        $this->faseDAO = new FaseDAO();
    }

    // Lista as fases e o progresso do usuário
    public function listarFases($idUsuario)
    {
        return $this->faseDAO->listarFasesPorUsuario($idUsuario);
    }

    // Busca uma fase pelo ID
    public function buscarFase($idFase)
    {
        return $this->faseDAO->buscarPorId($idFase);
    }
}

?>
