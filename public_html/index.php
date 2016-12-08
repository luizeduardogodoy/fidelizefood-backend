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
	
	/*pega o restaurante*/
	
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $_SESSION["UsuarioID"])){
		print $dados = ["status" => "!restaurante"];		
		exit;
	}
	
	try{
		$restcam = new RestauranteCampanha();
			
		$restcam->idcampanha = $restcam->nextId();
		$restcam->nomecampanha = $idx->getPostResponse("nomeCampanha");
		$restcam->datainicial = $idx->getPostResponse("dtInicio");
		$restcam->datafinal = $idx->getPostResponse("dtFim");
		$restcam->qtde = $idx->getPostResponse("qtd");
		$restcam->observacao = $idx->getPostResponse("obs");
		$restcam->restaurante_idrestaurante = $rest->idrestaurante;
		
		$restcam->Save();
		
		$dados = ["status" => "ok"];	
	}
	catch(Exception $e){
		$dados["status"] = "!ok";
	}
	
	print json_encode($dados);
	exit;
}


/* Verifica se ha campanha já cadastrada e quais suas informações */
if($idx->getPostResponse("req") == "consultacampanha"){
	
	// Recuperando informacoes do restaurante
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $_SESSION["UsuarioID"])){
		print $dados = ["status" => "!restaurante"];		
		exit;
	}
	
	// Recuperando informacoes da campanha
	$restCampanha = new RestauranteCampanha();
	if(!$restCampanha->Load("restaurante_idrestaurante = " . $rest->idrestaurante)){
		print $dados = ["status" => "!restauranteCampanha"];		
		exit;
	}
	
	// iterando no resultado e adicionando as informacoes para retorno via json
	if(!$restCampanha->EOF){
		$dados["nomecampanha"] = $restCampanha->nomecampanha;
		$dados["qtde"] = $restCampanha->qtde;  
		$dados["observacao"] = $restCampanha->observacao;
		
		list($ano, $mes, $dia) = explode("-", $restCampanha->datainicial);
			$dtini = $dia . "/" . $mes . "/" . $ano;
				
		list($ano, $mes, $dia) = explode("-", $restCampanha->datafinal);
			$dtfim = $dia . "/" . $mes . "/" . $ano;
			
		$dados["datainicial"] = $dtini;		
		$dados["datafinal"] = $dtfim;				
		
		
		// Recuperando usuarios ativos na campanha
		$sql = "SELECT a.idCampanhaFK, 
				   b.nome,
				   c.nomeCampanha, 
				   c.datainicial, 
				   c.datafinal, 
				   c.qtde, 
				   count(*) AS refeicoes, 
				   max(d.data) AS ultima 
				FROM fidelizefood.usuariocampanha a
				INNER JOIN fidelizefood.usuario b ON b.idUsuario = a.idUsuarioFK
				INNER JOIN fidelizefood.campanha c ON c.idcampanha = a.idcampanhafk
				INNER JOIN fidelizefood.usuariocampanhaitem d ON a.idusuariocampanha = d.idusuariocampanhafk
				WHERE a.idCampanhaFK = " . $restCampanha->idcampanha . " AND utilizado IS NULL				
				GROUP BY  a.idusuariocampanha 
				ORDER BY a.idusuariocampanha";
	
		$camativos = \ADOdbConnection::getConn()->Execute($sql);
	
		if($camativos){
			while(!$camativos->EOF){
				
				list($ano, $mes, $dia) = explode("-", $camativos->fields("ultima"));
				$ultima = $dia . "/" . $mes . "/" . $ano;
				
				$dados["registrosativos"][] = array("nome" => $camativos->fields("nome"),
														"qtde" => $camativos->fields("qtde"),  
														"refeicoes" => $camativos->fields("refeicoes"), 
														"ultima" => $ultima);				
				
				$camativos->MoveNext();
			}
		}
		
		
		// Recuperando usuarios premiados com o fidelize
		$sqlpremiados = "SELECT 
					b.nome,
					a.utilizado
				FROM fidelizefood.usuariocampanha a
				INNER JOIN fidelizefood.usuario b ON b.idUsuario = a.idUsuarioFK
				WHERE a.utilizado IS not NULL AND idCampanhaFK = " . $restCampanha->idcampanha . "  
				ORDER BY b.nome";
	
		$campremiados = \ADOdbConnection::getConn()->Execute($sqlpremiados);
	
		if($campremiados){
			while(!$campremiados->EOF){
				
				list($ano, $mes, $dia) = explode("-", $campremiados->fields("utilizado"));
				$ultilizado = $dia . "/" . $mes . "/" . $ano;
				
				$dados["registrospremiados"][] = array("nome" => $campremiados->fields("nome"), "utilizado" => $ultilizado);				
				
				$campremiados->MoveNext();
			}
		}
		
		$dados["status"] = "ok";				


	} else {
		
		$dados["status"] = "!ok";
		
	}
		
	print json_encode($dados);
	exit;
	
	
	/*$dados = ["status" => "!ok"];	
	
	$sql =  "SELECT a.nome, a.cidade, a.estado FROM restaurante a ";
	$sql .= "INNER JOIN usuario b ON a.usuario_idusuario = b.idusuario ";
	$sql .= "WHERE usuario_idusuario = " . $_POST["user_id"] . " AND b.tipo = 2 ";
	
	$res = \ADOdbConnection::getConn()->Execute($sql);
	
	if(!$res->EOF){
		$dados["nome"] = $res->fields("nome");
		$dados["cidade"] = $res->fields("cidade");  
		$dados["estado"] = $res->fields("estado");				
			
		$dados["status"] = "ok";
	}

	else{
		
		$dados["status"] = "!ok";
		
	}
		
	print json_encode($dados);
	exit;*/
}

