<?php

require "../config/autoload.php";
require "../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Controller\IndexController;

$idx = new IndexController();

$usario = new Usuario();

/*****LOGIN****/
if($idx->getPostResponse("req") == "login"){

	$dados = ["user" => $idx->getPostResponse("user"), "status" => "!ok"];

	$usu = new Usuario();
	
	if($usu->Load("email = '" . $idx->getPostResponse("user")  . "'")){

		if($usu->senha == $idx->getPostResponse("pass") ){
			$dados["status"] = "ok";
			
			$dados["nome"] = $usu->nome;
			$dados["tipo"] = $usu->tipo;
			$dados["id"] = $usu->idusuario;
		}		
	}
	
	print json_encode($dados);

	exit; 
	//comitado via actuary
}

/***CADASTRO USER***/

if($idx->getPostResponse("req") == "cadastrouser"){
	
	if($idx->createUser()){
		$dados = ["status" => "ok"];		
	}
	else
		$dados = ["status" => "!ok"];	

	print json_encode($dados);

	exit;
}

if($idx->getPostResponse("req") == "consultarestaurante"){
	
	if($idx->createUser()){
		$dados = ["status" => "ok"];		
	}
	else
		$dados = ["status" => "!ok"];	

	print json_encode($dados);

	exit;
}

/*
$user = new User();

//$user->iduserente = $conn->nextId("");
$user->nome = substr(md5(rand(1,99)),0,10);
$user->sobrenome = substr(md5(rand(25,50)),0,10);
$user->idade = rand(1,50);

$sexo = rand(0,1);
$user->sexo = $sexo == 0 ? "F" : "M";
$user->estado = "PR";
$user->Save();

var_dump($user->getListaUsers());

$ctrl = new IndexController();

var_dump($ctrl->getGetResponse());*/