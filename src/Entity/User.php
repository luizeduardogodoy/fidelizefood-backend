<?php

namespace FidelizeFood\Entity;

class User extends FFADO{

	var $_table = "usuario";	
	
	public function __construct(){
		
		parent::__construct();
	}

	public function getListaUsers(){
		$sql = "SELECT * FROM usuario";

		$res = $this->getConn()->Execute($sql);

		while(!$res->EOF){

			$clienteList[] = $res->fields("nome");

			$res->MoveNext();
		}

		return $clienteList;
	}

	public function save(){
		
		return parent::save();
	}

	public function __toString(){

		return $this->nome;
	}
}
