<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Interfaces\OutputMiddlewareInterface;
use ybc\octavia\Response;

class JsonEncode implements OutputMiddlewareInterface
{
	public function handle(Response $response)
	{
		$response->data = json_encode(["data" => $response->data]);
		$response->headers['Content-Type'] =  'application/json';

		return $response;
	}
}
