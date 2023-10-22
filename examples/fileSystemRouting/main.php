<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;

/**
 * ROUTES:
 * GET /api/v1
 * GET /api/v1/auth/login
 * GET /api/v1/auth/register
 */
$handler = new RequestHandler();
$handler->prefix("/api/v1");

$group = $handler->group();

// Require the other routes, keep this after the handler initialization and prefix setting
//require "./auth/auth.php";


$handler->include_group("./auth/auth.php", "/auth");

$group->get("/", function ($q, $b) {
	return "Hello World";
});

$handler->handle_request();
