<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
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
    exit;
}



?>