<?php

require_once __DIR__ . "/../../config/database.php";

class PokemonDesbloqueioDAO
{
    private $con;

    public function __construct()
    {
        $this->con = (new Database())->conectar();
    }

    public function estado($usuario)
    {
        $stmt = $this->con->prepare("SELECT id_usuario FROM escolha_inicial_pokemon WHERE id_usuario = ?");
        $stmt->execute([$usuario]);
        $escolheu = (bool) $stmt->fetchColumn();

        $stmt = $this->con->prepare("SELECT id_pokemon FROM pokemon_desbloqueado WHERE id_usuario = ?");
        $stmt->execute([$usuario]);

        return [
            "escolheu_iniciais" => $escolheu,
            "desbloqueados" => array_map("intval", $stmt->fetchAll(PDO::FETCH_COLUMN))
        ];
    }

    public function escolher($usuario, $time, $ids)
    {
        $this->con->beginTransaction();
        try {
            $stmt = $this->con->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? FOR UPDATE");
            $stmt->execute([$usuario]);
            if (!$stmt->fetchColumn() || $this->estado($usuario)["escolheu_iniciais"]) {
                throw new DomainException("A escolha inicial já foi feita.");
            }

            $stmt = $this->con->prepare(
                "SELECT m.id_pokemon, m.posicao FROM time_membro m
                 JOIN time_pokemon t ON t.id_time = m.id_time
                 WHERE t.id_time = ? AND t.id_usuario = ? AND t.ativo = 1 FOR UPDATE"
            );
            $stmt->execute([$time, $usuario]);
            $membros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($ids) !== 3 - count($membros) ||
                array_intersect($ids, array_column($membros, "id_pokemon"))) {
                throw new DomainException("Complete o time com exatamente três Pokémon distintos.");
            }

            $posicoes = array_map("intval", array_column($membros, "posicao"));
            $adicionar = $this->con->prepare("INSERT INTO time_membro (id_time, id_pokemon, posicao) VALUES (?, ?, ?)");
            $status = $this->con->prepare(
                "INSERT INTO pokemon_time_status (id_time, id_pokemon, hp_atual)
                 SELECT ?, id_pokemon, hp FROM pokemon WHERE id_pokemon = ?"
            );
            $liberar = $this->con->prepare(
                "INSERT INTO pokemon_desbloqueado (id_usuario, id_pokemon) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE id_pokemon = VALUES(id_pokemon)"
            );

            $posicao = 1;
            foreach ($ids as $id) {
                while (in_array($posicao, $posicoes, true)) {
                    $posicao++;
                }
                $adicionar->execute([$time, $id, $posicao++]);
                $status->execute([$time, $id]);
                $liberar->execute([$usuario, $id]);
            }

            $stmt = $this->con->prepare("INSERT INTO escolha_inicial_pokemon (id_usuario) VALUES (?)");
            $stmt->execute([$usuario]);
            $this->con->commit();
        } catch (Throwable $erro) {
            $this->con->rollBack();
            throw $erro;
        }
    }

    public function removerDoTime($time, $id)
    {
        $this->con->beginTransaction();
        try {
            $stmt = $this->con->prepare("SELECT id_time FROM time_pokemon WHERE id_time = ? FOR UPDATE");
            $stmt->execute([$time]);
            $stmt->fetchColumn();

            $stmt = $this->con->prepare("SELECT COUNT(*) FROM time_membro WHERE id_time = ?");
            $stmt->execute([$time]);
            if ((int) $stmt->fetchColumn() <= 3) {
                throw new DomainException("O time precisa manter pelo menos três Pokémon. Adicione outro primeiro.");
            }

            $stmt = $this->con->prepare("DELETE FROM time_membro WHERE id_time = ? AND id_pokemon = ?");
            $stmt->execute([$time, $id]);
            $removido = $stmt->rowCount() === 1;
            $this->con->commit();
            return $removido;
        } catch (Throwable $erro) {
            $this->con->rollBack();
            throw $erro;
        }
    }

    public function comprar($usuario, $id, int $preco)
    {
        $this->con->beginTransaction();
        try {
            $stmt = $this->con->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? FOR UPDATE");
            $stmt->execute([$usuario]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException("Conta inválida.");
            }

            $estado = $this->estado($usuario);
            if (!$estado["escolheu_iniciais"]) {
                throw new DomainException("Escolha seus três iniciais no Team Builder primeiro.");
            }
            if (in_array($id, $estado["desbloqueados"], true)) {
                throw new DomainException("Esse Pokémon já está desbloqueado.");
            }

            $stmt = $this->con->prepare(
                "UPDATE carteira SET saldo = saldo - ? WHERE id_usuario = ? AND saldo >= ?"
            );
            $stmt->execute([$preco, $usuario, $preco]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException("Saldo insuficiente.");
            }

            $stmt = $this->con->prepare("INSERT INTO pokemon_desbloqueado (id_usuario, id_pokemon) VALUES (?, ?)");
            $stmt->execute([$usuario, $id]);
            $this->con->commit();
        } catch (Throwable $erro) {
            $this->con->rollBack();
            throw $erro;
        }
    }
}
