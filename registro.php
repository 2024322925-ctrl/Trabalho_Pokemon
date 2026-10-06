<?php

include 'conexao.php';

if(isset($_POST['registro'])){
    $nome=$_POST['pNome'];
    $sobrenome=$_POST['uNome'];
    $email=$_POST['email'];
    $senha=$_POST['senha'];
    $senha=md5($senha);

    $checkEmail="SELECT * From usuario where email='$email'";
    $result=$conn->query($checkEmail);
    if($result->num_rows>0){
        header("Location: index.php?erro=email");
        exit();
    }
    else{
        $insertQuery="INSERT INTO usuario(nome,sobrenome,email,senha)
                        VALUES ('$nome','$sobrenome','$email','$senha')";
            if($conn->query($insertQuery)==TRUE){
                header("location: index.php");
            }
            else{
                echo "Erro:".$conn->error;
            }

    }
}

if(isset($_POST['entrar'])){
    $email=$_POST['email'];
    $senha=$_POST['senha'];
    $senha=md5($senha);

    $sql="SELECT * FROM usuario WHERE email='$email' and senha='$senha'";
    $result=$conn->query($sql);
    if($result->num_rows>0){
     session_start();
     $row=$result->fetch_assoc();
     $_SESSION['email']=$row['email'];
     header("Location: paginainicial.php");
     exit();
    }
    else{
        header("Location: index.php?erro=login");
    exit();
    }
}
?>