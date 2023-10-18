<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Context, Middleware};

class HtmlEncode extends Middleware
{
	public $stage = MiddlewareStages::BEFORE_OUTPUT;
	public function handle(Context $ctx): Context
	{
		$ctx->response->data = $ctx->response->data;
		$ctx->response->headers['Content-Type'] = 'text/html';

		return $ctx;
	}
}
