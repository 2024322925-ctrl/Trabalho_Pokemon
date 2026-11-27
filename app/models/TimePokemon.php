
<?php

class TimePokemon
{
    private $id_time;
    private $id_usuario;
    private $nome;
    private $ativo;

    private const LIMITE_MEMBROS = 6;

    /** @var TimeMembro[] */
    private array $membros = [];

    public static function doBanco(array $dados): self
    {
        $time = new self();
        $time->setIdTime($dados["id_time"]);
        $time->setIdUsuario($dados["id_usuario"]);
        $time->setNome($dados["nome"]);
        $time->setAtivo($dados["ativo"]);
        return $time;
    }

    
    public function getMembros(): array
    {
        return $this->membros;
    }

    public function registrarMembro(Pokemon $pokemon, int $posicao): TimeMembro
    {
        $membro = new TimeMembro($pokemon, $posicao);
        $this->membros[] = $membro;
        return $membro;
    }

    public function adicionarPokemon(Pokemon $pokemon): TimeMembro
    {
        if (count($this->membros) >= self::LIMITE_MEMBROS) {
            throw new DomainException("Seu time já está completo.");
        }

        $posicao = 1;
        foreach ($this->membros as $membro) {
            if ((int) $membro->getPokemon()->getId_pokemon() === (int) $pokemon->getId_pokemon()) {
                throw new DomainException("Esse Pokémon já está no seu time.");
            }
            $posicao = max($posicao, $membro->getPosicao() + 1);
        }

        return $this->registrarMembro($pokemon, $posicao);
    }

    public function getIdTime()
    {
        return $this->id_time;
    }

    public function setIdTime($id_time)
    {
        $this->id_time = $id_time;
    }

    public function getIdUsuario()
    {
        return $this->id_usuario;
    }

    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getAtivo()
    {
        return $this->ativo;
    }

    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
    }
}
