<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Enums\Health;


/**
 * ROUTES:
 * GET /api/v1/health
 */

$handler = new RequestHandler();
$group = $handler->group("/api/v1");


$group->health("/health", function ($q, $b, $s) {
	$db = false;
	$redis = false;
	return [
		"db" => $db ? Health::HEALTHY : Health::CRITICAL,
		"redis" => $redis ? Health::HEALTHY : Health::CRITICAL
	];
});

$handler->handle_request();
