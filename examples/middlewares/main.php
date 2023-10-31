
<?php

require '../../vendor/autoload.php';
require './middlewares/ModifyMethod.php';
require './middlewares/DataPrefix.php';

use ybc\octavia\RequestHandler;
use ybc\octavia\Middleware\Output\HtmlEncode;
use ybc\octavia\Middleware\Output\JsonEncode;
use ybc\octavia\Utils\Templater;

/**
 * ROUTES:
 * GET  /api/v1/hello/{name}
 * POST /api/v1/hello/{name}
 * ...  /api/v1/hello/{name}
 * GET  /api/v1/hello/{name}/json
 * ...  /api/v1/hello/{name}/json
 */

$templater = new Templater("templates");
$handler = new RequestHandler();

$handler->prefix("/api/v1");

// add "ModifyMethod" middleware, this will allow you to redirect all methods to GET
$handler->add(new ModifyMethod("GET"));

$group = $handler->group();

// remove the default "JsonEncode" middleware
// add "DataPrefix" middleware, this will prefix the name with some text and add "HtmlEncode" middleware
// this will be applied to the whole group
$group->no(JsonEncode::class)->add([new DataPrefix(), new HtmlEncode()]);

$group->get("/hello/{name}", function ($name, $q, $b, $s) use ($templater) {
	return $templater->render_from_file("index.html", ["name" => $name]);
});

$group->get("/hello/{name}/json", function ($name, $q, $b, $s) {
	return $name;

	// this will send the response as JSON and only be applied to this route
})->no(HtmlEncode::class)->add(new JsonEncode());

$handler->handle_request();
