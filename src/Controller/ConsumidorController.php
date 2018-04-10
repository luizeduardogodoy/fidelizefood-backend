<?php

namespace FidelizeFood\Controller;
use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\Restaurante;
use FidelizeFood\Entity\RestauranteCampanha;

class ConsumidorController extends Controller{

	public function __construct(){
		
	}
	
	public function listarCampanhasParticipando($id){
		global $db;
		
		$sql = "SELECT a.idusuariocampanha, b.nome, c.datainicial, c.datafinal, c.qtde, count(*) AS refeicoes, max(d.data) AS ultima FROM usuariocampanha a
				INNER JOIN restaurante b ON b.idrestaurante = a.idrestaurantefk
				INNER JOIN campanha c ON c.idcampanha = a.idcampanhafk
				INNER JOIN usuariocampanhaitem d ON a.idusuariocampanha = d.idusuariocampanhafk
				WHERE idusuariofk = " . $id . " AND utilizado = false			
				GROUP BY  a.idusuariocampanha , nome, datainicial, datafinal, qtde
				ORDER BY a.idusuariocampanha";
	
		$res = \ADOdbConnection::getConn()->Execute($sql);
	
		if($res){
			
			$dados["registros"] = array();
			
			while(!$res->EOF){
				
				list($ano, $mes, $dia) = explode("-", $res->fields("ultima"));
				$ultima = $dia . "/" . $mes . "/" . $ano;
				
				list($ano, $mes, $dia) = explode("-", $res->fields("datainicial"));
				$inicial = $dia . "/" . $mes . "/" . $ano;
				
				list($ano, $mes, $dia) = explode("-", $res->fields("datafinal"));
				$final = $dia . "/" . $mes . "/" . $ano;
				
				
				$dados["registros"][] = array("nomeRestaurante" => $res->fields("nome"),
														"qtde" => $res->fields("qtde"),  
														"refeicoes" => $res->fields("refeicoes"), 
														"ultima" => $ultima,
														"datainicial" => $inicial,
														"datafinal" => $final,
														"idusuariocampanha" => $res->fields("idusuariocampanha"));				
				
				$res->MoveNext();
			}
			
			$dados["status"] = "ok";
		}
	
		else{
			
			$dados["status"] = "!ok";
		}
		
		return $dados;
	}
	
	public function listarCarimbosParticipando(){
			
		$sql = "SELECT a.idusuariocampanha, b.nome, c.datainicial, c.datafinal, c.qtde, d.data AS datacarimbo
				FROM usuariocampanha a
				INNER JOIN restaurante b ON b.idrestaurante = a.idrestaurantefk
				INNER JOIN campanha c ON c.idcampanha = a.idcampanhafk
				INNER JOIN usuariocampanhaitem d ON a.idusuariocampanha = d.idusuariocampanhafk
				WHERE utilizado = false
				AND a.idusuariocampanha = ".$this->getPostResponse("idUsuarioCampanha")."
				ORDER BY d.data";
				//João
	
		$res = \ADOdbConnection::getConn()->Execute($sql);
	
		if($res){
			while(!$res->EOF){
				
				list($ano, $mes, $dia) = explode("-", $res->fields("datacarimbo"));
				$ultima = $dia . "/" . $mes . "/" . $ano;
				list($ano, $mes, $dia) = explode("-", $res->fields("datainicial"));
				$inicial = $dia . "/" . $mes . "/" . $ano;
				list($ano, $mes, $dia) = explode("-", $res->fields("datafinal"));
				$final = $dia . "/" . $mes . "/" . $ano;
				
				$dados["registros"][] = array("nomeRestaurante" => $res->fields("nome"),
														"qtde" => $res->fields("qtde"),  
														"inicial" => $inicial, 
														"final" => $final,
														"ultima" => $ultima,
														"idusuariocampanha" => $res->fields("idusuariocampanha"));				
				
				$res->MoveNext();
			}
			
			$dados["status"] = "ok";
		}
	
		else{
			
			$dados["status"] = "!ok";
		}
		
		return $dados;
	}
	
}