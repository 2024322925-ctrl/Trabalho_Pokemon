
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

        try {

            $this->conexao->beginTransaction();

            // Adiciona o Pokémon ao time
            $sql = "INSERT INTO time_membro (id_time, id_pokemon, posicao)
                    VALUES (:id_time, :id_pokemon, :posicao)";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(":id_time", $membro->getIdTime(), PDO::PARAM_INT);
            $stmt->bindValue(":id_pokemon", $membro->getIdPokemon(), PDO::PARAM_INT);
            $stmt->bindValue(":posicao", $membro->getPosicao(), PDO::PARAM_INT);

            $stmt->execute();


            // Cria automaticamente o status inicial do Pokémon
            $sqlStatus = "INSERT INTO pokemon_time_status
                          (id_time, id_pokemon, hp_atual, status_atual)
                          SELECT
                              :id_time,
                              :id_pokemon,
                              hp,
                              NULL
                          FROM pokemon
                          WHERE id_pokemon = :id_pokemon_busca";

            $stmtStatus = $this->conexao->prepare($sqlStatus);

            $stmtStatus->bindValue(":id_time", $membro->getIdTime(), PDO::PARAM_INT);
            $stmtStatus->bindValue(":id_pokemon", $membro->getIdPokemon(), PDO::PARAM_INT);
            $stmtStatus->bindValue(":id_pokemon_busca", $membro->getIdPokemon(), PDO::PARAM_INT);

            $stmtStatus->execute();

            $this->conexao->commit();

            return true;

        } catch (PDOException $e) {

            $this->conexao->rollBack();

            throw $e;
        }
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
