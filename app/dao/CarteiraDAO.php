
<?php

require_once __DIR__ . "/../../config/database.php";

class CarteiraDAO
{
    private $con;

    public function __construct()
    {
        $database = new Database();
        $this->con = $database->conectar();
    }

    public function buscarSaldoPorUsuario($id_usuario)
    {
        $sql = "SELECT saldo
                FROM carteira
                WHERE id_usuario = :id_usuario";

        $stmt = $this->con->prepare($sql);

        $stmt->bindValue(
            ":id_usuario",
            $id_usuario,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $saldo = $stmt->fetchColumn();

        return $saldo;
    }
}
