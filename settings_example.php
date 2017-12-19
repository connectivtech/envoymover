<?php

include "envoyfunctions.php";

$environment = 'dev';
$logPath	= '/dev/null' ; 

$envoyUrl =	"https://app.envoy.com/api/entries.json?api_key=xxx";


$dbhost     = "compute-1.amazonaws.com" ;
$dbuser     = "username" ;
$dbpassword = "secrets" ;
$dbdatabase = "dbname" ;
$dbVisitorTable = "envoyvisitors";


// AWS connection - diff from forum as we want to SSL this traffic
$aws_mysqli = mysqli_init();
if (!$aws_mysqli) {
    exit('mysqli_init failed');
}

//if (!$aws_mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
//    die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
//}

// set SSL using AWS CA -- hostname is too long, so using ec2 one fails SSL check
// $aws_mysqli->ssl_set(null,null,'rds-combined-ca-bundle.pem',null,null);

if (!$aws_mysqli->real_connect($dbhost, $dbuser, $dbpassword, $dbdatabase)) {
  echo logEvent("Failed to connect to AWS MySQL $dbhost/$dbdatabase: " . mysqli_connect_error());
	exit("Failed to connect to AWS MySQL $dbhost/$dbdatabase: " . mysqli_connect_error());
} else {
		echo ("Connected to AWS database: $dbhost/$dbdatabase \n" ) ; 
}

// ignoring SSL for now
// $res = $aws_mysqli->query("SHOW STATUS LIKE 'Ssl_cipher';");
// while($row = $res->fetch_array()) {
// 	$sslCipher = $row['Value'];
// 	$sslExpected = 'AES256-SHA';
// 	if($sslCipher != $sslExpected) { 
// 		echo logEvent("Error. SSL cipher incorrect or missing, expected $sslExpected, got: $sslCipher. Exiting."); 
// 		exit();
// 	} else {
// 		echo("Using SSL: $sslCipher");
// 		newLine();
// 	}
// }
// end new AWS conn


?>
