<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Interfaces\OutputMiddlewareInterface;
use ybc\octavia\Response;

class HtmlEncode implements OutputMiddlewareInterface
{
	public function handle(Response $response)
	{
		$response->data = $response->data;
		$response->headers['Content-Type'] = 'text/html';

		return $response;
	}
}
