const menuPrincipal = document.getElementById("menu-principal");
const menuAtaques = document.getElementById("menu-ataques");

const botaoAtacar = document.getElementById("botao-atacar");
const botaoVoltarAtaques = document.getElementById("botao-voltar-ataques");

const botoesAtaque = document.querySelectorAll(".botao-ataque");

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

    // Recupera o ID do Pokémon ativo no painel
    const idPokemon = painelStatusJogador.dataset.idPokemon;

    // Localiza o cartão correspondente ao Pokémon ativo
    const barraEquipe = document.querySelector(
        '[data-hp-equipe="' + idPokemon + '"]'
    );

    const textoEquipe = document.querySelector(
        '[data-texto-hp-equipe="' + idPokemon + '"]'
    );

    // Atualiza a barra do cartão
    if (barraEquipe) {
        barraEquipe.style.width =
            evento.porcentagem_hp_jogador + "%";
    }

    // Atualiza o texto do cartão
    if (textoEquipe) {
        textoEquipe.textContent =
            "HP: " + evento.hp_jogador +
            " / " + evento.hp_maximo_jogador;
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

// Abre o menu de ataques
botaoAtacar.addEventListener("click", function () {
    menuPrincipal.hidden = true;
    menuAtaques.hidden = false;
});

// Volta ao menu principal
botaoVoltarAtaques.addEventListener("click", function () {
    menuAtaques.hidden = true;
    menuPrincipal.hidden = false;
});

// Executa os ataques
botoesAtaque.forEach(function (botao) {

    botao.addEventListener("click", async function () {

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

        } catch (erro) {

            console.error("Erro ao executar a ação:", erro);

            adicionarMensagem(
                "Não foi possível executar o ataque."
            );

        } finally {

            // Libera os ataques novamente
            botoesAtaque.forEach(function (botao) {
                botao.disabled = false;
            });

        }

    });

});