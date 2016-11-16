<?php

//require_once "../config/connect.php";

use Entity;

$user = new User();

var_dump($_POST);

if(isset($_POST["createUser"]) && $_POST["createUser"] ==  "001"){
  
  
  
  echo "criação do usuário liberada";

}else{

  echo "nao é possivel criar o user";
}

echo "banana";