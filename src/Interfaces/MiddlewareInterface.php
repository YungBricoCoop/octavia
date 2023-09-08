<?php

namespace Ybc\Octavia\Interfaces;

use Ybc\Octavia\Request;
use Ybc\Octavia\Response;

interface MiddlewareInterface
{
	/**
	 * Handle the request before the router tries to match it to a route
	 * @param Request $request
	 * @return Request
	 */
	public function handle_before(Request $request);

	/**
	 * Handle the request after the route has been matched
	 * @param Response $request
	 * @return Response
	 */
	public function handle_after(Response $request);
}
