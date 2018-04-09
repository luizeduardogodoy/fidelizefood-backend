<?php

include("../vendor/adodb/adodb-php/adodb.inc.php");
include("../vendor/adodb/adodb-php/adodb-active-record.inc.php");

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
			/*self::$instance = ADOnewConnection("pdo");
			
			$user     = 'bcc21a07f6001f';
			$password = '83ec7971';
			$database = 'fidelizefood';
			$dsnString= "host=br-cdbr-azure-south-b.cloudapp.net;dbname=$database;charset=utf8mb4";

			self::$instance->connect('mysql:' . $dsnString,$user,$password);*/
			
			self::$instance = ADONewConnection('postgres');
			//self::$instance->debug = true;			
			
			self::$instance->Connect("localhost", "postgres", "postgres", "fidelize");
			
		}
		
		return self::$instance;
	}
	
}


//$db->debug = true;



