<?php

require_once __DIR__ . "/../dao/CompraDAO.php";

class CompraController
{
    private $compraDAO;

    public function __construct()
    {
        $this->compraDAO = new CompraDAO();
    }

    public function realizarCompra($id_usuario, array $itens)
    {
        return $this->compraDAO->realizarCompra(
            $id_usuario,
            $itens
        );
    }
}
