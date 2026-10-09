async function carregarPokemons() {
    const quantidade = document.getElementById("quantidade-pokemons");

    try {
        const resposta = await fetch("../../../public/api/listarPokemons.php");

        if (!resposta.ok) {
            throw new Error("Erro ao buscar os Pokémon.");
        }

        const pokemons = await resposta.json();

        const ids = new Set(pokemons.map(pokemon => pokemon.id_pokemon));

        quantidade.textContent = ids.size;

        console.log("Pokémon recebidos da API:", pokemons);

    } catch (erro) {
        quantidade.textContent = "Erro ao carregar";
        console.error("Erro ao carregar os Pokémon:", erro);
    }
}

carregarPokemons();