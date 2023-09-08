<?php

namespace Ybc\Octavia\Middleware;

use Ybc\Octavia\Interfaces\MiddlewareInterface;
use Ybc\Octavia\Request;
use Ybc\Octavia\Response;

class CorsMiddleware implements MiddlewareInterface
{
	private $origins = [];

	public function __construct($origins = [])
	{
		$this->origins = $origins;
	}

	public function handle_before(Request $request)
	{
		return $request;
	}

	public function handle_after(Response $response)
	{
		$response->headers['Access-Control-Allow-Origin'] = implode(', ', $this->origins);
		$response->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, OPTIONS';
		$response->headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization, X-Requested-With';
		$response->headers['Access-Control-Allow-Credentials'] = 'true';
		$response->headers['Access-Control-Max-Age'] = '86400';

		return $response;
	}
}
