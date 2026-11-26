
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

public function listarItensDeBatalha($id_usuario)
{
    $sql = "SELECT
                i.id_item,
                i.nome,
                i.descricao,
                i.imagem_item,
                i.tipo,
                inv.quantidade
            FROM inventario inv
            INNER JOIN item i
                ON i.id_item = inv.id_item
            WHERE inv.id_usuario = :id_usuario
            AND inv.quantidade > 0
            AND i.disponivel = 1
            AND i.id_item IN (1, 2, 3, 4, 5)
            ORDER BY i.id_item";

    $stmt = $this->con->prepare($sql);

    $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function buscarItemDeBatalha($id_usuario, $id_item)
{
    $sql = "SELECT
                i.id_item,
                i.nome,
                i.descricao,
                i.tipo,
                inv.quantidade
            FROM inventario inv
            INNER JOIN item i
                ON i.id_item = inv.id_item
            WHERE inv.id_usuario = :id_usuario
            AND i.id_item = :id_item
            AND i.id_item IN (1, 2, 3, 4, 5)
            AND i.disponivel = 1
            AND inv.quantidade > 0
            LIMIT 1";

    $stmt = $this->con->prepare($sql);

    $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt->bindValue(":id_item", $id_item, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function consumirItem($id_usuario, $id_item)
{
    $sql = "UPDATE inventario
            SET quantidade = quantidade - 1
            WHERE id_usuario = :id_usuario
            AND id_item = :id_item
            AND quantidade > 0";

    $stmt = $this->con->prepare($sql);

    $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt->bindValue(":id_item", $id_item, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->rowCount() > 0;
}


}
?>
