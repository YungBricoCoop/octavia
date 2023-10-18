<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Enums\MiddlewareStages;

abstract class Middleware
{
    public $stage = MiddlewareStages::BEFORE_ROUTING;
	abstract public function handle(Context $ctx) : Context;
}