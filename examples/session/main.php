<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;

/**
 * ROUTES:
 * GET /api/v1
 * GET /api/v1/login/{user}
 * GET /api/v1/logout
 */

$handler = new RequestHandler();
$handler->prefix("/api/v1");

$group = $handler->group();

$group->get("/", function ($q, $b, $s) {
	$user = $s->get("user") ?? "Guest";
	return "Hello " . $user;
});

$group->get("/login/{user}", function ($user, $q, $b, $s) {
	$s->set("user", $user);
});

$group->get("/logout", function ($q, $b, $s) {
	$s->destroy();
	// or
	// $s->remove("user");
});

$handler->handle_request();
