<?php

namespace Vendor\YbcFramework\Middleware;

use Vendor\YbcFramework\Interfaces\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
	private $origins = [];

	public function __construct($origins = [])
	{
		$this->origins = $origins;
	}

	public function handle_before($request, $next)
	{
		return $next($request);
	}

	public function handle_after($response, $next)
	{
		$response->header('Access-Control-Allow-Origin', implode(', ', $this->origins));
		$response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
		$response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
		$response->header('Access-Control-Allow-Credentials', 'true');
		$response->header('Access-Control-Max-Age', '86400');

		return $next($response);
	}
}
