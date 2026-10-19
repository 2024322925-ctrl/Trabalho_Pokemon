<?php

require_once __DIR__ . "/../models/Pokemon.php";
require_once __DIR__ . "/../../config/database.php";

class PokemonDAO
{
    private $conexao;

    public function __construct()
    {
        $database = new Database();
        $this->conexao = $database->conectar();
    }

    public function cadastrar(Pokemon $pokemon)
    {
        $sql = "INSERT INTO pokemon (
                    nome,
                    hp,
                    hp_atual,
                    velocidade,
                    ataque,
                    defesa,
                    ataque_especial,
                    defesa_especial,
                    status_atual,
                    ativo,
                    imagem
                ) VALUES (
                    :nome,
                    :hp,
                    :hp_atual,
                    :velocidade,
                    :ataque,
                    :defesa,
                    :ataque_especial,
                    :defesa_especial,
                    :status_atual,
                    :ativo,
                    :imagem
                )";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(":nome", $pokemon->getNome());
        $stmt->bindValue(":hp", $pokemon->getHp());
        $stmt->bindValue(":hp_atual", $pokemon->getHp_atual());
        $stmt->bindValue(":velocidade", $pokemon->getVelocidade());
        $stmt->bindValue(":ataque", $pokemon->getAtaque());
        $stmt->bindValue(":defesa", $pokemon->getDefesa());
        $stmt->bindValue(":ataque_especial", $pokemon->getAtaque_especial());
        $stmt->bindValue(":defesa_especial", $pokemon->getDefesa_especial());
        $stmt->bindValue(":status_atual", $pokemon->getStatus_atual());
        $stmt->bindValue(":ativo", $pokemon->getAtivo());
        $stmt->bindValue(":imagem", $pokemon->getImagem());

        return $stmt->execute();
    }

    public function buscarPorId($id_pokemon)
    {
        $sql = "SELECT * FROM pokemon
                WHERE id_pokemon = :id_pokemon";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_pokemon", $id_pokemon, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $pokemon = new Pokemon();

        $pokemon->setId_pokemon($dados["id_pokemon"]);
        $pokemon->setNome($dados["nome"]);
        $pokemon->setHp($dados["hp"]);
        $pokemon->setHp_atual($dados["hp_atual"]);
        $pokemon->setVelocidade($dados["velocidade"]);
        $pokemon->setAtaque($dados["ataque"]);
        $pokemon->setDefesa($dados["defesa"]);
        $pokemon->setAtaque_especial($dados["ataque_especial"]);
        $pokemon->setDefesa_especial($dados["defesa_especial"]);
        $pokemon->setStatus_atual($dados["status_atual"]);
        $pokemon->setAtivo($dados["ativo"]);
        $pokemon->setImagem($dados["imagem"]);

        return $pokemon;
    }

    public function listarTodos()
    {
        $sql = "SELECT 
                    p.*,
                    t.id_tipo,
                    t.nome AS nome_tipo,
                    t.imagem AS imagem_tipo
                FROM pokemon p
                LEFT JOIN pokemon_tipo pt
                    ON p.id_pokemon = pt.id_pokemon
                LEFT JOIN tipo t
                    ON pt.id_tipo = t.id_tipo
                WHERE p.ativo = 1
                ORDER BY p.nome";

        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarDaAPI(array $dados)
    {
        try {
            $this->conexao->beginTransaction();

            // Verifica se o Pokémon já existe
            $sql = "SELECT id_pokemon
                    FROM pokemon
                    WHERE nome = :nome";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":nome", $dados["nome"]);
            $stmt->execute();

            $idPokemon = $stmt->fetchColumn();

            if ($idPokemon) {
                // Atualiza os dados do Pokémon existente
                $sql = "UPDATE pokemon SET
                            hp = :hp,
                            hp_atual = :hp_atual,
                            velocidade = :velocidade,
                            ataque = :ataque,
                            defesa = :defesa,
                            ataque_especial = :ataque_especial,
                            defesa_especial = :defesa_especial,
                            status_atual = :status_atual,
                            ativo = :ativo,
                            imagem = :imagem
                        WHERE id_pokemon = :id_pokemon";

                $stmt = $this->conexao->prepare($sql);
                $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
            } else {
                // Cadastra um novo Pokémon
                $sql = "INSERT INTO pokemon (
                            nome, hp, hp_atual, velocidade,
                            ataque, defesa, ataque_especial,
                            defesa_especial, status_atual, ativo, imagem
                        ) VALUES (
                            :nome, :hp, :hp_atual, :velocidade,
                            :ataque, :defesa, :ataque_especial,
                            :defesa_especial, :status_atual, :ativo, :imagem
                        )";

                $stmt = $this->conexao->prepare($sql);
                $stmt->bindValue(":nome", $dados["nome"]);
            }

            $stmt->bindValue(":hp", $dados["hp"], PDO::PARAM_INT);
            $stmt->bindValue(":hp_atual", $dados["hp_atual"], PDO::PARAM_INT);
            $stmt->bindValue(":velocidade", $dados["velocidade"], PDO::PARAM_INT);
            $stmt->bindValue(":ataque", $dados["ataque"], PDO::PARAM_INT);
            $stmt->bindValue(":defesa", $dados["defesa"], PDO::PARAM_INT);
            $stmt->bindValue(":ataque_especial", $dados["ataque_especial"], PDO::PARAM_INT);
            $stmt->bindValue(":defesa_especial", $dados["defesa_especial"], PDO::PARAM_INT);
            $stmt->bindValue(":status_atual", $dados["status_atual"]);
            $stmt->bindValue(":ativo", $dados["ativo"], PDO::PARAM_BOOL);
            $stmt->bindValue(":imagem", $dados["imagem"]);

            $stmt->execute();

            // Descobre o ID do Pokémon cadastrado
            if (!$idPokemon) {
                $idPokemon = $this->conexao->lastInsertId();
            }

            // Salva os tipos e associa ao Pokémon
            foreach ($dados["tipos"] as $nomeTipo) {
                $sql = "SELECT id_tipo
                        FROM tipo
                        WHERE nome = :nome";

                $stmt = $this->conexao->prepare($sql);
                $stmt->bindValue(":nome", $nomeTipo);
                $stmt->execute();

                $idTipo = $stmt->fetchColumn();

                if (!$idTipo) {
                    $sql = "INSERT INTO tipo (nome)
                            VALUES (:nome)";

                    $stmt = $this->conexao->prepare($sql);
                    $stmt->bindValue(":nome", $nomeTipo);
                    $stmt->execute();

                    $idTipo = $this->conexao->lastInsertId();
                }

                $sql = "INSERT IGNORE INTO pokemon_tipo (
                            id_pokemon, id_tipo
                        ) VALUES (
                            :id_pokemon, :id_tipo
                        )";

                $stmt = $this->conexao->prepare($sql);
                $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
                $stmt->bindValue(":id_tipo", $idTipo, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->conexao->commit();

            return true;

        } catch (Throwable $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

            throw $e;
        }
    }
}