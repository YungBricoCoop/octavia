<?php

namespace ybc\octavia;

require_once "Exceptions.php";

use CustomException, ForbiddenException, UnauthorizedException, MethodNotAllowedException, NotFoundException, ConflictException, InternalServerErrorException;
use Exception;
use ybc\octavia\Interfaces\RequestHandlerInterface;
use ybc\octavia\Enums\HTTPMethods;
use ybc\octavia\Router\Router;
use ybc\octavia\Router\Route;
use ybc\octavia\Middleware\MiddlewareHandler;
use ybc\octavia\Middleware\JsonMiddleware;
use ybc\octavia\Utils\Utils;
use ybc\octavia\Utils\Log;
use ybc\octavia\Utils\Session;

class RequestHandler implements RequestHandlerInterface
{

	private Router $router;
	private Session $session;
	private ?Response $response = null;
	private $base_path = "";

	public MiddlewareHandler $middleware_handler;
	public ?Log $logger = null;

	/**
	 * Create a new RequestHandler
	 * @param array $session Instance of Session
	 */
	public function __construct($session = null)
	{
		$this->router = new Router();
		$this->middleware_handler = new MiddlewareHandler();
		$this->middleware_handler->add(new JsonMiddleware());
		$this->logger = new Log("RequestHandlerLogger");
		$this->session = $session ?? new Session();
		$this->response = new Response();
		$this->base_path = Utils::get_path_from_backtrace(1);
	}

	/**
	 * Register a GET route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function get(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::GET->value, $path, $func);
	}

	/**
	 * Register a POST route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function post(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::POST->value, $path, $func);
	}

	/**
	 * Register a PUT route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function put(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::PUT->value, $path, $func);
	}

	/**
	 * Register a DELETE route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function delete(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::DELETE->value, $path, $func);
	}

	/**
	 * Register a PATCH route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function patch(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::PATCH->value, $path, $func);
	}

	/**
	 * Register a OPTIONS route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function options(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::OPTIONS->value, $path, $func);
	}

	/**
	 * Register a HEAD route
	 * @param string $path The path of the route
	 * @param callable $callback Callback function
	 * @return Route
	 */
	public function head(string $path, callable $func): Route
	{
		return $this->register_route(HTTPMethods::HEAD->value, $path, $func);
	}

	/**
	 * Handle file(s) upload
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @param bool $allow_multiple_files Allow multiple files to be uploaded
	 * @param array $allowed_extensions Allowed file extensions
	 * @param string $max_size Max file size
	 * @example $router->upload("/upload", function($query, $body, $session, $files) { echo "Upload page"; }, true, ["jpg", "png"], "10MB");
	 * @return Route
	 */
	public function upload(string $path, callable $func, bool $allow_multiple_files = true, array $allowed_extensions = [], string $max_size = null): Route
	{
		$http_method = HTTPMethods::POST->value;

		// register the route
		$name = Utils::get_route_name($path);
		$route = null;
		try {
			$prefix_path = Utils::get_path_from_backtrace(1);
			$prefix = Utils::extract_folder_diff($this->base_path, $prefix_path);
			$route = $this->router->register($prefix, $name, $http_method, $path, true, false, $func);
			$route->upload->set_params("upload", $allow_multiple_files, $allowed_extensions, $max_size);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->response->data = "INTERNAL_SERVER_ERROR";
		}

		return $route;
	}

	/**
	 * Handle health check
	 * @param string $path The path of the route
	 * @param callable $func Callback function
	 * @example $router->health("/health", function($query, $body, $session) { return Health::HEALTHY; });
	 * @return Route
	 */
	public function health(string $path, callable $func): Route
	{
		$http_method = HTTPMethods::GET->value;

		// register the route
		$name = Utils::get_route_name($path);
		$route = null;
		try {
			$prefix_path = Utils::get_path_from_backtrace(1);
			$prefix = Utils::extract_folder_diff($this->base_path, $prefix_path);
			$route = $this->router->register($prefix, $name, $http_method, $path, false, true, $func);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->response->data = "INTERNAL_SERVER_ERROR";
		}

		return $route;
	}

	/**
	 * Register a new route
	 * @param string $http_method The http method of the route
	 * @param string $path The path of the route
	 * @return Route
	 */
	private function register_route(string $http_method, string $path, callable $func): Route
	{
		// register the route
		$name = Utils::get_route_name($path);
		$route = null;
		try {
			$prefix_path = Utils::get_path_from_backtrace(2);
			$prefix = Utils::extract_folder_diff($this->base_path, $prefix_path);
			$route = $this->router->register($prefix, $name, $http_method, $path, false, false, $func);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$this->response->data = "INTERNAL_SERVER_ERROR";
		}

		return $route;
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
		if ($route->requires_login && !$this->session->is_logged()) {
			throw new UnauthorizedException();
		}

		if ($route->requires_admin && !$this->session->is_admin()) {
			throw new ForbiddenException();
		}

		// validate the query, body and files
		$route->validate();

		// build the function params
		$function_params = $route->dynamic_segments_values;
		$function_params[] = $route->query;
		$function_params[] = $route->body;
		$function_params[] = $this->session;
		if ($route->upload) $function_params[] = $route->upload->get_uploaded_files();


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
}
