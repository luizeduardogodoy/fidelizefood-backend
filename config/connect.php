<?php

include("../vendor/adodb/adodb-php/adodb.inc.php");
include("../vendor/adodb/adodb-php/adodb-active-record.inc.php");

try{

	$driver = "pdo";
	$db = ADOnewConnection($driver);
	
	$user     = 'bcc21a07f6001f';
	$password = '83ec7971';
	$database = 'fidelizefood';
	$dsnString= "host=br-cdbr-azure-south-b.cloudapp.net;dbname=$database;charset=utf8mb4";

	$db->connect('mysql:' . $dsnString,$user,$password);
}
catch(Exception $e){
	echo "Problema com o banco de dados!";
}

//$db->debug = true;

//para funcionar o ORM tem que setar o objeto $db aqui neste método
ADOdb_Active_Record::SetDatabaseAdapter($db);