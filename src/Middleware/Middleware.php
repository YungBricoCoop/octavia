<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Enums\MiddlewareStages;

abstract class Middleware
{
    public $stage = MiddlewareStages::BEFORE_ROUTING;
	public $terminate_chain = false;
	abstract public function handle(Context $ctx) : Context;
}