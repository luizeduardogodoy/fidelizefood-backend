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
			self::$instance = ADOnewConnection("pdo");
			
			//$user     = 'bcc21a07f6001f';
			//$password = '83ec7971';
			//$database = 'fidelizefood';
			//$dsnString= "host=br-cdbr-azure-south-b.cloudapp.net;dbname=$database;charset=utf8mb4";

			$user     = 'root';
			$password = '1234';
			$database = 'fidelize';
			$dsnString= "host=localhost:3306;dbname=$database;charset=utf8mb4"; 

			self::$instance->connect('mysql:' . $dsnString,$user,$password);
			
			
			//self::$instance = ADONewConnection('mysql');
			//self::$instance->debug = true;			
			
			//self::$instance->Connect("localhost", "root", "1234", "fidelize");
			
		}
		
		return self::$instance;
	}
	
}


//$db->debug = true;



