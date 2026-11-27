<?php



class TimePokemonDAO
{
    private $conexao;

    public function __construct()
    {
        $database = new Database();
        $this->conexao = $database->conectar();
    }

    public function cadastrar(TimePokemon $time)
    {
        $sql = "INSERT INTO time_pokemon
                (id_usuario, nome, ativo)
                VALUES
                (:id_usuario, :nome, :ativo)";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(":id_usuario", $time->getIdUsuario());
        $stmt->bindValue(":nome", $time->getNome());
        $stmt->bindValue(":ativo", $time->getAtivo());

        $stmt->execute();

        return $this->conexao->lastInsertId();
    }

    public function buscarAtivoPorUsuario($id_usuario)
    {
        $sql = "SELECT *
                FROM time_pokemon
                WHERE id_usuario = :id_usuario
                  AND ativo = 1
                LIMIT 1";

        $stmt = $this->conexao->prepare($sql);

        $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);

        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            return null;
        }

        $time = TimePokemon::doBanco($dados);
        (new TimeMembroDAO($this->conexao))->carregarMembros($time);
        return $time;
    }
}
