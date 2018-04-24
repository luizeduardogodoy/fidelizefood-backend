<?php

require "../config/session.php";
require "../config/autoload.php";
require "../config/connect.php";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\UsuarioCampanha;
use FidelizeFood\Entity\UsuarioCampanhaItem;
use FidelizeFood\Entity\Restaurante;
use FidelizeFood\Entity\Campanha;
use FidelizeFood\Controller\IndexController;

$CKey = Date("Y");

$idx = new IndexController();

$_SESSION["NameAPP"] = "fidelizefood";

//se não existe usuário setado na sessão
/*if(!isset($_SESSION['UsuarioID'])){
			
	if($idx->getPostResponse("req") != "login" && $idx->getPostResponse("req") != "cadastrouser"){
		print json_encode(["status" => "!logado", "debug_UserId4" => $_SESSION['UsuarioID']]);
		
		exit;
	}
}*/

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
		$dados = ["status" => "ok", "acao" => "cadastro"];		
	}
	else
		$dados = ["status" => "!ok", "acao" => "cadastro"];	

	print json_encode($dados);

	exit;
}


/***CADASTRO RESTAURANTE CAMPANHA***/
if($idx->getPostResponse("req") == "cadastrocampanha"){
	
	/*pega o restaurante*/
	
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $idx->getPostResponse("UsuarioID"))){
		print $dados = ["status" => "!restaurante"];		
		exit;
	}
	
	try{
		$restcam = new Campanha();
			
		$restcam->idcampanha = $restcam->nextId();
		$restcam->nomecampanha = $idx->getPostResponse("nomeCampanha");
		$restcam->datainicial = $idx->getPostResponse("dtInicio");
		$restcam->datafinal = $idx->getPostResponse("dtFim");
		$restcam->qtde = $idx->getPostResponse("qtde");
		$restcam->observacao = $idx->getPostResponse("obs");
		$restcam->restaurante_idrestaurante = $rest->idrestaurante;
		
		$restcam->Save();
		//var_dump($restcam);
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
	if(!$rest->Load("usuario_idusuario = " . $idx->getPostResponse("UsuarioID"))){
		$dados = ["status" => "!restaurante"];
		print json_encode($dados);		
		exit;
	}

	/*verifica se tem campanha ativa*/
	$sql  = "SELECT * FROM campanha ";
	$sql .= "WHERE datainicial <= '" . Date("Y-m-d") . "' ";
	$sql .= "AND datafinal >= '" . Date("Y-m-d") . "' ";
	$sql .= "AND restaurante_idrestaurante = " . $rest->idrestaurante;

	$cam = \ADOdbConnection::getConn()->Execute($sql);

	$idcampanha = null;
	if(!$cam->EOF){

		$idcampanha = $cam->fields("idcampanha");
	}
	
	// Recuperando informacoes da campanha
	$restCampanha = new Campanha();
	if(!$restCampanha->Load("idcampanha = " . $idcampanha)){
		$dados = ["status" => "!restauranteCampanha"];		
		print json_encode($dados);
		exit;
	}
	
	// iterando no resultado e adicionando as informacoes para retorno via json
	if($restCampanha->idcampanha != ""){
		$dados["idcampanha"] = $restCampanha->idcampanha;
		$dados["idrestaurante"] = $rest->idrestaurante;
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
		$sql = " SELECT a.idcampanhafk, b.nome, count(*) AS refeicoes, max(d.data) AS ultima 
					 FROM usuariocampanha a
					 INNER JOIN usuario b ON b.idusuario = a.idusuariofk
					 INNER JOIN usuariocampanhaitem d ON a.idusuariocampanha = d.idusuariocampanhafk
					 WHERE a.idcampanhafk = " . $restCampanha->idcampanha . " AND utilizado IS NULL				
					 GROUP BY a.idcampanhafk, b.nome 
					 ORDER BY b.nome";
	
		$camativos = \ADOdbConnection::getConn()->Execute($sql);
	
		if($camativos){
			while(!$camativos->EOF){
				
				list($ano, $mes, $dia) = explode("-", $camativos->fields("ultima"));
				$ultima = $dia . "/" . $mes . "/" . $ano;
				
				$dados["registrosativos"][] = array("nome" => $camativos->fields("nome"),														
														"refeicoes" => $camativos->fields("refeicoes"), 
														"ultima" => $ultima);				
				
				$camativos->MoveNext();
			}
		}
		
		
		// Recuperando usuarios premiados com o fidelize
		$sqlpremiados = "SELECT b.nome, a.utilizado
				FROM usuariocampanha a
				INNER JOIN usuario b ON b.idUsuario = a.idUsuarioFK
				WHERE a.utilizado IS not NULL AND idCampanhaFK = " . $restCampanha->idcampanha . "  
				ORDER BY b.nome";
	
		$campremiados = \ADOdbConnection::getConn()->Execute($sqlpremiados);
	
		if($campremiados){
			while(!$campremiados->EOF){
				
				list($ano, $mes, $dia) = explode("-", $campremiados->fields("utilizado"));
				$ultilizado = $dia . "/" . $mes . "/" . $ano;
				
				$dados["registrospremiados"][] = array(
																"nome" => $campremiados->fields("nome"), 
																"utilizado" => $ultilizado
															);				
				
				$campremiados->MoveNext();
			}
		}
		
		$dados["status"] = "ok";				


	} else {
		
		$dados["status"] = "!ok";
		
	}
		
	print json_encode($dados);
	exit;
}

