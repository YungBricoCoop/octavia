<?php

require 'vendor/autoload.php';

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

	// get the tokens
	$tokens = $google_oauth_handler->get_tokens($q->code);

	echo "tokens: <br>";
	var_dump($tokens);
});

$handler->handle_request();
