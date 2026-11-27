
const menuPrincipal = document.getElementById("menu-principal");
const menuAtaques = document.getElementById("menu-ataques");


const menuItens = document.getElementById("menu-itens");

const botaoTrocar = document.getElementById("botao-trocar");

const botaoAtacar = document.getElementById("botao-atacar");
const botaoVoltarAtaques = document.getElementById("botao-voltar-ataques");

const botaoItens = document.getElementById("botao-itens");
const botaoVoltarItens = document.getElementById("botao-voltar-itens");

const botoesAtaque = document.querySelectorAll(".botao-ataque");

const botoesItem = document.querySelectorAll(".botao-usar-item");

const modalTroca = document.getElementById("modal-troca");

const botaoVoltarBatalha = document.getElementById("botao-voltar-batalha");

let trocaObrigatoria = modalTroca.dataset.trocaObrigatoria === "true";

const botoesTroca = modalTroca.querySelectorAll(".cartao-troca");

// Elementos da janela de seleção do Revive
const modalRevive = document.getElementById("modal-revive");
const listaRevive = document.getElementById("lista-revive");
const botaoVoltarRevive = document.getElementById("botao-voltar-revive");

let idItemRevive = null;
let botoesRevive = [];
let botaoItemRevive = null;

const botaoFugir = document.getElementById("botao-fugir");

// Elementos da tela de resultado
const modalResultado = document.getElementById("modal-resultado");
const tituloResultado = document.getElementById("titulo-resultado");
const mensagemResultado = document.getElementById("mensagem-resultado");
const valorRecompensa = document.getElementById("valor-recompensa");
const listaRecompensasXP = document.getElementById("lista-recompensas-xp");
const botaoContinuar = document.getElementById("botao-continuar");

// Elementos do registro
const linhasRegistro = [
    document.getElementById("linha-registro-1"),
    document.getElementById("linha-registro-2"),
    document.getElementById("linha-registro-3"),
    document.getElementById("linha-registro-4")
];

// Elemento de status do jogador
const painelStatusJogador = document.querySelector(".status-jogador");

// Guarda as quatro mensagens mais recentes
let mensagensRegistro = [
    linhasRegistro[0].textContent.trim()
];

// Controla o estado da batalha
let trocaPendente = !modalTroca.hidden;
let batalhaEncerrada = false;

// Impede sair da batalha pelo botão voltar do navegador
history.pushState({ batalha: true }, "", window.location.href);

window.addEventListener("popstate", async function () {

    if (batalhaEncerrada) {
        return;
    }

    // Mantém o jogador na página enquanto a fuga é processada
    history.pushState({ batalha: true }, "", window.location.href);

    batalhaEncerrada = true;

    try {

        const resposta = await fetch(
            "/Trabalho_Pokemon/public/api/acao.php",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "fugir=1"
            }
        );

        const dados = await resposta.json();

        if (!dados.sucesso) {

            batalhaEncerrada = false;

            adicionarMensagem(dados.mensagem);

            return;
        }

        adicionarMensagem(dados.mensagem);

        await esperar(900);

        window.location.href = "../index.php";

    } catch (erro) {

        console.error("Erro ao sair da batalha:", erro);

        batalhaEncerrada = false;

        adicionarMensagem(
            "Não foi possível sair da batalha."
        );
    }
});

console.log("BATALHA.JS CARREGADO");
console.log("Troca pendente:", trocaPendente);
console.log("Botão atacar:", botaoAtacar);

// Aguarda um intervalo
function esperar(tempo) {
    return new Promise(function (resolve) {
        setTimeout(resolve, tempo);
    });
}

// Adiciona uma mensagem ao registro
function adicionarMensagem(mensagem) {

    if (!mensagem) {
        return;
    }

    mensagensRegistro.push(mensagem);

    // Mantém somente as quatro últimas mensagens
    mensagensRegistro = mensagensRegistro.slice(-4);

    // Atualiza as quatro linhas
    linhasRegistro.forEach(function (linha, indice) {
        linha.textContent = mensagensRegistro[indice] || "";
    });
}

