
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

            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

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


    public function listarEvolucoes($idUsuario, $idTime) {
        $sql = "SELECT e.id_evolucao, e.id_pokemon, i.nome AS item, destino.nome AS destino,
                       COALESCE(inv.quantidade, 0) AS quantidade,
                       (existente.id_pokemon IS NOT NULL) AS destino_no_time
                FROM evolucao e
                JOIN time_membro tm ON tm.id_pokemon = e.id_pokemon AND tm.id_time = ?
                JOIN pokemon destino ON destino.id_pokemon = e.id_pokemon_evolucao AND destino.ativo = 1
                JOIN item i ON i.id_item = e.id_item AND i.tipo = 'evolucao'
                LEFT JOIN inventario inv ON inv.id_item = i.id_item AND inv.id_usuario = ?
                LEFT JOIN time_membro existente ON existente.id_time = tm.id_time
                    AND existente.id_pokemon = destino.id_pokemon
                ORDER BY destino.nome, i.nome";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$idTime, $idUsuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function evoluir($idUsuario, $idTime, $idPokemon, $idEvolucao) {
        $this->conexao->beginTransaction();

        try {
            $stmt = $this->conexao->prepare(
                "SELECT id_time FROM time_pokemon
                 WHERE id_time = ? AND id_usuario = ? AND ativo = 1 FOR UPDATE"
            );
            $stmt->execute([$idTime, $idUsuario]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException("Time ativo inválido.");
            }

            $stmt = $this->conexao->prepare(
                "SELECT tm.posicao, s.hp_atual, s.status_atual, s.turnos_status,
                        e.id_item, destino.id_pokemon AS destino, destino.hp AS hp_maximo
                 FROM time_membro tm
                 JOIN evolucao e ON e.id_pokemon = tm.id_pokemon AND e.id_evolucao = ?
                 JOIN pokemon destino ON destino.id_pokemon = e.id_pokemon_evolucao AND destino.ativo = 1
                 JOIN item i ON i.id_item = e.id_item AND i.tipo = 'evolucao'
                 LEFT JOIN pokemon_time_status s ON s.id_time = tm.id_time AND s.id_pokemon = tm.id_pokemon
                 WHERE tm.id_time = ? AND tm.id_pokemon = ? FOR UPDATE"
            );
            $stmt->execute([$idEvolucao, $idTime, $idPokemon]);
            $regra = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$regra || (int) $regra["destino"] === $idPokemon) {
                throw new DomainException("Essa evolução não está disponível para este membro do time.");
            }

            $stmt = $this->conexao->prepare(
                "SELECT 1 FROM time_membro WHERE id_time = ? AND id_pokemon = ? FOR UPDATE"
            );
            $stmt->execute([$idTime, $regra["destino"]]);
            if ($stmt->fetchColumn()) {
                throw new DomainException("A forma evoluída já está no seu time.");
            }

            $stmt = $this->conexao->prepare(
                "UPDATE inventario SET quantidade = quantidade - 1
                 WHERE id_usuario = ? AND id_item = ? AND quantidade > 0"
            );
            $stmt->execute([$idUsuario, $regra["id_item"]]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException("Compre o item de evolução na loja primeiro.");
            }

            $this->remover($idTime, $idPokemon);

            $stmt = $this->conexao->prepare(
                "INSERT INTO time_membro (id_time, id_pokemon, posicao) VALUES (?, ?, ?)"
            );
            $stmt->execute([$idTime, $regra["destino"], $regra["posicao"]]);

            $stmt = $this->conexao->prepare(
                "INSERT INTO pokemon_time_status (id_time, id_pokemon, hp_atual, status_atual, turnos_status)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $idTime,
                $regra["destino"],
                min((int) $regra["hp_maximo"], (int) ($regra["hp_atual"] ?? $regra["hp_maximo"])),
                $regra["status_atual"],
                $regra["turnos_status"] ?? 0
            ]);

            $stmt = $this->conexao->prepare(
                "INSERT INTO pokemon_desbloqueado (id_usuario, id_pokemon) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE id_pokemon = VALUES(id_pokemon)"
            );
            $stmt->execute([$idUsuario, $regra["destino"]]);
            $this->conexao->commit();
        } catch (Throwable $e) {
            $this->conexao->rollBack();
            throw $e;
        }
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
