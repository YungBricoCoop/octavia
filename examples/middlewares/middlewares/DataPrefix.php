<?php

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Middleware, Context};

class DataPrefix extends Middleware
{
	// this middleware will be executed just after routing
	public $stage = MiddlewareStages::AFTER_ROUTING;
	public function handle(Context $ctx): Context
	{
		$ctx->route->dynamic_segments_values[0] = "Mr. or Mrs. " . $ctx->route->dynamic_segments_values[0];
		return $ctx;
	}
}
