<?php
 ini_set('display_errors', 'On');
 error_reporting(E_ALL);
	require_once '../vendor/autoload.php';
	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/client_secret.json');
	
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'getAvailable':
				getAvailable($_POST['date']);
				break;
			case 'loadToday':
				loadToday($_POST['date']);
				break;
				}
	}	

	function getClient()
	{
		$client = new Google_Client();
		$client->setApplicationName('Dietiste Borah');
		$client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
		$client->setAuthConfig('client_secret.json');
		$client->setAccessType('offline');

		// Load previously authorized credentials from a file.
		$credentialsPath = '/home/borahv1q/public_html/credentials.json';
		if (file_exists($credentialsPath)) {
			$accessToken = json_decode(file_get_contents($credentialsPath), true);
		} else {
			printf("Er is een probleem met de kalendar. \n Gelieve een mail te sturen naar dietiste.borah@gmail.com");
		}
		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}
	
	function getAvailable($strdate){
		$today = new DateTime(); // This object represents current date/time
		$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison
		$match_date = DateTime::createFromFormat( "Y-m-d", $strdate );
		$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison
		$diff = $today->diff( $match_date );
		$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

		if($diffDays > 0){
			$open = false;
			$startOpen = "";
			$endOpen = "";
			$timeslots = array();
			$client = getClient();
			$service = new Google_Service_Calendar($client);
			$calendarId = 'dietiste.borah@gmail.com';
			
			//time max -> selected day + 1
			$nextdate = new DateTime($strdate);
			$nextdate->add(new DateInterval('P1D'));
						
			$optParams = array(
			  'orderBy' => 'startTime',
			  'singleEvents' => true,
			  'timeMax' => $nextdate->format('Y-m-d') . 'T00:00:00Z',
			  'timeMin' => $strdate . 'T00:00:00Z',
			);
			$results = $service->events->listEvents($calendarId, $optParams);
			if (!($results->getItems())) {
				print "No upcoming events found.\n";
			} else 
			{
				foreach ($results->getItems() as $event) {
					if($event->getSummary() == "Open"){
						$open=true;
						$startOpen = $event->start->dateTime;
						$endOpen = $event->getEnd()->dateTime;
						break;
					}
				}
				if($open){
					//Query the events during the opening times
					$optParams = array(
					  'orderBy' => 'startTime',
					  'singleEvents' => true,
					  'timeMax' => $endOpen,
					  'timeMin' => $startOpen,
					);
					$results = $service->events->listEvents($calendarId, $optParams);
					
					$endOpen=substr($endOpen, 11, 5);
					$previousEndTime = $endOpen;
					foreach ($results->getItems() as $event) {
						if(!($event->getSummary() == "Open")){
							//Check begintijd met eind tijd vorige afspraak. Daarna "eindtijd" op eigen eindtijd zetten. 
							//Op basis daarvan vrije momenten toevoegen aan de lijst met vrije uren (aantal minuten delen door 30 of 90)
							$startDateTime = $event->start->dateTime;
							$start = substr($startDateTime, 11, 5);
							$end = $event->getEnd()->dateTime;
							if(strtotime($start) > strtotime($previousEndTime)){
								$timeDifferenceInMinutes = (strtotime($start) - strtotime($previousEndTime))/60;
								printf("%s (%s) \n", $event->getSummary(), $timeDifferenceInMinutes);
							}
						}
						else{
							printf("%s \n", $event->getSummary());
						}
					}
					//$endHour=substr($endOpen, 11, 5);
					//if ($currentTime > strtotime('16:00:00')) {
					
					/*for($startHour=substr($startOpen, 11, 5);strtotime($startHour)<$endHour;$startHour = date("H:i", strtotime('+30 minutes', $startHour))){
						
					}*/
					
					/*
						eerst start uren nemen van de "open" taak,
						daarna checken of de taak de "open" taak is, "open" taak negeren en verder gaan
					*/
					/*$length = count($results) - 1;
					for($i = 0; $i < $length; ++$i) {
						if(current($results)->getSummary() == "Open"){
							$stringStartOpen = substr(current($results)->start->dateTime, 11, 5);
							$stringStartFirst = 
							;	
						}
						else {
						
						}
						/*if (current($results) === next($results)) {
							// they match
						}*/
				}
				else{
					print "Geen tijdstippen vrij op deze datum.\n";
				}
			}
		}
		else{
			print "Geen tijdstippen vrij op deze datum.\n";
		}
	}
	function loadToday($strdate){
		print "Geen tijdstippen vrij vandaag.\n";
	}
?>