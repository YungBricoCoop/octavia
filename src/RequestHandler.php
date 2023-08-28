<?php

namespace Vendor\YbcFramework;

require_once "Exceptions.php";

use CustomException, ForbiddenException, UnauthorizedException, MethodNotAllowedException, NotFoundException, ConflictException, InternalServerErrorException;
use Exception;
use Vendor\YbcFramework\Enums\HTTPMethods;
use Vendor\YbcFramework\Router;
use Vendor\YbcFramework\Utils\Utils;
use Vendor\YbcFramework\Utils\Log;


/**
 * @method Endpoint get(string $route, callable $callback = null)
 * @method Endpoint post(string $route, callable $callback = null)
 * @method Endpoint put(string $route, callable $callback = null)
 * @method Endpoint delete(string $route, callable $callback = null)
 * @method Endpoint patch(string $route, callable $callback = null)
 */
class RequestHandler
{

	private $router = null;
	private $user = [];
	private $cors_origin = "";
	public $logger = null;

	/**
	 * Create a new RequestHandler
	 * @param array $user The user that is currently logged in
	 */
	public function __construct($user = [], $cors_origin = "")
	{
		$this->router = new Router\Router();
		$this->logger = new Log("RequestHandlerLogger");
		$this->user = $user;
		$this->cors_origin = $cors_origin;
	}

	/**
	 * Register a new endpoint
	 * @return Endpoint
	 */
	public function __call($name, $arguments)
	{
		$http_methods = array_column(HTTPMethods::cases(), 'name');
		$http_method = strtoupper($name);
		// check if the method is allowed, by key
		if (!in_array($http_method, $http_methods)) {
			return;
		}

		$func = $arguments[1] ?? null;

		// register the endpoint
		$name = Utils::get_endpoint_name($arguments[0]);
		$path = $arguments[0];
		$path_segments = Utils::get_endpoints_path_segments($path);

		$endpoint = new Router\Route($name, $http_method, $path, $path_segments, false, $func);
		try {
			$this->router->register($endpoint);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			Utils::response(null, "INTERNAL_SERVER_ERROR", 500);
		}

		return $endpoint;
	}

	/**
	 * Handle file(s) upload
	 * @param string $path The path of the endpoint
	 * @param callable $func Callback function
	 * @return Endpoint
	 */
	public function upload($path, $func, $allow_multiple_files = true, $allowed_extensions = [], $max_size = 0)
	{
		// register the endpoint
		$name = Utils::get_endpoint_name($path);
		$path_segments = Utils::get_endpoints_path_segments($path);

		$endpoint = new Router\Route($name, (string) HTTPMethods::POST->value, $path, $path_segments, true, $func);
		$endpoint->upload = new Router\Upload(null, "upload", $allow_multiple_files, $allowed_extensions, $max_size);
		try {
			$this->router->register($endpoint);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			Utils::response(null, "INTERNAL_SERVER_ERROR", 500);
		}

		return $endpoint;
	}

	/**
	 * Handle the request
	 * Returns 404 if the endpoint is not found
	 * Returns 401 if the endpoint requires login and the user is not logged in
	 * Returns 403 if the user is not allowed to access the endpoint
	 * Calls the endpoint function if the endpoint is found and the user is allowed to access it
	 */
	public function handle_request()
	{
		try {
			$this->handle_request_with_exception();
		} catch (CustomException $e) {
			if ($e->getDetail()) {
				$this->logger->error($e->getMessage() . "(" . $e->getDetail() . ")", $e->getTrace());
				Utils::response(null, $e->getMessage(), $e->getStatusCode());
			}

			$this->logger->error($e->getMessage(), $e->getTrace());
			Utils::response(null, $e->getMessage(), $e->getStatusCode());
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			Utils::response(null, "INTERNAL_SERVER_ERROR", 500);
		}
	}

	private function handle_request_with_exception()
	{
		// handle cors
		if ($this->cors_origin) $this->handle_cors();

		// get the request method and ip
		$method = $_SERVER["REQUEST_METHOD"] ?? "GET";
		$ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";

		// route the request
		$path = $_GET["endpoint"] ?? "";
		$endpoint = $this->router->route($method, $path);

		if (!$endpoint) {
			throw new NotFoundException();
		}

		$this->logger->info("[$method] /$endpoint->path ($ip)");

		// build the query and body objects
		$query = new Router\Query($_GET, $endpoint->required_query_params);
		$body = file_get_contents("php://input");
		$body = new Router\Body(json_decode($body, true), $endpoint->required_body_params);
		$upload = $endpoint->upload;
		if ($upload) {
			$upload->set_files($_FILES);
			$upload->upload();
		}

		// check if the user is logged in and if the user is allowed to access the endpoint
		if ($endpoint->requires_login && !$this->user) {
			throw new UnauthorizedException();
		}

		if ($endpoint->requires_admin && !$this->user["is_admin"]) {
			throw new ForbiddenException();
		}

		// validate the query and body
		$query->validate();
		$body->validate();
		if ($upload) $files = $upload->validate();

		// call the endpoint function
		//$endpoint_function = isset($endpoint->func) ? $endpoint->func : $endpoint->name;
		$endpoint_function = $endpoint->func;
		if ($endpoint->upload) {
			$endpoint_function($query, $body, $files, $this->user);
			return;
		}
		$endpoint_function($query, $body, $this->user);
	}

	public function handle_cors()
	{

		header("Access-Control-Allow-Origin: " . $this->cors_origin);
		header("Access-Control-Allow-Credentials: true");

		if ($_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
			return;
		}

		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
		Utils::response("OK");
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
