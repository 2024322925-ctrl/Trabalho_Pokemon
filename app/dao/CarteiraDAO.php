<?php



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
        FROM vw_usuario_carteira
        WHERE id_usuario = :id_usuario
          AND saldo IS NOT NULL";

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
