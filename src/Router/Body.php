<?php

namespace Ybc\Octavia\Router;

use Ybc\Octavia\Utils\Utils;
use MissingBodyParameterException, MissingObjectPropertyException, WrongObjectPropertyTypeException;

class Body
{
	private $data = [];
	private $required_body = [];

	public function __construct($data = [], $required_body = [])
	{
		$this->data = $data;
		$this->required_body = $required_body;
	}

	/**
	 * Validate the body params
	 * @return bool
	 */
	public function validate()
	{
		if (!$this->required_body || empty($this->required_body)) return true;

		$this->validate_proprety($this->data, $this->required_body);
	}

	private function validate_proprety($data, $required)
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
	public function __get($key)
	{
		return $this->data[$key] ?? null;
	}

	public function set_data($data)
	{
		$this->data = $data;
	}

	public function set_required_body($required_body)
	{
		$this->required_body = $required_body;
	}
}
