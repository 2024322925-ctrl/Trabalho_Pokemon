
<?php

require_once __DIR__ . "/../../config/database.php";

class ItemDAO
{
    private $con;

    public function __construct()
    {
        $database = new Database();
        $this->con = $database->conectar();
    }

    public function listarItensDisponiveis()
    {
        $sql = "SELECT id_item, nome, descricao, imagem_item, preco, tipo
                FROM item
                WHERE disponivel = 1
                ORDER BY tipo, nome";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
