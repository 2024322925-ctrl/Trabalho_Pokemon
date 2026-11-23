
<?php

require_once __DIR__ . "/../../config/database.php";

class BatalhaDAO
{
    private $conexao;

    public function __construct()
    {
        $database = new Database();
        $this->conexao = $database->conectar();
    }

    // Busca o time ativo do usuário
    public function buscarTimeAtivoPorUsuario($idUsuario)
    {
        $sql = "SELECT id_time
                FROM time_pokemon
                WHERE id_usuario = :id_usuario
                  AND ativo = 1
                LIMIT 1";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lista os Pokémon disponíveis no time do usuário
    public function listarPokemonsDoTime($idUsuario)
    {
        $sql = "SELECT
                    p.id_pokemon,
                    p.nome,
                    p.hp,
                    p.velocidade,
                    p.ataque,
                    p.defesa,
                    p.ataque_especial,
                    p.defesa_especial,
                    p.imagem,
                    p.imagem_costas,
                    tm.id_time,
                    tm.posicao,
                    COALESCE(pts.hp_atual, p.hp_atual) AS hp_atual,
                    COALESCE(pts.status_atual, p.status_atual) AS status_atual,
                    COALESCE(pts.nivel, 1) AS nivel,
                    COALESCE(pts.xp, 0) AS xp
                FROM time_pokemon tp
                INNER JOIN time_membro tm
                    ON tp.id_time = tm.id_time
                INNER JOIN pokemon p
                    ON tm.id_pokemon = p.id_pokemon
                LEFT JOIN pokemon_time_status pts
                    ON pts.id_time = tm.id_time
                    AND pts.id_pokemon = tm.id_pokemon
                WHERE tp.id_usuario = :id_usuario
                  AND tp.ativo = 1
                  AND p.ativo = 1
                ORDER BY tm.posicao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca os três adversários cadastrados na fase
    public function listarPokemonsDaFase($idFase)
    {
        $sql = "SELECT
                    p.id_pokemon,
                    p.nome,
                    p.hp,
                    p.velocidade,
                    p.ataque,
                    p.defesa,
                    p.ataque_especial,
                    p.defesa_especial,
                    p.imagem,
                    p.imagem_costas,
                    fp.nivel AS nivel_adversario,
                    fp.posicao
                FROM fase_pokemon fp
                INNER JOIN pokemon p
                    ON fp.id_pokemon = p.id_pokemon
                WHERE fp.id_fase = :id_fase
                ORDER BY fp.posicao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_fase", $idFase, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca os quatro ataques cadastrados para um Pokémon
    public function listarAtaquesDoPokemon($idPokemon)
    {
        $sql = "SELECT
                    a.id_ataque,
                    a.nome,
                    a.poder,
                    a.precisao,
                    a.categoria,
                    a.efeito_status,
                    a.chance_efeito,
                    a.dados_efeito,
                    t.id_tipo,
                    t.nome AS tipo
                FROM pokemon_ataque pa
                INNER JOIN ataque a
                    ON pa.id_ataque = a.id_ataque
                INNER JOIN tipo t
                    ON a.id_tipo = t.id_tipo
                WHERE pa.id_pokemon = :id_pokemon
                ORDER BY pa.posicao";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca os tipos de um Pokémon
    public function listarTiposDoPokemon($idPokemon)
    {
        $sql = "SELECT t.id_tipo, t.nome
                FROM pokemon_tipo pt
                INNER JOIN tipo t
                    ON pt.id_tipo = t.id_tipo
                WHERE pt.id_pokemon = :id_pokemon";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
// Recupera o HP máximo dos Pokémon selecionados para a batalha
    public function recuperarHPDosPokemonsSelecionados($idTime, array $idsPokemons)
    {
        if (empty($idsPokemons)) {
            return false;
        }

        $parametros = [];

        foreach ($idsPokemons as $indice => $idPokemon) {
            $parametros[] = ":pokemon_" . $indice;
        }

        $listaParametros = implode(", ", $parametros);

        try {
            $this->conexao->beginTransaction();

            // Cria registros de status para Pokémon que ainda não possuem
            $sqlInserir = "INSERT INTO pokemon_time_status
                            (id_time, id_pokemon, hp_atual)
                        SELECT
                            tm.id_time,
                            tm.id_pokemon,
                            p.hp
                        FROM time_membro tm
                        INNER JOIN pokemon p
                            ON p.id_pokemon = tm.id_pokemon
                        WHERE tm.id_time = :id_time
                            AND tm.id_pokemon IN ($listaParametros)
                            AND NOT EXISTS (
                                SELECT 1
                                FROM pokemon_time_status pts
                                WHERE pts.id_time = tm.id_time
                                AND pts.id_pokemon = tm.id_pokemon
                            )";

            $stmtInserir = $this->conexao->prepare($sqlInserir);

            $stmtInserir->bindValue(":id_time", $idTime, PDO::PARAM_INT);

            foreach ($idsPokemons as $indice => $idPokemon) {
                $stmtInserir->bindValue(
                    ":pokemon_" . $indice,
                    $idPokemon,
                    PDO::PARAM_INT
                );
            }

            $stmtInserir->execute();

            // Atualiza o HP dos registros existentes
            $sqlAtualizar = "UPDATE pokemon_time_status pts
                            INNER JOIN pokemon p
                                ON p.id_pokemon = pts.id_pokemon
                            SET pts.hp_atual = p.hp
                            WHERE pts.id_time = :id_time
                            AND pts.id_pokemon IN ($listaParametros)";

            $stmtAtualizar = $this->conexao->prepare($sqlAtualizar);

            $stmtAtualizar->bindValue(":id_time", $idTime, PDO::PARAM_INT);

            foreach ($idsPokemons as $indice => $idPokemon) {
                $stmtAtualizar->bindValue(
                    ":pokemon_" . $indice,
                    $idPokemon,
                    PDO::PARAM_INT
                );
            }

            $stmtAtualizar->execute();

            $this->conexao->commit();

            return true;

        } catch (PDOException $erro) {

            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

            return false;
        }
    }



    // Atualiza o HP atual de um Pokémon do jogador
    public function atualizarHPPokemon($idTime, $idPokemon, $hpAtual)
    {
        $sql = "UPDATE pokemon_time_status
                SET hp_atual = :hp_atual
                WHERE id_time = :id_time
                  AND id_pokemon = :id_pokemon";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(":hp_atual", $hpAtual, PDO::PARAM_INT);
        $stmt->bindValue(":id_time", $idTime, PDO::PARAM_INT);
        $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() >= 0;
    }

}

?>
