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
