async function carregarPokemons() {
    const quantidade = document.getElementById("quantidade-pokemons");
    const lista = document.getElementById("lista-pokemons");
    const busca = document.getElementById("busca-pokemon");
    const detalhes = document.getElementById("detalhes-pokemon");
    const slots = document.querySelectorAll(".slot-pokemon");

    const apiCatalogo = "../../../public/api/listarPokemons.php";
    const apiTime = "../../../public/api/gerenciarTime.php";

    function criarElemento(tag, texto, classe) {
        const elemento = document.createElement(tag);
        if (texto !== undefined) elemento.textContent = texto;
        if (classe) elemento.classList.add(classe);
        return elemento;
    }

    function criarImagem(url, nome, classe) {
        const imagem = criarElemento("img", undefined, classe);
        imagem.src = url;
        imagem.alt = nome;
        return imagem;
    }

    function criarBotao(texto) {
        const botao = criarElemento("button", texto);
        botao.type = "button";
        return botao;
    }

    async function alterarTime(dados) {
        const resposta = await fetch(apiTime, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(dados)
        });

        const resultado = await resposta.json();
        if (!resposta.ok || !resultado.sucesso) {
            throw new Error(resultado.mensagem || "Não foi possível atualizar o time.");
        }
    }

    try {
        const resposta = await fetch(apiCatalogo);
        if (!resposta.ok) {
            throw new Error("Erro ao buscar o catálogo de Pokémon.");
        }

        const catalogo = new Map();
        for (const linha of await resposta.json()) {
            const id = Number(linha.id_pokemon);
            if (!catalogo.has(id)) {
                catalogo.set(id, { ...linha, tipos: [] });
            }

            const pokemon = catalogo.get(id);
            if (
                linha.nome_tipo &&
                !pokemon.tipos.some(tipo => tipo.nome === linha.nome_tipo)
            ) {
                pokemon.tipos.push({
                    nome: linha.nome_tipo,
                    imagem: linha.imagem_tipo
                });
            }
        }

        const pokemons = [...catalogo.values()];
        quantidade.textContent = pokemons.length;

        const respostaTime = await fetch(apiTime);
        const dadosTime = await respostaTime.json();
        if (!respostaTime.ok || !dadosTime.sucesso) {
            throw new Error(dadosTime.mensagem || "Erro ao carregar o time.");
        }

        const time = dadosTime.pokemons.map(
            pokemon => catalogo.get(Number(pokemon.id_pokemon)) ?? pokemon
        );
        const estado = dadosTime.desbloqueio;
        const idsLiberados = new Set(estado.desbloqueados.map(Number));
        const evolucoes = dadosTime.evolucoes ?? [];
        const escolhidos = new Map();
        const faltam = Math.max(0, 3 - time.length);

        const instrucoes = criarElemento("div");
        const texto = criarElemento("p");
        const confirmar = criarBotao("Confirmar equipe inicial");
        const loja = criarElemento("a", "\uD83D\uDED2 Pok\u00e9 Mart");
        loja.href = "../loja/index.php";
        loja.style.cssText = "display:inline-flex;align-items:center;padding:8px 14px;margin:6px 0 12px;background:#286486;color:white;border:2px solid #5ce5e8;border-radius:8px;font-weight:bold;text-decoration:none";
        instrucoes.append(texto, estado.escolheu_iniciais ? loja : confirmar);
        lista.before(instrucoes);

        function atualizarEscolha() {
            const nomes = [...escolhidos.values()]
                .map(pokemon => pokemon.nome)
                .join(", ");

            texto.textContent =
                `Escolha seus três Pokémon iniciais. ${time.length + escolhidos.size}/3` +
                (nomes ? ` · ${nomes}` : "");

            confirmar.disabled = escolhidos.size !== faltam;
        }

        if (estado.escolheu_iniciais) {
            texto.textContent = "Compre Pokémon bloqueados na Poké Mart para adicionar ao time.";
        } else {
            atualizarEscolha();
        }

        confirmar.addEventListener("click", async () => {
            confirmar.disabled = true;
            try {
                await alterarTime({
                    acao: "escolher_iniciais",
                    ids: [...escolhidos.keys()]
                });
                window.location.reload();
            } catch (erro) {
                alert(erro.message);
                atualizarEscolha();
            }
        });

        function criarTipos(pokemon) {
            const tipos = criarElemento("div", undefined, "tipos-pokemon");
            for (const tipo of pokemon.tipos ?? []) {
                const icone = criarImagem(tipo.imagem, tipo.nome, "icone-tipo");
                icone.title = tipo.nome;
                tipos.appendChild(icone);
            }
            return tipos;
        }

        function mostrarDetalhes(pokemon) {
            const id = Number(pokemon.id_pokemon);
            const noTime = time.some(membro => Number(membro.id_pokemon) === id);
            const atributos = criarElemento("div", undefined, "atributos-pokemon");
            const botao = criarBotao("Adicionar ao time");

            for (const [rotulo, valor] of [
                ["HP", pokemon.hp],
                ["Ataque", pokemon.ataque],
                ["Defesa", pokemon.defesa],
                ["Ataque Especial", pokemon.ataque_especial],
                ["Defesa Especial", pokemon.defesa_especial],
                ["Velocidade", pokemon.velocidade]
            ]) {
                atributos.appendChild(criarElemento("p", `${rotulo}: ${valor}`));
            }

            if (noTime) {
                botao.textContent = "Já está no time";
                botao.disabled = true;
            } else if (!estado.escolheu_iniciais) {
                botao.textContent = escolhidos.has(id)
                    ? "Desmarcar inicial"
                    : "Escolher como inicial";

                botao.addEventListener("click", () => {
                    if (escolhidos.has(id)) {
                        escolhidos.delete(id);
                    } else if (escolhidos.size < faltam) {
                        escolhidos.set(id, pokemon);
                    } else {
                        alert("Desmarque um inicial antes de escolher outro.");
                        return;
                    }

                    atualizarEscolha();
                    mostrarDetalhes(pokemon);
                });
            } else if (!idsLiberados.has(id)) {
                botao.textContent = "\uD83D\uDED2 Bloqueado \u00b7 Ir \u00e0 Pok\u00e9 Mart";
                botao.addEventListener("click", () => {
                    window.location.href = "../loja/index.php";
                });
            } else {
                botao.addEventListener("click", async () => {
                    if (time.length >= 6) {
                        alert("Seu time já está completo!");
                        return;
                    }

                    botao.disabled = true;
                    try {
                        await alterarTime({ acao: "adicionar", id_pokemon: id });
                        time.push(pokemon);
                        mostrarTime();
                        if (detalhes.contains(botao)) mostrarDetalhes(pokemon);
                    } catch (erro) {
                        alert(erro.message);
                        botao.disabled = false;
                    }
                });
            }

            detalhes.replaceChildren(
                criarElemento("h3", pokemon.nome),
                criarImagem(pokemon.imagem, pokemon.nome, "imagem-detalhes"),
                criarTipos(pokemon),
                atributos,
                botao
            );
        }

        function mostrarTime() {
            slots.forEach((slot, indice) => {
                slot.replaceChildren();
                const pokemon = time[indice];
                if (!pokemon) return;

                const id = Number(pokemon.id_pokemon);
                slot.append(
                    criarImagem(pokemon.imagem, pokemon.nome, "imagem-pokemon-time"),
                    criarElemento("p", pokemon.nome, "nome-pokemon-time")
                );

                if (!estado.escolheu_iniciais) return;

                const excluir = criarBotao("Excluir");
                excluir.addEventListener("click", async () => {
                    excluir.disabled = true;
                    try {
                        await alterarTime({ acao: "remover", id_pokemon: id });

                        const posicao = time.findIndex(
                            membro => Number(membro.id_pokemon) === id
                        );
                        if (posicao !== -1) time.splice(posicao, 1);

                        mostrarTime();
                        detalhes.replaceChildren();
                    } catch (erro) {
                        alert(erro.message);
                        excluir.disabled = false;
                    }
                });
                slot.appendChild(excluir);

                for (const regra of evolucoes) {
                    if (Number(regra.id_pokemon) !== id) continue;

                    const evoluir = criarBotao("Evoluir");
                    const jaNoTime = Number(regra.destino_no_time) === 1;
                    const semItem = Number(regra.quantidade) <= 0;
                    evoluir.disabled = jaNoTime || semItem;
                    evoluir.style.cssText = "display:inline-flex;align-items:center;gap:6px";

                    const nomeItem = regra.item.trim().toLowerCase();
                    const spritesEspeciais = {
                        "king's rock": "kings-rock",
                        "king\u2019s rock": "kings-rock",
                        "pretty feather": "pretty-wing"
                    };
                    const sprite = spritesEspeciais[nomeItem] ?? nomeItem.replace(/\s+/g, "-");
                    const icone = criarImagem(
                        `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/${sprite}.png`,
                        "",
                        "icone-item-evolucao"
                    );
                    icone.style.cssText = "width:22px;height:22px;object-fit:contain";
                    icone.addEventListener("error", () => icone.remove());
                    evoluir.appendChild(icone);

                    evoluir.title = `Evoluir para ${regra.destino} usando ${regra.item}`;
                    if (jaNoTime) evoluir.title += " · Forma evoluida ja no time";
                    else if (semItem) evoluir.title += " · Compre o item na loja";
                    evoluir.setAttribute("aria-label", evoluir.title);

                    evoluir.addEventListener("click", async () => {
                        const mensagem = `Usar 1 ${regra.item} para evoluir ${pokemon.nome} para ${regra.destino}?`;
                        if (!confirm(mensagem)) return;

                        evoluir.disabled = true;
                        try {
                            await alterarTime({
                                acao: "evoluir",
                                id_pokemon: id,
                                id_evolucao: regra.id_evolucao
                            });
                            window.location.reload();
                        } catch (erro) {
                            alert(erro.message);
                            evoluir.disabled = false;
                        }
                    });

                    slot.appendChild(evoluir);
                }
            });
        }

        function criarCardPokemon(pokemon) {
            const card = criarElemento("article", undefined, "card-pokemon");
            card.append(
                criarElemento("h3", pokemon.nome, "nome-pokemon"),
                criarImagem(pokemon.imagem, pokemon.nome, "imagem-pokemon"),
                criarTipos(pokemon)
            );

            if (
                estado.escolheu_iniciais &&
                !idsLiberados.has(Number(pokemon.id_pokemon))
            ) {
                card.appendChild(criarElemento("p", "Bloqueado"));
            }

            card.addEventListener("click", () => mostrarDetalhes(pokemon));
            return card;
        }

        function mostrarGrupo(titulo, grupo, mensagemVazio) {
            const cabecalho = criarElemento("h3", titulo);
            cabecalho.style.gridColumn = "1 / -1";
            cabecalho.style.borderBottom = "2px solid #b9d5e9";
            cabecalho.style.paddingBottom = "6px";
            lista.appendChild(cabecalho);

            if (grupo.length) {
                lista.append(...grupo.map(criarCardPokemon));
            } else {
                const mensagem = criarElemento("p", mensagemVazio);
                mensagem.style.gridColumn = "1 / -1";
                lista.appendChild(mensagem);
            }
        }

        function mostrarPokemons(listaPokemons) {
            lista.replaceChildren();

            if (!estado.escolheu_iniciais) {
                lista.append(...listaPokemons.map(criarCardPokemon));
                return;
            }

            const meus = [];
            const bloqueados = [];

            for (const pokemon of listaPokemons) {
                const grupo = idsLiberados.has(Number(pokemon.id_pokemon))
                    ? meus
                    : bloqueados;
                grupo.push(pokemon);
            }

            mostrarGrupo("Meus Pokémon", meus, "Nenhum Pokémon teu encontrado.");
            mostrarGrupo(
                "Pokémon bloqueados",
                bloqueados,
                "Nenhum Pokémon bloqueado encontrado."
            );
        }

        mostrarPokemons(pokemons);
        mostrarTime();

        busca.addEventListener("input", () => {
            const nomePesquisado = busca.value.trim().toLowerCase();
            mostrarPokemons(
                pokemons.filter(pokemon =>
                    pokemon.nome.toLowerCase().includes(nomePesquisado)
                )
            );
        });
    } catch (erro) {
        quantidade.textContent = "Erro ao carregar";
        lista.textContent = "Não foi possível carregar os Pokémon.";
        console.error("Erro ao carregar o Team Builder:", erro);
    }
}

carregarPokemons();