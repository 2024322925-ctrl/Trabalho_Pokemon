<?php

session_start();

require_once "../../controllers/AuthController.php";

$controller = new AuthController();

if (isset($_GET["acao"]) && $_GET["acao"] == "sair") {
    $controller->sair();
}

if (isset($_POST["registro"])) {

    $nome = $_POST["pNome"];
    $sobrenome = $_POST["uNome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $resultado = $controller->cadastrar(
        $nome,
        $sobrenome,
        $email,
        $senha
    );

    if ($resultado) {
        header("Location: ../home/index.php");
        exit();
    } else {
        header("Location: index.php?erro=email");
        exit();
    }
}

if (isset($_POST["entrar"])) {

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $usuario = $controller->login($email, $senha);

    if ($usuario != null && $usuario != false) {

        $_SESSION["id_usuario"] = $usuario->getId_usuario();
        $_SESSION["nome"] = $usuario->getNome();
        $_SESSION["sobrenome"] = $usuario->getSobrenome();
        $_SESSION["email"] = $usuario->getEmail();

        header("Location: ../home/index.php");
        exit();

    } else {

        header("Location: index.php?erro=login");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/styleLogin.css">
    
    <title>Registro e Login</title>
</head>
<body>
    <img src="../../../public/imagens/titulo2.png" alt="titulo" class="titulo">

    <div class="container" id="registro" style="display: none;">
        <h1 class="form-title">Registro</h1>
        <form method="post">
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="pNome" id="pNome" placeholder="Primeiro Nome" required>
            <label for="pNome">Primeiro Nome</label>
        </div>
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="uNome" id="uNome" placeholder="Último Nome" required>
            <label for="uNome">Último nome</label>
        </div>
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="emailLogin" placeholder="Email" required>
            <label for="emailLogin">Email</label>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="senha" id="senha" placeholder="Senha" required>
            <label for="senha">Senha</label>
        </div>

        <p id="mensagemRegistro" class="erro-login"></p>

        <input type="submit" class="btn" value="Registre-se" name="registro">
        </form>
        
        <div class="links">
            <p>já tem uma conta ?</p>
            <button id="BotaoEntrar">Entrar</button>
        </div>
        
    </div>


    <div class="container" id="entrar" >
        <h1 class="form-title">Entrar</h1>
        <form method="post">
          
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <label for="email">Email</label>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="senha" id="senha" placeholder="Senha" required>
            <label for="senha">Senha</label>
        </div>
        <p id="mensagem" class="erro-login"></p>
        
        <input type="submit" class="btn" value="Entrar" name="entrar">
        </form>
        
        <div class="links">
            <p>Não tem uma conta?</p>
            <button id="BotaoRegistro">Registre-se</button>
        </div>
        
    </div>
   
    <script src="../../../public/js/scriptLogin.js"></script>
</body>
</html>