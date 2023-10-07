<?php

namespace ybc\octavia\Methods;

use ybc\octavia\Router\Route;

class Upload extends Method
{
	public static $method = "POST";
	public function handle(Route $route)
	{
		$route->upload->upload();
	}
}
