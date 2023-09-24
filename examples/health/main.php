<?php

require 'vendor/autoload.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Enums\Health;


/**
 * ROUTES:
 * GET /api/v1/health
 */

$handler = new RequestHandler();
$handler->set_prefix("/api/v1");


$handler->health("/health", function ($q, $b, $s) {
	$db = false;
	$redis = false;
	return [
		"db" => $db ? Health::HEALTHY : Health::CRITICAL,
		"redis" => $redis ? Health::HEALTHY : Health::CRITICAL
	];
});

$handler->handle_request();
