<?php

namespace FidelizeFood\Entity;

class Campanha extends FFADO{
    
    
    var $_table = "campanha";

    public function __construct(){
			
		$this->nameId = "idcampanha";
		
		parent::__construct();
	}
    
    
}