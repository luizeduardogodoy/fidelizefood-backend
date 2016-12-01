<?php

namespace FidelizeFood\Entity;

class RestauranteCampanha extends FFADO{
 
 
    var $_table = "campanha";
	 
	 public function __construct(){
			
		$this->nameId = "idCampanha";
		
		parent::__construct();
	}

}