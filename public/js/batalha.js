
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

            document.getElementById("mensagem-batalha").textContent =
                dados.mensagem;

            if (!dados.sucesso) {
                return;
            }

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
