<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;

class Health extends RouteType
{
	public static $http_method = "GET";
	public function handle(Route $route)
	{
	}
}
