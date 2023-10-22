<?php

namespace ybc\octavia;

require_once "Exceptions.php";

use ybc\octavia\Config\Config;
use ybc\octavia\Enums\MiddlewareStages;
use ybc\octavia\Interfaces\RequestHandlerInterface;
use ybc\octavia\Middleware\Context;
use ybc\octavia\Router\Router;
use ybc\octavia\Router\Route;
use ybc\octavia\Middleware\MiddlewareHandler;
use ybc\octavia\Middleware\Input\JsonDecode;
use ybc\octavia\Middleware\Output\{JsonEncode};
use ybc\octavia\Router\RouteGroup;
use ybc\octavia\Utils\Utils;
use ybc\octavia\Utils\Log;
use ybc\octavia\Utils\Session;
use ybc\octavia\Router\RouteTypes\RouteType;

class RequestHandler implements RequestHandlerInterface
{

	private Router $router;
	private Session $session;
	private ?Response $response = null;

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
		$this->middleware_handler->add_many([
			new JsonDecode(),
			new JsonEncode(),
		]);
		$this->session = Session::get_instance();
		$this->response = new Response();
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
	 * Handle the request
	 * @return void, 404 if the route is not found, 401 if the user is not logged in, 403 if the user is not an admin, 500 if an error occurs
	 */
	public function handle_request()
	{
		try {
			$this->handle_request_with_exception();
		} catch (CustomException $e) {
			if ($e->getDetail()) {
				Log::error($e->getMessage() . "(" . $e->getDetail() . ")", $e->getTrace());
			}
			$this->send_response($e->getMessage(), $e->getCode());
		} catch (\Exception $e) {

			// get type of exception 
			$exception_type = get_class($e);

			// merge the exception type with the exception message
			$exception_message = $exception_type . ": " . $e->getMessage();

			Log::error($exception_message, $e->getTrace());
			$this->send_response("INTERNAL_SERVER_ERROR", 500);
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
		$route = $this->router->route($context->request->method, $path);

		if (!$route) {
			throw new NotFoundException();
		}

		Log::info("[$request->method] $route->path ($ip)");

		// set the query, body and files
		$route->query->set_data($context->request->query_params);
		$route->body->set_data($context->request->body);
		$route->upload->set_files($context->request->files);

		$context->route = $route;
		$context = $this->middleware_handler->handle(MiddlewareStages::AFTER_ROUTING, $context);

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
		$context = $this->middleware_handler->handle(MiddlewareStages::BEFORE_OUTPUT, $context);

		// send the response
		$context->response->send();
	}

	/**
	 * Send a response
	 * @param mixed $data The data to send
	 * @param int $status_code The status code to send
	 * @return void
	 */
	public function send_response(mixed $data, int $status_code = 200)
	{
		$this->response->data = $data;
		$this->response->status_code = $status_code;


		try {
			//INFO: Apply the middlewares to the response, this might cause problems if the middlewares create exceptions
			//TODO: Handle middlewares
			//$this->response = $this->middleware_handler->handle_after($this->response);
			$this->response->send();
		} catch (\Exception $e) {
			// if the middlewares create exceptions, default the response to json with code 500
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
	public function prefix(string $prefix)
	{
		$this->router->set_prefix($prefix);
	}
}
