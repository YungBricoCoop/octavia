<?php

namespace ybc\octavia\Middleware\Input;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Middleware, MiddlewareIdentifier, Context};

#[MiddlewareIdentifier("json_decode")]
class JsonDecode extends Middleware
{
	public $stage = MiddlewareStages::AFTER_ROUTING;
	public function handle(Context $ctx): Context
	{
		$ctx->request->body = json_decode(file_get_contents('php://input'), true);
		return $ctx;
	}
}
