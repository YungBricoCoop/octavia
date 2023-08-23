<?php

namespace Vendor\YbcFramework;

use MissingQueryParameterException;

class Query
{
	private $data = [];
	private $required_params = [];

	public function __construct($data, $required_params)
	{
		$this->data = $data;
		$this->required_params = $required_params;
	}

	/**
	 * Get a query param
	 * If the param is required and not set, the script will send a response with the code 400
	 */
	public function __get($key)
	{
		if (!array_key_exists($key, $this->data) && in_array($key, $this->required_params)) {
			throw new MissingQueryParameterException($key);
		}
		return $this->data[$key] ?? null;
	}
}
