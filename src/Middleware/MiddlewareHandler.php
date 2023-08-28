<?php

namespace Vendor\YbcFramework\Middleware;

class MiddlewareHandler
{
	private $middlewares = [];

	public function __construct($middlewares = [])
	{
		$this->middlewares = $middlewares;
	}

	public function register($middleware)
	{
		$this->middlewares[] = $middleware;
	}

	public function handle_before($request, $next)
	{
		$middlewares = $this->middlewares;
		$middlewares = array_reverse($middlewares);

		$next = function ($request) use ($next) {
			return $next($request);
		};

		foreach ($middlewares as $middleware) {
			$next = function ($request) use ($middleware, $next) {
				return $middleware->handle_before($request, $next);
			};
		}

		return $next($request);
	}

	public function handle_after($response, $next)
	{
		$middlewares = $this->middlewares;

		$next = function ($response) use ($next) {
			return $next($response);
		};

		foreach ($middlewares as $middleware) {
			$next = function ($response) use ($middleware, $next) {
				return $middleware->handle_after($response, $next);
			};
		}

		return $next($response);
	}
}
