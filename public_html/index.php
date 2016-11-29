<?php

session_start();

$expiry = 30 ;//session expiry required after 30 mins

if(isset($_SESSION['LAST']) && (time() - $_SESSION['LAST'] > $expiry)) {
	session_unset();
	session_destroy();
  
	print json_encode(["status" => "exp"]);
	
	exit;
}
else{
	$_SESSION['LAST'] = time();
}

require "../config/autoload.php";
require "../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Controller\IndexController;

$idx = new IndexController();

$_SESSION["NameAPP"] = "fidelizefood";

if(!isset($_SESSION['UserID']) && $idx->getPostResponse("req") != "login"){
		
	print json_encode(["status" => "exp1", "debug_UserId" => $_SESSION['UserID']]);
	
	exit;
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
			$_SESSION['UserID'] = $usu->idusuario;
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
	
	$sql =  "SELECT * FROM restaurante a ";
	$sql .= "INNER JOIN usuario b ON a.usuario_idusuario = b.idusuario ";
	$sql .= "WHERE usuario_idusuario = " . $_POST["user_id"] . " AND b.tipo = 2 ";
	
	$res = $db->Execute($sql);
	
	if(!$res->EOF){
		$dados = ["status" => "ok"];		
	}
	else
		$dados = ["status" => "!ok"];	

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