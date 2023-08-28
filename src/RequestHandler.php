<?php

namespace Vendor\YbcFramework;

require_once "Exceptions.php";

use CustomException, ForbiddenException, UnauthorizedException, MethodNotAllowedException, NotFoundException, ConflictException, InternalServerErrorException;
use Exception;
use Vendor\YbcFramework\Interfaces\RouteInterface;
use Vendor\YbcFramework\Enums\HTTPMethods;
use Vendor\YbcFramework\Router\Router;
use Vendor\YbcFramework\Router\Route;
use Vendor\YbcFramework\Utils\Utils;
use Vendor\YbcFramework\Utils\Log;


/**
 * @method RouteInterface get(string $route, callable $callback = null)
 * @method RouteInterface post(string $route, callable $callback = null)
 * @method RouteInterface put(string $route, callable $callback = null)
 * @method RouteInterface delete(string $route, callable $callback = null)
 * @method RouteInterface patch(string $route, callable $callback = null)
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
		$this->router = new Router();
		$this->logger = new Log("RequestHandlerLogger");
		$this->user = $user;
		$this->cors_origin = $cors_origin;
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
		$path_segments = Utils::get_route_path_segments($path);

		$route = null;
		try {
			$route = $this->router->register($name, $http_method, $path, $path_segments, false, $func);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			Utils::response(null, "INTERNAL_SERVER_ERROR", 500);
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
		$path_segments = Utils::get_route_path_segments($path);

		$route = null;
		try {
			$route = $this->router->register($name, $http_method, $path, $path_segments, true, $func);
			$route->upload->set_params("upload", $allow_multiple_files, $allowed_extensions, $max_size);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			Utils::response(null, "INTERNAL_SERVER_ERROR", 500);
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
		$path = $_GET["route"] ?? "";
		$route = $this->router->route($method, $path);

		if (!$route) {
			throw new NotFoundException();
		}

		$this->logger->info("[$method] /$route->path ($ip)");

		// set the query, body and files
		$route->query->set_data($_GET);
		$route->body->set_data(json_decode(file_get_contents("php://input"), true));
		$route->upload->set_files($_FILES);
		if ($route->is_upload) $route->upload->upload();

		// check if the user is logged in and if the user is allowed to access the route
		if ($route->requires_login && !$this->user) {
			throw new UnauthorizedException();
		}

		if ($route->requires_admin && !$this->user["is_admin"]) {
			throw new ForbiddenException();
		}

		// validate the query, body and files
		$route->query->validate();
		$route->body->validate();
		if ($route->is_upload) $route->upload->validate();

		// call the route function
		//$route_function = isset($route->func) ? $route->func : $route->name;
		$route_function = $route->func;
		if ($route->upload) {
			$route_function($route->query, $route->body, $route->upload->get_uploaded_files(), $this->user);
			return;
		}
		$route_function($route->query, $route->body, $this->user);
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
