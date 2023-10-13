<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Utils\GoogleOAuthHandler;

/**
 * ROUTES:
 * GET /api/oauth/google
 */

$handler = new RequestHandler();
$handler->set_prefix("/api/oauth/");


$handler->google_oauth("/google", function ($status, $q, $b, $s) {
	$google_oauth_handler = new GoogleOAuthHandler();

	// status of the authentification process
	// true: success
	// false: failure
	echo "status: " . $status . "<br>";

	// get the data
	$google_oauth_data = $google_oauth_handler->get_data();

	echo "data: <br>";
	var_dump($google_oauth_data);
});

$handler->handle_request();
