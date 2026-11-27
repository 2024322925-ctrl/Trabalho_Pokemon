<?php

class TipoPokemon
{
    protected const MODIFICADORES = [];

    private const CLASSES_POR_NOME = [
        "grass" => TipoPlanta::class,
        "poison" => TipoVeneno::class,
        "normal" => TipoNormal::class,
        "fire" => TipoFogo::class,
        "flying" => TipoVoador::class,
        "water" => TipoAgua::class,
        "bug" => TipoInseto::class,
        "electric" => TipoEletrico::class,
        "ground" => TipoTerrestre::class,
        "fairy" => TipoFada::class,
        "fighting" => TipoLutador::class,
        "psychic" => TipoPsiquico::class,
        "rock" => TipoPedra::class,
        "steel" => TipoAco::class,
        "ice" => TipoGelo::class,
        "ghost" => TipoFantasma::class,
        "dragon" => TipoDragao::class,
    ];

    private string $nome;

    final protected function __construct(string $nome)
    {
        $this->nome = $nome;
    }

    public static function porNome(string $nome): self
    {
        $nome = strtolower($nome);
        $classe = self::CLASSES_POR_NOME[$nome] ?? self::class;
        return new $classe($nome);
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function multiplicadorContra(TipoPokemon $defensor): float
    {
        return static::MODIFICADORES[$defensor->getNome()] ?? 1.0;
    }
}

final class TipoAco extends TipoPokemon
{
    protected const MODIFICADORES = [
        "fire" => 0.5, "water" => 0.5, "electric" => 0.5, "rock" => 2,
        "ice" => 2, "fairy" => 2, "steel" => 0.5,
    ];
}

final class TipoAgua extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 0.5, "fire" => 2, "water" => 0.5, "ground" => 2,
        "rock" => 2, "dragon" => 0.5,
    ];
}

final class TipoDragao extends TipoPokemon
{
    protected const MODIFICADORES = ["fairy" => 0, "steel" => 0.5, "dragon" => 2];
}

final class TipoEletrico extends TipoPokemon
{
    protected const MODIFICADORES = [
        "flying" => 2, "water" => 2, "electric" => 0.5, "grass" => 0.5,
        "ground" => 0, "dragon" => 0.5,
    ];
}

final class TipoFada extends TipoPokemon
{
    protected const MODIFICADORES = [
        "fire" => 0.5, "poison" => 0.5, "fighting" => 2, "steel" => 0.5,
        "dragon" => 2,
    ];
}

final class TipoFantasma extends TipoPokemon
{
    protected const MODIFICADORES = ["normal" => 0, "psychic" => 2, "ghost" => 2];
}

final class TipoFogo extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 2, "fire" => 0.5, "water" => 0.5, "bug" => 2,
        "rock" => 0.5, "steel" => 2, "ice" => 2, "dragon" => 0.5,
    ];
}

final class TipoGelo extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 2, "fire" => 0.5, "flying" => 2, "water" => 0.5,
        "ground" => 2, "steel" => 0.5, "ice" => 0.5, "dragon" => 2,
    ];
}

final class TipoInseto extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 2, "poison" => 0.5, "fire" => 0.5, "flying" => 0.5,
        "fighting" => 0.5, "psychic" => 2, "steel" => 0.5, "ghost" => 0.5,
        "fairy" => 0.5,
    ];
}

final class TipoLutador extends TipoPokemon
{
    protected const MODIFICADORES = [
        "normal" => 2, "flying" => 0.5, "fairy" => 0.5, "fighting" => 0.5,
        "psychic" => 0.5, "rock" => 2, "ghost" => 0, "steel" => 2,
        "ice" => 2,
    ];
}

final class TipoNormal extends TipoPokemon
{
    protected const MODIFICADORES = ["rock" => 0.5, "steel" => 0.5, "ghost" => 0];
}

final class TipoPedra extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 2, "fire" => 2, "flying" => 2, "bug" => 2,
        "fighting" => 0.5, "ground" => 0.5, "steel" => 0.5,
    ];
}

final class TipoPlanta extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 0.5, "poison" => 0.5, "fire" => 0.5, "flying" => 0.5,
        "water" => 2, "bug" => 0.5, "ground" => 2, "rock" => 2,
        "steel" => 0.5, "dragon" => 0.5,
    ];
}

final class TipoPsiquico extends TipoPokemon
{
    protected const MODIFICADORES = [
        "poison" => 2, "fighting" => 2, "psychic" => 0.5, "steel" => 0.5,
    ];
}

final class TipoTerrestre extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 0.5, "fire" => 2, "electric" => 2, "ground" => 2,
        "rock" => 2, "steel" => 2, "flying" => 0,
    ];
}

final class TipoVeneno extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 2, "poison" => 0.5, "ground" => 0.5, "rock" => 0.5,
        "steel" => 0, "ghost" => 0.5, "fairy" => 2,
    ];
}

final class TipoVoador extends TipoPokemon
{
    protected const MODIFICADORES = [
        "grass" => 2, "bug" => 2, "fighting" => 2, "electric" => 0.5,
        "rock" => 0.5, "steel" => 0.5,
    ];
}