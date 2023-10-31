<?php

use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Middleware\{Middleware, Context};

class ModifyMethod extends Middleware
{
	// this middleware will be executed before routing
	public $stage = MiddlewareStages::BEFORE_ROUTING;
	private $method = "GET";

	public function __construct($method)
	{
		$this->method = $method;
	}

	public function handle(Context $ctx): Context
	{
		// this will just override the request method	
		$ctx->request->method = $this->method;
		return $ctx;
	}
}
