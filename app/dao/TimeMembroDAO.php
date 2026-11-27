<?php

class TimeMembroDAO
{
    private PDO $conexao;

    public function __construct(?PDO $conexao = null)
    {
        $this->conexao = $conexao ?? (new Database())->conectar();
    }

    public function carregarMembros(TimePokemon $time): void
    {
        $stmt = $this->conexao->prepare(
            "SELECT p.*, tm.posicao, t.nome AS nome_tipo
             FROM time_membro tm
             INNER JOIN pokemon p ON tm.id_pokemon = p.id_pokemon
             LEFT JOIN pokemon_tipo pt ON pt.id_pokemon = p.id_pokemon
             LEFT JOIN tipo t ON t.id_tipo = pt.id_tipo
             WHERE tm.id_time = ?
             ORDER BY tm.posicao, t.id_tipo"
        );
        $stmt->execute([$time->getIdTime()]);

        $pokemons = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $id = (int) $linha["id_pokemon"];
            $pokemons[$id] ??= $linha + ["tipos" => []];

            if ($linha["nome_tipo"] !== null) {
                $pokemons[$id]["tipos"][] = ["nome" => $linha["nome_tipo"]];
            }
        }

        foreach ($pokemons as $dados) {
            $time->registrarMembro(Pokemon::doBanco($dados), (int) $dados["posicao"]);
        }
    }

    public function adicionar(TimePokemon $time, Pokemon $pokemon): void
    {
        $this->conexao->beginTransaction();

        try {
            $stmt = $this->conexao->prepare(
                "SELECT * FROM time_pokemon
                 WHERE id_time = ? AND id_usuario = ? AND ativo = 1
                 FOR UPDATE"
            );
            $stmt->execute([$time->getIdTime(), $time->getIdUsuario()]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$dados) {
                throw new DomainException("Time ativo inválido.");
            }

            $timeAtual = TimePokemon::doBanco($dados);
            $this->carregarMembros($timeAtual);
            $membro = $timeAtual->adicionarPokemon($pokemon);

            $stmt = $this->conexao->prepare(
                "INSERT INTO time_membro (id_time, id_pokemon, posicao) VALUES (?, ?, ?)"
            );
            $stmt->execute([
                $timeAtual->getIdTime(),
                $membro->getPokemon()->getId_pokemon(),
                $membro->getPosicao()
            ]);

            $stmt = $this->conexao->prepare(
                "INSERT INTO pokemon_time_status (id_time, id_pokemon, hp_atual)
                 SELECT ?, id_pokemon, hp FROM pokemon WHERE id_pokemon = ?"
            );
            $stmt->execute([$timeAtual->getIdTime(), $pokemon->getId_pokemon()]);

            $this->conexao->commit();
        } catch (Throwable $erro) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            throw $erro;
        }
    }
}