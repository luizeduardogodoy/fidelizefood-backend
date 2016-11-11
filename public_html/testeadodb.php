<?php
error_reporting(E_ALL);

include "../../vendor/adodb/adodb-php/adodb.inc.php";

$driver = "pdo";

$db = ADOnewConnection('pdo');

$server = "localhost";
$user = "root";
$pass = "";
$database = "cloud";

$user     = 'root';
$password = '';
$dsnString= "host=localhost;dbname=$database;charset=utf8mb4";

$db->connect('mysql:' . $dsnString,$user,$password);
$db->debug = true;

$query = "SELECT * FROM registration_tbl";
$res = $db->Execute($query);

while(!$res->EOF){

  echo $res->fields("name");
  echo "<br />";

  $res->MoveNext();
}