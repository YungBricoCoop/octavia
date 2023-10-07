<?php

namespace ybc\octavia\Methods;

use ybc\octavia\Router\Route;

#FIXME: The way the Methods are implemented and linked with the routes is weird
abstract class Method
{
	public static $method = "GET";
	public function handle(Route $route)
	{
	}
}
