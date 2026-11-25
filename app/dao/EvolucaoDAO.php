<?php

class EvolucaoDAO
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = (new Database())->conectar();
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
                WHERE existente.id_pokemon IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM pokemon_desbloqueado d
                      WHERE d.id_usuario = ? AND d.id_pokemon = destino.id_pokemon
                  )
                ORDER BY destino.nome, i.nome";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$idTime, $idUsuario, $idUsuario]);

        return array_map(static function (array $dados) {
            return new Evolucao($dados);
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function evoluir($idUsuario, $idTime, $idPokemon, $idEvolucao) {
        $this->conexao->beginTransaction();

        try {
            // Usa o mesmo bloqueio da compra para serializar o desbloqueio.
            $stmt = $this->conexao->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? FOR UPDATE");
            $stmt->execute([$idUsuario]);
            $stmt->closeCursor();

            $stmt = $this->conexao->prepare(
                "SELECT id_time FROM time_pokemon
                 WHERE id_time = ? AND id_usuario = ? AND ativo = 1 FOR UPDATE"
            );
            $stmt->execute([$idTime, $idUsuario]);
            if (!$stmt->fetchColumn()) {
                throw new EvolucaoException("Time ativo inválido.");
            }

            $stmt = $this->conexao->prepare(
                "SELECT tm.posicao, s.hp_atual, s.status_atual, s.turnos_status, s.nivel, s.xp,
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
                throw new EvolucaoException("Essa evolução não está disponível para este membro do time.");
            }

            $stmt = $this->conexao->prepare(
                "SELECT 1 FROM time_membro WHERE id_time = ? AND id_pokemon = ? FOR UPDATE"
            );
            $stmt->execute([$idTime, $regra["destino"]]);
            if ($stmt->fetchColumn()) {
                throw new EvolucaoException("A forma evoluída já está no seu time.");
            }

            $stmt = $this->conexao->prepare(
                "SELECT 1 FROM pokemon_desbloqueado WHERE id_usuario = ? AND id_pokemon = ? FOR UPDATE"
            );
            $stmt->execute([$idUsuario, $regra["destino"]]);
            if ($stmt->fetchColumn()) {
                throw new EvolucaoException("A forma evoluída já está desbloqueada na sua conta.");
            }

            $stmt = $this->conexao->prepare(
                "UPDATE inventario SET quantidade = quantidade - 1
                 WHERE id_usuario = ? AND id_item = ? AND quantidade > 0"
            );
            $stmt->execute([$idUsuario, $regra["id_item"]]);
            if ($stmt->rowCount() !== 1) {
                throw new EvolucaoException("Compre o item de evolução na loja primeiro.");
            }

            $stmt = $this->conexao->prepare("DELETE FROM time_membro WHERE id_time = ? AND id_pokemon = ?");
            $stmt->execute([$idTime, $idPokemon]);

            $stmt = $this->conexao->prepare(
                "INSERT INTO time_membro (id_time, id_pokemon, posicao) VALUES (?, ?, ?)"
            );
            $stmt->execute([$idTime, $regra["destino"], $regra["posicao"]]);

            $stmt = $this->conexao->prepare(
                "INSERT INTO pokemon_time_status (id_time, id_pokemon, hp_atual, status_atual, turnos_status, nivel, xp)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $idTime,
                $regra["destino"],
                min((int) $regra["hp_maximo"], (int) ($regra["hp_atual"] ?? $regra["hp_maximo"])),
                $regra["status_atual"],
                $regra["turnos_status"] ?? 0,
                $regra["nivel"] ?? 1,
                $regra["xp"] ?? 0
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

    public function importar(array $regras)
    {
        $buscar = $this->conexao->prepare(
            "SELECT origem.id_pokemon AS origem, destino.id_pokemon AS destino, i.id_item
             FROM pokemon origem
             JOIN pokemon destino ON destino.nome = ?
             JOIN item i ON i.nome = ? AND i.tipo = 'evolucao'
             WHERE origem.nome = ? FOR UPDATE"
        );
        $buscarRegra = $this->conexao->prepare(
            "SELECT id_pokemon_evolucao FROM evolucao WHERE id_pokemon = ? AND id_item = ? FOR UPDATE"
        );
        $inserir = $this->conexao->prepare(
            "INSERT INTO evolucao (id_pokemon, id_pokemon_evolucao, id_item) VALUES (?, ?, ?)"
        );
        $novas = 0;
        $this->conexao->beginTransaction();

        try {
            foreach ($regras as [$origem, $destino, $item]) {
                $buscar->execute([$destino, $item, $origem]);
                $ids = $buscar->fetch(PDO::FETCH_ASSOC);
                $buscar->closeCursor();
                if (!$ids) {
                    throw new EvolucaoException("Cadastro incompleto: $origem -> $destino ($item).");
                }

                $buscarRegra->execute([$ids["origem"], $ids["id_item"]]);
                $existente = $buscarRegra->fetchColumn();
                $buscarRegra->closeCursor();
                if ($existente !== false) {
                    if ((int) $existente !== (int) $ids["destino"]) {
                        throw new EvolucaoException("Regra conflitante: $origem ($item).");
                    }
                    continue;
                }

                $inserir->execute([$ids["origem"], $ids["destino"], $ids["id_item"]]);
                $novas++;
            }

            $this->conexao->commit();
            return $novas;
        } catch (Throwable $erro) {
            $this->conexao->rollBack();
            throw $erro;
        }
    }
}
