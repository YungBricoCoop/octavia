<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Enums\Health;


/**
 * ROUTES:
 * GET /api/v1/health
 */

$handler = new RequestHandler();
$handler->prefix("/api/v1");

$group = $handler->group();


$group->health("/health", function ($q, $b, $s) {
	$db = false;
	$redis = false;
	return [
		"db" => $db ? Health::HEALTHY : Health::CRITICAL,
		"redis" => $redis ? Health::HEALTHY : Health::CRITICAL
	];
},false);

$handler->handle_request();
