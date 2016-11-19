<?php

namespace FidelizeFood\Controller;
use FidelizeFood\Entity\Usuario;
use FidelizeFood\Entity\Restaurante;

class IndexController extends Controller{

	public function __construct(){
		
	}

	/**
	 * 
	 */
	public function createUser(){
		
		$user = new Usuario();
		
		$user->idusuario = $user->nextId();
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
	
	/**
  * 
  */
	public function createRestaurant(){

		$restaurante = new Restaurante();

		$restaurante->idRestaurante = $restaurante->nextId();
		$restaurante->nome = $this->getPostResponse("name");
		$restaurante->cnpj = $this->getPostResponse("cnpj");
		$restaurante->estado = $this->getPostResponse("state");
		$restaurante->cidade = $this->getPostResponse("city");
		$restaurante->endereco = $this->getPostResponse("address");
		$restaurante->telefone = $this->getPostResponse("phone");
		$restaurante->usuario_idusuario = $this->getPostResponse("idusuario");

		if($restaurante->Save()){

			return $restaurante;
		}
		return false;

	}
	
}