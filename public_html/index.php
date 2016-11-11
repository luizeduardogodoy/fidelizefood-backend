<?php

require "../config/autoload.php";
require "../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Controller\IndexController;

$idx = new IndexController();

if($idx->getPostResponse("req") == "login"){

	$dados = ["user" => $idx->getPostResponse("user"), "status" => "!ok"];

	$usu = new Usuario();
	
	if($usu->Load("email = '" . $idx->getPostResponse("user")  . "'")){

		if($usu->senha == $idx->getPostResponse("pass") ){
			$dados["status"] = "ok";
			
			$dados["nome"] = $usu->nome;
			$dados["tipo"] = $usu->tipo;
		}		
	}
	
	print json_encode($dados);

	exit; 
	//comitado via actuary
}

if($idx->getPostResponse("req") == "cadastrouser"){
	
	$dados = [];
	
	$user = new Usuario();
	
	$user->idusuario = $user->lastInsertID($db, "idUsuario") + 1;
	$user->nome = $idx->getPostResponse("name");
	$user->email = $idx->getPostResponse("email");
	$user->tipo = $idx->getPostResponse("tipo");
	$user->senha = $idx->getPostResponse("pass");	
	
	if($user->Save()){
		$dados = ["status" => "ok"];		
	}
	else
		$dados = ["status" => "!ok"];	

	print json_encode($dados);

	exit;
}

echo "a";

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
