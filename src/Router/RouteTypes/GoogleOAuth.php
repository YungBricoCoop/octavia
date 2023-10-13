<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;
use ybc\octavia\Utils\GoogleOAuthHandler;

class GoogleOAuth extends RouteType
{
	public static $http_method = "GET";

	public function handle(Route $route): bool
	{
		$auth_handler = new GoogleOAuthHandler();
		return $auth_handler->handle_initial_prompt($route->path);
	}
}
