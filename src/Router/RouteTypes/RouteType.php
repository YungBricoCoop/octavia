<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;

#FIXME: The way the Methods are implemented and linked with the routes is weird
abstract class RouteType
{
	public static $http_method = "GET";
	public function handle(Route $route)
	{
	}
}
