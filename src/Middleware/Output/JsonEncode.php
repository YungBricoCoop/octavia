<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Context, Middleware};

class JsonEncode extends Middleware
{
	public $stage = MiddlewareStages::BEFORE_OUTPUT;
	public function handle(Context $ctx): Context
	{
		$ctx->response->data = json_encode(["data" => $ctx->response->data]);
		$ctx->response->headers['Content-Type'] =  'application/json';

		return $ctx;
	}
}
