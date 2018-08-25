<?php
	require_once '/home/borahv1q/vendor/autoload.php';
	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/borah-secrets/client_secret.json');

	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'getAvailable':
				getAvailable($_POST['date'],$_POST['opvolg']);
				break;
			case 'createAppointment':
				createAppointment($_POST['date'],$_POST['time'],$_POST['name'],$_POST['email'],$_POST['phone'],$_POST['remark'],$_POST['type'],$_POST['reminder']);
				break;
			case 'loadToday':
				loadToday($_POST['date']);
				break;
			case 'freeAppointment':
				freeAppointment($_POST['type']);
				break;
			case 'highlightfreedays':
				highlightfreedays($_POST['month_year'],$_POST['type']);
				}
	}

	function getClient()
	{
		$client = new Google_Client();
		$client->setApplicationName('Dietiste Borah');
		$client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
		$client->setAuthConfig('/home/borahv1q/borah-secrets/client_secret.json');
		$client->setAccessType('offline');

		// Load previously authorized credentials from a file.
		$credentialsPath = '/home/borahv1q/borah-secrets/credentials.json';
		if (file_exists($credentialsPath)) {
			$accessToken = json_decode(file_get_contents($credentialsPath), true);
		} else {
			printf("Er is een probleem met de kalender. \n Gelieve een mail te sturen naar dietiste.borah@gmail.com");
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."getClient - Issue with credentialspath.\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");	
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
		$appType = 0;
		//check appointment type
		if($opvolg=="opvolg"){
			$appType=1;
		}
		//Check to make sure the day is different from today (no appointments to be made on the same day)
		if($diffDays > 0){
			$notime = true;
			//Create database connection
			$string = file_get_contents("/home/borahv1q/borah-secrets/pw.txt");
			$string = str_replace(array("\r", "\n"), '', $string);
			$link = mysqli_connect("localhost", "borahv1q", $string , "borahv1q_Agenda");
			if (!$link) {
				echo "Error: Unable to connect to MySQL." . PHP_EOL;
				echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
				echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."Error: Unable to connect to MySQL." . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
				error_log($errordate."--"."Debugging errno: " . mysqli_connect_errno() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
				error_log($errordate."--"."Debugging error: " . mysqli_connect_error() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
				exit;
			}
			//echo "Connect to mysql.\n" . PHP_EOL;

			$sql = "SELECT * FROM afspraken where date =\"".$strdate."\" and opvolg =".$appType;
			$result = mysqli_query($link, $sql);
			if (mysqli_num_rows($result) > 0) {
				// output data of each row
				while($row = mysqli_fetch_assoc($result)) {
					printf("%s;", substr($row["startTime"], 0, 5));
				}
			} else {
				print "Geen afspraken beschikbaar op deze datum.\n";
			}
		}
		else{
			print "Geen afspraken beschikbaar op deze datum.\n";
		}
	}
	function loadToday($strdate){
		print "Geen tijdstippen vrij vandaag.\n";
	}
	function createAppointment($date,$time,$name,$email,$phone,$remark,$type,$reminder){
			$complete = true;
			$bericht = "";
			if($name == ""){
				$complete = false;
				$bericht = "Gelieve jouw naam in te vullen.\n";
			}
			if($email == ""){
				$complete = false;
				$bericht = $bericht . "Gelieve jouw e-mailadres in te vullen.\n";
			}
			else{
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
					$bericht = $bericht . "Gelieve een geldig e-mailadres in te vullen.\n";
					$complete = false;
				}
			}
			if($phone == ""){
				$complete = false;
				$bericht = $bericht . "Gelieve jouw telefoonnummer in te vullen.\n";
			}
			//time is checked in javascript code
			if($complete){
					//create appointment in calendar
					create_calendar_appointment($date,$time,$name,$email,$phone,$remark,$type);
					//send mail
					send_email($date,$time,$name,$email,$phone,$remark,$type);
					//remove options from calendar
					remove_database_records($date,$time,$type);
					//create field for reminder
					if($reminder=="true"){
						create_reminder($date,$time,$name,$email,$phone,$remark,$type);
					}
					$bericht = 
					"<div class=\"col-md-12 top-buffer brown_text\">"
						."<h1 class=\"font_Khula\" brown_text\" align=\"left\">Bedankt voor het maken van een afspraak op <a class=\"orange_text bold_text\">".date("d-m-Y",strtotime($date))."</a> om <a class=\"orange_text bold_text\">".$time."</a>.</h1>"
					."</div>"
					."<div>"
					.'<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2510.7758153433583!2d4.330278215220721!3d51.001814755364286!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c3e9c37b0e00f1%3A0x3a07463f3d43ce8b!2sDi%C3%ABtiste+%26+Diabeteseducator+Borah+Van+Doorslaer!5e0!3m2!1snl!2sbe!4v1530373189648" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>'
					."</div>";
			}
			print $bericht;
	}
	function create_calendar_appointment($date,$time,$name,$email,$phone,$remark,$type){
		$client = getClient();
		$service = new Google_Service_Calendar($client);
		$calendarId = 'dietiste.borah@gmail.com';

		if($type=="opvolg"){
			$endTime = strtotime($time) + (30*60);			
		}
		else{
			$endTime = strtotime($time) + (90*60);
		}
		$event = new Google_Service_Calendar_Event(array(
		  'summary' => $name . ' '. $type,
		  'description' => $name . ' - '.$remark.' - '.$email.' '.$phone.' '.$type,
		  'start' => array(
			'dateTime' => $date.'T'.$time.':00',
			'timeZone' => 'Europe/Brussels',
		  ),
		  'end' => array(
			'dateTime' => $date.'T'.date("H:i:s",$endTime),
			'timeZone' => 'Europe/Brussels',
		  ),
		));

		$calendarId = 'primary';
		$event = $service->events->insert($calendarId, $event);
	}
	function encodeRecipients($recipient){
		$recipientsCharset = 'utf-8';
		if (preg_match("/(.*)<(.*)>/", $recipient, $regs)) {
			$recipient = '=?' . $recipientsCharset . '?B?'.base64_encode($regs[1]).'?= <'.$regs[2].'>';
		}
		return $recipient;
	}
	function remove_database_records($appdate,$time,$type){
		//Create database connection
		$string = file_get_contents("/home/borahv1q/borah-secrets/pw.txt");
		$string = str_replace(array("\r", "\n"), '', $string);
		$link = mysqli_connect("localhost", "borahv1q", $string , "borahv1q_Agenda");
		if (!$link) {
			echo "Error: Unable to connect to MySQL." . PHP_EOL;
			echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."Error: Unable to connect to MySQL." . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."Debugging errno: " . mysqli_connect_errno() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."Debugging error: " . mysqli_connect_error() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			exit;
		}
		//echo "Connect to mysql.\n" . PHP_EOL;		
		if($type=="opvolg"){ //consultatie 30min
			//verwijder opvolg consultatie
			$sql = "DELETE FROM afspraken WHERE date = \"".$appdate."\" && opvolg = 1 && startTime = \"".date("H:i:s",strtotime($time))."\"";
			if (mysqli_query($link, $sql)) {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."Opvolg-opvolg-Record deleted successfully.\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."Opvolg-opvolg".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
			//verwijder startconsultatie
			$sql = "DELETE FROM afspraken WHERE date = \"".$appdate."\" && opvolg = 0 && startTime > \"".date("H:i:s",strtotime($time)-(90*60))."\" && startTime < \"".date("H:i:s",strtotime($time)+(30*60))."\""; 
			if (mysqli_query($link, $sql)) {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."Opvolg-start-Record deleted successfully.\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."Opvolg-start".mysqli_error($link)."SQL query: ".$sql."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
		}
		else{
			//verwijder opvolg consultatie
			$sql = "DELETE FROM afspraken WHERE date = \"".$appdate."\" && opvolg = 1 && startTime >= \"".date("H:i:s",strtotime($time))."\" && startTime < \"".date("H:i:s",strtotime($time)+(90*60))."\"";
			if (mysqli_query($link, $sql)) {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."else-opvolg-Record deleted successfully.\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."else-opvolg".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
			//verwijder startconsultatie
			$sql = "DELETE FROM afspraken WHERE date = \"".$appdate."\" && opvolg = 0 && startTime > \"".date("H:i:s",strtotime($time)-(90*60))."\" && startTime < \"".date("H:i:s",strtotime($time)+(90*60))."\"";
			if (mysqli_query($link, $sql)) {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."else-start-Record deleted successfully\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."else-start".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
		}		
	}
	function freeAppointment($type) {
		//Create database connection
		$string = file_get_contents("/home/borahv1q/borah-secrets/pw.txt");
		$string = str_replace(array("\r", "\n"), '', $string);
		$link = mysqli_connect("localhost", "borahv1q", $string , "borahv1q_Agenda");
		if (!$link) {
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."Error: Unable to connect to MySQL." . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."Debugging errno: " . mysqli_connect_errno() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."Debugging error: " . mysqli_connect_error() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			exit;
		}
		//echo "Connect to mysql.\n" . PHP_EOL;	
		
		$today = new DateTime(); // This object represents current date/time

		if($type=="opvolg"){
			//find the first day with an "opvolg" appointment free
			$sql = "SELECT date FROM afspraken WHERE date > \"".$today->format('Y-m-d')."\" && opvolg = 1 LIMIT 1";
			$result = mysqli_query($link, $sql);
			if (mysqli_num_rows($result) == 1) {
				$row = mysqli_fetch_assoc($result);
				echo $row["date"];
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."freeAppointment-opvolg".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
		}
		else{
			//find the first day with a "start" appointment free
			$sql = "SELECT date FROM afspraken WHERE date > \"".$today->format('Y-m-d')."\" && opvolg = 0 LIMIT 1";
			$result = mysqli_query($link, $sql);
			if (mysqli_num_rows($result) == 1) {
				$row = mysqli_fetch_assoc($result);
				echo $row["date"];
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."freeAppointment-else".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
		}			
	}
	function send_email($date,$time,$name,$email,$phone,$remark,$type){
		$client = new Google_Client();
		$client->setApplicationName('Gmail API PHP Quickstart');
		// All Permissions
		$client->addScope("https://mail.google.com/");
		$client->setAuthConfig('/home/borahv1q/borah-secrets/client_secret_gmail.json');
		$client->setAccessType('offline');

		// Load previously authorized credentials from a file.
		$credentialsPath = '/home/borahv1q/borah-secrets/credentials_gmail.json';
		if (file_exists($credentialsPath)) {
			$accessToken = json_decode(file_get_contents($credentialsPath), true);
		} else {
			printf("Er is een probleem met de mailing functionaliteit. \n Gelieve een mail te sturen naar dietiste.borah@gmail.com");
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."mail-issue in send_email.\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
		}
		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
		}

		$service = new Google_Service_Gmail($client);

		if($type=="opvolg"){
			$strMailContent = 'Beste '. $name .',<br/><br/>hierbij bevestig ik jouw opvolgconsultatie op '.date("d-m-Y",strtotime($date)). ' om '.$time. '.<br/><br/>Volgende opmerkingen werden toegevoegd:<br/>'.$remark.'<br/><br/>Gelieve een seintje te geven indien je niet aanwezig kan zijn op deze afspraak.<br/><br/><br/>Met vriendelijke groeten,<br/><br/>Borah Van Doorslaer<br/>+32 485 36 04 09<br/>Stuiverstraat 17/1, 1840 Londerzeel';
		}
		else{
			$strMailContent = 'Beste '. $name .',<br/><br/>hierbij bevestig ik jouw startconsultatie op '.date("d-m-Y",strtotime($date)). ' om '.$time. '.<br/><br/>Volgende opmerkingen werden toegevoegd:<br/>'.$remark.'<br/><br/>Gelieve een seintje te geven indien je niet aanwezig kan zijn op deze afspraak.<br/><br/><br/>Met vriendelijke groeten,<br/><br/>Borah Van Doorslaer<br/>+32 485 36 04 09<br/>Stuiverstraat 17/1, 1840 Londerzeel';
		}
		$strMailTextVersion = strip_tags($strMailContent, '');

		$strRawMessage = "";
		$boundary = uniqid(rand(), true);
		$subjectCharset = $charset = 'utf-8';
		$strToMailName = $name;
		$strToMail = $email;
		$strToMailNameBcc = 'Diëtiste Borah';
		$strToMailBcc = 'dietiste.borah@gmail.com';
		$strSesFromName = 'Diëtiste Borah';
		$strSesFromEmail = 'dietiste.borah@gmail.com';
		$strSubject = 'Afspraak Dïetiste Borah op '. date("d-m-Y",strtotime($date)) .' om '. $time;

		$strRawMessage .= 'To: ' . encodeRecipients($strToMailName . " <" . $strToMail . ">") . "\r\n";
		$strRawMessage .= 'Bcc: '. encodeRecipients($strToMailNameBcc . " <" . $strToMailBcc . ">") . "\r\n";
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

			//print('Hartelijk dank voor het maken van een afspraak op '.$date.' om '.$time);

		} catch (Exception $e) {
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."mail-issue:".$e->getMessage()."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
		}

	}
	function create_reminder($date,$time,$name,$email,$phone,$remark,$type){
		/*
		 * creat the mail content
		 */
		if($type=="opvolg"){
			$strMailContent = 'Beste '. $name .',<br/><br/>Deze e-mail wordt u automatisch toegestuurd ter herinnering aan jouw opvolgconsultatie op '.date("d-m-Y",strtotime($date)). ' om '.$time. '.<br/><br/>Gelieve een seintje te geven indien het niet mogelijk is om op deze afspraak aanwezig te zijn.<br/><br/><br/>Met vriendelijke groeten,<br/><br/>Borah Van Doorslaer<br/><br/>+32 485 36 04 09<br/>Stuiverstraat 17/1, 1840 Londerzeel';
		}
		else{
			$strMailContent = 'Beste '. $name .',<br/><br/>Deze e-mail wordt u automatisch toegestuurd ter herinnering aan jouw startconsultatie op '.date("d-m-Y",strtotime($date)). ' om '.$time. '.<br/><br/>Gelieve een seintje te geven indien het niet mogelijk is om op deze afspraak aanwezig te zijn.<br/><br/><br/>Met vriendelijke groeten,<br/><br/>Borah Van Doorslaer<br/><br/>+32 485 36 04 09<br/>Stuiverstraat 17/1, 1840 Londerzeel';
		}
		$strMailTextVersion = strip_tags($strMailContent, '');

		$strRawMessage = "";
		$boundary = uniqid(rand(), true);
		$subjectCharset = $charset = 'utf-8';
		$strToMailName = $name;
		$strToMail = $email;
		$strSesFromName = 'Diëtiste Borah';
		$strSesFromEmail = 'dietiste.borah@gmail.com';
		$strSubject = 'Herrinering afspraak Dïetiste Borah op '. date("d-m-Y",strtotime($date)) .' om '. $time;

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

		/*
		 * insert the records into the database using mysqli
		 */
		$string = file_get_contents("/home/borahv1q/borah-secrets/pw.txt");
		$string = str_replace(array("\r", "\n"), '', $string);
		$link = mysqli_connect("localhost", "borahv1q", $string , "borahv1q_Agenda");
		if (!$link) {
			echo "Error: Unable to connect to MySQL." . PHP_EOL;
			echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."createOpvolg - Error: Unable to connect to MySQL." . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."createOpvolg - Debugging errno: " . mysqli_connect_errno() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."createOpvolg - Debugging error: " . mysqli_connect_error() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			exit;
		}
		$reminder_date = new DateTime($date);
		$reminder_date->modify('-2 days');
		$reminder_date_string = date_format($reminder_date, 'Y-m-d');
		$errordate = date('d.m.Y h:i:s'); 
		error_log($errordate."--"."reminder_date is ".$reminder_date_string." \n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");

		$sql = "INSERT INTO reminders (reminder_date, body)	VALUES ('".$reminder_date_string."','".$strRawMessage."')";
		if (mysqli_query($link, $sql)) {
			echo "_OK_";
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($link);
		}		
	}
	function highlightfreedays($month_year,$type) {
		$date = explode(" ", $month_year);
		$month = getMonthNumber($date[0]);
		$year = $date[1];

		//Create database connection
		$string = file_get_contents("/home/borahv1q/borah-secrets/pw.txt");
		$string = str_replace(array("\r", "\n"), '', $string);
		$link = mysqli_connect("localhost", "borahv1q", $string , "borahv1q_Agenda");
		if (!$link) {
			$errordate = date('d.m.Y h:i:s'); 
			error_log($errordate."--"."Error: Unable to connect to MySQL." . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."Debugging errno: " . mysqli_connect_errno() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			error_log($errordate."--"."Debugging error: " . mysqli_connect_error() . PHP_EOL ."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			exit;
		}
		//echo "Connect to mysql.\n" . PHP_EOL;	
		
		$today = new DateTime(); // This object represents current date/time

		if($type=="opvolg"){
			//find the first day with an "opvolg" appointment free
			$sql = "SELECT DISTINCT date FROM afspraken WHERE date like \"".$year."-".$month."-%\" && date > \"".$today->format('Y-m-d')."\" && opvolg = 1";
			$result = mysqli_query($link, $sql);
			if (mysqli_num_rows($result) > 0) {
				$resultdates = "";
				while( $row = mysqli_fetch_assoc( $result)){
					$resultdates = $resultdates.$row["date"].","; // Inside while loop
				}
				echo $resultdates;
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."highlightfreedays-opvolg".$sql."  ".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
		}
		else{
			//find the first day with a "start" appointment free
			$sql = "SELECT DISTINCT date FROM afspraken WHERE date like \"".$year."-".$month."-%\" && date > \"".$today->format('Y-m-d')."\" && opvolg = 0";
			$result = mysqli_query($link, $sql);
			if (mysqli_num_rows($result) > 0) {
				$resultdates = "";
				while( $row = mysqli_fetch_assoc( $result)){
					$resultdates = $resultdates.$row["date"].","; // Inside while loop
				}
				echo $resultdates;
			} else {
				$errordate = date('d.m.Y h:i:s'); 
				error_log($errordate."--"."highlightfreedays-else".$sql."  ".mysqli_error($link)."\n", 3, "/home/borahv1q/logs/php-afspraken-backend.log");
			}
		}			
	}
	function getMonthNumber($monthstr){
		$month="";
		//get number of month
		switch ($monthstr){
			case "Januari":
				$month="01";
				break;
			case "Februari":
				$month="02";
				break;
			case "Maart":
				$month="03";
				break;
			case "April":
				$month="04";
				break;
			case "Mei":
				$month="05";
				break;
			case "Juni":
				$month="06";
				break;
			case "Juli":
				$month="07";
				break;
			case "Augustus":
				$month="08";
				break;
			case "September":
				$month="09";
				break;
			case "Oktober":
				$month="10";
				break;
			case "November":
				$month="11";
				break;
			case "December":
				$month="12";
				break;
			default:
				break;		
		}
		return $month;
	}
?>
