<?php

namespace FidelizeFood\Entity;

class UsuarioCampanha extends FFADO{
	
	var $_table = "usuariocampanha";
	
	public function __construct(){
			
		$this->nameId = "idusuariocampanha";
		
		parent::__construct();
	}
}