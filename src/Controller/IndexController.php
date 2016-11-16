<?php

namespace FidelizeFood\Controller;
use FidelizeFood\Entity\Usuario;

class IndexController extends Controller{

	public function __construct(){


	}

	/**
	 * 
	 */
	public function createUser(){
		
		$user = new Usuario();
		
		$user->idusuario = $user->lastInsertID($user->getConn(), "idUsuario") + 1;
		$user->nome = $this->getPostResponse("name");
		$user->email = $this->getPostResponse("email");
		$user->tipo = $this->getPostResponse("tipo");
		$user->senha = $this->getPostResponse("pass");
		$user->datacriacao = Date("Y-m-d");
		
		if($user->Save()){
		
			return $user;
		}
		return false;
		
	}
	
}