<?php

namespace FidelizeFood\Controller;

abstract class Controller{

	public function __construct(){

		//echo "its work!";
	}

	public function getPostResponse($key = ""){
		
		if($key != ""){
			
			if(isset($_POST[$key]))
				return $_POST[$key];
		}
		
		return $_POST;
	}

	public function getGetResponse($extract = false){

		if($extract)
			return explode(";",$_GET["q"]);

		return $_GET;
	}

}