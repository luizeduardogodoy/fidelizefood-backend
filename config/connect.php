<?php

$base =  DIRNAME(__FILE__);

$base2 = DIRNAME($base) . "";

include($base2 . "/vendor/adodb/adodb-php/adodb.inc.php");
include($base2 . "/vendor/adodb/adodb-php/adodb-active-record.inc.php");

try{

	
}
catch(Exception $e){
	echo "Problema com o banco de dados!";
}

class ADOdbConnection {
	
	protected static $instance;
	
	protected function __construct(){
		
	}
	
	public static function getConn(){
		
		if(self::$instance == null){
			self::$instance = ADOnewConnection("pdo");
			
			//$user     = 'bcc21a07f6001f';
			//$password = '83ec7971';
			//$database = 'fidelizefood';
			//$dsnString= "host=br-cdbr-azure-south-b.cloudapp.net;dbname=$database;charset=utf8mb4";

			$user     = 'fidelize';
			$password = 'FiDeLLiZEEee!8';
			$database = 'fidelize';
			$dsnString= "host=fidelize.mysql.dbaas.com.br;dbname=$database;"; 
			//$dsnString= "host=179.188.16.208:3306;dbname=$database;"; 

			self::$instance->connect('mysql:' . $dsnString,$user,$password);
			
			
			//self::$instance = ADONewConnection('mysql');
			//self::$instance->debug = true;			
			
			//self::$instance->Connect("localhost", "root", "1234", "fidelize");
			
		}
		
		return self::$instance;
	}
	
}


//$db->debug = true;



