<?php
	error_reporting(E_ALL);

	require_once '/home/borahv1q/dietisteborah.github.io/vendor/autoload.php';

	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/service_account.json');
	
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'authAPI':
				insert();
				break;
		}
	}	
	
	function authAPI(){
		$client = new Google_Client();
		$client->useApplicationDefaultCredentials();
		$client->setScopes(array(
			'https://www.googleapis.com/auth/calendar.readonly'
		));
		$service = new Google_Service_Calendar($client);
		// Print the next 10 events on the user's calendar.
		$calendarId = 'getchallengedapp@gmail.com';
		$optParams = array(
		  'maxResults' => 10,
		  'orderBy' => 'startTime',
		  'singleEvents' => true,
		  'timeMin' => date('c'),
		);
		$results = $service->events->listEvents($calendarId, $optParams);

		if (empty($results->getItems())) {
			print "No upcoming events found.\n";
		} else {
			print "Upcoming events:\n";
			foreach ($results->getItems() as $event) {
				$start = $event->start->dateTime;
				if (empty($start)) {
					$start = $event->start->date;
				}
				printf("%s (%s)\n", $event->getSummary(), $start);
			}
		}
	}
?>