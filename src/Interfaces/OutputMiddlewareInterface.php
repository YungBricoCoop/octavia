<?php

namespace ybc\octavia\Interfaces;

use ybc\octavia\Response;

interface OutputMiddlewareInterface
{
	/**
	 * Handle the request after the route has been matched
	 * @param Response $request
	 * @return Response
	 */
	public function handle(Response $request);
}