// Exibe a tela de resultado da batalha
function mostrarResultado(dados) {

    const resultado = dados.resultado_batalha;
    const recompensas = dados.recompensas;

    if (!recompensas || !recompensas.sucesso) {
        console.error("Dados de recompensa não encontrados:", dados);
        adicionarMensagem("Não foi possível carregar as recompensas.");
        return;
    }

    batalhaEncerrada = true;

    // Esconde os menus e a janela de troca
    menuPrincipal.hidden = true;
    menuAtaques.hidden = true;
    menuItens.hidden = true;
    modalTroca.hidden = true;
    modalRevive.hidden = true;

    // Define o título e a mensagem
    if (resultado === "vitoria") {
        tituloResultado.textContent = "VITÓRIA!";
        mensagemResultado.textContent = "Você venceu a batalha!";
    } else {
        tituloResultado.textContent = "DERROTA!";
        mensagemResultado.textContent = "Você perdeu a batalha!";
    }

    // Mostra os Pokédollars recebidos
    valorRecompensa.textContent =
        recompensas.recompensa + " Pokédollars";

    // Limpa a lista anterior
    listaRecompensasXP.innerHTML = "";

    // Mostra o XP recebido por cada Pokémon
    recompensas.pokemons.forEach(function (pokemon) {

        const cartaoPokemon = document.querySelector(
            '.cartao-equipe[data-id-pokemon="' +
            pokemon.id_pokemon + '"]'
        );

        const nomePokemon = cartaoPokemon
            ? cartaoPokemon.querySelector("h3").textContent.trim()
            : "Pokémon";

        const item = document.createElement("div");
        item.classList.add("item-recompensa-xp");

        const nome = document.createElement("span");
        nome.textContent = nomePokemon;

        const experiencia = document.createElement("span");
        experiencia.textContent =
            "+" + recompensas.xp_por_pokemon + " XP";

        const nivel = document.createElement("span");
        nivel.textContent = "Nv. " + pokemon.nivel;

        item.appendChild(nome);
        item.appendChild(experiencia);
        item.appendChild(nivel);

        listaRecompensasXP.appendChild(item);
    });

    // Exibe a janela
    modalResultado.hidden = false;
}

botaoContinuar.addEventListener("click", function () {
    window.location.href = "../index.php";
});

// Atualiza o HP do jogador
function atualizarHPJogador(evento) {

    const barraPrincipal = document.getElementById("hp-jogador");
    const textoPrincipal = document.getElementById("texto-hp-jogador");

    if (barraPrincipal) {
        barraPrincipal.style.width =
            evento.porcentagem_hp_jogador + "%";
    }

    if (textoPrincipal) {
        textoPrincipal.textContent =
            "HP: " + evento.hp_jogador +
            " / " + evento.hp_maximo_jogador;
    }

    const idPokemon = painelStatusJogador.dataset.idPokemon;

    const barraEquipe = document.querySelector(
        '[data-hp-equipe="' + idPokemon + '"]'
    );

    const textoEquipe = document.querySelector(
        '[data-texto-hp-equipe="' + idPokemon + '"]'
    );

    if (barraEquipe) {
        barraEquipe.style.width =
            evento.porcentagem_hp_jogador + "%";
    }

    if (textoEquipe) {
        textoEquipe.textContent =
            "HP: " + evento.hp_jogador +
            " / " + evento.hp_maximo_jogador;
    }

    // Atualiza também o HP do Pokémon no modal de troca
    const textoModal = document.querySelector(
        '[data-texto-hp-modal="' + idPokemon + '"]'
    );

    if (textoModal) {
        textoModal.textContent =
            "HP: " + evento.hp_jogador +
            " / " + evento.hp_maximo_jogador;
    }

    const cartaoModal = document.querySelector(
        '.cartao-troca[data-id-pokemon="' + idPokemon + '"]'
    );

    if (cartaoModal && evento.hp_jogador <= 0) {
        cartaoModal.disabled = true;
        cartaoModal.classList.add("pokemon-derrotado");
    }
}

