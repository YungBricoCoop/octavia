<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Middleware, MiddlewareIdentifier, Context};

#[MiddlewareIdentifier("html")]
class HtmlEncode extends Middleware
{
	public $stage = MiddlewareStages::BEFORE_OUTPUT;
	public $terminate_chain = true;
	public function handle(Context $ctx): Context
	{
		$ctx->response->data = $ctx->response->data;
		$ctx->response->headers['Content-Type'] = 'text/html';

		return $ctx;
	}
}
