<?php

namespace Vendor\YbcFramework;

class Endpoint
{
	public $name = null;
	public $http_method = null;
	public $path = null;
	public $path_segments = null;
	public $func = null;
	public $requires_login = false;
	public $requires_admin = false;
	public $required_query_params = [];
	public $required_body_params = [];

	public function __construct($name, $http_method, $path, $path_segments, $func)
	{
		$this->name = $name;
		$this->http_method = $http_method;
		$this->path = $path;
		$this->path_segments = $path_segments;
		$this->func = $func;
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
	 *  @return Endpoint
	 */
	public function query($params)
	{
		$this->required_query_params = $params;
		return $this;
	}

	/**
	 * Set the required  params
	 *  @return Endpoint
	 */
	public function q($params)
	{
		$this->required_query_params = $params;
		return $this;
	}

	/**
	 * Set the required body params
	 * @param array|string $params
	 * Accepts an array of strings and assocative string => string arrays or a string (class)
	 * Ex : ["info", "user" => User::class] or directly User::class
	 * @return Endpoint
	 */
	public function body($params)
	{
		$this->required_body_params = $params;
		return $this;
	}

	/**
	 * Set the required body params
	 *  @return Endpoint
	 */
	public function b($params)
	{
		$this->required_body_params = $params;
		return $this;
	}

	/**
	 * Callback function
	 *  @return Endpoint
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
