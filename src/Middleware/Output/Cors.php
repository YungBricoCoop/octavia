<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Context, Middleware};

class Cors extends Middleware
{
	private $origins = [];
	public $stage = MiddlewareStages::BEFORE_OUTPUT;

	public function __construct($origins = [])
	{
		$this->origins = $origins;
	}

	public function handle(Context $ctx): Context
	{
		$ctx->response->headers['Access-Control-Allow-Origin'] = implode(', ', $this->origins);
		$ctx->response->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS';
		$ctx->response->headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization, X-Requested-With';
		$ctx->response->headers['Access-Control-Allow-Credentials'] = 'true';
		$ctx->response->headers['Access-Control-Max-Age'] = '86400';

		return $ctx;
	}
}
