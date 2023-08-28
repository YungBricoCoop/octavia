<?php

namespace Vendor\YbcFramework\Router;

use Vendor\YbcFramework\Interfaces\RouteInterface;

class Route implements RouteInterface
{
	public $name = null;
	public $http_method = null;
	public $path = null;
	public $path_segments = null;
	public $dynamic_segments_values = [];
	public $func = null;
	public ?Upload $upload = null;
	public ?Query $query = null;
	public ?Body $body = null;
	public $is_upload = false;
	public $requires_login = false;
	public $requires_admin = false;

	public function __construct($name, $http_method, $path, $path_segments, $is_upload, $func)
	{
		$this->name = $name;
		$this->http_method = $http_method;
		$this->path = $path;
		$this->path_segments = $path_segments;
		$this->is_upload = $is_upload;
		$this->func = $func;

		$this->query = new Query();
		$this->body = new Body();
		$this->upload = new Upload();
	}


	/**
	 * Need to be logged in to access this endpoint
	 */
	public function login()
	{
		$this->requires_login = true;
		return $this;
	}

	/**
	 * Need to be logged and an admin to access this endpoint
	 */
	public function admin()
	{
		$this->requires_admin = true;
		return $this;
	}

	/**
	 * Set the required params
	 * @param array|string $params
	 * Accepts an array of strings or a string (class)
	 *  @return Route
	 */
	public function query($params)
	{
		$this->query->set_required_params($params);
		return $this;
	}

	/**
	 * Set the required  params
	 *  @return Route
	 */
	public function q($params)
	{
		$this->query->set_required_params($params);
		return $this;
	}

	/**
	 * Set the required body params
	 * @param array|string $params
	 * Accepts an array of strings and assocative string => string arrays or a string (class)
	 * Ex : ["info", "user" => User::class] or directly User::class
	 * @return Route
	 */
	public function body($params)
	{
		$this->body->set_required_body($params);
		return $this;
	}

	/**
	 * Set the required body params
	 *  @return Route
	 */
	public function b($params)
	{
		$this->body->set_required_body($params);
		return $this;
	}

	/**
	 * Callback function
	 *  @return Route
	 */
	public function f($func)
	{
		$this->func = $func;
		return $this;
	}

	public function __toString()
	{
		return $this->http_method . "_" . $this->func;
	}
}
