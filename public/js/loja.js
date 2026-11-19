
const API_LISTAR_LOJA = "../../../public/api/listarLoja.php";
const API_COMPRAR = "../../../public/api/compra.php";

const listaItens = document.getElementById("lista-itens");
const saldoJogador = document.getElementById("saldo-jogador");
const mensagemLoja = document.getElementById("mensagem-loja");

// Formata os valores como Pokédollars
function formatarPreco(valor) {
    const numero = Number(valor);

    return "P$ " + numero.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Exibe mensagens na loja
function mostrarMensagem(mensagem, tipo = "erro") {
    mensagemLoja.textContent = mensagem;
    mensagemLoja.className = "mensagem-" + tipo;
}

// Carrega os itens e o saldo
async function carregarLoja() {

    try {

        const resposta = await fetch(API_LISTAR_LOJA);
        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || "Erro ao carregar loja.");
        }

        saldoJogador.textContent = formatarPreco(dados.saldo);

        exibirItens(dados.itens);

    } catch (erro) {

        listaItens.textContent = "Não foi possível carregar os itens.";
        saldoJogador.textContent = "--";

        mostrarMensagem(erro.message);
    }
}

// Cria os cards dos itens

// Cria os cards dos itens
function exibirItens(itens) {

    listaItens.replaceChildren();

    if (!Array.isArray(itens) || itens.length === 0) {
        listaItens.textContent = "Nenhum item disponível na loja.";
        return;
    }

    itens.forEach(item => {

        const card = document.createElement("article");
        card.classList.add("card-item");

        // Imagem do item
        if (item.imagem_item) {
            const imagem = document.createElement("img");

            imagem.src = item.imagem_item;
            imagem.alt = "Imagem de " + item.nome;
            imagem.classList.add("imagem-item");
            imagem.loading = "lazy";

            card.appendChild(imagem);
        }

        const nome = document.createElement("h3");
        nome.textContent = item.nome;

        const descricao = document.createElement("p");
        descricao.textContent = item.descricao;

        const tipo = document.createElement("p");
        tipo.textContent = "Categoria: " + item.tipo;

        const preco = document.createElement("p");
        preco.classList.add("valor-preco");
        preco.textContent = "Preço: " + formatarPreco(item.preco);

        const quantidade = document.createElement("input");
        quantidade.type = "number";
        quantidade.min = "1";
        quantidade.step = "1";
        quantidade.value = "1";
        quantidade.classList.add("quantidade-item");
        quantidade.setAttribute(
            "aria-label",
            "Quantidade de " + item.nome
        );

        const botaoComprar = document.createElement("button");
        botaoComprar.type = "button";
        botaoComprar.textContent = "Comprar";

        botaoComprar.addEventListener("click", () => {
            comprarItem(
                item.id_item,
                quantidade.value,
                botaoComprar
            );
        });

        card.append(
            nome,
            descricao,
            tipo,
            preco,
            quantidade,
            botaoComprar
        );

        listaItens.appendChild(card);
    });
}


// Realiza a compra de um item
async function comprarItem(idItem, quantidade, botao) {

    const quantidadeNumerica = Number(quantidade);

    if (
        !Number.isInteger(quantidadeNumerica) ||
        quantidadeNumerica <= 0
    ) {
        mostrarMensagem("Informe uma quantidade válida.");
        return;
    }

    botao.disabled = true;
    botao.textContent = "Comprando...";

    try {

        const resposta = await fetch(API_COMPRAR, {
            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                itens: [
                    {
                        id_item: Number(idItem),
                        quantidade: quantidadeNumerica
                    }
                ]
            })
        });

        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(
                dados.mensagem || "Não foi possível realizar a compra."
            );
        }

        await carregarLoja();

        mostrarMensagem(
            "Compra realizada com sucesso!",
            "sucesso"
        );

    } catch (erro) {

        mostrarMensagem(erro.message);

    } finally {

        botao.disabled = false;
        botao.textContent = "Comprar";
    }
}

// Inicia a loja
carregarLoja();
