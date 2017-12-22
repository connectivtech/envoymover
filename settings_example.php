<?php

include "envoyfunctions.php";

$environment = 'dev';
$logType	= '[envoy]';
$logPath	= 'c:\devroot\envoy.log' ; 


$envoyUrl =	"https://app.envoy.com/api/entries.json?api_key=xxx";


$dbhost     = "compute-1.amazonaws.com" ;
$dbuser     = "username" ;
$dbpassword = "secrets" ;
$dbdatabase = "dbname" ;
$dbVisitorTable = "envoyvisitors";

?>

