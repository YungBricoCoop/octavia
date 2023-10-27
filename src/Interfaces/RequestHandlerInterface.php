<?php

namespace ybc\octavia\Interfaces;

use ybc\octavia\Middleware\Middleware;
use ybc\octavia\Router\RouteGroup;

interface RequestHandlerInterface
{
	/**
	 * Set the prefix of the whole app
	 * @param string $prefix The prefix of the group
	 * @return RequestHandlerInterface
	 */
	public function prefix(string $prefix): RequestHandlerInterface;

	/**
	 * Register one or multiple middlewares
	 * @param Middleware|Middleware[] $middleware
	 */
	public function add($middleware);

	/**
	 * Create a new group of routes
	 * @return RouteGroup
	 */
	public function group(): RouteGroup;

	/**
	 * Handle the incoming requests
	 */
	public function handle_request();
}
