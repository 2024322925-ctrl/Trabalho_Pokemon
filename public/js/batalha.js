
const menuPrincipal = document.getElementById("menu-principal");
const menuAtaques = document.getElementById("menu-ataques");

const botaoAtacar = document.getElementById("botao-atacar");
const botaoVoltarAtaques = document.getElementById("botao-voltar-ataques");

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

// Identifica os ataques disponíveis
const botoesAtaque = document.querySelectorAll(".botao-ataque");

botoesAtaque.forEach(function (botao) {

    botao.addEventListener("click", function () {

        const idAtaque = this.dataset.idAtaque;

        // Evita enviar vários ataques ao mesmo tempo
        botoesAtaque.forEach(function (botao) {
            botao.disabled = true;
        });

        fetch("acao.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "id_ataque=" + encodeURIComponent(idAtaque)
        })
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (dados) {

            console.log(dados);

            const mensagemBatalha = document.getElementById("mensagem-batalha");

            // Verifica se o ataque foi executado corretamente
            if (!dados.sucesso) {
                mensagemBatalha.textContent = dados.mensagem;
                return;
            }

            // Exibe a mensagem do ataque utilizado
            let mensagem = dados.mensagem;

            // Acrescenta a mensagem de efetividade, quando existir
            if (dados.mensagem_efetividade) {
                mensagem += " " + dados.mensagem_efetividade;
            }

            mensagemBatalha.textContent = mensagem;

            // Atualiza a barra de HP do adversário
            if (dados.hp_atual !== undefined) {

                document.getElementById("hp-adversario").style.width =
                    dados.porcentagem_hp + "%";

                document.getElementById("texto-hp-adversario").textContent =
                    "HP: " + dados.hp_atual + " / " + dados.hp_maximo;

            }

        })
        .catch(function (erro) {
            console.error("Erro ao enviar ataque:", erro);

            document.getElementById("mensagem-batalha").textContent =
                "Não foi possível executar o ataque.";
        })
        .finally(function () {

            botoesAtaque.forEach(function (botao) {
                botao.disabled = false;
            });

        });

    });

});
