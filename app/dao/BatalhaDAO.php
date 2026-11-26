
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
                    pts.status_atual AS status_atual,
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


    // Busca as recompensas de uma fase
    public function buscarRecompensasFase($idFase)
    {
        $sql = "SELECT recompensa_vitoria, xp_recompensa
                FROM fase
                WHERE id_fase = :id_fase";

        $consulta = $this->conexao->prepare($sql);

        $consulta->execute([
            ':id_fase' => $idFase
        ]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


    
    // Recupera o HP máximo dos Pokémon selecionados para a batalha
    
    public function recuperarHPDosPokemonsSelecionados($idTime, array $idsPokemons)
    {
        if (empty($idsPokemons)) {
            return false;
        }

        try {

            $this->conexao->beginTransaction();

            // Cria registros para Pokémon que ainda não possuem status salvo
            $placeholders = [];
            $parametros = [
                ":id_time" => $idTime
            ];

            foreach ($idsPokemons as $indice => $idPokemon) {
                $placeholder = ":pokemon_" . $indice;

                $placeholders[] = $placeholder;
                $parametros[$placeholder] = $idPokemon;
            }

            $listaPokemons = implode(", ", $placeholders);

            $sql = "INSERT INTO pokemon_time_status
                        (id_time, id_pokemon, hp_atual, status_atual, turnos_status)
                    SELECT
                        tm.id_time,
                        tm.id_pokemon,
                        p.hp,
                        NULL,
                        0
                    FROM time_membro tm
                    INNER JOIN pokemon p
                        ON p.id_pokemon = tm.id_pokemon
                    WHERE tm.id_time = :id_time
                    AND tm.id_pokemon IN ($listaPokemons)
                    AND NOT EXISTS (
                        SELECT 1
                        FROM pokemon_time_status pts
                        WHERE pts.id_time = tm.id_time
                        AND pts.id_pokemon = tm.id_pokemon
                    )";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute($parametros);

            // Restaura HP e remove os status dos registros existentes
            $placeholdersUpdate = [];
            $parametrosUpdate = [
                ":id_time" => $idTime
            ];

            foreach ($idsPokemons as $indice => $idPokemon) {
                $placeholder = ":pokemon_update_" . $indice;

                $placeholdersUpdate[] = $placeholder;
                $parametrosUpdate[$placeholder] = $idPokemon;
            }

            $listaPokemonsUpdate = implode(", ", $placeholdersUpdate);

            $sql = "UPDATE pokemon_time_status pts
                    INNER JOIN pokemon p
                        ON p.id_pokemon = pts.id_pokemon
                    SET
                        pts.hp_atual = p.hp,
                        pts.status_atual = NULL,
                        pts.turnos_status = 0
                    WHERE pts.id_time = :id_time
                    AND pts.id_pokemon IN ($listaPokemonsUpdate)";

            $stmt = $this->conexao->prepare($sql);
            $stmt->execute($parametrosUpdate);

            $this->conexao->commit();

            return true;

        } catch (PDOException $e) {

            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }

            return false;
        }
    }


    public function recuperarEstadoDosPokemonsSelecionados($idTime, array $idsPokemons)
    {
        return $this->recuperarHPDosPokemonsSelecionados($idTime, $idsPokemons);
    }
    
  

public function atualizarEstadoPokemon($idTime, $idPokemon, $hpAtual, $statusAtual)
{
    $sql = "UPDATE pokemon_time_status
            SET hp_atual = :hp_atual,
                status_atual = :status_atual
            WHERE id_time = :id_time
            AND id_pokemon = :id_pokemon";

    $stmt = $this->conexao->prepare($sql);

    $stmt->bindValue(":id_time", $idTime, PDO::PARAM_INT);
    $stmt->bindValue(":id_pokemon", $idPokemon, PDO::PARAM_INT);
    $stmt->bindValue(":hp_atual", $hpAtual, PDO::PARAM_INT);

    if ($statusAtual === null || $statusAtual === "") {
        $stmt->bindValue(":status_atual", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":status_atual", $statusAtual, PDO::PARAM_STR);
    }

    $stmt->execute();

    $sqlVerificacao = "SELECT hp_atual, status_atual
                       FROM pokemon_time_status
                       WHERE id_time = :id_time
                       AND id_pokemon = :id_pokemon";

    $verificacao = $this->conexao->prepare($sqlVerificacao);

    $verificacao->execute([
        ":id_time" => $idTime,
        ":id_pokemon" => $idPokemon
    ]);

    $registro = $verificacao->fetch(PDO::FETCH_ASSOC);

    error_log("PERSISTENCIA BATALHA: " . json_encode([
        "id_time" => $idTime,
        "id_pokemon" => $idPokemon,
        "hp_enviado" => $hpAtual,
        "status_enviado" => $statusAtual,
        "linhas_afetadas" => $stmt->rowCount(),
        "registro_no_banco" => $registro
    ]));

    return true;
}




    
// Aplica XP aos Pokémon sem iniciar ou finalizar uma transação
private function aplicarExperienciaPokemons(
    $idTime,
    array $idsPokemons,
    $xpRecebido
) {
    $idTime = (int) $idTime;
    $xpRecebido = (int) $xpRecebido;

    if ($idTime <= 0 || $xpRecebido < 0 || empty($idsPokemons)) {
        return false;
    }

    $idsPokemons = array_unique(
        array_map('intval', $idsPokemons)
    );

    $sqlBuscar = "SELECT nivel, xp
                  FROM pokemon_time_status
                  WHERE id_time = :id_time
                  AND id_pokemon = :id_pokemon
                  FOR UPDATE";

    $consultaBuscar = $this->conexao->prepare($sqlBuscar);

    $sqlAtualizar = "UPDATE pokemon_time_status
                     SET nivel = :nivel,
                         xp = :xp
                     WHERE id_time = :id_time
                     AND id_pokemon = :id_pokemon";

    $consultaAtualizar = $this->conexao->prepare($sqlAtualizar);

    $resultados = [];

    foreach ($idsPokemons as $idPokemon) {

        if ($idPokemon <= 0) {
            return false;
        }

        $consultaBuscar->execute([
            ':id_time' => $idTime,
            ':id_pokemon' => $idPokemon
        ]);

        $pokemon = $consultaBuscar->fetch(PDO::FETCH_ASSOC);

        if (!$pokemon) {
            return false;
        }

        $nivel = (int) $pokemon["nivel"];
        $xp = (int) $pokemon["xp"];

        // Pokémon no nível máximo não recebe mais XP
        if ($nivel >= 100) {
            $nivel = 100;
            $xp = 0;
        } else {

            $xp += $xpRecebido;

            // Permite subir mais de um nível se houver XP suficiente
            while ($nivel < 100 && $xp >= ($nivel * 50)) {

                $xp -= ($nivel * 50);
                $nivel++;
            }

            // No nível 100, o XP fica zerado
            if ($nivel >= 100) {
                $nivel = 100;
                $xp = 0;
            }
        }

        $consultaAtualizar->execute([
            ':nivel' => $nivel,
            ':xp' => $xp,
            ':id_time' => $idTime,
            ':id_pokemon' => $idPokemon
        ]);

        $resultados[] = [
            "id_pokemon" => $idPokemon,
            "nivel" => $nivel,
            "xp" => $xp
        ];
    }

    return $resultados;
}

// Concede XP utilizando uma transação própria
public function concederExperienciaPokemons(
    $idTime,
    array $idsPokemons,
    $xpRecebido
) {
    try {

        $this->conexao->beginTransaction();

        $resultados = $this->aplicarExperienciaPokemons(
            $idTime,
            $idsPokemons,
            $xpRecebido
        );

        if ($resultados === false) {
            $this->conexao->rollBack();
            return false;
        }

        $this->conexao->commit();

        return $resultados;

    } catch (PDOException $e) {

        if ($this->conexao->inTransaction()) {
            $this->conexao->rollBack();
        }

        return false;
    }
}



    // Recupera uma batalha já finalizada pela chave única
private function buscarFinalizacaoPorChave(
    $idUsuario,
    $idFase,
    $resultado,
    $chaveBatalha
) {
    $sql = "SELECT
                id_batalha,
                id_usuario,
                id_fase,
                resultado,
                recompensa,
                xp_recebido
            FROM log_batalha
            WHERE chave_batalha = :chave_batalha
            LIMIT 1";

    $stmt = $this->conexao->prepare($sql);

    $stmt->execute([
        ':chave_batalha' => $chaveBatalha
    ]);

    $batalha = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ainda não existe registro para essa chave
    if (!$batalha) {
        return null;
    }

    // Confere se o registro pertence à mesma batalha
    if (
        (int) $batalha["id_usuario"] !== (int) $idUsuario ||
        (int) $batalha["id_fase"] !== (int) $idFase ||
        $batalha["resultado"] !== $resultado
    ) {
        return false;
    }

    // Recupera os Pokémon registrados nessa batalha
    $sqlPokemons = "SELECT
                        lbp.id_pokemon,
                        lbp.xp_recebido,
                        pts.nivel,
                        pts.xp
                    FROM log_batalha_pokemon lbp
                    INNER JOIN pokemon_time_status pts
                        ON pts.id_pokemon = lbp.id_pokemon
                    INNER JOIN log_batalha lb
                        ON lb.id_batalha = lbp.id_batalha
                        AND lb.id_batalha = :id_batalha
                    INNER JOIN time_pokemon tp
                        ON tp.id_usuario = lb.id_usuario
                        AND tp.id_time = pts.id_time
                        AND tp.ativo = 1
                    WHERE lbp.id_batalha = :id_batalha_pokemon
                    ORDER BY lbp.ordem";

    $stmtPokemons = $this->conexao->prepare($sqlPokemons);

    $stmtPokemons->execute([
        ':id_batalha' => $batalha["id_batalha"],
        ':id_batalha_pokemon' => $batalha["id_batalha"]
    ]);

    $pokemons = $stmtPokemons->fetchAll(PDO::FETCH_ASSOC);

    if (count($pokemons) !== 3) {
        return false;
    }

    $resultadoPokemons = [];

    foreach ($pokemons as $pokemon) {
        $resultadoPokemons[] = [
            "id_pokemon" => (int) $pokemon["id_pokemon"],
            "nivel" => (int) $pokemon["nivel"],
            "xp" => (int) $pokemon["xp"]
        ];
    }

    return [
        "sucesso" => true,
        "id_batalha" => (int) $batalha["id_batalha"],
        "resultado" => $batalha["resultado"],
        "recompensa" => (int) $batalha["recompensa"],
        "xp_por_pokemon" => (int) $pokemons[0]["xp_recebido"],
        "xp_total" => (int) $batalha["xp_recebido"],
        "pokemons" => $resultadoPokemons
    ];
}

    // Finaliza uma batalha e registra todas as recompensas
    // Finaliza uma batalha e registra todas as recompensas
public function finalizarBatalha(
    $idUsuario,
    $idTime,
    $idFase,
    $resultado,
    array $idsPokemons,
    $chaveBatalha
) {
    $idUsuario = (int) $idUsuario;
    $idTime = (int) $idTime;
    $idFase = (int) $idFase;

    // Valida os dados recebidos
    if (
        $idUsuario <= 0 ||
        $idTime <= 0 ||
        $idFase <= 0 ||
        !in_array($resultado, ["vitoria", "derrota"], true) ||
        empty($chaveBatalha) ||
        count(array_unique(array_map('intval', $idsPokemons))) !== 3
    ) {
        return false;
    }

    $idsPokemons = array_values(
        array_unique(array_map('intval', $idsPokemons))
    );

    foreach ($idsPokemons as $idPokemon) {
        if ($idPokemon <= 0) {
            return false;
        }
    }

    try {

        // Verifica se essa batalha já foi finalizada anteriormente
        $finalizacaoExistente = $this->buscarFinalizacaoPorChave(
            $idUsuario,
            $idFase,
            $resultado,
            $chaveBatalha
        );

        if ($finalizacaoExistente !== null) {
            return $finalizacaoExistente;
        }

        $this->conexao->beginTransaction();

        // Verifica se o time pertence ao usuário e está ativo
        $sqlTime = "SELECT id_time
                    FROM time_pokemon
                    WHERE id_time = :id_time
                    AND id_usuario = :id_usuario
                    AND ativo = 1
                    FOR UPDATE";

        $stmtTime = $this->conexao->prepare($sqlTime);

        $stmtTime->execute([
            ':id_time' => $idTime,
            ':id_usuario' => $idUsuario
        ]);

        if (!$stmtTime->fetch(PDO::FETCH_ASSOC)) {
            $this->conexao->rollBack();
            return false;
        }

        // Verifica novamente se a batalha já foi registrada
        $finalizacaoExistente = $this->buscarFinalizacaoPorChave(
            $idUsuario,
            $idFase,
            $resultado,
            $chaveBatalha
        );

        if ($finalizacaoExistente !== null) {
            $this->conexao->commit();
            return $finalizacaoExistente;
        }

        // Busca as recompensas cadastradas na fase
        $sqlFase = "SELECT recompensa_vitoria, xp_recompensa
                    FROM fase
                    WHERE id_fase = :id_fase";

        $stmtFase = $this->conexao->prepare($sqlFase);

        $stmtFase->execute([
            ':id_fase' => $idFase
        ]);

        $fase = $stmtFase->fetch(PDO::FETCH_ASSOC);

        if (!$fase) {
            $this->conexao->rollBack();
            return false;
        }

        $recompensa = (int) $fase["recompensa_vitoria"];
        $xpPorPokemon = (int) $fase["xp_recompensa"];

        // Aplica as porcentagens em caso de derrota
        if ($resultado === "derrota") {
            $recompensa = (int) floor($recompensa * 0.25);
            $xpPorPokemon = (int) floor($xpPorPokemon * 0.30);
        }

        // Verifica se o usuário possui carteira
        $sqlCarteira = "SELECT id_carteira
                        FROM carteira
                        WHERE id_usuario = :id_usuario
                        FOR UPDATE";

        $stmtCarteira = $this->conexao->prepare($sqlCarteira);

        $stmtCarteira->execute([
            ':id_usuario' => $idUsuario
        ]);

        if (!$stmtCarteira->fetch(PDO::FETCH_ASSOC)) {
            $this->conexao->rollBack();
            return false;
        }

        // Atualiza o saldo da carteira
        $sqlAtualizarCarteira = "UPDATE carteira
        SET saldo = LEAST(saldo + :recompensa, 99999999.99)
         WHERE id_usuario = :id_usuario";

        $stmtCarteira = $this->conexao->prepare($sqlAtualizarCarteira);

        $stmtCarteira->execute([
            ':recompensa' => $recompensa,
            ':id_usuario' => $idUsuario
        ]);

        // Registra a batalha no histórico
        $xpTotal = $xpPorPokemon * 3;

        $sqlLog = "INSERT INTO log_batalha
                    (
                        id_usuario,
                        id_fase,
                        resultado,
                        recompensa,
                        data_batalha,
                        xp_recebido,
                        chave_batalha
                    )
                   VALUES
                    (
                        :id_usuario,
                        :id_fase,
                        :resultado,
                        :recompensa,
                        CURRENT_TIMESTAMP,
                        :xp_recebido,
                        :chave_batalha
                    )";

        $stmtLog = $this->conexao->prepare($sqlLog);

        $stmtLog->execute([
            ':id_usuario' => $idUsuario,
            ':id_fase' => $idFase,
            ':resultado' => $resultado,
            ':recompensa' => $recompensa,
            ':xp_recebido' => $xpTotal,
            ':chave_batalha' => $chaveBatalha
        ]);

        $idBatalha = (int) $this->conexao->lastInsertId();

        // Registra os três Pokémon no histórico
        $sqlLogPokemon = "INSERT INTO log_batalha_pokemon
                            (
                                id_batalha,
                                id_pokemon,
                                ordem,
                                xp_recebido
                            )
                          VALUES
                            (
                                :id_batalha,
                                :id_pokemon,
                                :ordem,
                                :xp_recebido
                            )";

        $stmtLogPokemon = $this->conexao->prepare($sqlLogPokemon);

        foreach ($idsPokemons as $indice => $idPokemon) {

            $stmtLogPokemon->execute([
                ':id_batalha' => $idBatalha,
                ':id_pokemon' => $idPokemon,
                ':ordem' => $indice + 1,
                ':xp_recebido' => $xpPorPokemon
            ]);
        }

        // Concede XP individualmente aos três Pokémon
        $resultadoXP = $this->aplicarExperienciaPokemons(
            $idTime,
            $idsPokemons,
            $xpPorPokemon
        );

        if ($resultadoXP === false) {
            $this->conexao->rollBack();
            return false;
        }

        // Confirma todas as operações
        $this->conexao->commit();

        return [
            "sucesso" => true,
            "id_batalha" => $idBatalha,
            "resultado" => $resultado,
            "recompensa" => $recompensa,
            "xp_por_pokemon" => $xpPorPokemon,
            "xp_total" => $xpTotal,
            "pokemons" => $resultadoXP
        ];

    } catch (PDOException $e) {
        error_log("Falha ao finalizar batalha: " . $e->getMessage());

        if ($this->conexao->inTransaction()) {
            $this->conexao->rollBack();
        }

        // Verifica se a falha ocorreu porque a batalha já foi registrada
        try {

            $finalizacaoExistente = $this->buscarFinalizacaoPorChave(
                $idUsuario,
                $idFase,
                $resultado,
                $chaveBatalha
            );

            if ($finalizacaoExistente !== null) {
                return $finalizacaoExistente;
            }

        } catch (PDOException $erro) {
            error_log("Falha ao consultar finalização: " . $erro->getMessage());
            return false;
        }

        return false;
    }
}



    

}

?>
