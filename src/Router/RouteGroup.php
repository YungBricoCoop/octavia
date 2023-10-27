<?php

namespace ybc\octavia\Router;

use ybc\octavia\Interfaces\RouteInterface;
use ybc\octavia\Enums\MiddlewareScopes;
use ybc\octavia\Middleware\Middleware;
use ybc\octavia\Router\RouteTypes\RouteType;
use ybc\octavia\Utils\Utils;

class RouteGroup
{
	public string $prefix;
	//@var Route[] */
	public  $routes;
	/**@var Middleware[] */
	public $middlewares;
	/**@var Middleware[] */
	public $no_middlewares;


	public function __construct(string $prefix)
	{
		$this->prefix = $prefix;
		$this->routes = [];
		$this->middlewares = [];
		$this->no_middlewares = [];
	}

	/**
	 * Register one or multiple middlewares
	 * @param Middleware|Middleware[] $middleware
	 * @return RouteGroup
	 */
	public function add($middleware): RouteGroup
	{
		if (!is_array($middleware)) {
			$middleware->scope = MiddlewareScopes::GROUP;
			$this->middlewares[$middleware->stage->value][] = $middleware;
			return $this;
		}

		foreach ($middleware as $m) {
			$m->scope = MiddlewareScopes::GROUP;
			$this->middlewares[$m->stage->value][] = $m;
		}
		return $this;
	}

	/**
	 * Register one or multiple middleware class names that should not be applied to the route
	 * @param string|string[] $middleware Middleware class name(s)
	 * @return Route
	 */
	public function no($middleware): RouteGroup
	{
		if (!is_array($middleware)) {
			$this->no_middlewares[] = $middleware;
			return $this;
		}

		foreach ($middleware as $m) {
			$this->no_middlewares[] = $m;
		}
		return $this;
	}


	private function register_route(RouteType $type, string $path, callable $func): RouteInterface
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
	 * @return RouteInterface
	 */
	public function upload(string $path, callable $func, string $upload_dir = OCTAVIA_UPLOAD_DIR, bool $allow_multiple_files = OCTAVIA_UPLOAD_ALLOW_MULTIPLE_FILES, array $allowed_extensions = [], string $max_size = OCTAVIA_UPLOAD_MAX_SIZE): RouteInterface
	{
		return $this->register_route(new RouteTypes\Upload($upload_dir, $allow_multiple_files, $allowed_extensions, $max_size), $path, $func);
	}

	/**
	 * Handle health check
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @param bool $auth_required If the route requires authentication
	 * @example $router->health("/health", function($query, $body, $session) { return Health::HEALTHY; });
	 * @return RouteInterface
	 */
	public function health(string $path, callable $func, bool $auth_required = false): RouteInterface
	{
		return $this->register_route(new RouteTypes\Health($auth_required), $path, $func);
	}
	/**
	 * Handle google oauth
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @example $router->google_oauth("/google", function($status, $query, $body, $session) { $google_oauth_handler = new GoogleOAuthHandler(); });
	 * @return RouteInterface
	 */
	public function google_oauth(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\GoogleOAuth(), $path, $func);
	}

	/**
	 * Register a GET route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function get(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Get(), $path, $func);
	}

	/**
	 * Register a POST route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function post(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Post(), $path, $func);
	}

	/**
	 * Register a PUT route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function put(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Put(), $path, $func);
	}

	/**
	 * Register a DELETE route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function delete(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Delete(), $path, $func);
	}

	/**
	 * Register a PATCH route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function patch(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Patch(), $path, $func);
	}

	/**
	 * Register a OPTIONS route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function options(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Options(), $path, $func);
	}

	/**
	 * Register a HEAD route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return RouteInterface
	 */
	public function head(string $path, callable $func): RouteInterface
	{
		return $this->register_route(new RouteTypes\Head(), $path, $func);
	}
}
