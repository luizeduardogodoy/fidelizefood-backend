<?php

require "../config/session.php";
require "../config/autoload.php";
require "../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\UsuarioCampanha;
use FidelizeFood\Entity\UsuarioCampanhaItem;
use FidelizeFood\Entity\Restaurante;
use FidelizeFood\Entity\RestauranteCampanha;
use FidelizeFood\Controller\IndexController;

$idx = new IndexController();

$_SESSION["NameAPP"] = "fidelizefood";

//se não existe usuário setado na sessão
if(!isset($_SESSION['UsuarioID'])){
			
	if($idx->getPostResponse("req") != "login" && $idx->getPostResponse("req") != "cadastrouser"){
		print json_encode(["status" => "!logado", "debug_UserId" => $_SESSION['UsuarioID']]);
		
		exit;
	}
}

/*****LOGIN****/
if($idx->getPostResponse("req") == "login"){
	
	$_SESSION['LAST'] = null;
	$_SESSION['UsuarioID'] = null;
	$_SESSION['UsuarioTipo'] = null;
	
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

/***CADASTRO RESTAURANTE***/
if($idx->getPostResponse("req") == "cadastrorestaurant"){
	
	if($idx->createRestaurant()){
		$dados = ["status" => "ok"];		
	}
	else
		$dados = ["status" => "!ok"];	

	print json_encode($dados);

	exit;
}

/***CADASTRO RESTAURANTE CAMPANHA***/
if($idx->getPostResponse("req") == "cadastrocampanha"){
	
	if($idx->createRestauranteCampanha()){
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
	
	$dados = ["status" => "!ok"];	
	
	//verifica se existe o cliente informado
	$cliente = new Usuario();
	if(!$cliente->Load('idusuario = ' . $idx->getPostResponse("idusercliente"))){
		
		$dados["mensagem"] = "Cliente não encontrado";
	
		print json_encode($dados);
		
		exit;		
	}
	
	/*pega o restaurante*/
	
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $_SESSION["UsuarioID"])){
		print $dados = ["status" => "!restaurante"];		
		exit;
	}
	
	/*pega a campanha ativa*/
	$sql  = "SELECT * FROM campanha ";
	$sql .= "WHERE datainicial <= '" . Date("Y-m-d") . "' ";
	$sql .= "AND datafinal >= '" . Date("Y-m-d") . "' ";
	$sql .= "AND restaurante_idrestaurante = " . $rest->idrestaurante;
	
	$cam = $db->Execute($sql);
	//var_dump($cam);
	if($cam->EOF){
		print json_encode(["status" => "!temcampanha"]);
		
		exit;		
	}
	
	$cam_qtde = $cam->fields("qtde");
	
	try{
		$usucam = new UsuarioCampanha();
		
		//se nao existe um registro nesta tabela, insere, se não adiciona o item no registro ja existente
		if(!$usucam->Load("idrestaurantefk = " . $rest->idrestaurante . " AND utilizado IS NULL AND idusuariofk = " . $idx->getPostResponse("idusercliente"))){
			
			$usucam->idusuariocampanha = $usucam->nextId();
			$usucam->idrestaurantefk = $rest->idrestaurante;
			$usucam->idusuariofk = $idx->getPostResponse("idusercliente");
			$usucam->idcampanhafk = $cam->fields("idCampanha");
			//$usucam->utilizado = "";
			$usucam->Save();
		}
		
		$sql = "SELECT count(*) AS qtde FROM usuariocampanhaitem WHERE idusuariocampanhafk = " . $usucam->idusuariocampanha;
		$qtde = $db->Execute($sql);
		
		//aqui faz a validação para verificar se o cliente atingiu o numero de registros necessários
		if($cam_qtde > $qtde->fields("qtde")){
		
			$usucamitem = new UsuarioCampanhaItem();
			$usucamitem->idusuariocampanhaitem = $usucamitem->nextId();
			$usucamitem->idusuariocampanhafk = $usucam->idusuariocampanha;
			$usucamitem->data = Date("Y-m-d");
			$usucamitem->Save();
			
			$dados["mensagem"] = "Refeição adicionada";
			
			if($cam_qtde == $qtde->fields("qtde") + 1)
				$dados["mensagem"] .= " - Atingiu";
				
		}
		else{
			$dados["mensagem"] = "Cliente atingiu o total de refeições estipulado na campanha";
		}
		
		$dados["status"] = "ok";
		
	}
	catch(Exception $e){
		$dados["status"] = "!ok";
	}
	
	print json_encode($dados);
	exit;
}

//var_dump($_SESSION);