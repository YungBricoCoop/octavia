<?php

namespace Vendor\YbcFramework;

use MissingBodyParameterException;

class Body
{
	private $data = [];
	private $required_body = [];

	public function __construct($data, $required_body)
	{
		$this->data = $data;
		$this->required_body = $required_body;
	}

	/**
	 * Get a body param
	 * If the param is required and not set, the script will send a response with the code 400
	 */
	public function __get($key)
	{

		if (!array_key_exists($key, $this->data) && in_array($key, $this->required_body)) {
			throw new MissingBodyParameterException($key);
		}
		return $this->data[$key] ?? null;
	}

	public function get_data()
	{
		return $this->data;
	}
}
