<?php

namespace Vendor\YbcFramework;

use MissingBodyParameterException, MissingObjectPropertyException, WrongObjectPropertyTypeException;

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
		//TODO: Also validate if the required param only contains the class name (not an array)
		if (!array_key_exists($key, $this->data) && (in_array($key, $this->required_body) || array_key_exists($key, $this->required_body))) {
			throw new MissingBodyParameterException($key);
		}

		$required_param_value = $this->required_body[$key];
		$param_value = $this->data[$key] ?? null;

		// check if required param is a class
		if (class_exists($required_param_value)) {
			$class_name = $required_param_value;
			$result = Utils::validate($param_value, $class_name);
			if ($result["is_missing"]) {
				throw new MissingObjectPropertyException($result["property"]);
			}
			if ($result["is_wrong_type"]) {
				throw new WrongObjectPropertyTypeException($result["property"] . ", expected {" . $result["expected_type"] . "} but got {" . $result["actual_type"] . "}");
			}
		}

		return $this->data[$key] ?? null;
	}

	public function get_data()
	{
		return $this->data;
	}
}
