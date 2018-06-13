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
		$client = getClient();
		$service = new Google_Service_Calendar($client);
		// Print the next 10 events on the user's calendar.
		//$calendarId = 'calendar-service-php@dietiste-calendar-site.iam.gserviceaccount.com';
		$calendarId = 'dietiste.borah@gmail.com';
		
		//time max
		//$strdate = date_create_from_format('YYYY-MM-DD', $strdate);
		//$strdate->add(new DateInterval('P1D'));

		//$date = strtotime($strdate);
		//$realdate = date('YYYY-MM-DD', $date)->add(new DateInterval('P1D'));
		$nextdate = new DateTime($strdate);
		$nextdate->add(new DateInterval('P1D'));
		//echo $nextdate->format('Y-m-d') . "\n" . $strdate;
				

		
		$optParams = array(
		  'maxResults' => 10,
		  'orderBy' => 'startTime',
		  'singleEvents' => true,
		  'timeMax' => $nextdate->format('Y-m-d') . 'T00:00:00Z',
		  'timeMin' => $strdate . 'T00:00:00Z',
		);
		$results = $service->events->listEvents($calendarId, $optParams);
		if (!($results->getItems())) {
			print "No upcoming events found.\n";
		} else {
			print "Upcoming events:\n";
			foreach ($results->getItems() as $event) {
				$start = $event->start->dateTime;
				if (!($start)) {
					$start = $event->start->date;
				}
				printf("%s (%s)\n", $event->getSummary(), $start);
			}
		}
		
	}
?>