/* Verifica se ha campanha já cadastrada e quais suas informações, busca pelo id da campanha */
if($idx->getPostResponse("req") == "consultacampanhabyid"){
		
	// Recuperando informacoes da campanha
	$restCampanha = new Campanha();
	if(!$restCampanha->Load("idcampanha = " . $idx->getPostResponse("idcampanha"))){
		$dados = ["status" => "!restauranteCampanha"];		
		print json_encode($dados);
		exit;
	}
	
	// iterando no resultado e adicionando as informacoes para retorno via json
	if(!$restCampanha->EOF){

		$dados["idcampanha"] = $restCampanha->idcampanha;
		$dados["nomecampanha"] = $restCampanha->nomecampanha;
		$dados["qtde"] = $restCampanha->qtde;  
		$dados["observacao"] = $restCampanha->observacao;
		
		list($ano, $mes, $dia) = explode("-", $restCampanha->datainicial);
			$dtini = $ano . "-" . $mes . "-" . $dia;
				
		list($ano, $mes, $dia) = explode("-", $restCampanha->datafinal);
			$dtfim = $ano . "-" . $mes . "-" . $dia;
			
		$dados["datainicial"] = $dtini;		
		$dados["datafinal"] = $dtfim;

		$dados["status"] = "ok";				

	}		
			
	print json_encode($dados);
	exit;
}


/* atualização de campanhas */
if($idx->getPostResponse("req") == "atualizarcampanha"){
	
	// Recuperando informacoes da campanha
	$restCampanha = new Campanha();
	if(!$restCampanha->Load("idCampanha = " . $idx->getPostResponse("idcampanha"))){
		$dados = ["status" => "!restauranteCampanha"];		
		print json_encode($dados);
		exit;
	}
	
	try{
		
		// iterando no resultado e adicionando as informacoes para retorno via json
		if(!$restCampanha->EOF){
			
			$restCampanha->idcampanha = $idx->getPostResponse("idcampanha");
			$restCampanha->idrestaurante = $idx->getPostResponse("idcampanha");
			$restCampanha->nomecampanha = $idx->getPostResponse("nomeCampanha");
			$restCampanha->datafinal = $idx->getPostResponse("dtFim");
			$restCampanha->observacao = $idx->getPostResponse("obs");
			
			$restCampanha->Save();
			
			$dados = ["status" => "ok"];				
		}
		
	}
	catch(Exception $e){
		$dados["status"] = "!ok";
	}
	
	print json_encode($dados);
	exit;
}


/*Verifica se o user logado ja tem restaurante informado, isso so vale para user do tipo == 2*/
if($idx->getPostResponse("req") == "consultarestaurante"){
	
	$dados = ["status" => "!ok", "acao" => "consulta"];	
	
	// Pega o restaurante
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $idx->getPostResponse("UsuarioID"))){
	
		$dados = ["status" => "!restaurante"];		
	
	} else { 
		
		$dados["nome"] = $rest->nome;
		$dados["estado"] = $rest->estado;
		$dados["cidade"] = $rest->cidade;
		$dados["endereco"] = $rest->endereco;
		$dados["telefone"] = $rest->telefone;
		
		// Recuperando informacoes da campanha
		$restCampanha = new Campanha();
		if(!$restCampanha->Load("restaurante_idrestaurante = " . $rest->idrestaurante)){
			
			$dados["status"] = "!campanha";		
			
		}else{
			
			$dados["status"] = "ok";		
		
		}
	
	}
	
	print json_encode($dados);

	exit;
}




