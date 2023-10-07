<?php

namespace ybc\octavia\Router;

use ybc\octavia\Router\RouteTypes\RouteType;
use ybc\octavia\Utils\Utils;

class Router
{
	/** @var Route[] */
	private $routes = [];
	private $prefix = "";

	/**
	 * Get the route that matches the given path
	 * Routes defined order is important
	 * @param string $http_method The http method
	 * @param string $route_path The path of the route
	 * @return Route|null
	 */
	public function route(string $http_method, string $route_path)
	{
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
			if ($route->type::$http_method != $http_method) continue;
			$route->dynamic_segments_values = $route_dynamic_segments_values;
			return $route;
		}

		// if no route matches, return null
		return null;
	}

	/**
	 * Register a new route
	 * @param string $prefix The prefix of the route
	 * @param string $name The name of the route
	 * @param RouteType $type The type of the route
	 * @param string $path The path of the route
	 * @param bool $is_upload If the route is an upload route
	 * @param bool $is_health If the route is a health check route
	 * @param callable $callback The callback of the route
	 * @throws \InvalidArgumentException
	 * @example $router->register("", "home", "GET", "/", false, function() { echo "Home page"; });
	 * @return Route
	 */
	public function register(string $prefix, string $name, RouteType $type, string $path, bool $is_upload, bool $is_health, callable $callback)
	{
		// add the prefix to the path if it exists
		if ($prefix) {
			$path = $prefix . $path;
		}
		$path = $this->prefix . $path;

		$path_segments = Utils::get_route_path_segments($path);
		$dynamic_path_segments_types = Utils::get_route_dynamic_path_segments_types($path_segments);
		$route = new Route($name, $type, $path, $path_segments, $dynamic_path_segments_types, $is_upload, $is_health, $callback);
		$key = $type::$http_method . $path;
		if (array_key_exists($key, $this->routes)) {
			throw new \InvalidArgumentException("ENDPOINT_ALREADY_REGISTERED");
		}
		$this->routes[$key] = $route;
		return $route;
	}

	/**
	 * Set the prefix for all routes
	 * @param string $prefix
	 */
	public function set_prefix(string $prefix)
	{
		$this->prefix = $prefix;
	}
}
