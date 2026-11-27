<?php



class CarteiraController
{
    private $carteiraDAO;

    public function __construct()
    {
        $this->carteiraDAO = new CarteiraDAO();
    }

    public function buscarSaldoPorUsuario($id_usuario)
    {
        return $this->carteiraDAO->buscarSaldoPorUsuario($id_usuario);
    }
}
?>