if($idx->getPostResponse("req") == "listausers"){
	//$dados = "teste";
	$sql = "SELECT * FROM usuario ORDER BY nome LIMIT 100";
	$res = \ADOdbConnection::getConn()->Execute($sql);
		
	$dados["status"] = "ok";	
		
	while(!$res->EOF){
		
		$dados["users"][] = ["nome"  => $res->fields("nome"), 
						"email" => $res->fields("email"), 
						"senha" => $res->fields("senha"),
						"tipo"  => $res->fields("tipo")];
		
		$res->MoveNext();
	}

	print json_encode($dados);
	
	exit;
}


/*REGISTRA A REFEIÇÃO*/
if($idx->getPostResponse("req") == "carimbo"){
	
	$dados = ["status" => "!ok"];	
	
	//verifica se existe o cliente informado, aqui também verifica se o usuário é do tipo 1 = Consumidor
	$cliente = new Usuario();
	
	if(!$cliente->Load("cpf = '" . $idx->getPostResponse("idusercliente") . "' AND tipo = 1 ")){
		
		$dados["mensagem"] = "Cliente não encontrado";
	
		print json_encode($dados);
		
		exit;		
	}else{
		$dados["nome"] = $cliente->nome;
	}
	
	/*pega o restaurante*/
	
	$rest = new Restaurante();
	if(!$rest->Load("usuario_idusuario = " . $idx->getPostResponse("UsuarioID"))){
		print json_encode(["status" => "!restaurante"]);		
		exit;
	}
	
	/*verifica se tem campanha ativa*/
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
		
		//Verifica se ja existe a ligação entre este usuário e esta campanha
		$usucam = new UsuarioCampanha();
		
		$where  = "idcampanhafk = " . $cam->fields('idcampanha');
		$where .= " AND utilizado IS NULL "; 
		$where .= "AND idusuariofk = " . $cliente->idusuario;
		
		//se nao existe um registro nesta tabela, insere, se não adiciona o item no registro ja existente
		if(!$usucam->Load($where)){
			
			$usucam->idusuariocampanha = $usucam->nextId();
			$usucam->idrestaurantefk = $rest->idrestaurante;
			$usucam->idusuariofk = $cliente->idusuario;
			$usucam->idcampanhafk = $cam->fields("idcampanha");
			//$usucam->utilizado = "f";
			
			$usucam->Save();	
		}
		
		$sql = "SELECT count(*) AS qtde FROM usuariocampanhaitem WHERE idusuariocampanhafk = " . $usucam->idusuariocampanha;
		$qtde = \ADOdbConnection::getConn()->Execute($sql);
		
		//aqui faz a validação para verificar se o cliente atingiu o número de registros necessários
		if($cam_qtde > $qtde->fields("qtde")){
		
			$usucamitem = new UsuarioCampanhaItem();
			$usucamitem->idusuariocampanhaitem = $usucamitem->nextId();
			$usucamitem->idusuariocampanhafk = $usucam->idusuariocampanha;
			$usucamitem->data = Date("Y-m-d");
			
			// verifica se já não existe Carimbo para esta data
			$sql  = "SELECT * FROM usuariocampanhaitem ";
			$sql .= "WHERE idusuariocampanhafk = " . $usucam->idusuariocampanha;
			$sql .= " AND data = '" . $usucamitem->data . "' ";
			
			$jaExisteEsteCarimbo = \ADOdbConnection::getConn()->Execute($sql);
			
			if(!$jaExisteEsteCarimbo->EOF){
				$dados["mensagem"] = "Já foi carimbado hoje!!";
					
				$dados["status"] = "ok";
			}
			else{
			
				if($usucamitem->Save()){
				
					$dados["mensagem"] = "Refeição adicionada!!";
					
					if($cam_qtde == $qtde->fields("qtde") + 1)
						$dados["mensagem"] .= " - Atingiu";
					
					$dados["status"] = "ok";
				}
			}
				
		}
		else{
			$dados["mensagem"] = "Cliente atingiu o total de refeições permitido na campanha";
			$dados["status"] = "ok";
		}	
	}
	catch(Exception $e){
		$dados["status"] = "!ok";
	}
	
	print json_encode($dados);
	exit;
}

/*Lista campanhas que o usuário consumidor participa*/

