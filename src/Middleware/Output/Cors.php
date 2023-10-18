<?php

namespace ybc\octavia\Middleware\Output;

use ybc\octavia\Interfaces\OutputMiddlewareInterface;
use ybc\octavia\Response;

class Cors implements OutputMiddlewareInterface
{
	private $origins = [];

	public function __construct($origins = [])
	{
		$this->origins = $origins;
	}

	public function handle(Response $response)
	{
		$response->headers['Access-Control-Allow-Origin'] = implode(', ', $this->origins);
		$response->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS';
		$response->headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization, X-Requested-With';
		$response->headers['Access-Control-Allow-Credentials'] = 'true';
		$response->headers['Access-Control-Max-Age'] = '86400';

		return $response;
	}
}
