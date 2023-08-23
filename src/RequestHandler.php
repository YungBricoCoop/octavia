<?php

namespace Vendor\YbcFramework;

use Vendor\YbcFramework\Enums\HTTPMethods;

/**
 * @method get(string $route, callable $callback = null)
 * @method post(string $route, callable $callback = null)
 * @method put(string $route, callable $callback = null)
 * @method delete(string $route, callable $callback = null)
 * @method patch(string $route, callable $callback = null)
 */
class RequestHandler
{
	private $endpoints = [];
	private $user = [];
	private $cors_origin = "";

	/**
	 * Create a new RequestHandler
	 * @param array $user The user that is currently logged in
	 */
	public function __construct($user = [], $cors_origin = "")
	{
		$this->user = $user;
		$this->cors_origin = $cors_origin;
	}

	public function __call($name, $arguments)
	{
		$httpMethods = array_column(HTTPMethods::cases(), 'name');
		$httpMethod = strtoupper($name);
		// check if the method is allowed, by key
		if (!in_array($httpMethod, $httpMethods)) {
			return;
		}

		$func = $arguments[1] ?? null;

		// register the endpoint
		$this->endpoints[$httpMethod . "_" . $arguments[0]] = [
			"method" => $httpMethod,
			"func" => $func,
			"requires_login" => false,
			"requires_admin" => false,
			"required_params" => [],
			"required_body" => []
		];
	}

	/**
	 * Register an endpoint
	 * 
	 * @param string $name The name of the endpoint
	 * @param bool $requires_login Whether the endpoint requires the user to be logged in
	 * @param array $allowed_groups An array of groups that are allowed to access the endpoint
	 */
	public function register_endpoint($name, $method, $requires_login = false, $requires_admin = false, $required_params = [], $required_body = [])
	{
		if (isset($this->endpoints[$method . "_" . $name])) {
			//send_response("ERROR", true, 500); //TODO: Implement logging
		}

		$this->endpoints[$method . "_" . $name] = [
			"method" => $method,
			"requires_login" => $requires_login,
			"requires_admin" => $requires_admin,
			"required_params" => $required_params,
			"required_body" => $required_body
		];
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

		// handle cors
		if ($this->cors_origin) $this->handle_cors();

		// get the ip address
		$ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";

		// get the endpoint name and method
		$endpoint_name = $_GET["endpoint"] ?? "";
		$method = $_SERVER["REQUEST_METHOD"] ?? "GET";
		//info("[$method] /$endpoint_name ($ip)"); //TODO: Implement logging
		$endpoint_name = $method . "_" . $endpoint_name;

		// check if the endpoint exists and if the method is allowed
		if (!isset($this->endpoints[$endpoint_name])) {
			//send_response("NOT_FOUND", true, 404); //TODO: Implement send_responsee
		}

		/* if ($method != $this->endpoints[$endpoint_name]["method"]) {
			send_response("METHOD_NOT_ALLOWED", true, 405);
		} */

		if (!isset($this->endpoints[$endpoint_name])) {
			//send_response("NOT_FOUND", true, 404); //TODO: Implement send_responsee
			return;
		}


		// get the endpoint
		$endpoint = $this->endpoints[$endpoint_name];

		// build the query and body objects
		$params = new Query($_GET, $endpoint["required_params"]);
		$body = file_get_contents("php://input");
		$body = new Body(json_decode($body, true), $endpoint["required_body"]);

		// check if the user is logged in and if the user is allowed to access the endpoint
		if ($endpoint["requires_login"] && !$this->user) {
			//error("UNAUTHORIZED"); //TODO: Implement logging
			//send_response("UNAUTHORIZED", true, 403); //TODO: Implement send_responsee
		}

		if ($endpoint["requires_admin"] && !$this->user["is_admin"]) {
			$ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
			//error("FORBIDDEN"); //TODO: Implement logging
			//send_response("FORBIDDEN", true, 403); //TODO: Implement send_responsee
		}

		// call the endpoint function
		// each endpoint function is named api_<endpoint_name> for avoiding name conflicts
		$endpoint_function = isset($endpoint["func"]) ? $endpoint["func"] : $endpoint_name;
		$endpoint_function($params, $body, $this->user);
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
		//send_response("OK"); //TODO: Implement send_responsee
	}

	public function get_endpoints()
	{
		return $this->endpoints;
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
