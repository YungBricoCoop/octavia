<?php

namespace ybc\octavia;

require_once "Exceptions.php";

use ybc\octavia\Config\Config;
use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Interfaces\RequestHandlerInterface;
use ybc\octavia\Middleware\Context;
use ybc\octavia\Router\Router;
use ybc\octavia\Middleware\Middleware;
use ybc\octavia\Middleware\MiddlewareHandler;
use ybc\octavia\Middleware\Input\JsonDecode;
use ybc\octavia\Middleware\Output\{JsonEncode};
use ybc\octavia\Router\Route;
use ybc\octavia\Router\RouteGroup;
use ybc\octavia\Utils\Log;
use ybc\octavia\Utils\Session;

class RequestHandler implements RequestHandlerInterface
{

	private Router $router;
	private Session $session;
	private Response $response;
	private ?RouteGroup $current_group;
	private ?Route $current_route;

	public MiddlewareHandler $middleware_handler;

	/**
	 * Create a new RequestHandler
	 * @param array $config The config array
	 */
	public function __construct($config = [])
	{
		Config::load($config);
		$this->router = new Router();
		$this->middleware_handler = new MiddlewareHandler();
		$this->middleware_handler->add([
			new JsonDecode(),
			new JsonEncode(),
		]);
		$this->session = Session::get_instance();
		$this->response = new Response();
		$this->current_group = null;
		$this->current_route = null;
	}

	/**
	 * Create a new group of routes
	 * @return RouteGroup
	 */
	public function group(): RouteGroup
	{
		return $this->router->group("/");
	}

	/**
	 * Include a group of routes from a file
	 * @param string $path The path of the file
	 * @param string $prefix The prefix of the group
	 * @return RouteGroup
	 */
	public function include_group($path, $prefix = "")
	{
		$group = $this->router->group($prefix);

		$group_func = require_once($path);

		if (!is_callable($group_func)) {
			throw new \InvalidArgumentException("GROUP_FUNCTION_NOT_CALLABLE");
		}

		$group_func($group);

		return $group;
	}

	/**
	 * Add one or multiple middlewares
	 * @param Middleware|Middleware[] $middleware
	 * @return void
	 */
	public function add($middleware): RequestHandlerInterface
	{
		$this->middleware_handler->add($middleware);
		return $this;
	}

	/**
	 * Handle the request
	 * @return void, 404 if the route is not found, 401 if the user is not logged in, 403 if the user is not an admin, 500 if an error occurs
	 */
	public function handle_request()
	{
		try {
			$this->handle_request_with_exception();
		} catch (CustomException $e) {
			if ($e->getDetail()) {
				//TODO: Log the method and ip
				Log::error($e->getMessage() . "(" . $e->getDetail() . ")", $e->getTrace());
			} else {
				Log::error($e->getMessage(), $e->getTrace());
			}
			$context = new Context(new Request(), $this->current_route, $this->response);

			$this->send_response($e->getMessage(), $e->getCode(), $context);
		} catch (\Exception $e) {
			Log::error($e->getMessage(), $e->getTrace());
			$this->send_response($e->getMessage(), $e->getCode());
		}
	}


	private function handle_request_with_exception()
	{
		// get the request method and ip
		$ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";

		$request = new Request();

		// Process the request with the middlewares
		$context = new Context($request);
		$context = $this->middleware_handler->handle(MiddlewareStages::BEFORE_ROUTING, $context);

		// route the request
		$path = $context->request->query_params["route"] ?? "";
		$result = $this->router->route($context->request->method, $path);

		if (!$result) {
			throw new NotFoundException();
		}
		$group = $result["group"];
		$this->current_group = $group;
		$group_middlewares = $group->middlewares;
		$group_exclude_middlewares = $group->no_middlewares;

		$route = $result["route"];
		$this->current_route = $route;
		$route_middlewares = $route->middlewares;
		$route_exclude_middlewares = $route->no_middlewares;


		Log::info("[$request->method] $route->path ($ip)");

		// set the query, body and files
		$route->query->set_data($context->request->query_params);
		$route->body->set_data($context->request->body);
		$route->upload->set_files($context->request->files);

		$context->route = $route;
		$context = $this->middleware_handler->handle(MiddlewareStages::AFTER_ROUTING, $context, $group_middlewares, $group_exclude_middlewares, $route_middlewares, $route_exclude_middlewares);

		// check if the user is logged in and if the user is allowed to access the route
		if ($route->requires_login && !$this->session->is_logged()) {
			throw new UnauthorizedException();
		}

		if ($route->requires_admin && !$this->session->is_admin()) {
			throw new ForbiddenException();
		}

		// handle the route based on the type
		$handle_return = $route->handle();

		// validate the query, body and files
		$route->validate();

		// build the function params
		$callback_param = $route->get_callback_params($this->session);

		// merge the params with the handle return if not null
		if (!is_null($handle_return)) {
			$callback_param = array_merge([$handle_return], $callback_param);
		}

		// call the route function
		$result = call_user_func_array($route->func, $callback_param);

		if ($result instanceof Response) {
			$this->response = $result;
		} else {
			$this->response = new Response($result);
		}

		$context->response = $this->response;

		// process the response with the middlewares
		$context = $this->middleware_handler->handle(MiddlewareStages::BEFORE_OUTPUT, $context, $group_middlewares, $group_exclude_middlewares, $route_middlewares, $route_exclude_middlewares);

		// send the response
		$context->response->send();
	}

	/**
	 * Send a response
	 * @param mixed $data The data to send
	 * @param int $status_code The status code to send
	 * @return void
	 */
	public function send_response(mixed $data, int $status_code = 200, Context $context = null)
	{
		$this->response->data = $data;
		$this->response->status_code = $status_code;

		try {
			if ($context) {
				$context->response = $this->response;

				// Apply middleware only if there are specific middlewares for the route or group.
				if ($this->current_group && $this->current_route) {
					$group_middlewares = $this->current_group->middlewares;
					$group_exclude_middlewares = $this->current_group->no_middlewares;
					$route_middlewares = $this->current_route->middlewares;
					$route_exclude_middlewares = $this->current_route->no_middlewares;

					$context = $this->middleware_handler->handle(
						MiddlewareStages::BEFORE_OUTPUT,
						$context,
						$group_middlewares,
						$group_exclude_middlewares,
						$route_middlewares,
						$route_exclude_middlewares
					);
				}

				$this->response = $context->response;
			}
			$this->response->send();
		} catch (\Exception $e) {
			Log::error($e->getMessage(), $e->getTrace());
			$this->response->data = json_encode("INTERNAL_SERVER_ERROR");
			$this->response->status_code = 500;
			$this->response->send();
		}
	}

	/**
	 * Set the prefix for all routes
	 * @param string $prefix
	 */
	public function prefix(string $prefix): RequestHandlerInterface
	{
		$this->router->set_prefix($prefix);
		return $this;
	}
}
