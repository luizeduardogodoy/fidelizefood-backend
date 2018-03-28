<?php

session_start();

$expiry = 6000;//session expiry required after 1 min

if(isset($_SESSION['LAST']) && (time() - $_SESSION['LAST'] > $expiry)) {
	session_unset();
	session_destroy();
  
	print json_encode(["status" => "exp"]);
	
	exit;
}

$_SESSION['LAST'] = time();
date_default_timezone_set('America/Sao_Paulo');