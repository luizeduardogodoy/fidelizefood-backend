<?php

require "../../config/session.php";
require "../../config/autoload.php";
require "../../config/connect.php";

//inclui header html com o bootstrap
include "../../src/Template/head.html";

use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\UsuarioCampanha;
use FidelizeFood\Entity\UsuarioCampanhaItem;
use FidelizeFood\Entity\Restaurante;
use FidelizeFood\Entity\Campanha;
use FidelizeFood\Controller\IndexController;

$usu = new Usuario();
$usu->Load("idusuario = ?", $_SESSION["UsuarioID"] );

echo "
<div class=''><h1>Fidelizefood</h1>";
echo "<hr>";
echo "<h3><b>" . $usu->nome . "</b>, seja bem vindo!</h3>";

echo "<h4>Lista de Clientes</h4> ";

if($usu->idusuario != ""){

	$sql = "SELECT b.nome, e.nome as nomecliente, e.email, max(f.data) as ultima,
			MAX(idusuariocampanhaitem) as maxidusuariocampanhaitem
			FROM usuario a
			INNER JOIN restaurante b ON a.idusuario = b.usuario_idusuario
			INNER JOIN campanha c ON c.restaurante_idrestaurante = b.idrestaurante
			INNER JOIN usuariocampanha d ON d.idcampanhafk = c.idcampanha
			INNER JOIN usuario e ON d.idusuariofk = e.idusuario
			INNER JOIN usuariocampanhaitem f ON f.idusuariocampanhafk = d.idusuariocampanha
			WHERE a.idusuario =  " . $usu->idusuario . "
			GROUP BY a.nome, e.nome, e.email
			ORDER by maxidusuariocampanhaitem DESC ";
			
	$res = \ADOdbConnection::getConn()->Execute($sql);
	
	$dados["status"] = "ok";
	
	echo "<table class='table  table-striped table-dark'> ";
	echo "<thead ><tr>";
	echo "<th>Nome</th>";
	echo "<th>Email</th>";
	echo "<th>Ultima Refeicao</th></tr></thead></tbdoy>";
	
	while(!$res->EOF){
		echo "<tr><td>" . $res->fields("nomecliente") . "</td> ";
		echo "<td>" . $res->fields("email") . "</td> ";
		
		$ultima = explode("-", $res->fields("ultima"));
		echo "<td>" . ( $ultima[2] . "/" . $ultima[1] . "/" . substr($ultima[0],2,3)) . "</td></tr>";
		
		//echo $ultima . " ";
	
		$res->MoveNext();
	}
	echo "</tbody>";
	echo "</table></div>";
}