
async function carregarPokemons() {
    const quantidade = document.getElementById("quantidade-pokemons");
    const lista = document.getElementById("lista-pokemons");
    const busca = document.getElementById("busca-pokemon");
    const detalhes = document.getElementById("detalhes-pokemon");

    try {
        const resposta = await fetch("../../../public/api/listarPokemons.php");

        if (!resposta.ok) {
            throw new Error("Erro ao buscar os Pokémon.");
        }

        const dados = await resposta.json();
        const pokemons = [];

        dados.forEach(function (pokemon) {
            let pokemonExistente = pokemons.find(function (item) {
                return item.id_pokemon == pokemon.id_pokemon;
            });

            if (!pokemonExistente) {
                pokemonExistente = {
                    id_pokemon: pokemon.id_pokemon,
                    nome: pokemon.nome,
                    imagem: pokemon.imagem,
                    hp: pokemon.hp,
                    velocidade: pokemon.velocidade,
                    ataque: pokemon.ataque,
                    defesa: pokemon.defesa,
                    ataque_especial: pokemon.ataque_especial,
                    defesa_especial: pokemon.defesa_especial,
                    tipos: []
                };

                pokemons.push(pokemonExistente);
            }

            if (pokemon.nome_tipo) {
                let tipoExistente = pokemonExistente.tipos.find(function (tipo) {
                    return tipo.nome == pokemon.nome_tipo;
                });

                if (!tipoExistente) {
                    pokemonExistente.tipos.push({
                        nome: pokemon.nome_tipo,
                        imagem: pokemon.imagem_tipo
                    });
                }
            }
        });

        quantidade.textContent = pokemons.length;

        function mostrarDetalhes(pokemon) {
            detalhes.replaceChildren();

            const nome = document.createElement("h3");
            nome.textContent = pokemon.nome;

            const imagem = document.createElement("img");
            imagem.src = pokemon.imagem;
            imagem.alt = pokemon.nome;
            imagem.classList.add("imagem-detalhes");

            const tipos = document.createElement("div");
            tipos.classList.add("tipos-pokemon");

            pokemon.tipos.forEach(function (tipo) {
                const icone = document.createElement("img");
                icone.src = tipo.imagem;
                icone.alt = tipo.nome;
                icone.title = tipo.nome;
                icone.classList.add("icone-tipo");

                tipos.appendChild(icone);
            });

            const atributos = document.createElement("div");
            atributos.classList.add("atributos-pokemon");

            const hp = document.createElement("p");
            hp.textContent = `HP: ${pokemon.hp}`;

            const ataque = document.createElement("p");
            ataque.textContent = `Ataque: ${pokemon.ataque}`;

            const defesa = document.createElement("p");
            defesa.textContent = `Defesa: ${pokemon.defesa}`;

            const velocidade = document.createElement("p");
            velocidade.textContent = `Velocidade: ${pokemon.velocidade}`;

            const ataqueEspecial = document.createElement("p");
            ataqueEspecial.textContent = `Ataque Especial: ${pokemon.ataque_especial}`;

            const defesaEspecial = document.createElement("p");
            defesaEspecial.textContent = `Defesa Especial: ${pokemon.defesa_especial}`;

            atributos.append(
                hp,
                ataque,
                defesa,
                ataqueEspecial,
                defesaEspecial,
                velocidade
            );

            detalhes.append(nome, imagem, tipos, atributos);
        }

        function mostrarPokemons(listaPokemons) {
            lista.replaceChildren();

            listaPokemons.forEach(function (pokemon) {
                const card = document.createElement("article");
                card.classList.add("card-pokemon");

                const nome = document.createElement("h3");
                nome.classList.add("nome-pokemon");
                nome.textContent = pokemon.nome;

                const imagem = document.createElement("img");
                imagem.classList.add("imagem-pokemon");
                imagem.src = pokemon.imagem;
                imagem.alt = pokemon.nome;

                const tipos = document.createElement("div");
                tipos.classList.add("tipos-pokemon");

                pokemon.tipos.forEach(function (tipo) {
                    const icone = document.createElement("img");
                    icone.classList.add("icone-tipo");
                    icone.src = tipo.imagem;
                    icone.alt = tipo.nome;
                    icone.title = tipo.nome;

                    tipos.appendChild(icone);
                });

                card.append(nome, imagem, tipos);

                card.addEventListener("click", function () {
                    mostrarDetalhes(pokemon);
                });

                lista.appendChild(card);
            });
        }

        mostrarPokemons(pokemons);

        busca.addEventListener("input", function () {
            const nomePesquisado = busca.value.toLowerCase().trim();

            const resultado = pokemons.filter(function (pokemon) {
                return pokemon.nome.toLowerCase().includes(nomePesquisado);
            });

            mostrarPokemons(resultado);
        });

        console.log("Pokémon recebidos da API:", pokemons);

    } catch (erro) {
        quantidade.textContent = "Erro ao carregar";
        lista.textContent = "Não foi possível carregar os Pokémon.";

        console.error("Erro ao carregar os Pokémon:", erro);
    }
}

carregarPokemons();