// Atualiza o HP do adversário
function atualizarHPAdversario(evento) {

    const barra = document.getElementById("hp-adversario");
    const texto = document.getElementById("texto-hp-adversario");

    if (barra) {
        barra.style.width =
            evento.porcentagem_hp_adversario + "%";
    }

    if (texto) {
        texto.textContent =
            "HP: " + evento.hp_adversario +
            " / " + evento.hp_maximo_adversario;
    }
}

// Abre a janela de troca
function abrirJanelaTroca(obrigatoria = true) {

    trocaPendente = true;
    trocaObrigatoria = obrigatoria;

    menuPrincipal.hidden = true;
    menuAtaques.hidden = true;
    menuItens.hidden = true;

    botoesAtaque.forEach(function (botao) {
        botao.disabled = true;
    });

    // Mostra o botão de voltar somente na troca voluntária
    botaoVoltarBatalha.hidden = obrigatoria;

    modalTroca.hidden = false;
}

botaoTrocar.addEventListener("click", function () {

    if (trocaPendente || batalhaEncerrada) {
        return;
    }

    abrirJanelaTroca(false);
});

// Fecha a janela de troca
function fecharJanelaTroca() {
    modalTroca.hidden = true;
    trocaPendente = false;
}

// Abre a janela para escolher um Pokémon derrotado
function abrirJanelaRevive(pokemons) {

    menuItens.hidden = true;

    listaRevive.innerHTML = "";

    pokemons.forEach(function (pokemon) {

        const cartao = document.createElement("button");

        cartao.type = "button";
        cartao.classList.add("cartao-troca");
        cartao.dataset.idPokemon = pokemon.id_pokemon;

        const imagem = document.createElement("img");

        if (pokemon.imagem) {
            imagem.src = pokemon.imagem;
            imagem.alt = pokemon.nome;
        }

        const informacoes = document.createElement("div");

        const nome = document.createElement("h3");
        nome.textContent = pokemon.nome;

        const hp = document.createElement("p");
        hp.textContent = "Derrotado";

        informacoes.appendChild(nome);
        informacoes.appendChild(hp);

        cartao.appendChild(imagem);
        cartao.appendChild(informacoes);

        listaRevive.appendChild(cartao);
    });

    modalRevive.hidden = false;

    botoesRevive = listaRevive.querySelectorAll(".cartao-troca");
}

