<?php
session_start();
include("conexao.php");

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Página Inicial</title>
</head>
<body>
    <div style="text-align:center; padding: 15%;">
        <p style="font-size:50px; font-weight:bold;">
            Olá <?php
            if(isset($_SESSION['email'])){
                $email=$_SESSION['email'];
                $query=mysqli_query($conn, "SELECT usuario.* FROM `usuario` WHERE usuario.email='$email'");
                while($row=mysqli_fetch_array($query)){
                    echo $row['nome'].' '.$row['sobrenome'];
                }
            }
            ?>
        </p>
        <a href="login/sair.php">Sair</a>

    </div>
</body>
</html>