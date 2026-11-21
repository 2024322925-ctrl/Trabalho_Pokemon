
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

    // Verifica se o usuário pode acessar uma fase
    public function verificarAcessoFase($idUsuario, $idFase)
    {
        if ($idFase < 1 || $idFase > 8) {
            return false;
        }

        $fases = $this->listarFases($idUsuario);

        foreach ($fases as $fase) {
            if ((int) $fase["id_fase"] === (int) $idFase) {
                return $idFase <= (int) $fase["maior_fase_desbloqueada"];
            }
        }

        return false;
    }
}

?>
