
<?php

require_once __DIR__ . "/../dao/ItemDAO.php";

class ItemController
{
    private $itemDAO;

    public function __construct()
    {
        $this->itemDAO = new ItemDAO();
    }

    public function listarItensDisponiveis()
    {
        return $this->itemDAO->listarItensDisponiveis();
    }
}
?>
