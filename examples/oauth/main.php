<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Utils\GoogleOAuthHandler;

/**
 * ROUTES:
 * GET /api/oauth/google
 */

$handler = new RequestHandler();
$group = $handler->group("/api/oauth");

$group->google_oauth("/google", function ($status, $q, $b, $s) {
	$google_oauth_handler = new GoogleOAuthHandler();

	$data = $google_oauth_handler->get_data();

	return [
		"data" => $data
	];
});

$handler->handle_request();
