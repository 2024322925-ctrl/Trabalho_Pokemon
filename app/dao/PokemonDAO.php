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
                    imagem,
                    imagem_costas
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
                    :imagem,
                    :imagem_costas
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
        $stmt->bindValue(":imagem_costas", $pokemon->getImagem_costas());

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
        $pokemon->setImagem_costas($dados["imagem_costas"]);

        return $pokemon;
    }

    private const INICIAIS_TEAM_BUILDER = [
        "bulbasaur", "charmander", "squirtle", "caterpie", "weedle",
        "pidgey", "rattata", "spearow", "ekans", "pikachu", "sandshrew",
        "nidoran-f", "nidoran-m", "clefairy", "vulpix", "jigglypuff",
        "zubat", "oddish", "paras", "venonat", "diglett", "meowth",
        "psyduck", "mankey", "poliwag", "abra", "machop", "bellsprout",
        "tentacool", "geodude", "slowpoke", "magnemite", "doduo",
        "seel", "grimer", "shellder", "gastly", "drowzee", "krabby",
        "voltorb", "exeggcute", "cubone", "koffing", "rhyhorn",
        "horsea", "goldeen", "staryu", "magikarp", "eevee"
    ];

    public function listarTeamBuilder()
    {
        return array_values(array_filter($this->listarTodos(), static function ($pokemon) {
            return in_array(strtolower($pokemon["nome"]), self::INICIAIS_TEAM_BUILDER, true);
        }));
    }

    public function listarLoja()
    {
        $regras = require __DIR__ . "/../models/regrasEvolucao.php";
        $formasEvoluidas = array_fill_keys(array_column($regras, 1), true);
        $catalogo = array_values(array_filter($this->listarTodos(), static function ($pokemon) use ($formasEvoluidas) {
            return !isset($formasEvoluidas[strtolower($pokemon["nome"])]);
        }));
        foreach ($catalogo as &$pokemon) {
            $pokemon["preco_pokemon"] = $this->calcularPrecoLoja($pokemon);
        }
        unset($pokemon);

        return $catalogo;
    }

    private function calcularPrecoLoja(array $pokemon): int
    {
        $nome = strtolower($pokemon["nome"]);
        $precosEspeciais = [
            "ditto" => 5500,
            "lapras" => 5000,
            "articuno" => 8000,
            "zapdos" => 8500,
            "moltres" => 8500,
            "mew" => 8500,
            "mewtwo" => 9000
        ];
        if (isset($precosEspeciais[$nome])) {
            return $precosEspeciais[$nome];
        }

        if (in_array($nome, self::INICIAIS_TEAM_BUILDER, true)) {
            return 3000;
        }

        $total = $pokemon["hp"] + $pokemon["ataque"] + $pokemon["defesa"]
            + $pokemon["ataque_especial"] + $pokemon["defesa_especial"] + $pokemon["velocidade"];

        if ($total >= 550) return 7000;
        if ($total >= 500) return 6000;
        if ($total >= 450) return 5000;
        if ($total >= 400) return 4500;
        return 4000;
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
                ORDER BY p.id_pokemon ASC";

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
                            imagem = :imagem,
                            imagem_costas = :imagem_costas
                        WHERE id_pokemon = :id_pokemon";

                $stmt = $this->conexao->prepare($sql);
                $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
            } else {
                // Cadastra um novo Pokémon
                $sql = "INSERT INTO pokemon (
                        nome, hp, hp_atual, velocidade,
                        ataque, defesa, ataque_especial,
                        defesa_especial, status_atual, ativo,
                        imagem, imagem_costas
                    ) VALUES (
                        :nome, :hp, :hp_atual, :velocidade,
                        :ataque, :defesa, :ataque_especial,
                        :defesa_especial, :status_atual, :ativo,
                        :imagem, :imagem_costas
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
            $stmt->bindValue(":imagem_costas", $dados["imagem_costas"]);

            $stmt->execute();

            // Descobre o ID do Pokémon cadastrado
            if (!$idPokemon) {
                $idPokemon = $this->conexao->lastInsertId();
            }

            // Remove as associações antigas de tipos
            $sqlExcluirTipos = "DELETE FROM pokemon_tipo
                                WHERE id_pokemon = :id_pokemon";

            $stmtExcluirTipos = $this->conexao->prepare($sqlExcluirTipos);
            $stmtExcluirTipos->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
            $stmtExcluirTipos->execute();

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

            // Salva os ataques e associa ao Pokémon
            $this->salvarAtaquesDoPokemon(
                $idPokemon,
                $dados["ataques"]
            );

            $this->conexao->commit();

            return true;

        } catch (Throwable $e) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

            throw $e;
        }
    }

    private function obterOuCriarTipo($nomeTipo)
    {
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

        return $idTipo;
    }

    private function salvarAtaquesDoPokemon($idPokemon, array $ataques)
    {
        // Remove as associações antigas
        $sql = "DELETE FROM pokemon_ataque
                WHERE id_pokemon = :id_pokemon";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
        $stmt->execute();

        foreach ($ataques as $ataque) {

            // Localiza ou cria o tipo do ataque
            $idTipo = $this->obterOuCriarTipo($ataque["tipo"]);

            // Transforma os efeitos em JSON
            $dadosEfeito = json_encode(
                $ataque["dados_efeito"] ?? [],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($dadosEfeito === false) {
                throw new Exception(
                    "Erro ao converter os efeitos do ataque para JSON."
                );
            }

            // Verifica se o ataque já existe
            $sql = "SELECT id_ataque
                    FROM ataque
                    WHERE nome = :nome";

            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(":nome", $ataque["nome"]);
            $stmt->execute();

            $idAtaque = $stmt->fetchColumn();

            if ($idAtaque) {

                // Atualiza os dados do ataque existente
                $sql = "UPDATE ataque SET
                            id_tipo = :id_tipo,
                            poder = :poder,
                            precisao = :precisao,
                            categoria = :categoria,
                            efeito_status = :efeito_status,
                            chance_efeito = :chance_efeito,
                            dados_efeito = :dados_efeito
                        WHERE id_ataque = :id_ataque";

                $stmt = $this->conexao->prepare($sql);

                $stmt->bindValue(":id_ataque", $idAtaque, PDO::PARAM_INT);

            } else {

                // Cadastra um novo ataque
                $sql = "INSERT INTO ataque (
                            nome,
                            id_tipo,
                            poder,
                            precisao,
                            categoria,
                            efeito_status,
                            chance_efeito,
                            dados_efeito
                        ) VALUES (
                            :nome,
                            :id_tipo,
                            :poder,
                            :precisao,
                            :categoria,
                            :efeito_status,
                            :chance_efeito,
                            :dados_efeito
                        )";

                $stmt = $this->conexao->prepare($sql);

                $stmt->bindValue(":nome", $ataque["nome"]);
            }

            $stmt->bindValue(":id_tipo", $idTipo, PDO::PARAM_INT);
            $stmt->bindValue(":poder", $ataque["poder"], PDO::PARAM_INT);
            $stmt->bindValue(":precisao", $ataque["precisao"]);
            $stmt->bindValue(":categoria", $ataque["categoria"]);
            $stmt->bindValue(":efeito_status", $ataque["efeito_status"]);
            $stmt->bindValue(":chance_efeito", $ataque["chance_efeito"]);
            $stmt->bindValue(":dados_efeito", $dadosEfeito, PDO::PARAM_STR);

            $stmt->execute();

            // Recupera o ID do ataque cadastrado
            if (!$idAtaque) {
                $idAtaque = $this->conexao->lastInsertId();
            }

            // Relaciona o ataque ao Pokémon
            $sql = "INSERT INTO pokemon_ataque (
                        id_pokemon,
                        id_ataque,
                        posicao
                    ) VALUES (
                        :id_pokemon,
                        :id_ataque,
                        :posicao
                    )";

            $stmt = $this->conexao->prepare($sql);

            $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
            $stmt->bindValue(":id_ataque", $idAtaque, PDO::PARAM_INT);
            $stmt->bindValue(":posicao", $ataque["posicao"], PDO::PARAM_INT);

            $stmt->execute();
        }
    }
}