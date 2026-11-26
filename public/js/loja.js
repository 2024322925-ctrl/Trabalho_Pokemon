const API_LISTAR_LOJA = "../../../public/api/listarLoja.php";
const API_COMPRAR = "../../../public/api/compra.php";

const API_EQUIPE = "../../../public/api/gerenciarTime.php";
const listaPokemonsLoja = document.getElementById("lista-pokemons-loja");

async function carregarPokemonsLoja() {
  try {
    const resposta = await fetch(API_EQUIPE);
    const dados = await resposta.json();
    if (!resposta.ok || !dados.sucesso) {
      throw new Error(dados.mensagem || "Falha ao carregar Pokémon.");
    }
    const estado = dados.desbloqueio;
    if (!estado.escolheu_iniciais) {
      listaPokemonsLoja.textContent =
        "Escolha seus três iniciais no Team Builder para liberar as compras.";
      return;
    }
    const catalogo = await fetch(
      "../../../public/api/listarPokemons.php?catalogo=loja",
    );
    if (!catalogo.ok) {
      throw new Error("Falha ao carregar o catálogo.");
    }
    const pokemons = new Map(
      (await catalogo.json()).map((pokemon) => [
        Number(pokemon.id_pokemon),
        pokemon,
      ]),
    );
    const liberados = new Set(estado.desbloqueados.map(Number));
    listaPokemonsLoja.replaceChildren();

    for (const [id, pokemon] of pokemons) {
      if (liberados.has(id)) continue;

      const card = document.createElement("article");
      card.className = "card-item";
      const nome = document.createElement("h3");
      nome.textContent = pokemon.nome;
      const imagem = document.createElement("img");
      imagem.src = pokemon.imagem;
      imagem.alt = pokemon.nome;
      imagem.className = "imagem-item";
      imagem.loading = "lazy";
      const botao = document.createElement("button");
      botao.type = "button";
      botao.textContent = "Comprar";
      botao.setAttribute("aria-label", "Comprar " + pokemon.nome);
      const preco = document.createElement("p");
      preco.classList.add("valor-preco");
      preco.textContent = "Preço: " + formatarPreco(pokemon.preco_pokemon);
      botao.addEventListener("click", async () => {
        botao.disabled = true;
        botao.textContent = "Comprando...";
        try {
          const compra = await fetch(API_EQUIPE, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ acao: "comprar_pokemon", id_pokemon: id }),
          });
          const resultado = await compra.json();
          if (!compra.ok || !resultado.sucesso) {
            throw new Error(resultado.mensagem || "Falha na compra.");
          }
          mostrarMensagem(
            `${pokemon.nome} desbloqueado no Team Builder!`,
            "sucesso",
          );
          await Promise.all([carregarPokemonsLoja(), carregarLoja()]);
        } catch (erro) {
          mostrarMensagem(erro.message);
          botao.disabled = false;
          botao.textContent = "Comprar";
        }
      });
      card.append(nome, imagem, preco, botao);
      listaPokemonsLoja.appendChild(card);
    }
    if (!listaPokemonsLoja.children.length) {
      listaPokemonsLoja.textContent =
        "Todos os Pokémon do catálogo já estão desbloqueados.";
    }
  } catch (erro) {
    listaPokemonsLoja.textContent = erro.message;
  }
}

carregarPokemonsLoja();

const listaItens = document.getElementById("lista-itens");
const saldoJogador = document.getElementById("saldo-jogador");
const mensagemLoja = document.getElementById("mensagem-loja");

// Formata os valores como Pokédollars
function formatarPreco(valor) {
  const numero = Number(valor);

  return (
    "P$ " +
    numero.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
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

  itens.forEach((item) => {
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
    quantidade.setAttribute("aria-label", "Quantidade de " + item.nome);

    const botaoComprar = document.createElement("button");
    botaoComprar.type = "button";
    botaoComprar.textContent = "Comprar";

    botaoComprar.addEventListener("click", () => {
      comprarItem(item.id_item, quantidade.value, botaoComprar);
    });

    card.append(nome, descricao, tipo, preco, quantidade, botaoComprar);

    listaItens.appendChild(card);
  });
}

// Realiza a compra de um item
async function comprarItem(idItem, quantidade, botao) {
  const quantidadeNumerica = Number(quantidade);

  if (!Number.isInteger(quantidadeNumerica) || quantidadeNumerica <= 0) {
    mostrarMensagem("Informe uma quantidade válida.");
    return;
  }

  botao.disabled = true;
  botao.textContent = "Comprando...";

  try {
    const resposta = await fetch(API_COMPRAR, {
      method: "POST",

      headers: {
        "Content-Type": "application/json",
      },

      body: JSON.stringify({
        itens: [
          {
            id_item: Number(idItem),
            quantidade: quantidadeNumerica,
          },
        ],
      }),
    });

    const dados = await resposta.json();

    if (!resposta.ok || !dados.sucesso) {
      throw new Error(dados.mensagem || "Não foi possível realizar a compra.");
    }

    await carregarLoja();

    mostrarMensagem("Compra realizada com sucesso!", "sucesso");
  } catch (erro) {
    mostrarMensagem(erro.message);
  } finally {
    botao.disabled = false;
    botao.textContent = "Comprar";
  }
}

// Inicia a loja
carregarLoja();
