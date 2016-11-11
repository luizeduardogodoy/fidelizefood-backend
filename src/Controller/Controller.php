<?php

namespace FidelizeFood\Controller;

abstract class Controller{

	public function __construct(){

		//echo "its work!";
	}

	public function getPostResponse($key = ""){
  
		return $key != "" ? $_POST[$key] : $_POST;
	}

	public function getGetResponse($extract = false){

		if($extract)
			return explode(";",$_GET["q"]);

		return $_GET;
	}

}