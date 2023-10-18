<?php

namespace ybc\octavia\Interfaces;

use ybc\octavia\Request;

interface InputMiddlewareInterface
{
	/**
	 * Handle the request before the router tries to match it to a route
	 * @param Request $request
	 * @return Request
	 */
	public function handle(Request $request);
}
