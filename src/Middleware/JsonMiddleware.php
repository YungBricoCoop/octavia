<?php

namespace ybc\octavia\Middleware;

use ybc\octavia\Interfaces\MiddlewareInterface;
use ybc\octavia\Request;
use ybc\octavia\Response;

class JsonMiddleware implements MiddlewareInterface
{
	public function handle_before(Request $request)
	{
		$request->body = json_decode(file_get_contents('php://input'), true);
		return $request;
	}

	public function handle_after(Response $response)
	{
		$response->data = json_encode(["data" => $response->data]);
		$response->headers['Content-Type'] =  'application/json';

		return $response;
	}
}
