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
	//FIXME: This works for top level properties, but not for nested properties, since the __get method is only called for top level properties
	// So validating nested properties will not work, a validation method should be added to this class and called when a request is made
	public function __get($key)
	{
		// If the required body is a class, validate the object based on the class
		if (is_string($this->required_body) && class_exists($this->required_body)) {
			$class_name = $this->required_body;
			$result = Utils::validate($this->data, $class_name);
			if ($result["is_missing"]) {
				throw new MissingObjectPropertyException($result["property"]);
			}
			if ($result["is_wrong_type"]) {
				throw new WrongObjectPropertyTypeException($result["property"] . ", expected {" . $result["expected_type"] . "} but got {" . $result["actual_type"] . "}");
			}
			return $this->data;
		}

		// If the key is not set and is required, throw an exception
		if (!array_key_exists($key, $this->data) && (in_array($key, $this->required_body) || array_key_exists($key, $this->required_body))) {
			throw new MissingBodyParameterException($key);
		}

		$required_param_value = $this->required_body[$key];
		$param_value = $this->data[$key] ?? null;

		// if required param is not a class, just return the value
		if (!class_exists($required_param_value)) return $param_value;

		// if required param is a class, validate the object based on the class
		$class_name = $required_param_value;
		$result = Utils::validate($param_value, $class_name);
		if ($result["is_missing"]) {
			throw new MissingObjectPropertyException($result["property"]);
		}
		if ($result["is_wrong_type"]) {
			throw new WrongObjectPropertyTypeException($result["property"] . ", expected {" . $result["expected_type"] . "} but got {" . $result["actual_type"] . "}");
		}

		return $param_value;
	}

	public function get_data()
	{
		return $this->data;
	}
}
