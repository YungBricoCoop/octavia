<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;


$handler = new RequestHandler();
$handler->set_prefix("/api/v1");

// Require the other routes, keep this after the handler initialization and prefix setting
require "./auth/auth.php";

$handler->get("/", function ($q, $b) {
	return "Hello World";
});

/**
 * Now we can access the following routes:
 * GET /api/v1
 * GET /api/v1/auth/login
 * GET /api/v1/auth/register
 */


$handler->handle_request();
