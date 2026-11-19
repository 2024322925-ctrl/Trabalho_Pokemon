
<?php

require_once __DIR__ . "/../../config/database.php";

class CompraDAO
{
    private $con;

    public function __construct()
    {
        $database = new Database();
        $this->con = $database->conectar();
    }

    public function realizarCompra($id_usuario, array $itens)
    {
        if ($id_usuario <= 0 || empty($itens)) {
            throw new InvalidArgumentException(
                "Usuário ou itens inválidos."
            );
        }

        // Agrupa itens repetidos e valida as quantidades
        $itensAgrupados = [];

        foreach ($itens as $item) {

            if (
                !isset($item["id_item"]) ||
                !isset($item["quantidade"])
            ) {
                throw new InvalidArgumentException(
                    "Dados do item incompletos."
                );
            }

            $id_item = filter_var(
                $item["id_item"],
                FILTER_VALIDATE_INT
            );

            $quantidade = filter_var(
                $item["quantidade"],
                FILTER_VALIDATE_INT
            );

            if (
                $id_item === false ||
                $quantidade === false ||
                $id_item <= 0 ||
                $quantidade <= 0
            ) {
                throw new InvalidArgumentException(
                    "Item ou quantidade inválidos."
                );
            }

            if (!isset($itensAgrupados[$id_item])) {
                $itensAgrupados[$id_item] = 0;
            }

            $itensAgrupados[$id_item] += $quantidade;
        }

        try {

            $this->con->beginTransaction();

            // Busca e bloqueia a carteira do usuário
            $sqlCarteira = "SELECT saldo
                            FROM carteira
                            WHERE id_usuario = :id_usuario
                            FOR UPDATE";

            $stmtCarteira = $this->con->prepare($sqlCarteira);

            $stmtCarteira->bindValue(
                ":id_usuario",
                $id_usuario,
                PDO::PARAM_INT
            );

            $stmtCarteira->execute();

            $carteira = $stmtCarteira->fetch(PDO::FETCH_ASSOC);

            if (!$carteira) {
                throw new RuntimeException(
                    "Carteira não encontrada."
                );
            }

            $saldoCentavos = (int) round(
                (float) $carteira["saldo"] * 100
            );

            $totalCentavos = 0;
            $itensCompra = [];

            // Consulta os preços diretamente no banco
            foreach ($itensAgrupados as $id_item => $quantidade) {

                $sqlItem = "SELECT id_item, preco
                            FROM item
                            WHERE id_item = :id_item
                            AND disponivel = 1
                            FOR UPDATE";

                $stmtItem = $this->con->prepare($sqlItem);

                $stmtItem->bindValue(
                    ":id_item",
                    $id_item,
                    PDO::PARAM_INT
                );

                $stmtItem->execute();

                $dadosItem = $stmtItem->fetch(PDO::FETCH_ASSOC);

                if (!$dadosItem) {
                    throw new RuntimeException(
                        "Item não encontrado ou indisponível."
                    );
                }

                $precoCentavos = (int) round(
                    (float) $dadosItem["preco"] * 100
                );

                $subtotal = $precoCentavos * $quantidade;

                $totalCentavos += $subtotal;

                $itensCompra[] = [
                    "id_item" => $id_item,
                    "quantidade" => $quantidade,
                    "preco_unitario" => number_format(
                        $precoCentavos / 100,
                        2,
                        ".",
                        ""
                    )
                ];
            }

            // Verifica se existe saldo suficiente
            if ($saldoCentavos < $totalCentavos) {
                throw new RuntimeException(
                    "Saldo insuficiente para realizar a compra."
                );
            }

            $valorTotal = number_format(
                $totalCentavos / 100,
                2,
                ".",
                ""
            );

            // Desconta o valor da carteira
            $sqlDesconto = "UPDATE carteira
                            SET saldo = saldo - :valor_desconto
                            WHERE id_usuario = :id_usuario
                            AND saldo >= :valor_condicao";

            $stmtDesconto = $this->con->prepare($sqlDesconto);

            $stmtDesconto->bindValue(
                ":valor_desconto",
                $valorTotal
            );
            
            $stmtDesconto->bindValue(
                ":id_usuario",
                $id_usuario,
                PDO::PARAM_INT
            );
            
            $stmtDesconto->bindValue(
                ":valor_condicao",
                $valorTotal
            );
            $stmtDesconto->execute();

            if ($stmtDesconto->rowCount() !== 1) {
                throw new RuntimeException(
                    "Não foi possível descontar o saldo."
                );
            }

            // Registra a compra
            $sqlCompra = "INSERT INTO compra
                          (id_usuario, valor_total)
                          VALUES
                          (:id_usuario, :valor_total)";

            $stmtCompra = $this->con->prepare($sqlCompra);

            $stmtCompra->bindValue(
                ":id_usuario",
                $id_usuario,
                PDO::PARAM_INT
            );

            $stmtCompra->bindValue(
                ":valor_total",
                $valorTotal
            );

            $stmtCompra->execute();

            $id_compra = $this->con->lastInsertId();

            // Registra os itens da compra
            // A Trigger atualizará o inventário automaticamente
            $sqlCompraItem = "INSERT INTO compra_item
                              (id_compra, id_item, quantidade, preco_unitario)
                              VALUES
                              (:id_compra, :id_item, :quantidade, :preco_unitario)";

            $stmtCompraItem = $this->con->prepare($sqlCompraItem);

            foreach ($itensCompra as $item) {

                $stmtCompraItem->bindValue(
                    ":id_compra",
                    $id_compra,
                    PDO::PARAM_INT
                );

                $stmtCompraItem->bindValue(
                    ":id_item",
                    $item["id_item"],
                    PDO::PARAM_INT
                );

                $stmtCompraItem->bindValue(
                    ":quantidade",
                    $item["quantidade"],
                    PDO::PARAM_INT
                );

                $stmtCompraItem->bindValue(
                    ":preco_unitario",
                    $item["preco_unitario"]
                );

                $stmtCompraItem->execute();
            }

            $this->con->commit();

            return $id_compra;

        } catch (Throwable $e) {

            if ($this->con->inTransaction()) {
                $this->con->rollBack();
            }

            throw $e;
        }
    }
}