// Escolhe o Pokémon que será revivido
listaRevive.addEventListener("click", async function (evento) {

    const cartao = evento.target.closest(".cartao-troca");

    if (!cartao || !idItemRevive || batalhaEncerrada) {
        return;
    }

    const idPokemon = cartao.dataset.idPokemon;

    // Impede escolher mais de um Pokémon ao mesmo tempo
    botoesRevive.forEach(function (botao) {
        botao.disabled = true;
    });

    try {

        const resposta = await fetch(
            "/Trabalho_Pokemon/public/api/acao.php",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body:
                    "id_item=" + encodeURIComponent(idItemRevive) +
                    "&id_pokemon_reviver=" + encodeURIComponent(idPokemon)
            }
        );

        const dados = await resposta.json();

        if (!dados.sucesso) {

            adicionarMensagem(dados.mensagem);

            botoesRevive.forEach(function (botao) {
                botao.disabled = false;
            });

            return;
        }

        // Fecha a janela de seleção
        modalRevive.hidden = true;

        idItemRevive = null;

        // Atualiza a quantidade do Revive
        if (dados.item_utilizado && botaoItemRevive) {

            const quantidade =
                dados.item_utilizado.quantidade_restante;

            const cartaoItem = botaoItemRevive.closest(".cartao-item");

            if (cartaoItem) {

                if (quantidade <= 0) {

                    cartaoItem.remove();

                } else {

                    const textoQuantidade =
                        cartaoItem.querySelector(".dados-item p");

                    if (textoQuantidade) {
                        textoQuantidade.textContent =
                            "Quantidade: x" + quantidade;
                    }
                }
            }
        }

        botaoItemRevive = null;

        // Atualiza o HP do Pokémon revivido na equipe
        if (Array.isArray(dados.pokemons_selecionados)) {

            dados.pokemons_selecionados.forEach(function (pokemon) {

                const barraEquipe = document.querySelector(
                    '[data-hp-equipe="' + pokemon.id_pokemon + '"]'
                );

                const textoEquipe = document.querySelector(
                    '[data-texto-hp-equipe="' + pokemon.id_pokemon + '"]'
                );

                const porcentagem = (pokemon.hp_atual / pokemon.hp_maximo) * 100;

                if (barraEquipe) {
                    barraEquipe.style.width = porcentagem + "%";
                }

                if (textoEquipe) {
                    textoEquipe.textContent =
                        "HP: " + pokemon.hp_atual +
                        " / " + pokemon.hp_maximo;
                }
            });
        }

        // Exibe os acontecimentos da utilização do Revive
        if (Array.isArray(dados.eventos)) {

            for (const evento of dados.eventos) {

                atualizarHPJogador(evento);
                atualizarHPAdversario(evento);

                adicionarMensagem(evento.mensagem);

                await esperar(1100);
            }
        }

        // Verifica se precisa trocar de Pokémon
        if (dados.solicitar_troca) {

            await esperar(500);

            abrirJanelaTroca();

            return;
        }
        // Verifica se entrou um novo adversário
        if (dados.pokemon_adversario_entrou) {

            await esperar(900);

            window.location.reload();

            return;
        }

        // Verifica se a batalha terminou
        if (dados.resultado_batalha) {

            mostrarResultado(dados);

            return;
        }


    } catch (erro) {

        console.error("Erro ao utilizar Revive:", erro);

        adicionarMensagem(
            "Não foi possível reviver o Pokémon."
        );

        botoesRevive.forEach(function (botao) {
            botao.disabled = false;
        });
    }
});

// Volta à lista de itens sem consumir o Revive
botaoVoltarRevive.addEventListener("click", function () {

    modalRevive.hidden = true;

    idItemRevive = null;
    botaoItemRevive = null;

    menuItens.hidden = false;
});

// Volta à batalha sem trocar de Pokémon
botaoVoltarBatalha.addEventListener("click", function () {

    if (!trocaPendente || trocaObrigatoria) {
        return;
    }

    fecharJanelaTroca();

    botaoVoltarBatalha.hidden = true;

    menuPrincipal.hidden = false;

    botoesAtaque.forEach(function (botao) {
        botao.disabled = false;
    });
});

botaoAtacar.addEventListener("click", function () {

    if (trocaPendente || batalhaEncerrada) {
        return;
    }

    menuPrincipal.hidden = true;
    menuAtaques.hidden = false;
});

// Volta ao menu principal
botaoVoltarAtaques.addEventListener("click", function () {

    if (trocaPendente || batalhaEncerrada) {
        return;
    }

    menuAtaques.hidden = true;
    menuPrincipal.hidden = false;
});

// Abre o menu de itens
botaoItens.addEventListener("click", function () {
    if (trocaPendente || batalhaEncerrada) return;

    menuItens.hidden = false;
});

// Volta ao menu principal
botaoVoltarItens.addEventListener("click", function () {
    if (trocaPendente || batalhaEncerrada) return;

    menuItens.hidden = true;
});

