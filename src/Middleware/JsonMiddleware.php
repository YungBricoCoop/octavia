<?php

namespace Vendor\YbcFramework\Middleware;

use Vendor\YbcFramework\Interfaces\MiddlewareInterface;
use Vendor\YbcFramework\Request;
use Vendor\YbcFramework\Response;

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
