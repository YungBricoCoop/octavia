<?php

require '../../vendor/autoload.php';

use ybc\octavia\RequestHandler;

/**
 * ROUTES:
 * POST /api/v1/{user}/profile-picture
 * POST /api/v1/{user}/cv
 * POST /api/v1/{user}/certificates
 */

$handler = new RequestHandler();
$handler->set_prefix("/api/v1");

$handler->upload("/{user}/profile-picture", function ($user, $q, $b, $s, $files) {
	return $files;
}, false, ["png", "jpg"], "2MB");

$handler->upload("/{user}/cv", function ($user, $q, $b, $s, $files) {
	return $files;
}, false, ["pdf"], "200KB");

$handler->upload("/{user}/certificates", function ($user, $q, $b, $s, $files) {
	return $files;
}, true, ["pdf"], "400KB");

$handler->handle_request();
