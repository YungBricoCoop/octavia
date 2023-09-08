<?php

namespace ybc\octavia\Router;

use ybc\octavia\Utils\Utils;

class Router
{
	/** @var Route[] */
	private $routes = [];

	/**
	 * Get the route that matches the given path
	 * Routes defined order is important
	 * @param string $http_method The http method
	 * @param string $route_path The path of the route
	 * @return Route|null
	 */
	public function route($http_method, $route_path)
	{
		/* INFO: This code is in comments because I think it could break the order priority of the routes
		// if the route does not use dynamic variables, we can directly find it
		if (array_key_exists($key, $this->routes)) {
			return $this->routes[$key];
		} */

		// match routes by number of segments
		$segments = Utils::get_route_path_segments($route_path);
		/** * @var Route[] */
		$matched = [];
		foreach ($this->routes as $route) {
			if (count($segments) == count($route->path_segments)) {
				$matched[] = $route;
			}
		}

		// if no route matches, return null
		if (empty($matched)) return null;

		// match the route based on the order of the segements and their name
		foreach ($matched as $route) {
			$route_segments = $route->path_segments;
			$route_dynamic_segments_values = [];
			$route_matched = true;
			foreach ($segments as $index => $segment) {
				// if the segment is a variable, it matches
				if (substr($route_segments[$index], 0, 1) == "{") {
					$route_dynamic_segments_values[] = $segment;
					continue;
				}

				// if the segment is not a variable, it must match the segment
				if ($segment == $route_segments[$index]) continue;
				$route_matched = false;
				break;
			}
			if (!$route_matched) continue;
			if ($route->http_method != $http_method) continue;
			$route->dynamic_segments_values = $route_dynamic_segments_values;
			return $route;
		}

		// if no route matches, return null
		return null;
	}

	/**
	 * Register a new route
	 * @param Route $route
	 * @return Route
	 */
	public function register($name, $http_method, $path, $path_segments, $is_upload, $func)
	{
		$route = new Route($name, $http_method, $path, $path_segments, $is_upload, $func);
		$key = $http_method . $path;
		if (array_key_exists($key, $this->routes)) {
			throw new \InvalidArgumentException("ENDPOINT_ALREADY_REGISTERED");
		}
		//INFO: Might need to check segments without dynamic variables to avoid conflicts
		$this->routes[$key] = $route;
		return $route;
	}

	/**
	 * Get all the registered routes
	 * @return Route[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}
}
