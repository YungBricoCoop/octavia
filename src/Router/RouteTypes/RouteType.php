<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;
use ybc\octavia\Utils\Session;

abstract class RouteType
{
	public static $http_method = "GET";
	public function handle(Route $route)
	{
	}

	public function get_callback_params(Route $route)
	{
	}
}
