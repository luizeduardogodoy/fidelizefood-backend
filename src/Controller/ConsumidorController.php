<?php

namespace FidelizeFood\Controller;
use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\Restaurante;
use FidelizeFood\Entity\RestauranteCampanha;

class ConsumidorController extends Controller{

	public function __construct(){
		
	}
	
	public function listarCampanhasParticipando(){
		global $db;
		
		$sql = "SELECT a.idusuariocampanha, b.nome, c.datainicial, c.datafinal, c.qtde, count(*) AS refeicoes, max(d.data) AS ultima FROM usuariocampanha a
				INNER JOIN restaurante b ON b.idrestaurante = a.idrestaurantefk
				INNER JOIN campanha c ON c.idcampanha = a.idcampanhafk
				INNER JOIN usuariocampanhaitem d ON a.idusuariocampanha = d.idusuariocampanhafk
				WHERE idusuariofk = " . $_SESSION["UsuarioID"] . " AND utilizado IS NULL				
				GROUP BY  a.idusuariocampanha 
				ORDER BY a.idusuariocampanha";
	
		$res = \ADOdbConnection::getConn()->Execute($sql);
	
		if($res){
			while(!$res->EOF){
				
				list($ano, $mes, $dia) = explode("-", $res->fields("ultima"));
				$ultima = $dia . "/" . $mes . "/" . $ano;
				
				$dados["registros"][] = array("nomeRestaurante" => $res->fields("nome"),
														"qtde" => $res->fields("qtde"),  
														"refeicoes" => $res->fields("refeicoes"), 
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