
<?php

require_once __DIR__ . "/../../config/database.php";

class FaseDAO
{
    private $conexao;

    public function __construct()
    {
        $database = new Database();
        $this->conexao = $database->conectar();
    }

    // Lista todas as fases e o progresso do usuário
    public function listarFasesPorUsuario($idUsuario)
    {
        $sql = "SELECT
                    f.id_fase,
                    f.nome,
                    f.tipo,
                    COALESCE(pu.maior_fase_desbloqueada, 1) AS maior_fase_desbloqueada,
                    CASE
                        WHEN ufc.id_fase IS NOT NULL THEN 1
                        ELSE 0
                    END AS concluida
                FROM fase f
                LEFT JOIN progresso_usuario pu
                    ON pu.id_usuario = :id_usuario
                LEFT JOIN usuario_fase_concluida ufc
                    ON ufc.id_fase = f.id_fase
                    AND ufc.id_usuario = :id_usuario_conclusao
                ORDER BY f.id_fase";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(":id_usuario_conclusao", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca uma fase específica
    public function buscarPorId($idFase)
    {
        $sql = "SELECT *
                FROM fase
                WHERE id_fase = :id_fase";

        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id_fase", $idFase, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>
