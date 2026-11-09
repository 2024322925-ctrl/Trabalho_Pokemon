
<?php

require_once __DIR__ . "/../models/TimeMembro.php";
require_once __DIR__ . "/../../config/database.php";

class TimeMembroDAO {

    private $conexao;

    public function __construct() {
        $database = new Database();
        $this->conexao = $database->conectar();
    }

    public function adicionar(TimeMembro $membro) {
        $sql = "INSERT INTO time_membro (id_time, id_pokemon, posicao)
                VALUES (:id_time, :id_pokemon, :posicao)";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(":id_time", $membro->getIdTime(), PDO::PARAM_INT);
        $stmt->bindValue(":id_pokemon", $membro->getIdPokemon(), PDO::PARAM_INT);
        $stmt->bindValue(":posicao", $membro->getPosicao(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function listarPorTime($id_time) {
        $sql = "SELECT p.*, tm.posicao
                FROM time_membro tm
                INNER JOIN pokemon p ON tm.id_pokemon = p.id_pokemon
                WHERE tm.id_time = :id_time
                ORDER BY tm.posicao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_time", $id_time, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function remover($id_time, $id_pokemon) {
        $sql = "DELETE FROM time_membro
                WHERE id_time = :id_time
                AND id_pokemon = :id_pokemon";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(":id_time", $id_time, PDO::PARAM_INT);
        $stmt->bindValue(":id_pokemon", $id_pokemon, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

?>
