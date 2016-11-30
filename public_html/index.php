<?php

require "../config/session.php";
require "../config/autoload.php";
require "../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Controller\IndexController;

$idx = new IndexController();

$_SESSION["NameAPP"] = "fidelizefood";

//se não existe usuário setado na sessão
if(!isset($_SESSION['UserID'])){
			
	if($idx->getPostResponse("req") != "login" && $idx->getPostResponse("req") != "cadastrouser"){
		print json_encode(["status" => "!logado", "debug_UserId" => $_SESSION['UserID']]);
		
		exit;
	}
}

/*****LOGIN****/
if($idx->getPostResponse("req") == "login"){
	
	$_SESSION['LAST'] = null;
	
	$dados = ["user" => $idx->getPostResponse("user"), "status" => "!ok"];

	$usu = new Usuario();
	
	if($usu->Load("email = '" . $idx->getPostResponse("user")  . "'")){

		if($usu->senha == $idx->getPostResponse("pass") ){
			$dados["status"] = "ok";
			
			$dados["nome"] = $usu->nome;
			$dados["tipo"] = $usu->tipo;
			$dados["id"] = $usu->idusuario;
			$_SESSION['LAST'] = time();
			$_SESSION['UsuarioID'] = $usu->idusuario;
			$_SESSION['UsuarioTipo'] = $usu->tipo;
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

if($idx->getPostResponse("req") == "cadastrorestaurant"){
	
	if($idx->createRestaurant()){
		$dados = ["status" => "ok"];		
	}
	else
		$dados = ["status" => "!ok"];	

	print json_encode($dados);

	exit;
}

/*Verifica se o user logado ja tem restaurante informado, isso so vale para user do tipo == 2*/

if($idx->getPostResponse("req") == "consultarestaurante"){
	
	$dados = ["status" => "!ok"];	
	
	$sql =  "SELECT * FROM restaurante a ";
	$sql .= "INNER JOIN usuario b ON a.usuario_idusuario = b.idusuario ";
	$sql .= "WHERE usuario_idusuario = " . $_POST["user_id"] . " AND b.tipo = 2 ";
	
	$res = $db->Execute($sql);
	
	if(!$res->EOF)
		$dados = ["status" => "ok"];		
	
	print json_encode($dados);

	exit;
}

if($idx->getPostResponse("req") == "listausers"){
	
	$sql = "SELECT * FROM usuario ORDER BY nome LIMIT 100";
	$res = $db->Execute($sql);
		
	while(!$res->EOF){
		
		$dados[] = ["nome"  => $res->fields("nome"), "email" => $res->fields("email"), "senha" => $res->fields("senha")] ;
		
		$res->MoveNext();
	}

	print json_encode($dados);
	
	exit;
}

if($idx->getPostResponse("req") == "carimbo"){
	
	
	print json_encode($_SESSION);
	
}

//var_dump($_SESSION);