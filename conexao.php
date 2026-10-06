<?php

$host="localhost";
$user="root";
$pass="";
$db="jogo_pokemon";
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){
    echo "Falha em Conectar DB".$conn->connect_error;
}
?>