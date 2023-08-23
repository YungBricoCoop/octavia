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

	public function validate()
	{
		if (!$this->required_body || empty($this->required_body)) return true;

		$this->validateProperty($this->data, $this->required_body);
	}

	private function validateProperty($data, $required)
	{
		// If the value is a string and represents a class
		if (is_string($required) && class_exists($required)) {
			$result = Utils::validate($data, $required);
			if ($result["is_missing"]) {
				throw new MissingObjectPropertyException($result["property"]);
			}
			if ($result["is_wrong_type"]) {
				throw new WrongObjectPropertyTypeException($result["property"] . ", expected {" . $result["expected_type"] . "} but got {" . $result["actual_type"] . "}");
			}
		}

		foreach ($required as $key => $value) {

			// If the key does not exist in the data array, throw an exception
			if (!array_key_exists($key, $data)) {
				throw new MissingBodyParameterException($key);
			}

			// Recursively validate if the required value is an array
			if (is_array($value)) {
				$this->validateProperty($data[$key], $value);
			}
		}
	}

	/**
	 * Get a body param
	 * @param string $key The key of the param
	 * @return mixed The value of the param, or null if it does not exist
	 */
	public function __get($key)
	{
		return $this->data[$key] ?? null;
	}

	public function get_data()
	{
		return $this->data;
	}
}
