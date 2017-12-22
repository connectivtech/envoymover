<?php

include "settings.php";
include "db_init.php";

logEvent('Envoy mover started');

//todo: query envoy API
$envoyDateFilters = '';
// $envoyDateFilters = "&from_date=2017-11-30&to_date=2017-12-01";
$successCounter = 0;
$failureCounter = 0;
$newVisitors    = 0;

$curl = curl_init($envoyUrl . $envoyDateFilters);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

//$curl_response = curl_exec($curl);
$curl_response = curl_exec($curl);

if (curl_error($curl)) {
	die('Curl error: ' . curl_error($curl));
}

// later we can see if 200 and log error
// $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
// echo $httpcode;

curl_close($curl);

//todo: parse into JSON
$decoded = json_decode($curl_response);
if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
 	echo logEvent($decoded->response->errormessage);
    die('error occured: ' . $decoded->response->errormessage);
}
// if this is greater than 25, we need to make the date range less as the envoy API only returns 25 max
echo 'response ok! returned: ' . count($decoded) ;

echo logEvent ("Visitors returned: " . count($decoded));



// for debugging, print array
// print_r($decoded);

//todo: iterate thru results
foreach($decoded as $i => $item) {
	echo ("\n");
	$visitor_id = $aws_mysqli->real_escape_string($decoded[$i]->{'id'});
	$boolVisitorExist = doesVisitorExist($visitor_id);

//todo: check if visitor ID already exists
	if ($boolVisitorExist === true) {
		// dont insert again if already exists, and we can goto next visitor
		echo ("Visitor ID exists: $visitor_id");
	} elseif ($boolVisitorExist === false) {
		// insert new visitor
		echo ("No visitor ID found for $visitor_id, lets insert it");
		$newVisitors++;
		echo "\n";
		// echo $decoded[$i]->{'id'} . "\n" ;
		echo $decoded[$i]->{'signed_in_time_utc'} . "\n" ;
		echo $decoded[$i]->{'signed_in_time_local'} . "\n" ;
		echo $decoded[$i]->{'your_full_name'} . "\n" ;
		echo $decoded[$i]->{'purpose_of_visit'} . "\n" ;
		echo $decoded[$i]->{'photo_url'} . "\n" ;
		
		$signed_in_utc = $aws_mysqli->real_escape_string($decoded[$i]->{'signed_in_time_utc'});
		$signed_in_local = $aws_mysqli->real_escape_string($decoded[$i]->{'signed_in_time_local'});
		$visitor_name = $aws_mysqli->real_escape_string($decoded[$i]->{'your_full_name'});
		$visitor_purpose = $aws_mysqli->real_escape_string($decoded[$i]->{'purpose_of_visit'});
		$email_address = '' ; // define and set later if used

		if ($decoded[$i]->{'purpose_of_visit'} == "I am a prospective member")
		{
			echo $decoded[$i]->{'who_will_you_be_meeting_for_a_tour?'} . "\n" ;
			echo $decoded[$i]->{'your_email_address'} . "\n" ;
			$member_visited = $aws_mysqli->real_escape_string($decoded[$i]->{'who_will_you_be_meeting_for_a_tour?'});
			$email_address = $aws_mysqli->real_escape_string($decoded[$i]->{'your_email_address'});
		} elseif ($decoded[$i]->{'purpose_of_visit'} == "I'm visiting a member") {
			echo $decoded[$i]->{'member_you\'re_visiting'} . "\n" ;
			$member_visited = $aws_mysqli->real_escape_string($decoded[$i]->{'member_you\'re_visiting'});
		} else {
			echo logEvent("Error: Purpose of visit not expected: " . $decoded[$i]->{'purpose_of_visit'} );
		}

		$photo_url = $aws_mysqli->real_escape_string($decoded[$i]->{'photo_url'});

		//todo: if not exists already, save to database
		insertVisitor($visitor_id, $signed_in_utc, $signed_in_local, $visitor_name, $visitor_purpose, $member_visited, $email_address, $photo_url);		
	} else {
		// broke
		echo logEvent("Error: Visitor check broke");
		die();
	}

} // end foreach

function doesVisitorExist ($visitor_id) {
	global $aws_mysqli, $dbVisitorTable;
	$visitor_id = $aws_mysqli->real_escape_string($visitor_id);
	$queryExist = "SELECT id_envoy FROM $dbVisitorTable WHERE id_envoy = $visitor_id";
	$resultExist = $aws_mysqli->query($queryExist);
	if(!$resultExist) {
		echo logEvent ("Error $aws_mysqli->error to check if visitor ID exists: $queryExist");
	} elseif($resultExist->num_rows == 0) {
		return false;
	  } else {
	  	return true;
	  }
} // end doesVisitorExist

function insertVisitor ($visitor_id, $signed_in_utc, $signed_in_local, $visitor_name, $visitor_purpose, $member_visited, $email_address, $photo_url) {
	global $aws_mysqli, $dbVisitorTable, $successCounter, $failureCounter;

	$insertFields = " id_envoy, signed_in_utc, signed_in_local, visitor_name, visitor_purpose, member_visited, email_address, photo_url ";

	$queryInsertVisitor = "INSERT INTO $dbVisitorTable ($insertFields) VALUES " . 
		" ( $visitor_id, '$signed_in_utc', '$signed_in_local', '$visitor_name', '$visitor_purpose', '$member_visited', '$email_address', '$photo_url' ); " ;

	echo $queryInsertVisitor ;

	$resultInsert = $aws_mysqli->query($queryInsertVisitor);
	if(!$resultInsert) {
		echo logEvent ("Error $aws_mysqli->error to insert visitor ID $visitor_id: $queryInsertVisitor");
		$failureCounter++;
		return false;
	  } else {
	  	echo("Insert success");
	  	$successCounter++;
	  	return true;
	  }
} // end insertVisitor

echo logEvent ("New visitors: $newVisitors");
echo logEvent ("Insert success: $successCounter");
echo logEvent ("Insert failure: $failureCounter");
echo logEvent ("Envoy finished");

?>
