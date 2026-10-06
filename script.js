const botaoRegistro=document.getElementById('BotaoRegistro');
const botaoEntrar=document.getElementById('BotaoEntrar');
const entrarForm=document.getElementById('entrar');
const registroForm=document.getElementById('registro');

botaoRegistro.addEventListener('click', function(){
     entrarForm.style.display="none";
     registroForm.style.display="block";
})
botaoEntrar.addEventListener('click', function(){
    entrarForm.style.display="block";
    registroForm.style.display="none";
})

const url = new URLSearchParams(window.location.search);

if(url.get("erro") === "login"){
    document.getElementById("mensagem").textContent =
        "Conta não encontrada. Crie uma conta para continuar.";
}

if(url.get("erro") === "email"){
    registroForm.style.display = "block";
    entrarForm.style.display = "none";

    document.getElementById("mensagemRegistro").textContent =
        "Este email já está cadastrado.";
}

window.addEventListener('pageshow', function() {
    document.activeElement.blur();
});

//fim página de login