/*Verifica se o user logado ja tem restaurante informado, isso so vale para user do tipo == 2*/

if($idx->getPostResponse("req") == "consultarestaurante"){
	
	$dados = ["status" => "!ok"];	
	
	// Pega o restaurante
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $_SESSION["UsuarioID"])){
	
		$dados = ["status" => "!restaurante"];		
	
	} else { 
	
		// Recuperando informacoes da campanha
		$restCampanha = new RestauranteCampanha();
		if(!$restCampanha->Load("restaurante_idrestaurante = " . $rest->idrestaurante)){
			
			$dados = ["status" => "!campanha"];		
			
		}else{
			
			$dados = ["status" => "ok"];		
		
		}
	
	}
	
	print json_encode($dados);

	exit;
}

/*if($idx->getPostResponse("req") == "listausers"){
	
	$sql = "SELECT * FROM usuario ORDER BY nome LIMIT 100";
	$res = \ADOdbConnection::getConn()->Execute($sql);
		
	while(!$res->EOF){
		
		$dados[] = ["nome"  => $res->fields("nome"), "email" => $res->fields("email"), "senha" => $res->fields("senha")] ;
		
		$res->MoveNext();
	}

	print json_encode($dados);
	
	exit;
}*/


/*REGISTRA A REFEIÇÃO*/
if($idx->getPostResponse("req") == "carimbo"){
	
	$dados = ["status" => "!ok"];	
	
	//verifica se existe o cliente informado, aqui também verifica se o usuário é do tipo 1 = Consumidor
	$cliente = new Usuario();
	
	if(!$cliente->Load('idusuario = ' . $idx->getPostResponse("idusercliente") . " AND tipo = 1 ")){
		
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
	
	$cam = \ADOdbConnection::getConn()->Execute($sql);
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
		$qtde = \ADOdbConnection::getConn()->Execute($sql);
		
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

/*Lista campanhas que o usuário consumidor particia*/

if($idx->getPostResponse("req") == "listarcampanhaspart"){
	
	$consumidorController = new FidelizeFood\Controller\ConsumidorController();
	
	$dados = $consumidorController->listarCampanhasParticipando();
	
	print json_encode($dados);
	exit;
	
}

//var_dump($_SESSION);