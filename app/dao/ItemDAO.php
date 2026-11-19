
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

    public function atualizarDadosDaAPI($id_item, $nome, $imagem)
    {
        $sql = "UPDATE item
                SET nome = :nome,
                    imagem_item = COALESCE(:imagem, imagem_item)
                WHERE id_item = :id_item";

        $stmt = $this->con->prepare($sql);

        $stmt->bindValue(":nome", $nome);
        $stmt->bindValue(
            ":imagem",
            $imagem,
            $imagem === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        $stmt->bindValue(":id_item", $id_item, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>
