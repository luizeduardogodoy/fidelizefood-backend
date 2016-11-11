<?php

namespace FidelizeFood\Entity;

class Cliente extends FFADO{

  public function getListaClientes(){
		$sql = "SELECT * FROM tblclientes";

		$res = $this->getConn()->Execute($sql);

		while(!$res->EOF){

			$clienteList[] = $res->fields("nome");

			$res->MoveNext();
		}

}