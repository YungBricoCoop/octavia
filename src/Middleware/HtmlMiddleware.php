<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Interfaces\MiddlewareInterface;
use ybc\octavia\Request;
use ybc\octavia\Response;

class HtmlMiddleware implements MiddlewareInterface
{
	public function handle_before(Request $request)
	{
		return $request;
	}

	public function handle_after(Response $response)
	{
		$response->data = $response->data;
		$response->headers['Content-Type'] = 'text/html';

		return $response;
	}
}
