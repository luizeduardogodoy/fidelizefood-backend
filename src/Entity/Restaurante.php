<?php

namespace FidelizeFood\Entity;

class Restaurante extends FFADO{
    
    var $_table = "restaurante";
	 
	 public function __construct(){
			
		$this->nameId = "idrestaurante";
		
		parent::__construct();
	}

}