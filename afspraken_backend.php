<?php
 ini_set('display_errors', 'On');
 error_reporting(E_ALL);
	require_once '../vendor/autoload.php';
	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/client_secret.json');
	
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'getAvailable':
				getAvailable($_POST['date'],$_POST['opvolg']);
				break;
			case 'createAppointment':
				createAppointment($_POST['date'],$_POST['time'],$_POST['name'],$_POST['email'],$_POST['phone'],$_POST['remark']);
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
			printf("Er is een probleem met de kalender. \n Gelieve een mail te sturen naar dietiste.borah@gmail.com");
		}
		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}
	
	function getAvailable($strdate,$opvolg){
		$today = new DateTime(); // This object represents current date/time
		$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison
		$match_date = DateTime::createFromFormat( "Y-m-d", $strdate );
		$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison
		$diff = $today->diff( $match_date );
		$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval
		$appType = false;
		//check appointment type
		if($opvolg=="opvolg"){
			$appType=true;
		}
		if($diffDays > 0){
			$open = false;
			$startOpen = "";
			$endOpen = "";
			$timeslots = array();
			$notime = true;
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
				print "Geen tijdstippen vrij op deze datum.\n";
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
					
					$startOpen=substr($startOpen, 11, 5);
					$previousEndTime = $startOpen; //First time, difference between Open "openingtime" and first appointment has to be found
					$opvolg=false;
					foreach ($results->getItems() as $event) {
						if(!($event->getSummary() == "Open")){
							//Check begintijd met eind tijd vorige afspraak. Daarna "eindtijd" op eigen eindtijd zetten. 
							//Op basis daarvan vrije momenten toevoegen aan de lijst met vrije uren (aantal minuten delen door 30 of 90)
							$startDateTime = $event->start->dateTime;
							$start = substr($startDateTime, 11, 5);						
							if(strtotime($start) > strtotime($previousEndTime)){
								$timeDifferenceInMinutes = (strtotime($start) - strtotime($previousEndTime))/60;
								if($opvolg && ($timeDifferenceInMinutes/30) >= 1){ //afspraak 30 min
									$noTime = false;
									$amountOfAppointments = $timeDifferenceInMinutes/30;
									for($i=0;$i<$amountOfAppointments;$i++){
										$add = 30 + (30*$i);
										$newStartTime = strtotime($previousEndTime) + (30*60*$i); 
										printf("%s;", date("H:i",$newStartTime));
									}
								}
								elseif((!$opvolg && ($timeDifferenceInMinutes/90) >= 1)){ //afspraak van 90 min
									$noTime = false;
									$amountOfAppointments = $timeDifferenceInMinutes/30; //elke 30 min een afspraak
									for($i=0;$i<($amountOfAppointments-2);$i++){
										$add = 30 + (30*$i);
										$newStartTime = strtotime($previousEndTime) + (30*60*$i); 
										printf("%s;", date("H:i",$newStartTime));
									}
								}
							}
							else{
								//Do nothing, no time left
							}
							$previousEndTime = substr($event->getEnd()->dateTime,11,5);
						}
					}
					//do the check for the last appointment & closing time
					$endOpen=substr($endOpen, 11, 5);
					if(strtotime($endOpen) > strtotime($previousEndTime)){
						$timeDifferenceInMinutes = (strtotime($endOpen) - strtotime($previousEndTime))/60;
						if($opvolg && ($timeDifferenceInMinutes/30) >= 1){ //afspraak 30 min
							$noTime = false;
							$amountOfAppointments = $timeDifferenceInMinutes/30;
							for($i=0;$i<$amountOfAppointments;$i++){
								$add = 30 + (30*$i);
								$newStartTime = strtotime($previousEndTime) + (30*60*$i); 
								printf("%s;", date("H:i",$newStartTime));
							}
						}
						elseif((!$opvolg && ($timeDifferenceInMinutes/90) >= 1)){ //afspraak van 90 min
							$noTime = false;
							$amountOfAppointments = $timeDifferenceInMinutes/30; //elke 30 min een afspraak
							for($i=0;$i<($amountOfAppointments-2);$i++){
								$add = 30 + (30*$i);
								$newStartTime = strtotime($previousEndTime) + (30*60*$i); 
								printf("%s;", date("H:i",$newStartTime));
							}
						}
					}		
					if($noTime){
						print "Geen tijdstippen vrij op deze datum.\n";
					}
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
	function createAppointment($date,$time,$name,$email,$phone,$remark){
			$complete = true;
			$bericht = "";
			if($name == ""){
				$complete = false;
				$bericht = "Gelieve jouw naam in te vullen.\n";
			} 
			if($email == ""){
				$complete = false;
				$bericht = $bericht + "Gelieve jouw e-mailadres in te vullen.\n";
			}
			else{
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
					$bericht = $bericht + "Gelieve een geldig e-mailadres in te vullen.\n";
					$complete = false;
				}
			}
			if($phone == ""){
				$complete = false;
				$bericht = $bericht + "Gelieve jouw telefoonnummer in te vullen.\n";
			}
			/*if($time == ""){
				$bericht = $bericht + "Gelieve een tijdstip te kiezen.";
				$complete = false;
			}*/
			
			if($complete){
				//send mail
				send_email();
			}
			print $bericht;
	}
	function loadToday($strdate){
		print "Geen tijdstippen vrij vandaag.\n";
	}
	function encodeRecipients($recipient){
		$recipientsCharset = 'utf-8';
		if (preg_match("/(.*)<(.*)>/", $recipient, $regs)) {
			$recipient = '=?' . $recipientsCharset . '?B?'.base64_encode($regs[1]).'?= <'.$regs[2].'>';
		}
		return $recipient;
	}
	function send_email(){
		$client = new Google_Client();
		$client->setApplicationName('Gmail API PHP Quickstart');
		// Permissions
		$client->addScope("https://mail.google.com/");
		
		$client->setAuthConfig('client_secret_gmail.json');
		$client->setAccessType('offline');
		
		// Load previously authorized credentials from a file.
		$credentialsPath = '/home/borahv1q/public_html/credentials_gmail.json';
		if (file_exists($credentialsPath)) {
			$accessToken = json_decode(file_get_contents($credentialsPath), true);
		} else {
			printf("Er is een probleem met de mailing functionaliteit. \n Gelieve een mail te sturen naar dietiste.borah@gmail.com");
		}
		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
		}
		
		$service = new Google_Service_Gmail($client);

		$strMailContent = 'This is a test mail which is sent via using Gmail API client library.<br/><br/><br/>Thanks,<br/>GMail API Team.';
		$strMailTextVersion = strip_tags($strMailContent, '');

		$strRawMessage = "";
		$boundary = uniqid(rand(), true);
		$subjectCharset = $charset = 'utf-8';
		$strToMailName = 'Dries Goossens';
		$strToMail = 'driesgoossens93@gmail.com';
		$strSesFromName = 'Diëtiste Borah';
		$strSesFromEmail = 'dietiste.borah@gmail.com';
		$strSubject = 'Test mail using GMail API -';

		$strRawMessage .= 'To: ' . encodeRecipients($strToMailName . " <" . $strToMail . ">") . "\r\n";
		$strRawMessage .= 'From: '. encodeRecipients($strSesFromName . " <" . $strSesFromEmail . ">") . "\r\n";

		$strRawMessage .= 'Subject: =?' . $subjectCharset . '?B?' . base64_encode($strSubject) . "?=\r\n";
		$strRawMessage .= 'MIME-Version: 1.0' . "\r\n";
		$strRawMessage .= 'Content-type: Multipart/Alternative; boundary="' . $boundary . '"' . "\r\n";
 
 
		$strRawMessage .= "\r\n--{$boundary}\r\n";
		$strRawMessage .= 'Content-Type: text/plain; charset=' . $charset . "\r\n";
		$strRawMessage .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
		$strRawMessage .= $strMailTextVersion . "\r\n";

		$strRawMessage .= "--{$boundary}\r\n";
		$strRawMessage .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
		$strRawMessage .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
		$strRawMessage .= $strMailContent . "\r\n";
		
		//Send Mails
		//Prepare the message in message/rfc822
		try {
			// The message needs to be encoded in Base64URL
			$mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
			$msg = new Google_Service_Gmail_Message();
			$msg->setRaw($mime);
			$objSentMsg = $service->users_messages->send("me", $msg);

			print('Message sent object');
			print($objSentMsg);

		} catch (Exception $e) {
			print($e->getMessage());
		}
		
	}
?>