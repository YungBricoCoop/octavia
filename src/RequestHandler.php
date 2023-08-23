<?php

namespace Vendor\YbcFramework;

use Vendor\YbcFramework\Enums\HTTPMethods;
use Vendor\YbcFramework\Utils;
/**
 * @method Endpoint get(string $route, callable $callback = null)
 * @method Endpoint post(string $route, callable $callback = null)
 * @method Endpoint put(string $route, callable $callback = null)
 * @method Endpoint delete(string $route, callable $callback = null)
 * @method Endpoint patch(string $route, callable $callback = null)
 */
class RequestHandler
{
	public $endpoints = [];
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
		$endpoint_name = $arguments[0];
		$endpoint_name = Utils::endpoint_to_function_name($endpoint_name);
		$key = $httpMethod . "_" . $endpoint_name;

		$this->endpoints[$key] = [
			"method" => $httpMethod,
			"func" => $func,
			"requires_login" => false,
			"requires_admin" => false,
			"required_params" => [],
			"required_body" => []
		];

		return new Endpoint($this, $key);
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
		$endpoint_name = Utils::endpoint_to_function_name($endpoint_name);
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
