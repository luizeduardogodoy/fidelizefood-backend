<?php

include("../vendor/adodb/adodb-php/adodb.inc.php");
include("../vendor/adodb/adodb-php/adodb-active-record.inc.php");

try{

	
}
catch(Exception $e){
	echo "Problema com o banco de dados!";
}

class ADOdbConnection {
	
	public static function getConn(){
		
		$db = ADOnewConnection("pdo");
		
		$user     = 'bcc21a07f6001f';
		$password = '83ec7971';
		$database = 'fidelizefood';
		$dsnString= "host=br-cdbr-azure-south-b.cloudapp.net;dbname=$database;charset=utf8mb4";

		 $db->connect('mysql:' . $dsnString,$user,$password);
		 
		 return $db;
	}
}


//$db->debug = true;



