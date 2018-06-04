<?php
require_once '/home/borahv1q/etc/google-api-php-client/src/Google/autoload.php';

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'insert':
            insert();
            break;
        case 'select':
            select();
            break;
    }
}
function select() {
    echo "The select function is called.";
    exit;
}
function insert() {
    echo "The insert function is called.";
	
	putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/service_account.json');

	$client = new Google_Client();
	$client->useApplicationDefaultCredentials();

	$sqladmin = new Google_Service_SQLAdmin($client);
	$response = $sqladmin->instances
		->listInstances('challenger')->getItems();
	echo json_encode($response) . "\n";
	
    exit;
}



?>