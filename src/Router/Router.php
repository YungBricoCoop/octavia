<?php

namespace ybc\octavia\Router;

use ybc\octavia\Utils\Utils;

class Router
{
	/** @var RouteGroup[] */
	private $route_groups;
	private $prefix;

	public function __construct()
	{
		$this->route_groups = [];
		$this->prefix = "";
	}

	/**
	 * Get the route and its group that matches the given path
	 * Routes defined order is important
	 * @param string $http_method The http method
	 * @param string $route_path The path of the route
	 * @return array|null An array containing the Group and Route if found, otherwise null
	 */
	public function route(string $http_method, string $route_path)
	{
		// if the route doesn't start with the prefix, return null
		if (substr($route_path, 0, strlen($this->prefix)) != $this->prefix) return null;

		$route_path = substr($route_path, strlen($this->prefix));

		$best_match_length = -1;
		$best_match_routes = [];
		$best_match_group = null;

		// find the route group with the longest matching prefix
		foreach ($this->route_groups as $route_group) {
			$prefix_length = strlen($route_group->prefix);
			if (substr($route_path, 0, $prefix_length) == $route_group->prefix && $prefix_length > $best_match_length) {
				$best_match_routes = $route_group->routes;
				$best_match_group = $route_group;
				$best_match_length = $prefix_length;
			}
		}

		$sub_route_path = substr($route_path, $best_match_length);
		$segments = Utils::get_route_path_segments($sub_route_path);
		$num_segments = count($segments);

		foreach ($best_match_routes as $route) {
			if ($num_segments != count($route->path_segments)) {
				continue;
			}

			$route_segments = $route->path_segments;
			$route_dynamic_segments_values = [];
			$route_matched = true;

			foreach ($segments as $index => $segment) {
				if (substr($route_segments[$index], 0, 1) == "{") {
					$route_dynamic_segments_values[] = $segment;
					continue;
				}

				if ($segment != $route_segments[$index]) {
					$route_matched = false;
					break;
				}
			}

			// if the route doesn't match or the http method doesn't match, continue
			if (!$route_matched) continue;
			if ($route->type::$http_method != $http_method) continue;

			$route->dynamic_segments_values = $route_dynamic_segments_values;
			return ['group' => $best_match_group, 'route' => $route];
		}

		// if no route matches, return null
		return null;
	}

	/**
	 * Register a new route group
	 * @param string $prefix The prefix of the group
	 * @return RouteGroup
	 */
	public function group(string $prefix)
	{
		if (substr($prefix, -1) == "/") {
			$prefix = substr($prefix, 0, -1);
		}

		if (array_key_exists($prefix, $this->route_groups)) {
			throw new \InvalidArgumentException("GROUP_ALREADY_REGISTERED");
		}

		$route_group = new RouteGroup($prefix);
		$this->route_groups[$this->prefix . $prefix] = $route_group;
		return $route_group;
	}

	/**
	 * Set the prefix for all routes
	 * @param string $prefix
	 */
	public function set_prefix(string $prefix)
	{
		if (substr($prefix, -1) == "/") {
			$prefix = substr($prefix, 0, -1);
		}
		$this->prefix = $prefix;
	}
}
