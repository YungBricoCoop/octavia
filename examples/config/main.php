<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;

/**
 * ROUTES:
 * GET /api/v1
 */

$handler = new RequestHandler(); // load config from .env file
// $handler = new RequestHandler(".env"); // load config from custom .env file
// $handler = new RequestHandler(["LOG_FILE" => "mylog.log"]);  // load config from array

$handler->set_prefix("/api/v1");

$handler->get("/", function ($q, $b, $s) {
	return "Hey !";
});

$handler->handle_request();