if($idx->getPostResponse("req") == "listarcampanhaspart"){
	
	$consumidorController = new FidelizeFood\Controller\ConsumidorController();
	
	$dados = $consumidorController->listarCampanhasParticipando($idx->getPostResponse("ID_USER"));
	
	print json_encode($dados);
	exit;
	
}

/*Lista Carimbos das campanhas que o usuário consumidor participa*/

if($idx->getPostResponse("req") == "listarCarimbosPart"){
	
	$consumidorController = new FidelizeFood\Controller\ConsumidorController();
	
	$dados = $consumidorController->listarCarimbosParticipando();
	
	print json_encode($dados);
	exit;
	
}

/*Funcionalidade para registrar o premio concedido*/

if($idx->getPostResponse("req") == "premio"){
	
	$dados = ["status" => "!ok"];	
	
	try{
	
		//verifica se existe o cliente informado, aqui também verifica se o usuário é do tipo 1 = Consumidor
		$cliente = new Usuario();
		
		if(!$cliente->Load("cpf = '" . $idx->getPostResponse("idusercliente") . "' AND tipo = 1 ")){
			
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
		
		$usucam = new UsuarioCampanha();
		$usucam->Load("idusuariofk = " . $cliente->idusuario . " AND idcampanhafk = " . $cam->fields("idCampanha") . " AND utilizado IS NULL");
		
		if($usucam->idusuariocampanha != ""){
			$sql = "SELECT count(*) AS qtde, max(data) AS ult_ref FROM usuariocampanhaitem WHERE idusuariocampanhafk = " . $usucam->idusuariocampanha;
			$qtde = \ADOdbConnection::getConn()->Execute($sql);
			
			$dados["cam_qtde"] = $cam_qtde;
			$dados["qtde_ref"] = $qtde->fields("qtde");
			$dados["ult_ref"]  = $qtde->fields("ult_ref");
			
			if($cam_qtde == $qtde->fields("qtde")){
				$usucam->utilizado = Date("Y-m-d");
				$usucam->Save();
				
				$dados["mensagem"] = "Operação efetuada com sucesso!";
			}
			else{
				$dados["mensagem"] = "Cliente ainda não atingiu o qtde necessária: " .  $qtde->fields("qtde") . " - " . $cam_qtde;
				
			}
		}
		else{
			$dados["mensagem"] = "Não foi encontrado uma participação ativa";
		}
		
		$dados["status"] = "ok";
		
	}
	catch(Exception $e){
		
		
		$dados["status"] = "!ok";
	}
	
	print json_encode($dados);
	exit;
}

/*Verifica se o email utilizado para cadastro já existe ou não*/

if($idx->getPostResponse("req") == "verificaemail"){
	
	try{
		$usu = new Usuario();
		
		if($usu->Load("email = '" . $idx->getPostResponse("email")  . "'")){
			print '{"jaExiste":"sim"}';

		}
		else{
			print '{"jaExiste":"nao"}';
		}
	}
	catch(Exception $e){
		print '{"status":"!ok"}';
	}
	
	exit;
}

if($idx->getPostResponse("req") == "relClientes"){
	
	$sql = "SELECT b.nome, e.nome as nomecliente, e.email, max(f.data) as ultima,
			MAX(idusuariocampanhaitem) as maxidusuariocampanhaitem
			FROM usuario a
			INNER JOIN restaurante b ON a.idusuario = b.usuario_idusuario
			INNER JOIN campanha c ON c.restaurante_idrestaurante = b.idrestaurante
			INNER JOIN usuariocampanha d ON d.idcampanhafk = c.idcampanha
			INNER JOIN usuario e ON d.idusuariofk = e.idusuario
			INNER JOIN usuariocampanhaitem f ON f.idusuariocampanhafk = d.idusuariocampanha
			WHERE a.idusuario =  " . $idx->getPostResponse("UsuarioID") . "
			GROUP BY a.nome, e.nome, e.email
			ORDER by maxidusuariocampanhaitem DESC ";
	
			
	$res = \ADOdbConnection::getConn()->Execute($sql);
	
	$dados["status"] = "ok";
	
	while(!$res->EOF){
		
		$ultima = explode("-", $res->fields("ultima"));
		$ultima = $ultima[2] . "/" . $ultima[1] . "/" . substr($ultima[0],2,3);
		
		$dados["users"][] = array("nomecliente" => $res->fields("nomecliente"),
										  "email" =>  $res->fields("email"),
										  "ultima" => $ultima);
	
	
		$res->MoveNext();
	}
	
	print json_encode($dados);
	
}