<?php
 ini_set('display_errors', 'On');
 error_reporting(E_ALL);
	require_once '/home/borahv1q/etc/google-api-php-client/src/Google/autoload.php';
	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/service_account.json');
	
	header('Content-Type: application/json');

    $aResult = array();
	
	if( !isset($_POST['functionname']) ) { $aResult['error'] = 'No function name!'; }
	
	if( !isset($aResult['error']) ) {
        switch($_POST['functionname']) {
            case 'action':
				$client = new Google_Client();
				$client->useApplicationDefaultCredentials();
				$client->setScopes(array(
					'https://www.googleapis.com/auth/calendar.readonly'
				));
				$service = new Google_Service_Calendar($client);
				// Print the next 10 events on the user's calendar.
				$calendarId = 'dietiste.borah@gmail.com';
				$optParams = array(
				  'maxResults' => 10,
				  'orderBy' => 'startTime',
				  'singleEvents' => true,
				  'timeMin' => date('c'),
				);
				$results = $service->events->listEvents($calendarId, $optParams);
				if (empty($results->getItems())) {
					print "No upcoming events found.\n";
					$aResult['result'] = "No upcoming events found.\n";
				} else {
					print "Upcoming events:\n";
					foreach ($results->getItems() as $event) {
						$start = $event->start->dateTime;
						if (empty($start)) {
							$start = $event->start->date;
						}
						$aResult['result'] = $event->getSummary();
						printf("%s (%s)\n", $event->getSummary(), $start);
					}
				}
                
               break;

            default:
               $aResult['error'] = 'Not found function '.$_POST['functionname'].'!';
               break;
        }
    }
	echo json_encode($aResult);
	
	function authAPI(){
		$client = new Google_Client();
		$client->useApplicationDefaultCredentials();
		$client->setScopes(array(
			'https://www.googleapis.com/auth/calendar.readonly'
		));
		$service = new Google_Service_Calendar($client);
		// Print the next 10 events on the user's calendar.
		$calendarId = 'dietiste.borah@gmail.com';
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