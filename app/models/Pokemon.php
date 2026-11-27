<?php

class Pokemon
{
    private $id_pokemon;
    private $nome;
    private $hp;
    private $hp_atual;
    private $velocidade;
    private $ataque;
    private $defesa;
    private $ataque_especial;
    private $defesa_especial;
    private $status_atual;
    private $ativo;
    private $imagem;
    private $imagem_costas;

    private const CAMPOS = [
        "id_pokemon",
        "nome",
        "hp",
        "hp_atual",
        "velocidade",
        "ataque",
        "defesa",
        "ataque_especial",
        "defesa_especial",
        "status_atual",
        "ativo",
        "imagem_costas",
        "imagem"
    ];

    
    private array $tipos = [];

    public static function doBanco(array $dados): self
    {
        $pokemon = new self();
        foreach (self::CAMPOS as $campo) {
            $pokemon->$campo = $dados[$campo] ?? null;
        }

        $tipos = array_map(
            static fn(array $tipo): TipoPokemon => TipoPokemon::porNome($tipo["nome"]),
            $dados["tipos"] ?? []
        );
        $pokemon->definirTipos(...$tipos);
        return $pokemon;
    }

    public function dados(): array
    {
        $dados = [];
        foreach (self::CAMPOS as $campo) {
            $dados[$campo] = $this->$campo;
        }
        return $dados;
    }

    public function definirTipos(TipoPokemon ...$tipos): void
    {
        $this->tipos = $tipos;
    }

    public function multiplicadorRecebidoDe(TipoPokemon $ataque): float
    {
        $multiplicador = 1.0;
        foreach ($this->tipos as $tipo) {
            $multiplicador *= $ataque->multiplicadorContra($tipo);
        }
        return $multiplicador;
    }

    public function getId_pokemon()
    {
        return $this->id_pokemon;
    }

    public function setId_pokemon($id_pokemon)
    {
        $this->id_pokemon = $id_pokemon;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getHp()
    {
        return $this->hp;
    }

    public function setHp($hp)
    {
        $this->hp = $hp;
    }

    public function getHp_atual()
    {
        return $this->hp_atual;
    }

    public function setHp_atual($hp_atual)
    {
        $this->hp_atual = $hp_atual;
    }

    public function getVelocidade()
    {
        return $this->velocidade;
    }

    public function setVelocidade($velocidade)
    {
        $this->velocidade = $velocidade;
    }

    public function getAtaque()
    {
        return $this->ataque;
    }

    public function setAtaque($ataque)
    {
        $this->ataque = $ataque;
    }

    public function getDefesa()
    {
        return $this->defesa;
    }

    public function setDefesa($defesa)
    {
        $this->defesa = $defesa;
    }

    public function getAtaque_especial()
    {
        return $this->ataque_especial;
    }

    public function setAtaque_especial($ataque_especial)
    {
        $this->ataque_especial = $ataque_especial;
    }

    public function getDefesa_especial()
    {
        return $this->defesa_especial;
    }

    public function setDefesa_especial($defesa_especial)
    {
        $this->defesa_especial = $defesa_especial;
    }

    public function getStatus_atual()
    {
        return $this->status_atual;
    }

    public function setStatus_atual($status_atual)
    {
        $this->status_atual = $status_atual;
    }

    public function getAtivo()
    {
        return $this->ativo;
    }

    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
    }

    public function getImagem()
    {
        return $this->imagem;
    }

    public function setImagem($imagem)
    {
        $this->imagem = $imagem;
    }

    public function getImagem_costas()
    {
        return $this->imagem_costas;
    }

    public function setImagem_costas($imagem_costas)
    {
        $this->imagem_costas = $imagem_costas;
    }
}
