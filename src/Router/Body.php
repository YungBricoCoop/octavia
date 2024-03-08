<?php

namespace ybc\octavia\Router;

use ybc\octavia\Utils\Utils;
use ybc\octavia\{MissingBodyParameterException, MissingObjectPropertyException, WrongObjectPropertyTypeException};

class Body
{
	private mixed $data;
	private mixed $required_body;

	public function __construct(mixed $data = [], mixed $required_body = [])
	{
		$this->data = $data;
		$this->required_body = $required_body;
	}

	/**
	 * Validate all the body params
	 * @throws MissingBodyParameterException
	 * @throws MissingObjectPropertyException
	 * @throws WrongObjectPropertyTypeException
	 * @return bool
	 */
	public function validate()
	{
		if (!$this->required_body || empty($this->required_body)) return true;

		$this->validate_proprety($this->data, $this->required_body);
	}

	private function validate_proprety(mixed $data, mixed $required)
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

			return true;
		}


		//FIXME: This might not work with mixed arrays, or with simple arrays
		foreach ($required as $key => $value) {
			// Check if a class is passed as a string
			if (is_string($value) && class_exists($value)) {
				$this->validate_proprety($data[$key], $value);
			}

			// If the key is an integer, it means that the value is a simple array
			if (!is_int($key)) continue;

			if (!array_key_exists($value, $data)) {
				throw new MissingBodyParameterException($value);
			}
		}
	}

	/**
	 * Get a body param
	 * @param string $key The key of the param
	 * @return mixed The value of the param, or null if it does not exist
	 */
	public function __get(string $key)
	{
		return $this->data[$key] ?? null;
	}

	/**
	 * Set the input body data
	 * @param mixed $data
	 * @return void
	 */
	public function set_data(mixed $data)
	{
		$this->data = $data;
	}

	/**
	 * Set the required body
	 * @param mixed $required_body
	 * @return void
	 */
	public function set_required_body(mixed $required_body)
	{
		$this->required_body = $required_body;
	}
}
