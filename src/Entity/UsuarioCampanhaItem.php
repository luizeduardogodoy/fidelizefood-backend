<?php

namespace FidelizeFood\Entity;

class UsuarioCampanhaItem extends FFADO{
	
	var $_table = "usuariocampanhaitem";
	
	public function __construct(){
			
		$this->nameId = "idusuariocampanhaitem";
		
		parent::__construct();
	}
}