<?php

namespace ybc\octavia\Router\RouteTypes;

use ybc\octavia\Router\Route;

class Upload extends RouteType
{
	public static $http_method = "POST";
	public function handle(Route $route)
	{
		$route->upload->upload();
	}
}