// Utiliza um item durante a batalha
botoesItem.forEach(function (botao) {

    botao.addEventListener("click", async function () {

        if (trocaPendente || batalhaEncerrada) {
            return;
        }

        const idItem = this.dataset.idItem;

        // Impede utilizar vários itens ao mesmo tempo
        botoesItem.forEach(function (botao) {
            botao.disabled = true;
        });

        try {

            const resposta = await fetch(
                "/Trabalho_Pokemon/public/api/acao.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id_item=" + encodeURIComponent(idItem)
                }
            );

            const dados = await resposta.json();

            // Verifica se o servidor solicitou a escolha de um Pokémon para reviver
            if (dados.solicitar_revive) {

                idItemRevive = idItem;
                botaoItemRevive = this;

                abrirJanelaRevive(dados.pokemons_para_reviver);

                return;
            }

            if (!dados.sucesso) {

                adicionarMensagem(dados.mensagem);

                if (dados.solicitar_troca) {
                    menuItens.hidden = true;
                    abrirJanelaTroca();
                }

                return;
            }

            // Fecha a janela de itens
            menuItens.hidden = true;

            // Atualiza a quantidade do item utilizado
            if (dados.item_utilizado) {

                const quantidade =
                    dados.item_utilizado.quantidade_restante;

                const cartao = this.closest(".cartao-item");

                if (cartao) {

                    if (quantidade <= 0) {

                        cartao.remove();

                    } else {

                        const textoQuantidade =
                            cartao.querySelector(".dados-item p");

                        if (textoQuantidade) {
                            textoQuantidade.textContent =
                                "Quantidade: x" + quantidade;
                        }
                    }
                }
            }

            // Mostra as mensagens e atualiza os HP
            if (Array.isArray(dados.eventos)) {

                for (const evento of dados.eventos) {

                    atualizarHPJogador(evento);
                    atualizarHPAdversario(evento);

                    adicionarMensagem(evento.mensagem);

                    await esperar(1100);
                }
            }

            // Verifica se precisa trocar de Pokémon
            if (dados.solicitar_troca) {

                await esperar(500);

                window.location.reload();

                return;
            }

            // Verifica se entrou um novo adversário
            if (dados.pokemon_adversario_entrou) {

                await esperar(900);

                window.location.reload();

                return;
            }

            // Verifica se a batalha terminou
            if (dados.resultado_batalha) {

                mostrarResultado(dados);

                return;
            }

        } catch (erro) {

            console.error("Erro ao utilizar item:", erro);

            adicionarMensagem(
                "Não foi possível utilizar o item."
            );

        } finally {

            // Libera os itens se a batalha ainda estiver acontecendo
            if (!trocaPendente && !batalhaEncerrada) {

                botoesItem.forEach(function (botao) {
                    botao.disabled = false;
                });
            }
        }
    });
});

// Seleciona o Pokémon que entrará na batalha
botoesTroca.forEach(function (botao) {

    botao.addEventListener("click", async function () {

        if (botao.disabled || !trocaPendente) {
            return;
        }

        const idPokemon = this.dataset.idPokemon;

        // Impede múltiplas escolhas simultâneas
        botoesTroca.forEach(function (botao) {
            botao.disabled = true;
        });

        // Define o endpoint conforme o tipo de troca
        const endpoint = trocaObrigatoria
            ? "/Trabalho_Pokemon/public/api/trocar.php"
            : "/Trabalho_Pokemon/public/api/acao.php";

        // Define os dados enviados
        const dadosEnvio = "id_pokemon=" + encodeURIComponent(idPokemon);

        try {

            const resposta = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: dadosEnvio
            });

            const dados = await resposta.json();

            if (!dados.sucesso) {

                adicionarMensagem(dados.mensagem);

                // Libera novamente os Pokémon disponíveis
                botoesTroca.forEach(function (botao) {
                    if (!botao.classList.contains("pokemon-derrotado")) {
                        botao.disabled = false;
                    }
                });

                return;
            }

            // TROCA OBRIGATÓRIA
            if (trocaObrigatoria) {

                adicionarMensagem(dados.mensagem);

                await esperar(900);

                window.location.reload();

                return;
            }

            // TROCA VOLUNTÁRIA
            // O adversário já executou seu ataque no PHP.
            // Percorre os acontecimentos retornados.
            if (Array.isArray(dados.eventos)) {

                for (const evento of dados.eventos) {

                    // Atualiza o HP do jogador
                    atualizarHPJogador(evento);

                    // Atualiza o HP do adversário
                    atualizarHPAdversario(evento);

                    // Registra a mensagem
                    adicionarMensagem(evento.mensagem);

                    await esperar(1100);
                }
            }

            // Verifica se o jogador precisa trocar novamente
            if (dados.solicitar_troca) {

                await esperar(500);

                window.location.reload();

                return;
            }

            // Verifica se um novo adversário entrou
            if (dados.pokemon_adversario_entrou) {

                await esperar(900);

                window.location.reload();

                return;
            }

            // Verifica se a batalha terminou
            if (dados.resultado_batalha) {
                mostrarResultado(dados);
                return;
            }

            // Atualiza a interface com o Pokémon escolhido
            await esperar(900);

            window.location.reload();

        } catch (erro) {

            console.error("Erro ao trocar Pokémon:", erro);

            adicionarMensagem(
                "Não foi possível trocar de Pokémon."
            );

            botoesTroca.forEach(function (botao) {
                if (!botao.classList.contains("pokemon-derrotado")) {
                    botao.disabled = false;
                }
            });
        }
    });
});

