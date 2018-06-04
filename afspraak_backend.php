<?php
error_reporting(E_ALL);

ini_set(‘display_errors’, TRUE);

ini_set(‘display_startup_errors’, TRUE);

phpinfo();

require_once '/home/borahv1q/etc/google-api-php-client/src/Google/autoload.php';

putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/borahv1q/public_html/service_account.json');

$client = new Google_Client();
$client->useApplicationDefaultCredentials();

$sqladmin = new Google_Service_SQLAdmin($client);
$response = $sqladmin->instances->listInstances('challenger')->getItems();
echo json_encode($response) . "\n";
?>