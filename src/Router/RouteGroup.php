<?php

namespace ybc\octavia\Router;

use ybc\octavia\Middleware\Middleware;
use ybc\octavia\Router\RouteTypes\RouteType;
use ybc\octavia\Utils\Utils;

class RouteGroup
{
	public string $prefix;
	//@var Route[] */
	public  $routes;
	/**@var Middleware[] */
	private $middlewares;


	public function __construct(string $prefix)
	{
		$this->prefix = $prefix;
		$this->routes = [];
		$this->middlewares = [];
	}

	private function register_route(RouteType $type, string $path, callable $func): Route
	{
		$key = $type::$http_method . $path;

		if (array_key_exists($key, $this->routes)) {
			throw new \InvalidArgumentException("ENDPOINT_ALREADY_REGISTERED");
		}

		$name = Utils::get_route_name($path);
		$path_segments = Utils::get_route_path_segments($path);
		$dynamic_path_segments_types = Utils::get_route_dynamic_path_segments_types($path_segments);

		$route = new Route($name, $type, $path, $path_segments, $dynamic_path_segments_types, $func);

		$this->routes[$key] = $route;
		return $route;
	}


	/**
	 * Handle file(s) upload
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @param string $upload_dir The upload directory
	 * @param bool $allow_multiple_files Allow multiple files to be uploaded
	 * @param array $allowed_extensions Allowed file extensions
	 * @param string $max_size Max file size
	 * @example $router->upload("/upload", function($query, $body, $session, $files) { echo "Upload page"; }, true, ["jpg", "png"], "10MB");
	 * @return Route
	 */
	public function upload(string $path, callable $func, string $upload_dir = OCTAVIA_UPLOAD_DIR, bool $allow_multiple_files = OCTAVIA_UPLOAD_ALLOW_MULTIPLE_FILES, array $allowed_extensions = [], string $max_size = OCTAVIA_UPLOAD_MAX_SIZE): Route
	{
		return $this->register_route(new RouteTypes\Upload($upload_dir, $allow_multiple_files, $allowed_extensions, $max_size), $path, $func);
	} 

	/**
	 * Handle health check
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @param bool $auth_required If the route requires authentication
	 * @example $router->health("/health", function($query, $body, $session) { return Health::HEALTHY; });
	 * @return Route
	 */
	public function health(string $path, callable $func, bool $auth_required = false): Route
	{
		return $this->register_route(new RouteTypes\Health($auth_required), $path, $func);
	}
	/**
	 * Handle google oauth
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @example $router->google_oauth("/google", function($status, $query, $body, $session) { $google_oauth_handler = new GoogleOAuthHandler(); });
	 * @return Route
	 */
	public function google_oauth(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\GoogleOAuth(), $path, $func);
	} 

	/**
	 * Register a GET route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function get(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Get(), $path, $func);
	}

	/**
	 * Register a POST route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function post(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Post(), $path, $func);
	}

	/**
	 * Register a PUT route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function put(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Put(), $path, $func);
	}

	/**
	 * Register a DELETE route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function delete(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Delete(), $path, $func);
	}

	/**
	 * Register a PATCH route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function patch(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Patch(), $path, $func);
	}

	/**
	 * Register a OPTIONS route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function options(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Options(), $path, $func);
	}

	/**
	 * Register a HEAD route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function head(string $path, callable $func): Route
	{
		return $this->register_route(new RouteTypes\Head(), $path, $func);
	}
}
