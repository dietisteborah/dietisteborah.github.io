<?php
 ini_set('display_errors', 'On');
 error_reporting(E_ALL);
	require_once '../vendor/autoload.php';
	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/client_secret.json');
	
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'authAPI':
				authAPI();
				break;
		}
	}	

	function getClient()
	{
		$client = new Google_Client();
		$client->setApplicationName('Dietiste Borah');
		$client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
		$client->setAuthConfig('/home/borahv1q/public_html/client_secret.json');
		$client->setAccessType('offline');

		// Load previously authorized credentials from a file.
		$credentialsPath = '/home/borahv1q/public_html/credentials.json';
			$authUrl = $client->createAuthUrl();

			$authCode = "4/AADfMmnbu4mKSh9cXlIFqha9v-kThnv47Ty67ZO7cwA_JqZmFheBRb4";

			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

			// Store the credentials to disk.
			if (!file_exists(dirname($credentialsPath))) {
				mkdir(dirname($credentialsPath), 0700, true);
			}
			file_put_contents($credentialsPath, json_encode($accessToken));
			printf("Credentials saved to %s\n", $credentialsPath);
			
		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}
	
	function authAPI(){
		$client = getClient();
		$service = new Google_Service_Calendar($client);
		
		// Print the next 10 events on the user's calendar.
		//$calendarId = 'calendar-service-php@dietiste-calendar-site.iam.gserviceaccount.com';
		$calendarId = 'dietiste.borah@gmail.com';
		$optParams = array(
		  'maxResults' => 10,
		  'orderBy' => 'startTime',
		  'singleEvents' => true,
		  'timeMin' => date('c'),
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