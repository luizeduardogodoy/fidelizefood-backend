<?php

require "../../config/session.php";
require "../../config/autoload.php";
require "../../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\UsuarioCampanha;
use FidelizeFood\Entity\UsuarioCampanhaItem;
use FidelizeFood\Entity\Restaurante;
use FidelizeFood\Entity\Campanha;
use FidelizeFood\Controller\IndexController;

//inclui header html com o bootstrap

//faz o login
if(isset($_POST) && count($_POST) > 0){
	
	$_SESSION['LAST'] = null;
	$_SESSION['UsuarioID'] = null;
	$_SESSION['UsuarioTipo'] = null;

	$usu = new Usuario();
	
	if($usu->Load("email = '" . $_POST["user"]  . "' AND tipo = 2")){

		if($usu->senha == $_POST["pass"] ){
			$dados["status"] = "ok";
			
			$dados["nome"] = $usu->nome;
			$dados["tipo"] = $usu->tipo;
			$dados["id"] = $usu->idusuario;
			$_SESSION['LAST'] = time();
			$_SESSION['UsuarioID'] = $usu->idusuario;
			$_SESSION['UsuarioTipo'] = $usu->tipo;
			
			Header("Location: main.php");
		}else{
			Echo "usuário nãoo encontrado!";
		}
	}
	else{
		Echo "usuário não encontrado!";
	}
		

	exit; 
	
}

require "../../src/Template/head.html";

include("../../src/Template/index.html");