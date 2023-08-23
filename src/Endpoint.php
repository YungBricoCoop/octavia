<?php

namespace Vendor\YbcFramework;

class Endpoint
{
	private $parent;
	private $key;

	public function __construct($parent, $key)
	{
		$this->parent = $parent;
		$this->key = $key;
	}

	/**
	 * Need to be logged in to access this endpoint
	 */
	public function login()
	{
		$this->parent->endpoints[$this->key]['requires_login'] = true;
		return $this;
	}

	/**
	 * Need to be logged and an admin to access this endpoint
	 */
	public function admin()
	{
		$this->parent->endpoints[$this->key]['requires_admin'] = true;
		return $this;
	}

	/**
	 * Set the required params
	 */
	public function query($params)
	{
		$this->parent->endpoints[$this->key]['required_params'] = $params;
		return $this;
	}

	/**
	 * Set the required  params
	 */
	public function q($params)
	{
		$this->parent->endpoints[$this->key]['required_params'] = $params;
		return $this;
	}

	/**
	 * Set the required body params
	 */
	public function body($params)
	{
		$this->parent->endpoints[$this->key]['required_body'] = $params;
		return $this;
	}

	/**
	 * Set the required body params
	 */
	public function b($params)
	{
		$this->parent->endpoints[$this->key]['required_body'] = $params;
		return $this;
	}

	/**
	 * Callback function
	 */
	public function f($func)
	{
		$this->parent->endpoints[$this->key]['func'] = $func;
		return $this;
	}
}
