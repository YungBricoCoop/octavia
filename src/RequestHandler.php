<?php

namespace ybc\octavia;

require_once "Exceptions.php";

use CustomException, ForbiddenException, UnauthorizedException, MethodNotAllowedException, NotFoundException, ConflictException, InternalServerErrorException;
use Exception;
use ybc\octavia\Interfaces\RouteInterface;
use ybc\octavia\Enums\HTTPMethods;
use ybc\octavia\Router\Router;
use ybc\octavia\Router\Route;
use ybc\octavia\Middleware\MiddlewareHandler;
use ybc\octavia\Middleware\JsonMiddleware;
use ybc\octavia\Utils\Utils;
use ybc\octavia\Utils\Log;


/**
 * @method RouteInterface get(string $route, callable $callback = null)
 * @method RouteInterface post(string $route, callable $callback = null)
 * @method RouteInterface put(string $route, callable $callback = null)
 * @method RouteInterface delete(string $route, callable $callback = null)
 * @method RouteInterface patch(string $route, callable $callback = null)
 */
class RequestHandler
{

	private Router $router;
	public MiddlewareHandler $middleware_handler;
	private $user = [];
	private $response = null;
	private $base_path = "";
	public $logger = null;

	/**
	 * Create a new RequestHandler
	 * @param array $user The user that is currently logged in
	 */
	public function __construct($user = [])
	{
		$this->router = new Router();
		$this->middleware_handler = new MiddlewareHandler();
		$this->middleware_handler->add(new JsonMiddleware());
		$this->logger = new Log("RequestHandlerLogger");
		$this->user = $user;
		$this->response = new Response();
		$this->base_path = Utils::get_path_from_backtrace(1);
	}

	/**
	 * Register a new route
	 * @return Route
	 */
	public function __call($name, $arguments)
	{
		$http_methods = array_column(HTTPMethods::cases(), 'name');
		$http_method = strtoupper($name);
		// check if the method is allowed, by key
		if (!in_array($http_method, $http_methods)) {
			//TODO: Throw exception
			return;
		}

		$func = $arguments[1] ?? null;

		// register the route
		$name = Utils::get_route_name($arguments[0]);
		$path = $arguments[0];

		$route = null;
		try {
			$prefix_path = Utils::get_path_from_backtrace(1);
			$prefix = Utils::extract_folder_diff($this->base_path, $prefix_path);
			$route = $this->router->register($prefix, $name, $http_method, $path, false, $func);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->response->data = "INTERNAL_SERVER_ERROR";
		}

		return $route;
	}

	/**
	 * Handle file(s) upload
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @param bool $allow_multiple_files Allow multiple files to be uploaded
	 * @param array $allowed_extensions Allowed file extensions
	 * @param int $max_size Max file size in bytes
	 * @return Route
	 */
	public function upload($path, $func, $allow_multiple_files = true, $allowed_extensions = [], $max_size = 0)
	{
		$http_method = HTTPMethods::POST->value;

		// register the route
		$name = Utils::get_route_name($path);
		$route = null;
		try {
			$prefix_path = Utils::get_path_from_backtrace(1);
			$prefix = Utils::extract_folder_diff($this->base_path, $prefix_path);
			$route = $this->router->register($prefix, $name, $http_method, $path, true, $func);
			$route->upload->set_params("upload", $allow_multiple_files, $allowed_extensions, $max_size);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->response(null, "INTERNAL_SERVER_ERROR", 500);
		}

		return $route;
	}

	/**
	 * Handle the request
	 * Returns 404 if the route is not found
	 * Returns 401 if the route requires login and the user is not logged in
	 * Returns 403 if the user is not allowed to access the route
	 * Calls the route function if the route is found and the user is allowed to access it
	 */
	public function handle_request()
	{
		try {
			$this->handle_request_with_exception();
		} catch (CustomException $e) {
			if ($e->getDetail()) {
				$this->logger->error($e->getMessage() . "(" . $e->getDetail() . ")", $e->getTrace());
			}
			$this->send_response($e->getMessage(), $e->getCode());
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->send_response("INTERNAL_SERVER_ERROR", 500);
		}
	}

	private function handle_request_with_exception()
	{
		// get the request method and ip
		$ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";

		$request = new Request();

		// Process the request with the middlewares
		$request = $this->middleware_handler->handle_before($request);

		// route the request
		$path = $request->query_params["route"] ?? "";
		$route = $this->router->route($request->method, $path);

		if (!$route) {
			throw new NotFoundException();
		}

		$this->logger->info("[$request->method] $route->path ($ip)");

		// set the query, body and files
		$route->query->set_data($request->query_params);
		$route->body->set_data($request->body);
		$route->upload->set_files($request->files);
		if ($route->is_upload) $route->upload->upload();

		// check if the user is logged in and if the user is allowed to access the route
		if ($route->requires_login && !$this->user) {
			throw new UnauthorizedException();
		}

		if ($route->requires_admin && !$this->user["is_admin"]) {
			throw new ForbiddenException();
		}

		// validate the query, body and files
		$route->validate();

		// build the function params
		$function_params = $route->dynamic_segments_values;
		$function_params[] = $route->query;
		$function_params[] = $route->body;
		if ($route->upload) $function_params[] = $route->upload->get_uploaded_files();
		$function_params[] = $this->user;

		// call the route function
		$result = call_user_func_array($route->func, $function_params);

		if ($result instanceof Response) {
			$this->response = $result;
		} else {
			$this->response = new Response($result);
		}

		// process the response with the middlewares
		$this->response = $this->middleware_handler->handle_after($this->response);

		// send the response
		$this->response->send();
	}

	/**
	 * Send a response
	 * @param mixed $data The data to send
	 * @param string $error The error to send
	 * @param int $status_code The status code to send
	 * @return void
	 */
	public function send_response($data, $status_code = 200)
	{
		$this->response->data = $data;
		$this->response->status_code = $status_code;


		try {
			//INFO: Apply the middlewares to the response, this might cause problems if the middlewares create exceptions
			$this->response = $this->middleware_handler->handle_after($this->response);
			$this->response->send();
		} catch (\Exception $e) {
			// if the middlewares create exceptions, default the response to json with code 500
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->response->data = json_encode("INTERNAL_SERVER_ERROR");
			$this->response->status_code = 500;
			$this->response->send();
		}
	}

	/**
	 * Set the prefix for all routes
	 * @param string $prefix
	 */
	public function set_prefix($prefix)
	{
		$this->router->set_prefix($prefix);
	}

	public function set_user($user)
	{
		$this->user = $user;
	}

	public function get_user()
	{
		return $this->user;
	}
}
