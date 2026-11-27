<?php



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

    public function listarItensDeBatalha($id_usuario)
    {
        return $this->itemDAO->listarItensDeBatalha($id_usuario);
    }

    public function consumirItem($id_usuario, $id_item)
    {
        return $this->itemDAO->consumirItem($id_usuario, $id_item);
    }

    public function buscarItemDeBatalha($id_usuario, $id_item)
    {
        return $this->itemDAO->buscarItemDeBatalha(
            $id_usuario,
            $id_item
        );
    }
}
?>