// Executa os ataques
botoesAtaque.forEach(function (botao) {

    botao.addEventListener("click", async function () {

        if (trocaPendente || batalhaEncerrada) {
            return;
        }

        const idAtaque = this.dataset.idAtaque;

        // Impede múltiplas ações simultâneas
        botoesAtaque.forEach(function (botao) {
            botao.disabled = true;
        });

        try {

            const resposta = await fetch(
                "/Trabalho_Pokemon/public/api/acao.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id_ataque=" + encodeURIComponent(idAtaque)
                }
            );

            const dados = await resposta.json();

            console.log(dados);

            if (!dados.sucesso) {

                adicionarMensagem(dados.mensagem);

                if (dados.solicitar_troca) {
                    abrirJanelaTroca();
                }

                return;
            }

            // Percorre os acontecimentos na ordem dos turnos
            if (Array.isArray(dados.eventos)) {

                for (const evento of dados.eventos) {

                    // Atualiza as barras de HP
                    atualizarHPJogador(evento);
                    atualizarHPAdversario(evento);

                    // Adiciona a mensagem ao registro
                    adicionarMensagem(evento.mensagem);

                    await esperar(1100);
                }
            }

            // Verifica se o jogador precisa trocar de Pokémon
            if (dados.solicitar_troca) {
                abrirJanelaTroca();
                return;
            }

            // Atualiza a interface quando um novo adversário entra
            if (dados.pokemon_adversario_entrou) {
                await esperar(900);
                window.location.reload();
                return;
            }

            // Verifica se a batalha terminou
            if (dados.resultado_batalha) {
                mostrarResultado(dados);
                return;
            }

        } catch (erro) {

            console.error("Erro ao executar a ação:", erro);

            adicionarMensagem(
                "Não foi possível executar o ataque."
            );

        } finally {

            // Libera os ataques somente se não houver troca pendente
            if (!trocaPendente && !batalhaEncerrada) {

                botoesAtaque.forEach(function (botao) {
                    botao.disabled = false;
                });
            }
        }

    });

});

// Foge da batalha
botaoFugir.addEventListener("click", async function () {

    if (batalhaEncerrada) {
        return;
    }

    if (!confirm("Tem certeza de que deseja fugir da batalha?")) {
        return;
    }

    botaoFugir.disabled = true;

    try {

        const resposta = await fetch("/Trabalho_Pokemon/public/api/acao.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "fugir=1"
        });

        const dados = await resposta.json();

        if (!dados.sucesso) {
            adicionarMensagem(dados.mensagem);
            botaoFugir.disabled = false;
            return;
        }

        batalhaEncerrada = true;

        adicionarMensagem(dados.mensagem);

        await esperar(900);

        window.location.href = "../index.php";

    } catch (erro) {

        console.error("Erro ao fugir da batalha:", erro);

        adicionarMensagem("Não foi possível fugir da batalha.");

        botaoFugir.disabled = false;
    }
});
