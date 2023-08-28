<?php

namespace Vendor\YbcFramework\Interfaces;

interface MiddlewareInterface
{
	public function handle_before($request, $next);
	public function handle_after($request, $next);
}
